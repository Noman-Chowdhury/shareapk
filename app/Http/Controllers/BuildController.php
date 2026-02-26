<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use ApkParser\Parser as ApkParser;
use Illuminate\Support\Facades\Storage;
use App\Notifications\GeneralNotification;
use App\Models\ActivityLog;
use Illuminate\Support\Str;

class BuildController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Builds/Create');
    }

    /**
     * High-speed metadata extraction using direct aapt calls
     */
    private function extractMetadata($fullPath)
    {
        $aaptPath = '/usr/bin/aapt';
        $cmd = "$aaptPath dump badging " . escapeshellarg($fullPath) . " 2>&1 | grep -E 'package:|application-label|application-icon|application: label='";
        $output = shell_exec($cmd);

        if (!$output) {
            return ['success' => false, 'message' => 'Failed to analyze APK. Ensure aapt is installed.'];
        }

        $res = ['success' => true, 'package_name' => '', 'version_code' => '', 'version_name' => '', 'app_name' => '', 'icon_data' => '', 'icon_path' => ''];

        if (preg_match("/package: name='([^']+)' versionCode='([^']+)' versionName='([^']+)'/", $output, $matches)) {
            $res['package_name'] = $matches[1];
            $res['version_code'] = $matches[2];
            $res['version_name'] = $matches[3];
        }

        if (preg_match_all("/application-label(?:-[a-z-_]+)?:\s*['\"]([^'\"]+)['\"]/i", $output, $matches)) {
            foreach ($matches[0] as $i => $fullLine) {
                $candidate = $matches[1][$i];
                if (!str_contains($fullLine, '-') && !str_starts_with($candidate, '0x')) {
                    $res['app_name'] = $candidate;
                    break;
                }
            }
            // Fallback to any non-hex label
            if (!$res['app_name'] || str_starts_with($res['app_name'], '0x')) {
                foreach ($matches[1] as $candidate) {
                    if (!str_starts_with($candidate, '0x')) {
                        $res['app_name'] = $candidate;
                        break;
                    }
                }
            }
        }
        
        if ((!$res['app_name'] || str_starts_with($res['app_name'], '0x')) && preg_match("/application: label=['\"]([^'\"]+)['\"]/i", $output, $matches)) {
            if (!str_starts_with($matches[1], '0x')) $res['app_name'] = $matches[1];
        }

        $bestIcon = null; $maxSize = 0;
        if (preg_match_all("/application-icon-(\d+):\s*['\"]([^'\"]+)['\"]/i", $output, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $size = (int)$match[1]; $path = $match[2];
                // Prefer PNG over XML
                if ($size >= $maxSize || (str_ends_with(strtolower($bestIcon ?? ''), '.xml') && !str_ends_with(strtolower($path), '.xml'))) {
                    $maxSize = $size;
                    $bestIcon = $path;
                }
            }
        }
        if (!$bestIcon && preg_match("/icon=['\"]([^'\"]+)['\"]/i", $output, $matches)) {
            $bestIcon = $matches[1];
        }

        // Fix for Adaptive Icons (XML) - Find a PNG version in the APK (Skip library icons like Chucker)
        if ($bestIcon && str_ends_with(strtolower($bestIcon), '.xml')) {
            $pngSearch = shell_exec("/usr/bin/unzip -l " . escapeshellarg($fullPath) . " | grep -Ei 'ic_launcher.*\.png' | grep -iv 'chucker' | sort -rn | head -n 1 | awk '{print $4}'");
            if (trim($pngSearch)) $bestIcon = trim($pngSearch);
        }

        if ($bestIcon) {
            $res['icon_path'] = $bestIcon;
            $ext = pathinfo($bestIcon, PATHINFO_EXTENSION);
            $tempIcon = storage_path('app/public/temp/pre_' . time() . '.' . $ext);
            shell_exec("/usr/bin/unzip -p " . escapeshellarg($fullPath) . " " . escapeshellarg($bestIcon) . " > " . escapeshellarg($tempIcon));
            
            if (file_exists($tempIcon) && filesize($tempIcon) > 0) {
                $mime = (strtolower($ext) == 'webp') ? 'image/webp' : 'image/png';
                $res['icon_data'] = 'data:'.$mime.';base64,' . base64_encode(file_get_contents($tempIcon));
                @unlink($tempIcon);
            }
        }

        if (!$res['app_name'] && $res['package_name']) {
            $segments = explode('.', $res['package_name']);
            $res['app_name'] = ucwords(str_replace('_', ' ', end($segments)));
        }

        return $res;
    }

    /**
     * Pre-analyze APK and return metadata.
     */
    public function preAnalyze(Request $request)
    {
        $request->validate(['apk_file' => 'required|file|max:512000']);

        $file = $request->file('apk_file');
        $tempPath = $file->store('temp', 'public');
        $fullPath = storage_path('app/public/' . $tempPath);

        $data = $this->extractMetadata($fullPath);

        if (!$data['success']) {
            @unlink($fullPath);
            return response()->json(['success' => false, 'message' => $data['message']], 422);
        }

        return response()->json(array_merge($data, [
            'temp_path' => $tempPath,
            'file_size' => $file->getSize(),
        ]));
    }

    public function store(Request $request)
    {
        $finalPath = null;
        $fileSize = 0;

        if ($request->filled('temp_path')) {
            $tempPath = $request->input('temp_path');
            $fullTempPath = storage_path('app/public/' . $tempPath);
            
            if (!file_exists($fullTempPath)) {
                return back()->withErrors(['apk_file' => 'Session expired. Please select the file again.']);
            }

            $request->validate([
                'build_type' => 'required|in:Alpha,Beta,RC,Production',
                'release_notes' => 'nullable|string'
            ]);

            $newPath = 'builds/' . basename($tempPath);
            Storage::disk('public')->move($tempPath, $newPath);
            $finalPath = $newPath;
            $fileSize = filesize(storage_path('app/public/' . $finalPath));
        } else {
            if ($request->hasFile('apk_file') && !$request->file('apk_file')->isValid()) {
                return back()->withErrors(['apk_file' => 'Upload Error: ' . $request->file('apk_file')->getErrorMessage()]);
            }

            $request->validate([
                'apk_file' => 'required|file|max:512000',
                'build_type' => 'required|in:Alpha,Beta,RC,Production',
                'release_notes' => 'nullable|string'
            ]);

            $file = $request->file('apk_file');
            $finalPath = $file->store('builds', 'public');
            $fileSize = $file->getSize();
        }

        $fullPath = storage_path('app/public/' . $finalPath);

        try {
            $data = $this->extractMetadata($fullPath);
            if (!$data['success']) {
                throw new \Exception($data['message']);
            }

            // Final Metadata
            $packageName = $data['package_name'];
            $versionCode = $data['version_code'];
            $versionName = $data['version_name'];
            $appName = $data['app_name'];
            $iconInternalPath = $data['icon_path'];

            $project = Project::where('package_name', $packageName)->first();
            $iconUrl = $project ? $project->icon_url : null;

            if ($iconInternalPath) {
                $ext = pathinfo($iconInternalPath, PATHINFO_EXTENSION);
                $iconFilename = 'project_' . time() . '_' . Str::random(5) . '.' . $ext;
                $iconLocalPath = storage_path('app/public/icons/' . $iconFilename);
                
                if (!file_exists(storage_path('app/public/icons'))) {
                    mkdir(storage_path('app/public/icons'), 0755, true);
                }
                
                shell_exec("/usr/bin/unzip -p " . escapeshellarg($fullPath) . " " . escapeshellarg($iconInternalPath) . " > " . escapeshellarg($iconLocalPath));
                
                if (file_exists($iconLocalPath) && filesize($iconLocalPath) > 0) {
                    $iconUrl = 'icons/' . $iconFilename;
                }
            }

            if (!$project) {
                $project = Project::create([
                    'package_name' => $packageName,
                    'name' => $appName ?: $packageName,
                    'icon_url' => $iconUrl
                ]);
            } else {
                $updateData = [];
                if ($appName && ($project->name === $packageName || 
                                 $project->name === 'Admin' || 
                                 $project->name === 'admin' || 
                                 str_starts_with($project->name, '0x'))) {
                    $updateData['name'] = $appName;
                }
                if ($iconUrl) {
                    $updateData['icon_url'] = $iconUrl;
                }
                if (!empty($updateData)) {
                    $project->update($updateData);
                }
            }

            // Create Build Entry
            $build = Build::create([
                'project_id' => $project->id,
                'user_id' => $request->user()->id,
                'version_name' => $versionName,
                'version_code' => $versionCode,
                'build_type' => $request->input('build_type', 'Beta'),
                'release_notes' => $request->input('release_notes'),
                'file_path' => $finalPath,
                'file_size' => $fileSize,
                'status' => 'Pending'
            ]);

            ActivityLog::log('Build Uploaded', "Uploaded v{$versionName} for project {$project->name}", $build);
            return redirect()->route('builds.show', $build->id)->with('success', 'APK Uploaded Successfully!');

        } catch (\Exception $e) {
            if ($finalPath) Storage::disk('public')->delete($finalPath);
            return back()->withErrors(['apk_file' => 'Failed to parse APK: ' . $e->getMessage()]);
        }
    }

    public function show(Build $build)
    {
        $build->load([
            'project',
            'uploader',
            'approver',
            'feedbacks.author',
            'feedbacks.assignee',
            'feedbacks.comments.author',
            'tasks.creator',
            'tasks.assignee',
            'tasks.comments.author',
            'comments.author',
            'shareLinks',
            'downloads.user',
            'downloads.shareLink',
        ]);

        $build->loadCount(['feedbacks', 'tasks', 'downloads', 'comments']);

        return Inertia::render('Builds/Show', [
            'build' => $build,
            'users' => \App\Models\User::all(['id', 'name']),
        ]);
    }

    public function download(Build $build)
    {
        // Log the download
        \App\Models\Download::create([
            'build_id'   => $build->id,
            'user_id'    => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Storage::disk('public')->download(
            $build->file_path,
            $build->project->name . '_v' . $build->version_name . '.apk',
            ['Content-Type' => 'application/vnd.android.package-archive']
        );
    }

    public function destroy(Build $build)
    {
        // Only Admins (or the uploader?) can delete. Let's stick to Admin as requested.
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        $projectName = $build->project->name;
        $version = $build->version_name;
        $filePath = $build->file_path;

        // Delete the record first (cascading deletes for feedback/tasks should be in migration)
        $build->delete();

        // Delete the file from storage
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        ActivityLog::log('Build Deleted', "Deleted v{$version} from project {$projectName}");

        return redirect()->route('projects.show', $build->project_id)->with('success', 'Build and associated file deleted.');
    }
}

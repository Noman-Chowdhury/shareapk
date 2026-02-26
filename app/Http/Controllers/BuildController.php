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
     * Pre-analyze APK and return metadata.
     */
    public function preAnalyze(Request $request)
    {
        $request->validate([
            'apk_file' => 'required|file|max:512000',
        ]);

        $file = $request->file('apk_file');
        
        // Use a persistent temporary path if possible, or just store it in 'temp' disk
        $tempPath = $file->store('temp', 'public');
        $fullPath = storage_path('app/public/' . $tempPath);

        try {
            $apk = new ApkParser($fullPath, ['tmp_path' => storage_path('app/apk_temp')]);
            $manifest = $apk->getManifest();
            
            $packageName = $manifest->getPackageName();
            $versionCode = $manifest->getVersionCode();
            $versionName = $manifest->getVersionName();
            
            // App Name
            $appName = null;
            try {
                $aaptOutput = shell_exec("aapt dump badging " . escapeshellarg($fullPath) . " 2>/dev/null | grep 'application-label:'");
                if ($aaptOutput && preg_match("/application-label:'([^']+)'/", $aaptOutput, $matches)) {
                    $appName = $matches[1];
                }
            } catch (\Exception $e) {}

            if (!$appName) {
                try {
                    $parsedLabel = $manifest->getApplication()->getLabel();
                    if ($parsedLabel && !str_starts_with($parsedLabel, '0x')) $appName = $parsedLabel;
                } catch (\Exception $e) {}
            }

            if (!$appName) {
                $segments = explode('.', $packageName);
                $appName = ucwords(str_replace('_', ' ', end($segments)));
            }

            // Icon Extraction (Base64 for preview)
            $iconData = null;
            try {
                $iconOutput = shell_exec("aapt dump badging " . escapeshellarg($fullPath) . " 2>/dev/null | grep 'application-icon-'");
                $iconPath = null;
                $bestSize = 0;

                if ($iconOutput && preg_match_all("/application-icon-(\d+):'([^']+)'/", $iconOutput, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $size = (int)$match[1];
                        if ($size > $bestSize && str_ends_with(strtolower($match[2]), '.png')) {
                            $bestSize = $size;
                            $iconPath = $match[2];
                        }
                    }
                }

                if ($iconPath) {
                    $tempIcon = storage_path('app/public/temp/icon_' . time() . '.png');
                    shell_exec("unzip -p " . escapeshellarg($fullPath) . " " . escapeshellarg($iconPath) . " > " . escapeshellarg($tempIcon));
                    if (file_exists($tempIcon)) {
                        $iconData = 'data:image/png;base64,' . base64_encode(file_get_contents($tempIcon));
                        @unlink($tempIcon);
                    }
                }
            } catch (\Exception $e) {}

            return response()->json([
                'success'      => true,
                'package_name' => $packageName,
                'version_code' => $versionCode,
                'version_name' => $versionName,
                'app_name'     => $appName,
                'icon_data'    => $iconData,
                'temp_path'    => $tempPath,
                'file_size'    => $file->getSize(),
            ]);

        } catch (\Exception $e) {
            @unlink($fullPath);
            return response()->json([
                'success' => false,
                'message' => 'Failed to parse APK: ' . $e->getMessage()
            ], 422);
        }
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
            // Parse APK
            $apk = new ApkParser($fullPath, ['tmp_path' => storage_path('app/apk_temp')]);
            $manifest = $apk->getManifest();
            
            $packageName = $manifest->getPackageName();
            $versionCode = $manifest->getVersionCode();
            $versionName = $manifest->getVersionName();
            
            // App Name extraction
            $appName = null;
            try {
                $aaptOutput = shell_exec("aapt dump badging " . escapeshellarg($fullPath) . " 2>/dev/null | grep 'application-label:'");
                if ($aaptOutput && preg_match("/application-label:'([^']+)'/", $aaptOutput, $matches)) {
                    $appName = $matches[1];
                }
            } catch (\Exception $e) {}

            if (!$appName) {
                try {
                    $parsedLabel = $manifest->getApplication()->getLabel();
                    if ($parsedLabel && !str_starts_with($parsedLabel, '0x')) $appName = $parsedLabel;
                } catch (\Exception $e) {}
            }

            if (!$appName) {
                $segments = explode('.', $packageName);
                $appName = ucwords(str_replace('_', ' ', end($segments)));
            }

            // Automate Project Creation
            $project = Project::firstOrCreate(
                ['package_name' => $packageName],
                ['name' => $appName ?? $packageName]
            );

            // Icon Extraction using aapt
            try {
                $iconOutput = shell_exec("aapt dump badging " . escapeshellarg($fullPath) . " 2>/dev/null | grep 'application-icon-'");
                $iconPath = null;
                $bestSize = 0;

                if ($iconOutput && preg_match_all("/application-icon-(\d+):'([^']+)'/", $iconOutput, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $size = (int)$match[1];
                        if ($size > $bestSize && str_ends_with(strtolower($match[2]), '.png')) {
                            $bestSize = $size;
                            $iconPath = $match[2];
                        }
                    }
                }

                if (!$iconPath) {
                    $baseIconOutput = shell_exec("aapt dump badging " . escapeshellarg($fullPath) . " 2>/dev/null | grep 'application: '");
                    if ($baseIconOutput && preg_match("/icon='([^']+)'/", $baseIconOutput, $baseIconMatches)) {
                        $iconPath = $baseIconMatches[1];
                    }
                }

                if ($iconPath && str_ends_with(strtolower($iconPath), '.png')) {
                    $iconExt = pathinfo($iconPath, PATHINFO_EXTENSION);
                    $iconFilename = 'project_' . $project->id . '_' . time() . '.' . $iconExt;
                    $iconLocalPath = storage_path('app/public/icons/' . $iconFilename);
                    
                    if (!file_exists(storage_path('app/public/icons'))) {
                        mkdir(storage_path('app/public/icons'), 0755, true);
                    }
                    shell_exec("unzip -p " . escapeshellarg($fullPath) . " " . escapeshellarg($iconPath) . " > " . escapeshellarg($iconLocalPath));
                    
                    if (file_exists($iconLocalPath) && filesize($iconLocalPath) > 0) {
                        $project->update(['icon_url' => 'icons/' . $iconFilename]);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Icon extraction failed: " . $e->getMessage());
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

            return redirect()->route('builds.show', $build->id)->with('success', 'APK Uploaded Successfully! Version: ' . $versionName);

        } catch (\Exception $e) {
            if ($finalPath) {
                Storage::disk('public')->delete($finalPath);
            }
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

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
     * Pre-analyze APK and return metadata.
     */
    public function preAnalyze(Request $request)
    {
        $request->validate([
            'apk_file' => 'required|file|max:512000',
        ]);

        $file = $request->file('apk_file');
        
        // Use a persistent temporary path
        $tempPath = $file->store('temp', 'public');
        $fullPath = storage_path('app/public/' . $tempPath);

        try {
            $apk = new ApkParser($fullPath, ['tmp_path' => storage_path('app/apk_temp')]);
            $manifest = $apk->getManifest();
            
            $packageName = $manifest->getPackageName();
            $versionCode = $manifest->getVersionCode();
            $versionName = $manifest->getVersionName();
            
            // App Name / Icon extraction using aapt (Full output parsing)
            $aaptPath = '/usr/bin/aapt';
            $aaptOutput = shell_exec("$aaptPath dump badging " . escapeshellarg($fullPath) . " 2>&1");
            
            $appName = null;
            $iconPath = null;
            $iconData = null;

            if ($aaptOutput) {
                // 1. App Name (Find the primary application-label first)
                if (preg_match("/application-label:'([^']+)'/", $aaptOutput, $matches)) {
                    $appName = $matches[1];
                } elseif (preg_match("/application: label='([^']+)'/", $aaptOutput, $matches)) {
                    $appName = $matches[1];
                }

                // 2. Icon Path (Find the best available icon)
                $tempIconPath = null;
                $currentBestSize = 0;
                
                // Extract all icons using match_all
                if (preg_match_all("/application-icon-(\d+):'([^']+)'/", $aaptOutput, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $size = (int)$match[1];
                        if ($size >= $currentBestSize && (str_ends_with(strtolower($match[2]), '.png') || str_ends_with(strtolower($match[2]), '.webp'))) {
                            $currentBestSize = $size;
                            $tempIconPath = $match[2];
                        }
                    }
                }

                if (!$tempIconPath && preg_match("/icon='([^']+)'/", $aaptOutput, $matches)) {
                    $tempIconPath = $matches[1];
                }

                // Extract Icon if found
                if ($tempIconPath) {
                    $ext = pathinfo($tempIconPath, PATHINFO_EXTENSION);
                    $tempIcon = storage_path('app/public/temp/icon_' . time() . '.' . $ext);
                    $unzipPath = '/usr/bin/unzip';
                    
                    shell_exec("$unzipPath -p " . escapeshellarg($fullPath) . " " . escapeshellarg($tempIconPath) . " > " . escapeshellarg($tempIcon));
                    
                    if (file_exists($tempIcon) && filesize($tempIcon) > 0) {
                        $mimeType = ($ext === 'webp') ? 'image/webp' : 'image/png';
                        $iconData = 'data:'.$mimeType.';base64,' . base64_encode(file_get_contents($tempIcon));
                        @unlink($tempIcon);
                    }
                }
            }

            // Fallbacks for App Name
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
            \Log::error("PreAnalyze Error: " . $e->getMessage());
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
            
            // App Name / Icon extraction using aapt (Full output parsing)
            $aaptPath = '/usr/bin/aapt';
            $aaptOutput = shell_exec("$aaptPath dump badging " . escapeshellarg($fullPath) . " 2>&1");
            
            $appName = null;
            $iconPath = null;

            if ($aaptOutput) {
                // 1. App Name
                if (preg_match("/application-label:'([^']+)'/", $aaptOutput, $matches)) {
                    $appName = $matches[1];
                } elseif (preg_match("/application: label='([^']+)'/", $aaptOutput, $matches)) {
                    $appName = $matches[1];
                }

                // 2. Icon Path
                $tempIconPath = null;
                $currentBestSize = 0;
                
                if (preg_match_all("/application-icon-(\d+):'([^']+)'/", $aaptOutput, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $size = (int)$match[1];
                        if ($size >= $currentBestSize && (str_ends_with(strtolower($match[2]), '.png') || str_ends_with(strtolower($match[2]), '.webp'))) {
                            $currentBestSize = $size;
                            $tempIconPath = $match[2];
                        }
                    }
                }

                if (!$tempIconPath && preg_match("/icon='([^']+)'/", $aaptOutput, $matches)) {
                    $tempIconPath = $matches[1];
                }

                // Extract Icon for Project
                if ($tempIconPath) {
                    $ext = pathinfo($tempIconPath, PATHINFO_EXTENSION);
                    $iconFilename = 'project_' . time() . '_' . Str::random(5) . '.' . $ext;
                    $iconLocalPath = storage_path('app/public/icons/' . $iconFilename);
                    
                    if (!file_exists(storage_path('app/public/icons'))) {
                        mkdir(storage_path('app/public/icons'), 0755, true);
                    }
                    
                    $unzipPath = '/usr/bin/unzip';
                    shell_exec("$unzipPath -p " . escapeshellarg($fullPath) . " " . escapeshellarg($tempIconPath) . " > " . escapeshellarg($iconLocalPath));
                    
                    if (file_exists($iconLocalPath) && filesize($iconLocalPath) > 0) {
                        $iconPath = 'icons/' . $iconFilename;
                    }
                }
            }

            // Fallbacks
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

            if ($iconPath) {
                $project->update(['icon_url' => $iconPath]);
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

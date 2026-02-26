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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Manually check for upload errors before validation
        if ($request->hasFile('apk_file') && !$request->file('apk_file')->isValid()) {
            $error = $request->file('apk_file')->getErrorMessage();
            $errorCode = $request->file('apk_file')->getError();
            \Log::error("APK Upload failed: " . $error . " (Code: " . $errorCode . ")");
            return back()->withErrors(['apk_file' => 'Upload Error: ' . $error . ' (Code ' . $errorCode . ')']);
        }

        $request->validate([
            'apk_file' => 'required|file|max:512000', // Increased to 500MB in validation
            'build_type' => 'required|in:Alpha,Beta,RC,Production',
            'release_notes' => 'nullable|string'
        ]);

        $file = $request->file('apk_file');
        
        if ($file->getClientOriginalExtension() !== 'apk') {
            return back()->withErrors(['apk_file' => 'The file must be an APK extension.']);
        }

        try {
            // First store it locally so Parser can access it safely
            $path = $file->store('builds', 'public');
            $fullPath = storage_path('app/public/' . $path);
            
            // Parse APK
            $apk = new ApkParser($fullPath);
            $manifest = $apk->getManifest();
            
            $packageName = $manifest->getPackageName();
            $versionCode = $manifest->getVersionCode();
            $versionName = $manifest->getVersionName();
            
            // Extract app name using aapt if available (more reliable than parsing resource.arsc manually in PHP)
            $appName = null;
            try {
                $aaptOutput = shell_exec("aapt dump badging " . escapeshellarg($fullPath) . " 2>/dev/null | grep 'application-label:'");
                if ($aaptOutput && preg_match("/application-label:'([^']+)'/", $aaptOutput, $matches)) {
                    $appName = $matches[1];
                }
            } catch (\Exception $e) {}

            // Fallback options
            if (!$appName || str_starts_with($appName, '0x') || str_starts_with($appName, '@')) {
                // Try php-apk-parser format
                try {
                    $parsedLabel = $manifest->getApplication()->getLabel();
                    if ($parsedLabel && !str_starts_with($parsedLabel, '0x') && !str_starts_with($parsedLabel, '@')) {
                        $appName = $parsedLabel;
                    }
                } catch (\Exception $e) {}
            }

            if (!$appName || str_starts_with($appName, '0x') || str_starts_with($appName, '@')) {
                // Ultimate Fallback: Title Case the last segment of the package name (e.g. com.example.my_app -> My App)
                $segments = explode('.', $packageName);
                $appName = ucwords(str_replace('_', ' ', end($segments)));
            }

            // Automate Project Creation based on package name!
            $project = Project::firstOrCreate(
                ['package_name' => $packageName],
                ['name' => $appName ?? $packageName]
            );

            // Extract App Icon using aapt
            try {
                // Find all application-icon entries
                $iconOutput = shell_exec("aapt dump badging " . escapeshellarg($fullPath) . " 2>/dev/null | grep 'application-icon-'");
                $iconPath = null;
                $bestSize = 0;

                if ($iconOutput && preg_match_all("/application-icon-(\d+):'([^']+)'/", $iconOutput, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $size = (int)$match[1];
                        $path = $match[2];
                        if ($size > $bestSize && str_ends_with(strtolower($path), '.png')) {
                            $bestSize = $size;
                            $iconPath = $path;
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
                    
                    // Extract icon using unzip
                    shell_exec("unzip -p " . escapeshellarg($fullPath) . " " . escapeshellarg($iconPath) . " > " . escapeshellarg($iconLocalPath));
                    
                    if (file_exists($iconLocalPath) && filesize($iconLocalPath) > 0) {
                        $project->update(['icon_url' => 'icons/' . $iconFilename]);
                    }
                }
            } catch (\Exception $e) {
                // Ignore icon extraction errors
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
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'status' => 'Pending'
            ]);

            ActivityLog::log('Build Uploaded', "Uploaded v{$versionName} for project {$project->name}", $build);

            return redirect()->route('builds.show', $build->id)->with('success', 'APK Uploaded Successfully! Version: ' . $versionName);

        } catch (\Exception $e) {
            if (isset($path)) {
                Storage::disk('public')->delete($path);
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
            $build->project->name . '_v' . $build->version_name . '.apk'
        );
    }

    public function approve(Build $build)
    {
        $build->update([
            'status'           => 'Approved',
            'approved_by'      => auth()->id(),
            'approval_remarks' => request('remarks'),
        ]);

        $build->uploader->notify(new GeneralNotification(
            'Build Approved ✅',
            "Your build for {$build->project->name} (v{$build->version_name}) has been approved.",
            route('builds.show', $build->id)
        ));

        ActivityLog::log('Build Approved', "Approved v{$build->version_name} for project {$build->project->name}", $build);

        return back()->with('success', 'Build approved successfully.');
    }

    public function reject(Build $build)
    {
        $build->update([
            'status'           => 'Rejected',
            'approved_by'      => auth()->id(),
            'approval_remarks' => request('remarks'),
        ]);

        $build->uploader->notify(new GeneralNotification(
            'Build Rejected ❌',
            "Your build for {$build->project->name} (v{$build->version_name}) has been rejected. Reason: " . request('remarks'),
            route('builds.show', $build->id)
        ));

        ActivityLog::log('Build Rejected', "Rejected v{$build->version_name} for project {$build->project->name}", $build);

        return back()->with('success', 'Build rejected.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\ShareLink;
use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;
use App\Models\ActivityLog;

class ShareLinkController extends Controller
{
    /**
     * Store a new share link.
     */
    public function store(Request $request, Build $build)
    {
        $request->validate([
            'expires_at' => 'nullable|date|after:now',
            'download_limit' => 'nullable|integer|min:1',
            'password' => 'nullable|string|max:255',
        ]);

        $shareLink = $build->shareLinks()->create([
            'user_id' => auth()->id(),
            'download_limit' => $request->download_limit,
            'expires_at' => $request->expires_at,
            'password' => $request->password,
        ]);

        ActivityLog::log('Share Link Created', "Created share link for build v{$build->version_name}", $shareLink);

        return back()->with('success', 'Public share link created.');
    }

    /**
     * Delete a share link.
     */
    public function destroy(ShareLink $shareLink)
    {
        $build = $shareLink->build;
        $shareLink->delete();
        ActivityLog::log('Share Link Deleted', "Deleted share link for build v{$build->version_name}");
        return back()->with('success', 'Share link deleted.');
    }

    /**
     * Public page for viewing/downloading via share link.
     */
    public function publicShow(Request $request, $token)
    {
        $shareLink = ShareLink::where('token', $token)->with(['build.project', 'build.uploader'])->firstOrFail();

        if (!$shareLink->isValid()) {
            return Inertia::render('Public/ShareError', [
                'message' => 'This link has expired or reached its download limit.'
            ]);
        }

        // Handle Password Protection
        if ($shareLink->password) {
            $sessionKey = 'share_link_auth_' . $shareLink->id;
            if (Session::get($sessionKey) !== $shareLink->password) {
                return Inertia::render('Public/SharePassword', [
                    'token' => $token,
                ]);
            }
        }

        return Inertia::render('Public/BuildShare', [
            'shareLink' => $shareLink,
            'project' => $shareLink->build->project,
            'build' => $shareLink->build,
        ]);
    }

    /**
     * Verify password for share link.
     */
    public function verifyPassword(Request $request, $token)
    {
        $shareLink = ShareLink::where('token', $token)->firstOrFail();
        
        $request->validate([
            'password' => 'required|string',
        ]);

        if ($request->password === $shareLink->password) {
            Session::put('share_link_auth_' . $shareLink->id, $shareLink->password);
            return redirect()->route('public.share.show', $token);
        }

        return back()->withErrors(['password' => 'Invalid password.']);
    }

    /**
     * Public download via share link.
     */
    public function publicDownload($token)
    {
        $shareLink = ShareLink::where('token', $token)->with('build.project')->firstOrFail();

        if (!$shareLink->isValid()) {
            abort(403, 'Link invalid or expired.');
        }

        // Re-verify password in case someone tries to guess the download URL
        if ($shareLink->password) {
            $sessionKey = 'share_link_auth_' . $shareLink->id;
            if (Session::get($sessionKey) !== $shareLink->password) {
                abort(403, 'Password verification required.');
            }
        }

        // Tracking
        $shareLink->increment('download_count');
        
        Download::create([
            'build_id' => $shareLink->build_id,
            'share_link_id' => $shareLink->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Storage::disk('public')->download(
            $shareLink->build->file_path,
            $shareLink->build->project->name . '_v' . $shareLink->build->version_name . '.apk'
        );
    }
}

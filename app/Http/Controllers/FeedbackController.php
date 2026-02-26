<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Feedback;
use App\Models\User;
use App\Models\ActivityLog;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with(['author', 'assignee', 'build.project'])->latest()->get();
        return \Inertia\Inertia::render('Feedback/Index', [
            'feedbacks' => $feedbacks,
            'users'     => User::all(['id', 'name']),
        ]);
    }

    public function show(Feedback $feedback)
    {
        $feedback->load(['author', 'assignee', 'build.project', 'comments.author']);
        return \Inertia\Inertia::render('Feedback/Show', [
            'feedback' => $feedback,
            'users'    => User::all(['id', 'name']),
        ]);
    }

    public function store(Request $request, Build $build)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'required|string',
            'type'            => 'required|in:Bug,Feature,Improvement,Suggestion,Other',
            'severity'        => 'nullable|in:Critical,High,Medium,Low',
            'assignee_id'     => 'nullable|exists:users,id',
            'device_model'    => 'nullable|string|max:255',
            'os_version'      => 'nullable|string|max:255',
            'screen_size'     => 'nullable|string|max:255',
            'attachments'     => 'nullable|array',
            'attachments.*'   => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt,zip',
            'new_attachments' => 'nullable|array',
            'new_attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt,zip',
        ]);

        $attachmentPaths = array_merge(
            $this->storeFiles($request, 'attachments'),
            $this->storeFiles($request, 'new_attachments')
        );

        $feedback = $build->feedbacks()->create([
            'user_id'      => auth()->id(),
            'assignee_id'  => $request->input('assignee_id') ?: null,
            'title'        => $request->input('title'),
            'description'  => $request->input('description'),
            'type'         => $request->input('type'),
            'severity'     => $request->input('severity') ?: null,
            'status'       => 'Open',
            'device_model' => $request->input('device_model') ?: null,
            'os_version'   => $request->input('os_version') ?: null,
            'screen_size'  => $request->input('screen_size') ?: null,
            'attachments'  => $attachmentPaths,
        ]);

        // Notify assignee if set
        if ($feedback->assignee_id) {
            $feedback->assignee->notify(new GeneralNotification(
                "Feedback Assigned to You",
                "You have been assigned to feedback: {$feedback->title}",
                route('builds.show', $build->id)
            ));
        }

        // Notify build uploader
        if ($build->user_id && $build->user_id !== auth()->id()) {
            $build->uploader->notify(new GeneralNotification(
                "New {$feedback->type} Reported",
                "New feedback on build v{$build->version_name}: {$feedback->title}",
                route('builds.show', $build->id)
            ));
        }

        // Notify Admins
        $admins = User::role('Admin')->get();
        Notification::send($admins, new GeneralNotification(
            "Global Feedback: {$feedback->title}",
            "User " . auth()->user()->name . " reported a {$feedback->type} on {$build->project->name}.",
            route('feedback.index')
        ));

        ActivityLog::log('Feedback Submitted', "Reported {$feedback->type} on build v{$build->version_name}", $feedback);

        return back()->with('success', 'Feedback submitted successfully.');
    }

    public function update(Request $request, Feedback $feedback)
    {
        $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'required|string',
            'type'                => 'required|in:Bug,Feature,Improvement,Suggestion,Other',
            'severity'            => 'nullable|in:Critical,High,Medium,Low',
            'status'              => 'required|in:Open,In Progress,Resolved,Closed',
            'assignee_id'         => 'nullable|exists:users,id',
            'device_model'        => 'nullable|string|max:255',
            'os_version'          => 'nullable|string|max:255',
            'screen_size'         => 'nullable|string|max:255',
            'new_attachments'     => 'nullable|array',
            'new_attachments.*'   => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt,zip',
            'keep_attachments'    => 'nullable|array',
        ]);

        // Build the final attachment list
        $existingAttachments = $feedback->attachments ?? [];

        // Filter old attachments to only those the user wants to keep
        $keepPaths = $request->input('keep_attachments', []);
        $keptAttachments = array_values(array_filter($existingAttachments, fn($a) => in_array($a['path'], $keepPaths)));

        // Delete removed attachments from disk
        foreach ($existingAttachments as $att) {
            if (!in_array($att['path'], $keepPaths)) {
                Storage::disk('public')->delete($att['path']);
            }
        }

        // Add newly uploaded files
        $newAttachments = $this->storeFiles($request, 'new_attachments');

        $finalAttachments = array_merge($keptAttachments, $newAttachments);

        $oldAssigneeId = $feedback->assignee_id;

        $feedback->update([
            'title'        => $request->input('title'),
            'description'  => $request->input('description'),
            'type'         => $request->input('type'),
            'severity'     => $request->input('severity') ?: $feedback->severity,
            'status'       => $request->input('status'),
            'assignee_id'  => $request->input('assignee_id') ?: null,
            'device_model' => $request->input('device_model') ?: null,
            'os_version'   => $request->input('os_version') ?: null,
            'screen_size'  => $request->input('screen_size') ?: null,
            'attachments'  => $finalAttachments,
        ]);

        // Notify new assignee if changed
        $newAssigneeId = $request->input('assignee_id');
        if ($newAssigneeId && $newAssigneeId != $oldAssigneeId) {
            $feedback->fresh()->assignee->notify(new GeneralNotification(
                "Feedback Assigned to You",
                "You have been assigned to feedback: {$feedback->title}",
                route('builds.show', $feedback->build_id)
            ));
        }

        return back()->with('success', 'Feedback updated.');
    }

    public function destroy(Feedback $feedback)
    {
        // Delete stored attachments
        foreach ($feedback->attachments ?? [] as $att) {
            Storage::disk('public')->delete($att['path']);
        }
        $feedback->delete();
        return back()->with('success', 'Feedback deleted.');
    }

    /**
     * Store uploaded files and return metadata array.
     */
    private function storeFiles(Request $request, string $field): array
    {
        $stored = [];
        if ($request->hasFile($field)) {
            foreach ($request->file($field) as $file) {
                if ($file->isValid()) {
                    $path = $file->store('feedback_attachments', 'public');
                    $stored[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                    ];
                }
            }
        }
        return $stored;
    }
}

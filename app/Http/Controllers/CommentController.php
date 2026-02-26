<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\GeneralNotification;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'commentable_id' => 'required|integer',
            // Enforce valid class names mapping to our models
            'commentable_type' => 'required|string|in:App\Models\Build,App\Models\Feedback,App\Models\Task',
            'attachment' => 'nullable|file|max:10240', // Max 10MB
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('comments_attachments', 'public');
            $attachmentName = $file->getClientOriginalName();
        }

        $comment = \App\Models\Comment::create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'commentable_id' => $validated['commentable_id'],
            'commentable_type' => $validated['commentable_type'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        // Notifications
        $parent = $comment->commentable;
        $usersToNotify = [];

        if ($parent instanceof \App\Models\Task) {
            if ($parent->creator_id) $usersToNotify[] = $parent->creator;
            if ($parent->assignee_id) $usersToNotify[] = $parent->assignee;
        } elseif ($parent instanceof \App\Models\Feedback) {
            if ($parent->user_id) $usersToNotify[] = $parent->author;
        }

        foreach (array_unique($usersToNotify) as $user) {
            if ($user && $user->id !== auth()->id()) {
                $user->notify(new GeneralNotification(
                    "New comment on {$parent->title}",
                    auth()->user()->name . " commented: " . substr($comment->body, 0, 50) . "...",
                    $parent instanceof \App\Models\Build ? route('builds.show', $parent->id) : route('builds.show', $parent->build_id)
                ));
            }
        }

        return back()->with('success', 'Comment posted.');
    }
}

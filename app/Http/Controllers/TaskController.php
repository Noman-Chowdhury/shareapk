<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Task;
use App\Models\User;
use App\Models\ActivityLog;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with(['creator', 'assignee', 'build.project'])->latest()->get();
        $users = \App\Models\User::all(['id', 'name']);
        
        return \Inertia\Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'users' => $users,
        ]);
    }

    public function store(Request $request, Build $build)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'status' => 'required|in:Todo,In Progress,Done',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt,zip',
            'new_attachments' => 'nullable|array',
            'new_attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt,zip',
        ]);

        $attachmentPaths = array_merge(
            $this->storeFiles($request, 'attachments'),
            $this->storeFiles($request, 'new_attachments')
        );

        $task = $build->tasks()->create([
            'creator_id' => auth()->id(),
            'title' => $request->input('title'),
            'description' => $request->input('description') ?? null,
            'priority' => $request->input('priority'),
            'status' => $request->input('status'),
            'assignee_id' => $request->input('assignee_id') ?: null,
            'due_date' => $request->input('due_date') ?? null,
            'attachments' => $attachmentPaths,
        ]);

        if ($task->assignee_id) {
            $task->assignee->notify(new GeneralNotification(
                'New Task Assigned',
                "You have been assigned the task: {$task->title}",
                route('builds.show', $build->id)
            ));
        }

        ActivityLog::log('Task Created', "Created task '{$task->title}' for build {$build->version_name}", $task);

        return back()->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'status' => 'required|in:Todo,In Progress,Done',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'new_attachments' => 'nullable|array',
            'new_attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt,zip',
            'keep_attachments' => 'nullable|array',
        ]);

        $existingAttachments = $task->attachments ?? [];
        $keepPaths = $request->input('keep_attachments', []);
        $keptAttachments = array_values(array_filter($existingAttachments, fn($a) => in_array($a['path'], $keepPaths)));

        // Delete removed files
        foreach ($existingAttachments as $att) {
            if (!in_array($att['path'], $keepPaths)) {
                Storage::disk('public')->delete($att['path']);
            }
        }

        $newAttachments = $this->storeFiles($request, 'new_attachments');
        $finalAttachments = array_merge($keptAttachments, $newAttachments);

        $oldAssigneeId = $task->assignee_id;
        
        $task->update([
            'title' => $request->input('title'),
            'description' => $request->input('description') ?? null,
            'priority' => $request->input('priority'),
            'status' => $request->input('status'),
            'assignee_id' => $request->input('assignee_id') ?: null,
            'due_date' => $request->input('due_date') ?? null,
            'attachments' => $finalAttachments,
        ]);

        if ($task->assignee_id && $task->assignee_id != $oldAssigneeId) {
            $task->assignee->notify(new GeneralNotification(
                'Task Assigned to You',
                "The task '{$task->title}' has been assigned to you.",
                route('builds.show', $task->build_id)
            ));
        }

        ActivityLog::log('Task Updated', "Updated task '{$task->title}'", $task);

        return back()->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        foreach ($task->attachments ?? [] as $att) {
            Storage::disk('public')->delete($att['path']);
        }
        $task->delete();
        return back()->with('success', 'Task deleted.');
    }

    private function storeFiles(Request $request, string $field): array
    {
        $stored = [];
        if ($request->hasFile($field)) {
            foreach ($request->file($field) as $file) {
                if ($file->isValid()) {
                    $path = $file->store('task_attachments', 'public');
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

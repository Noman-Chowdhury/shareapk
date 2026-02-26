<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Task;
use App\Models\User;
use App\Models\ActivityLog;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'status' => 'required|in:Todo,In Progress,Done',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task = $build->tasks()->create([
            'creator_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'assignee_id' => $validated['assignee_id'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'status' => 'required|in:Todo,In Progress,Done',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $oldAssigneeId = $task->assignee_id;
        
        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'assignee_id' => $validated['assignee_id'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
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
        $task->delete();
        return back()->with('success', 'Task deleted.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Project;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::withCount('builds')
            ->withCount(['tasks as pending_tasks_count' => function ($query) {
                $query->where('tasks.status', '!=', 'Done');
            }])
            ->withCount(['feedbacks as pending_feedbacks_count' => function ($query) {
                $query->where('feedback.status', '!=', 'Closed')->where('feedback.status', '!=', 'Resolved');
            }])
            ->with(['latestBuild'])
            ->latest()
            ->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function show(Project $project)
    {
        $project->load(['latestBuild.uploader']);
        $project->loadCount([
            'builds',
            'tasks as pending_tasks_count' => function ($query) {
                $query->where('tasks.status', '!=', 'Done');
            },
            'feedbacks as pending_feedbacks_count' => function ($query) {
                $query->where('feedback.status', '!=', 'Closed')->where('feedback.status', '!=', 'Resolved');
            }
        ]);

        $builds = $project->builds()
            ->with(['uploader', 'approver'])
            ->withCount(['feedbacks', 'tasks', 'downloads', 'comments'])
            ->withCount(['tasks as pending_tasks_count' => function ($query) {
                $query->where('tasks.status', '!=', 'Done');
            }])
            ->withCount(['feedbacks as pending_feedbacks_count' => function ($query) {
                $query->where('feedback.status', '!=', 'Closed')->where('feedback.status', '!=', 'Resolved');
            }])
            ->latest()
            ->paginate(10);

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'builds'  => $builds,
        ]);
    }
}

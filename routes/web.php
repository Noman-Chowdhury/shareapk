<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => false,
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'totalBuilds' => \App\Models\Build::count(),
        'openFeedback' => \App\Models\Feedback::where('status', 'Open')->count(),
        'myTasksCount' => \App\Models\Task::where('assignee_id', auth()->id())->where('status', '!=', 'Done')->count(),
        'myTasks' => \App\Models\Task::where('assignee_id', auth()->id())->with(['build.project'])->where('status', '!=', 'Done')->orderBy('due_date')->take(10)->get(),
        'myFeedback' => \App\Models\Feedback::where('user_id', auth()->id())->with(['build.project'])->where('status', '!=', 'Resolved')->latest()->take(10)->get(),
        'downloads' => \App\Models\Download::count(),
        'recentBuilds' => \App\Models\Build::with(['project', 'uploader'])
            ->withCount(['tasks as pending_tasks_count' => function ($query) {
                $query->where('status', '!=', 'Done');
            }])
            ->withCount(['feedbacks as pending_feedbacks_count' => function ($query) {
                $query->where('status', '!=', 'Closed')->where('status', '!=', 'Resolved');
            }])
            ->latest()->take(5)->get(),
        'recentActivity' => \App\Models\Comment::with(['user', 'commentable'])->latest()->take(5)->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Projects
    Route::get('/projects', [\App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'show'])->name('projects.show');

    // Builds
    Route::get('/builds/create', [\App\Http\Controllers\BuildController::class, 'create'])->name('builds.create');
    Route::post('/builds', [\App\Http\Controllers\BuildController::class, 'store'])->name('builds.store');
    Route::post('/builds/pre-analyze', [\App\Http\Controllers\BuildController::class, 'preAnalyze'])->name('builds.pre-analyze');
    Route::get('/builds/{build}', [\App\Http\Controllers\BuildController::class, 'show'])->name('builds.show');
    Route::get('/builds/{build}/download', [\App\Http\Controllers\BuildController::class, 'download'])->name('builds.download');
    Route::delete('/builds/{build}', [\App\Http\Controllers\BuildController::class, 'destroy'])->name('builds.destroy');

    // Feedback
    Route::get('/feedback', [\App\Http\Controllers\FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/{feedback}', [\App\Http\Controllers\FeedbackController::class, 'show'])->name('feedback.show');
    Route::post('/builds/{build}/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');
    Route::put('/feedback/{feedback}', [\App\Http\Controllers\FeedbackController::class, 'update'])->name('feedback.update');
    Route::delete('/feedback/{feedback}', [\App\Http\Controllers\FeedbackController::class, 'destroy'])->name('feedback.destroy');

    // Tasks
    Route::get('/tasks', [\App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');
    Route::post('/builds/{build}/tasks', [\App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');

    // Comments
    Route::post('/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    // Share Links
    Route::post('/builds/{build}/share-links', [\App\Http\Controllers\ShareLinkController::class, 'store'])->name('share-links.store');
    Route::delete('/share-links/{shareLink}', [\App\Http\Controllers\ShareLinkController::class, 'destroy'])->name('share-links.destroy');

    // Users
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

    // Notifications
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/clear', [\App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clear');

    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
});

// Public Share Routes
Route::get('/s/{token}', [\App\Http\Controllers\ShareLinkController::class, 'publicShow'])->name('public.share.show');
Route::post('/s/{token}/verify', [\App\Http\Controllers\ShareLinkController::class, 'verifyPassword'])->name('public.share.verify');
Route::get('/s/{token}/download', [\App\Http\Controllers\ShareLinkController::class, 'publicDownload'])->name('public.share.download');

require __DIR__.'/auth.php';

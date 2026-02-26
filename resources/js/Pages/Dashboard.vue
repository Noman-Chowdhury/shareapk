<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    totalBuilds: Number,
    openFeedback: Number,
    myTasksCount: Number,
    myTasks: Array,
    myFeedback: Array,
    downloads: Number,
    recentBuilds: Array,
    recentActivity: Array,
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="h4 font-weight-bold text-dark mb-0">Dashboard</h2>
        </template>

        <div class="row g-4 mt-2">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Total Builds</h6>
                        <h2 class="mb-0">{{ totalBuilds }}</h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Open Feedback</h6>
                        <h2 class="mb-0 text-danger">{{ openFeedback }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">My Tasks</h6>
                        <h2 class="mb-0 text-warning">{{ myTasksCount }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Downloads</h6>
                        <h2 class="mb-0 text-primary">{{ downloads }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-2">
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Builds</h5>
                        <Link :href="route('builds.create')" class="btn btn-sm btn-primary">Upload APK</Link>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" v-if="recentBuilds && recentBuilds.length > 0">
                            <Link v-for="build in recentBuilds" :key="build.id" :href="route('builds.show', build.id)" class="list-group-item list-group-item-action py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded p-2 me-3 flex-shrink-0" style="width:40px; height:40px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                        <img v-if="build.project?.icon_url" :src="'/storage/' + build.project.icon_url" alt="Icon" style="max-width:100%; max-height:100%; object-fit:contain;" />
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#4F46E5" class="bi bi-android2" viewBox="0 0 16 16">
                                            <path d="m10.213 1.471.691-1.216a.104.104 0 0 0-.033-.142.106.106 0 0 0-.142.033l-.705 1.238A8 8 0 0 0 8 1.139a8 8 0 0 0-2.024.26L5.27.161a.105.105 0 0 0-.142-.033.104.104 0 0 0-.033.142l.691 1.216C3.906 2.502 2.146 4.606 1.838 7.21h12.324c-.308-2.604-2.068-4.708-3.949-5.739M5.4 5.378a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0m6.839 0a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0M1.6 8.21h12.8A1.6 1.6 0 0 1 16 9.81v3.2a1.6 1.6 0 0 1-1.6 1.6H1.6A1.6 1.6 0 0 1 0 13.01v-3.2A1.6 1.6 0 0 1 1.6 8.21Z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="mb-0 fw-bold">{{ build.project?.name || 'Unknown Project' }} <span class="text-muted fw-normal ms-1">v{{ build.version_name }} ({{ build.version_code }})</span></h6>
                                                <span v-if="build.pending_feedbacks_count > 0" class="badge bg-danger rounded-pill" style="font-size: 0.65rem;" title="Unresolved Feedback">
                                                    {{ build.pending_feedbacks_count }} Bugs
                                                </span>
                                                <span v-if="build.pending_tasks_count > 0" class="badge bg-warning text-dark rounded-pill" style="font-size: 0.65rem;" title="Pending Tasks">
                                                    {{ build.pending_tasks_count }} Tasks
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1 small text-muted">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge" :class="{'bg-primary': build.build_type === 'Beta', 'bg-warning text-dark': build.build_type === 'Alpha', 'bg-success': build.build_type === 'Production', 'bg-info text-dark': build.build_type === 'RC'}">{{ build.build_type }}</span>
                                                <span>• Uploaded by {{ build.uploader?.name || 'Unknown' }}</span>
                                                <span>• {{ new Date(build.created_at).toLocaleDateString() }}</span>
                                            </div>
                                            <div>{{ (build.file_size / 1048576).toFixed(1) }} MB</div>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                        <div class="p-3 text-center" v-else>
                            <p class="text-muted mb-0">No builds uploaded yet.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Recent Activity</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" v-if="recentActivity && recentActivity.length > 0">
                            <div v-for="activity in recentActivity" :key="activity.id" class="list-group-item">
                                <div class="d-flex gap-2">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;font-size:12px;font-weight:600;">
                                        {{ (activity.user?.name ?? 'U').charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="small">
                                            <strong>{{ activity.user?.name }}</strong> commented on 
                                            <span class="text-primary fw-semibold">{{ activity.commentable_type.split('\\').pop() }}</span>
                                        </div>
                                        <div class="small text-muted mb-1">{{ activity.body }}</div>
                                        <div v-if="activity.attachment_path" class="mb-1">
                                            <a :href="'/storage/' + activity.attachment_path" target="_blank" class="badge bg-secondary text-decoration-none small">📎 Attachment</a>
                                        </div>
                                        <div class="text-muted" style="font-size:11px;">{{ new Date(activity.created_at).toLocaleString() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 text-center text-muted" v-else>No recent activity.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-2 mb-4">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">My Assigned Tasks</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" v-if="myTasks && myTasks.length > 0">
                            <template v-for="task in myTasks" :key="task.id">
                                <Link v-if="task.id"
                                      :href="route('tasks.show', task.id)" 
                                      class="list-group-item list-group-item-action py-3">
                                    <div class="d-flex gap-3 align-items-center">
                                        <div class="bg-light rounded p-2 flex-shrink-0" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                            <img v-if="task.build?.project?.icon_url" :src="'/storage/' + task.build.project.icon_url" style="max-width:100%;max-height:100%;object-fit:contain;" />
                                            <i v-else class="bi bi-check2-circle text-primary h4 mb-0"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-semibold">{{ task.title }}</h6>
                                                <small class="text-danger" v-if="task.due_date">Due: {{ (new Date(task.due_date)).toLocaleDateString() }}</small>
                                            </div>
                                            <div class="mb-2 text-muted small">
                                                {{ task.build?.project?.name || 'Unknown Project' }} • v{{ task.build?.version_name }}
                                            </div>
                                            <div>
                                                <span class="badge me-1" :class="task.status === 'Todo' ? 'bg-secondary' : 'bg-primary'">{{ task.status }}</span>
                                                <span class="badge" :class="{'bg-danger': task.priority === 'Urgent', 'bg-warning text-dark': task.priority === 'High', 'bg-info text-dark': task.priority === 'Medium', 'bg-light text-dark': task.priority === 'Low'}">{{ task.priority }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </template>
                        </div>
                        <div class="p-4 text-center" v-else>
                            <p class="text-muted mb-0">No pending tasks assigned to you. 🎉</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">My Open Feedback</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" v-if="myFeedback && myFeedback.length > 0">
                            <template v-for="fb in myFeedback" :key="fb.id">
                                <Link v-if="fb.id"
                                      :href="route('feedback.show', fb.id)" 
                                      class="list-group-item list-group-item-action py-3">
                                    <div class="d-flex gap-3 align-items-center">
                                        <div class="bg-light rounded p-2 flex-shrink-0" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                            <img v-if="fb.build?.project?.icon_url" :src="'/storage/' + fb.build.project.icon_url" style="max-width:100%;max-height:100%;object-fit:contain;" />
                                            <i v-else class="bi bi-bug text-danger h4 mb-0"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-semibold">{{ fb.title }}</h6>
                                                <span class="badge" :class="{'bg-danger': fb.status === 'Open', 'bg-warning text-dark': fb.status === 'In Progress'}">{{ fb.status }}</span>
                                            </div>
                                            <div class="mb-2 text-muted small">
                                                {{ fb.build?.project?.name || 'Unknown Project' }} • v{{ fb.build?.version_name }}
                                            </div>
                                            <div>
                                                <span class="badge bg-secondary me-1">{{ fb.type }}</span>
                                                <span class="badge" v-if="fb.severity" :class="{'bg-danger': fb.severity === 'Critical', 'bg-warning text-dark': fb.severity === 'High', 'bg-info text-dark': fb.severity === 'Medium', 'bg-light text-dark': fb.severity === 'Low'}">{{ fb.severity }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </template>
                        </div>
                        <div class="p-4 text-center" v-else>
                            <p class="text-muted mb-0">No open feedback from you right now.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

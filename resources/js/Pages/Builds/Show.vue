<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import * as bootstrap from 'bootstrap';
import QrcodeVue from 'qrcode.vue';

const props = defineProps({ build: Object, users: Array });

const currentUrl = computed(() => {
    return window.location.origin + route('builds.download', props.build.id);
});

const activeTab = ref('overview');

const shareForm = useForm({ expires_at: '', download_limit: '', password: '' });

function submitShare() {
    shareForm.post(route('share-links.store', props.build.id), {
        preserveScroll: true,
        onSuccess: () => {
            shareForm.reset();
            const modalEl = document.getElementById('shareModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        },
    });
}

function deleteShare(id) {
    if (confirm('Are you sure you want to delete this public link?')) {
        router.delete(route('share-links.destroy', id), { preserveScroll: true });
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Share link copied to clipboard!');
    });
}

const editingFeedback = ref(null);
const feedbackForm = useForm({
    title: '', description: '', type: 'Bug', severity: 'Medium', status: 'Open',
    device_model: '', os_version: '', screen_size: ''
});

const editingTask = ref(null);
const taskForm = useForm({
    title: '', description: '', priority: 'Medium', status: 'Todo',
    assignee_id: '', due_date: ''
});

function openFeedbackModal(fb = null) {
    editingFeedback.value = fb;
    if (fb) {
        feedbackForm.title = fb.title;
        feedbackForm.description = fb.description;
        feedbackForm.type = fb.type;
        feedbackForm.severity = fb.severity || 'Medium';
        feedbackForm.status = fb.status || 'Open';
        feedbackForm.device_model = fb.device_model || '';
        feedbackForm.os_version = fb.os_version || '';
        feedbackForm.screen_size = fb.screen_size || '';
    } else {
        feedbackForm.reset();
        feedbackForm.status = 'Open';
    }
    const modalEl = document.getElementById('feedbackModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

function openTaskModal(tk = null) {
    editingTask.value = tk;
    if (tk) {
        taskForm.title = tk.title;
        taskForm.description = tk.description || '';
        taskForm.priority = tk.priority || 'Medium';
        taskForm.status = tk.status || 'Todo';
        taskForm.assignee_id = tk.assignee_id || '';
        taskForm.due_date = tk.due_date || '';
    } else {
        taskForm.reset();
    }
    const modalEl = document.getElementById('taskModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

// Comment Form (now for Tasks & Feedback)
const commentTarget = ref(null);
const commentForm = useForm({ body: '', commentable_id: '', commentable_type: '', attachment: null });

function handleAttachment(e) {
    commentForm.attachment = e.target.files[0];
}

function openCommentBox(item, type) {
    const fullType = 'App\\Models\\' + type;
    if (commentTarget.value?.id === item.id && commentForm.commentable_type === fullType) {
        commentTarget.value = null; // Toggle off
    } else {
        commentTarget.value = item;
        commentForm.commentable_id = item.id;
        commentForm.commentable_type = fullType;
        commentForm.body = '';
        commentForm.attachment = null;
        
        // Reset file input if exists
        const fileInput = document.getElementById('comment-attachment');
        if (fileInput) fileInput.value = '';
    }
}

function submitComment() {
    commentForm.post(route('comments.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            commentForm.reset();
            commentTarget.value = null;
            const fileInput = document.getElementById('comment-attachment');
            if (fileInput) fileInput.value = '';
        },
    });
}

function submitFeedback() {
    const url = editingFeedback.value 
        ? route('feedback.update', editingFeedback.value.id) 
        : route('feedback.store', props.build.id);
    const method = editingFeedback.value ? 'put' : 'post';

    feedbackForm[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            const modalEl = document.getElementById('feedbackModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            feedbackForm.reset();
            editingFeedback.value = null;
        },
    });
}

function deleteFeedback(id) {
    if (confirm('Are you sure you want to delete this feedback?')) {
        router.delete(route('feedback.destroy', id), { preserveScroll: true });
    }
}

function submitTask() {
    const url = editingTask.value 
        ? route('tasks.update', editingTask.value.id) 
        : route('tasks.store', props.build.id);
    const method = editingTask.value ? 'put' : 'post';

    taskForm[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            const modalEl = document.getElementById('taskModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            taskForm.reset();
            editingTask.value = null;
        },
    });
}

function deleteTask(id) {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(route('tasks.destroy', id), { preserveScroll: true });
    }
}

function deleteBuild() {
    if (confirm('CAUTION: Are you sure you want to delete this build? This will permanently remove the record and the physical APK file.')) {
        router.delete(route('builds.destroy', props.build.id));
    }
}

function buildTypeBadge(type) {
    const map = { Production: 'bg-success', RC: 'bg-info text-dark', Beta: 'bg-primary', Alpha: 'bg-warning text-dark' };
    return map[type] ?? 'bg-secondary';
}

function statusBadge(status) {
    const map = { Approved: 'bg-success', Rejected: 'bg-danger', Pending: 'bg-warning text-dark' };
    return map[status] ?? 'bg-secondary';
}

function severityBadge(sev) {
    const map = { Critical: 'bg-danger', High: 'bg-warning text-dark', Medium: 'bg-info text-dark', Low: 'bg-secondary' };
    return map[sev] ?? 'bg-secondary';
}

function feedbackStatusBadge(status) {
    const map = { Open: 'bg-danger', 'In Progress': 'bg-warning text-dark', Resolved: 'bg-success', Closed: 'bg-secondary' };
    return map[status] ?? 'bg-secondary';
}

function taskStatusBadge(status) {
    const map = { Todo: 'bg-secondary', 'In Progress': 'bg-primary', Done: 'bg-success' };
    return map[status] ?? 'bg-secondary';
}

function priorityBadge(priority) {
    const map = { Urgent: 'bg-danger', High: 'bg-warning text-dark', Medium: 'bg-info text-dark', Low: 'bg-secondary' };
    return map[priority] ?? 'bg-secondary';
}

function formatBytes(bytes) {
    if (!bytes) return '—';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return bytes + ' B';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function formatDateShort(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head :title="`${build.project?.name} v${build.version_name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 rounded p-1" style="width:32px; height:32px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                        <img v-if="build.project?.icon_url" :src="'/storage/' + build.project.icon_url" alt="Icon" style="max-width:100%; max-height:100%; object-fit:contain;" />
                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#4F46E5" class="bi bi-android2" viewBox="0 0 16 16">
                            <path d="m10.213 1.471.691-1.216a.104.104 0 0 0-.033-.142.106.106 0 0 0-.142.033l-.705 1.238A8 8 0 0 0 8 1.139a8 8 0 0 0-2.024.26L5.27.161a.105.105 0 0 0-.142-.033.104.104 0 0 0-.033.142l.691 1.216C3.906 2.502 2.146 4.606 1.838 7.21h12.324c-.308-2.604-2.068-4.708-3.949-5.739M5.4 5.378a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0m6.839 0a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0M1.6 8.21h12.8A1.6 1.6 0 0 1 16 9.81v3.2a1.6 1.6 0 0 1-1.6 1.6H1.6A1.6 1.6 0 0 1 0 13.01v-3.2A1.6 1.6 0 0 1 1.6 8.21Z"/>
                        </svg>
                    </div>
                    <Link :href="route('projects.index')" class="text-muted text-decoration-none">Projects</Link>
                    <span class="text-muted">/</span>
                    <Link :href="route('projects.show', build.project?.id)" class="text-muted text-decoration-none">{{ build.project?.name }}</Link>
                    <span class="text-muted">/</span>
                    <h2 class="h5 mb-0 fw-bold">v{{ build.version_name }}</h2>
                    <span class="badge" :class="buildTypeBadge(build.build_type)">{{ build.build_type }}</span>
                    <span class="badge" :class="statusBadge(build.status)">{{ build.status }}</span>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#qrModal">
                        <i class="bi bi-qr-code"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#shareModal">
                        🔗 Share Link
                    </button>
                    <a :href="route('builds.download', build.id)" class="btn btn-sm btn-success">
                        ⬇ Download APK
                    </a>
                    <button v-if="$page.props.auth.user.roles.includes('Admin')" class="btn btn-sm btn-danger" @click="deleteBuild">
                        <i class="bi bi-trash"></i> Delete Build
                    </button>
                </div>
            </div>
        </template>

        <!-- Stats Row -->
        <div class="row g-3 mt-1 mb-4">
            <div class="col-4 col-md-4">
                <div class="card border-0 shadow-sm text-center py-3 cursor-pointer" @click="activeTab='feedback'">
                    <div class="h3 mb-0 text-danger fw-bold">{{ build.feedbacks_count }}</div>
                    <div class="text-muted small">Feedback</div>
                </div>
            </div>
            <div class="col-4 col-md-4">
                <div class="card border-0 shadow-sm text-center py-3 cursor-pointer" @click="activeTab='tasks'">
                    <div class="h3 mb-0 text-warning fw-bold">{{ build.tasks_count }}</div>
                    <div class="text-muted small">Tasks</div>
                </div>
            </div>
            <div class="col-4 col-md-4">
                <div class="card border-0 shadow-sm text-center py-3 cursor-pointer" @click="activeTab='downloads'">
                    <div class="h3 mb-0 text-primary fw-bold">{{ build.downloads_count }}</div>
                    <div class="text-muted small">Downloads</div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-0">
                <ul class="nav nav-tabs border-bottom-0 px-3">
                    <li class="nav-item">
                        <button class="nav-link" :class="{ active: activeTab === 'overview' }" @click="activeTab='overview'">Overview</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" :class="{ active: activeTab === 'feedback' }" @click="activeTab='feedback'">
                            Feedback <span class="badge bg-danger ms-1">{{ build.feedbacks_count }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" :class="{ active: activeTab === 'tasks' }" @click="activeTab='tasks'">
                            Tasks <span class="badge bg-warning text-dark ms-1">{{ build.tasks_count }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" :class="{ active: activeTab === 'downloads' }" @click="activeTab='downloads'">Downloads</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" :class="{ active: activeTab === 'share' }" @click="activeTab='share'">
                            Share Links <span class="badge bg-secondary ms-1">{{ build.share_links?.length || 0 }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">

                <!-- OVERVIEW TAB -->
                <div v-if="activeTab === 'overview'">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3">Build Information</h6>
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" style="width:140px">Package</td>
                                        <td class="font-monospace">{{ build.project?.package_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Version Name</td>
                                        <td class="fw-semibold">{{ build.version_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Version Code</td>
                                        <td>{{ build.version_code }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Build Type</td>
                                        <td><span class="badge" :class="buildTypeBadge(build.build_type)">{{ build.build_type }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Status</td>
                                        <td><span class="badge" :class="statusBadge(build.status)">{{ build.status }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">File Size</td>
                                        <td>{{ formatBytes(build.file_size) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Uploaded By</td>
                                        <td>{{ build.uploader?.name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Uploaded At</td>
                                        <td>{{ formatDate(build.created_at) }}</td>
                                    </tr>
                                    <tr v-if="build.approved_by">
                                        <td class="text-muted">{{ build.status }} By</td>
                                        <td>{{ build.approver?.name ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3">Release Notes</h6>
                            <div v-if="build.release_notes" class="bg-light rounded p-3" style="white-space:pre-wrap">{{ build.release_notes }}</div>
                            <p v-else class="text-muted fst-italic">No release notes provided.</p>


                        </div>
                    </div>
                </div>

                <!-- FEEDBACK TAB -->
                <div v-if="activeTab === 'feedback'">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold">Feedback Reports</h6>
                        <button class="btn btn-sm btn-danger" @click="openFeedbackModal()">Report Bug / Feedback</button>
                    </div>

                    <div v-if="build.feedbacks.length === 0" class="text-center py-5">
                        <div class="display-4 text-muted">🐛</div>
                        <p class="text-muted mt-2">No feedback submitted yet.</p>
                    </div>
                    <div v-else class="list-group list-group-flush">
                        <div v-for="fb in build.feedbacks" :key="fb.id" class="list-group-item px-0 py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ fb.title }}</strong>
                                    <div class="mt-1 d-flex gap-1">
                                        <span class="badge bg-secondary">{{ fb.type }}</span>
                                        <span v-if="fb.severity" class="badge" :class="severityBadge(fb.severity)">{{ fb.severity }}</span>
                                        <span class="badge" :class="feedbackStatusBadge(fb.status)">{{ fb.status }}</span>
                                    </div>
                                    <p class="text-muted small mt-2 mb-1">{{ fb.description }}</p>
                                    <div v-if="fb.device_model || fb.os_version" class="text-muted small">
                                        📱 {{ fb.device_model }} {{ fb.os_version }}
                                    </div>
                                </div>
                                <div class="text-end text-muted small text-nowrap ms-3 d-flex flex-column align-items-end gap-1">
                                    <div>{{ fb.author?.name ?? '—' }}</div>
                                    <div>{{ formatDateShort(fb.created_at) }}</div>
                                    <div class="mt-1">
                                        <button class="btn btn-sm btn-link text-decoration-none p-0 me-3" @click="openCommentBox(fb, 'Feedback')">Comments ({{ fb.comments?.length || 0 }})</button>
                                        <button class="btn btn-sm btn-link text-decoration-none p-0 me-2" @click="openFeedbackModal(fb)">Edit</button>
                                        <button class="btn btn-sm btn-link text-danger text-decoration-none p-0" @click="deleteFeedback(fb.id)">Delete</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Feedback Comments Section -->
                            <div v-if="commentTarget?.id === fb.id && commentForm.commentable_type === 'App\\Models\\Feedback'" class="mt-3 bg-light rounded p-3 border">
                                <h6 class="fw-bold small mb-3">Comments on {{ fb.title }}</h6>
                                
                                <div v-if="fb.comments && fb.comments.length > 0" class="mb-3">
                                    <div v-for="comment in fb.comments" :key="comment.id" class="d-flex gap-2 mb-2 pb-2 border-bottom">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:28px;height:28px;font-size:12px;font-weight:600;">
                                            {{ (comment.author?.name ?? 'U').charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong class="small">{{ comment.author?.name ?? 'Unknown' }}</strong>
                                                <small class="text-muted" style="font-size:11px;">{{ formatDate(comment.created_at) }}</small>
                                            </div>
                                            <p class="mb-0 small mt-1">{{ comment.body }}</p>
                                            <div v-if="comment.attachment_path" class="mt-1">
                                                <a :href="'/storage/' + comment.attachment_path" target="_blank" class="badge bg-secondary text-decoration-none">📎 {{ comment.attachment_name }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-muted small mb-3 fst-italic" v-else>No comments yet.</div>
                                
                                <form @submit.prevent="submitComment">
                                    <div class="mb-2">
                                        <textarea v-model="commentForm.body" class="form-control bg-white form-control-sm" rows="2" placeholder="Write a comment..." required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <input type="file" id="comment-attachment" @change="handleAttachment" class="form-control form-control-sm w-50" accept="image/*,.pdf,.doc,.docx,.txt">
                                        <button class="btn btn-sm btn-primary" type="submit" :disabled="commentForm.processing">Post Comment</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- TASKS TAB -->
                <div v-if="activeTab === 'tasks'">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold">Assigned Tasks</h6>
                        <button class="btn btn-sm btn-warning text-dark" @click="openTaskModal()">Create Task</button>
                    </div>

                    <div v-if="build.tasks.length === 0" class="text-center py-5">
                        <div class="display-4 text-muted">✅</div>
                        <p class="text-muted mt-2">No tasks created for this build.</p>
                    </div>
                    <div v-else class="list-group list-group-flush">
                        <div v-for="task in build.tasks" :key="task.id" class="list-group-item px-0 py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ task.title }}</strong>
                                    <div class="mt-1 d-flex gap-1">
                                        <span class="badge" :class="taskStatusBadge(task.status)">{{ task.status }}</span>
                                        <span class="badge" :class="priorityBadge(task.priority)">{{ task.priority }}</span>
                                    </div>
                                    <p v-if="task.description" class="text-muted small mt-2 mb-0">{{ task.description }}</p>
                                </div>
                                <div class="text-end text-muted small text-nowrap ms-3 d-flex flex-column align-items-end gap-1">
                                    <div>{{ task.assignee?.name ?? 'Unassigned' }}</div>
                                    <div v-if="task.due_date" class="text-danger">Due: {{ formatDateShort(task.due_date) }}</div>
                                    <div class="mt-1">
                                        <button class="btn btn-sm btn-link text-decoration-none p-0 me-3" @click="openCommentBox(task, 'Task')">Comments ({{ task.comments?.length || 0 }})</button>
                                        <button class="btn btn-sm btn-link text-decoration-none p-0 me-2" @click="openTaskModal(task)">Edit</button>
                                        <button class="btn btn-sm btn-link text-danger text-decoration-none p-0" @click="deleteTask(task.id)">Delete</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Task Comments Section -->
                            <div v-if="commentTarget?.id === task.id && commentForm.commentable_type === 'App\\Models\\Task'" class="mt-3 bg-light rounded p-3 border">
                                <h6 class="fw-bold small mb-3">Comments on {{ task.title }}</h6>
                                
                                <div v-if="task.comments && task.comments.length > 0" class="mb-3">
                                    <div v-for="comment in task.comments" :key="comment.id" class="d-flex gap-2 mb-2 pb-2 border-bottom">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:28px;height:28px;font-size:12px;font-weight:600;">
                                            {{ (comment.author?.name ?? 'U').charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong class="small">{{ comment.author?.name ?? 'Unknown' }}</strong>
                                                <small class="text-muted" style="font-size:11px;">{{ formatDate(comment.created_at) }}</small>
                                            </div>
                                            <p class="mb-0 small mt-1">{{ comment.body }}</p>
                                            <div v-if="comment.attachment_path" class="mt-1">
                                                <a :href="'/storage/' + comment.attachment_path" target="_blank" class="badge bg-secondary text-decoration-none">📎 {{ comment.attachment_name }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-muted small mb-3 fst-italic" v-else>No comments yet.</div>
                                
                                <form @submit.prevent="submitComment">
                                    <div class="mb-2">
                                        <textarea v-model="commentForm.body" class="form-control bg-white form-control-sm" rows="2" placeholder="Write a comment..." required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <input type="file" id="comment-attachment" @change="handleAttachment" class="form-control form-control-sm w-50" accept="image/*,.pdf,.doc,.docx,.txt">
                                        <button class="btn btn-sm btn-primary" type="submit" :disabled="commentForm.processing">Post Comment</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- SHARE LINKS TAB -->
                <div v-if="activeTab === 'share'">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold">Active Public Links</h6>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#shareModal">New Share Link</button>
                    </div>

                    <div v-if="!build.share_links || build.share_links.length === 0" class="text-center py-5">
                        <div class="display-4 text-muted">🔗</div>
                        <p class="text-muted mt-2">No public share links created yet.</p>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Token / Link</th>
                                    <th>Status</th>
                                    <th>Security</th>
                                    <th>Downloads</th>
                                    <th>Expires</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="link in build.share_links" :key="link.id">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <code class="p-1 bg-light rounded">{{ link.token }}</code>
                                            <button class="btn btn-sm btn-link p-0" title="Copy URL" @click="copyToClipboard(route('public.share.show', link.token))">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <span v-if="new Date(link.expires_at) < new Date() && link.expires_at" class="badge bg-danger">Expired</span>
                                        <span v-else-if="link.download_limit && link.download_count >= link.download_limit" class="badge bg-warning text-dark">Limit Reached</span>
                                        <span v-else class="badge bg-success">Active</span>
                                    </td>
                                    <td>
                                        <span v-if="link.password" class="badge bg-dark" title="Password Protected">
                                            <i class="bi bi-lock-fill"></i> Locked
                                        </span>
                                        <span v-else class="text-muted small">Public</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ link.download_count }}</span>
                                        <span class="text-muted small" v-if="link.download_limit"> / {{ link.download_limit }}</span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ link.expires_at ? formatDate(link.expires_at) : 'Never' }}
                                    </td>
                                    <td class="text-end">
                                        <Link :href="route('public.share.show', link.token)" target="_blank" class="btn btn-sm btn-outline-secondary me-2">Visit</Link>
                                        <button class="btn btn-sm btn-outline-danger" @click="deleteShare(link.id)">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- DOWNLOADS TAB -->
                <div v-if="activeTab === 'downloads'">
                    <div v-if="build.downloads.length === 0" class="text-center py-5">
                        <div class="display-4 text-muted">📥</div>
                        <p class="text-muted mt-2">No downloads recorded yet.</p>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Source / User</th>
                                    <th>IP Address</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="dl in build.downloads" :key="dl.id">
                                    <td>
                                        <div v-if="dl.share_link">
                                            <span class="badge bg-info text-dark me-2">Public Link</span>
                                            <code class="small">{{ dl.share_link.token }}</code>
                                        </div>
                                        <div v-else>
                                            <span class="badge bg-secondary me-2">Portal</span>
                                            {{ dl.user?.name ?? 'Anonymous' }}
                                        </div>
                                    </td>
                                    <td class="text-muted font-monospace small">{{ dl.ip_address }}</td>
                                    <td class="text-muted small">{{ formatDate(dl.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>



    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form @submit.prevent="submitFeedback">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editingFeedback ? 'Edit Feedback' : 'Submit Feedback' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input v-model="feedbackForm.title" type="text" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type</label>
                                <select v-model="feedbackForm.type" class="form-select">
                                    <option>Bug</option><option>Feature</option><option>Improvement</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Severity</label>
                                <select v-model="feedbackForm.severity" class="form-select">
                                    <option>Critical</option><option>High</option><option>Medium</option><option>Low</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select v-model="feedbackForm.status" class="form-select">
                                    <option>Open</option><option>In Progress</option><option>Resolved</option><option>Closed</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label">Device Model (optional)</label>
                                <input v-model="feedbackForm.device_model" type="text" class="form-control" placeholder="e.g. Pixel 7">
                            </div>
                            <div class="col-4">
                                <label class="form-label">OS Version (optional)</label>
                                <input v-model="feedbackForm.os_version" type="text" class="form-control" placeholder="e.g. Android 14">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea v-model="feedbackForm.description" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" :disabled="feedbackForm.processing">{{ editingFeedback ? 'Save Changes' : 'Submit Feedback' }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Task Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog">
            <form @submit.prevent="submitTask">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editingTask ? 'Edit Task' : 'Create Task' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-12">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input v-model="taskForm.title" type="text" class="form-control" required>
                        </div>
                        <div class="col-8">
                            <label class="form-label">Assignee</label>
                            <select v-model="taskForm.assignee_id" class="form-select">
                                <option value="">Unassigned</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Priority</label>
                            <select v-model="taskForm.priority" class="form-select">
                                <option>Low</option><option>Medium</option><option>High</option><option>Urgent</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select v-model="taskForm.status" class="form-select">
                                <option>Todo</option><option>In Progress</option><option>Done</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Due Date (optional)</label>
                            <input v-model="taskForm.due_date" type="date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea v-model="taskForm.description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold" :disabled="taskForm.processing">{{ editingTask ? 'Save Changes' : 'Create Task' }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Share Link Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1">
        <div class="modal-dialog">
            <form @submit.prevent="submitShare">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">🔗 Create Public Share Link</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-12">
                            <p class="text-muted small mb-0">Anyone with this link will be able to download the APK without logging in.</p>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Download Limit</label>
                            <input v-model="shareForm.download_limit" type="number" class="form-control" placeholder="Optional (e.g. 50)">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Expiry Date</label>
                            <input v-model="shareForm.expires_at" type="datetime-local" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password Protection (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                <input v-model="shareForm.password" type="text" class="form-control" placeholder="Leave blank for public access">
                            </div>
                            <small class="text-muted">If set, testers must enter this password to view the download page.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="shareForm.processing">Generate Link</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center pt-0">
                    <h5 class="fw-bold mb-3">Scan to Download</h5>
                    <QrcodeVue :value="currentUrl" :size="220" level="H" class="mx-auto" />
                    <p class="text-muted small mt-3 mb-0">Direct link to version v{{ build.version_name }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

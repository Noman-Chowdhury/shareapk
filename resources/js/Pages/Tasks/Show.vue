<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import * as bootstrap from 'bootstrap';

const props = defineProps({
    task: Object,
    users: Array,
});

const editingTask = ref(null);
const taskNewFiles = ref([]);
const taskKeepAttachments = ref([]);

const taskForm = useForm({
    title: '', description: '', priority: 'Medium', status: 'Todo',
    assignee_id: '', due_date: ''
});

function openTaskModal() {
    editingTask.value = props.task;
    taskForm.title = props.task.title;
    taskForm.description = props.task.description || '';
    taskForm.priority = props.task.priority || 'Medium';
    taskForm.status = props.task.status || 'Todo';
    taskForm.assignee_id = props.task.assignee_id || '';
    taskForm.due_date = props.task.due_date || '';
    taskNewFiles.value = [];
    taskKeepAttachments.value = (props.task.attachments || []).map(a => a.path);
    
    const modalEl = document.getElementById('taskModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

function handleTaskFiles(e) {
    taskNewFiles.value = Array.from(e.target.files);
}

function removeTaskKeepAttachment(path) {
    taskKeepAttachments.value = taskKeepAttachments.value.filter(p => p !== path);
}

function submitTask() {
    const url = route('tasks.update', props.task.id);
    const payload = taskForm.data();
    
    // Attach files
    if (taskNewFiles.value.length) {
        taskNewFiles.value.forEach((file, i) => {
            payload[`new_attachments[${i}]`] = file;
        });
    }

    taskKeepAttachments.value.forEach((path, i) => {
        payload[`keep_attachments[${i}]`] = path;
    });
    
    payload['_method'] = 'PUT';

    taskForm.transform(() => payload).post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            const modalEl = document.getElementById('taskModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            taskForm.reset();
            taskNewFiles.value = [];
            editingTask.value = null;
        },
    });
}

const commentForm = useForm({ body: '', commentable_id: props.task.id, commentable_type: 'App\\Models\\Task', attachment: null });

function handleCommentAttachment(e) {
    commentForm.attachment = e.target.files[0];
}

function submitComment() {
    commentForm.post(route('comments.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            commentForm.reset();
            const fileInput = document.getElementById('comment-attachment');
            if (fileInput) fileInput.value = '';
        },
    });
}

function taskStatusBadge(status) {
    const map = { Todo: 'bg-secondary', 'In Progress': 'bg-primary', Done: 'bg-success' };
    return map[status] ?? 'bg-secondary';
}

function priorityBadge(priority) {
    const map = { Urgent: 'bg-danger', High: 'bg-warning text-dark', Medium: 'bg-info text-dark', Low: 'bg-secondary' };
    return map[priority] ?? 'bg-secondary';
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString();
}
</script>

<template>
    <Head :title="'Task: ' + task.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><Link :href="route('tasks.index')">Global Tasks</Link></li>
                        <li class="breadcrumb-item active">Task #{{ task.id }}</li>
                    </ol>
                </nav>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-warning text-dark fw-bold" @click="openTaskModal">Edit Task</button>
                </div>
            </div>
        </template>

        <div class="row pt-4">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h2 class="h4 fw-bold mb-0 text-dark">{{ task.title }}</h2>
                            <span class="badge" :class="taskStatusBadge(task.status)">{{ task.status }}</span>
                        </div>
                        
                        <div class="d-flex gap-2 mb-4">
                            <span class="badge" :class="priorityBadge(task.priority)">{{ task.priority }} Priority</span>
                            <span v-if="task.due_date" class="badge bg-danger">Due: {{ (new Date(task.due_date)).toLocaleDateString() }}</span>
                        </div>

                        <div v-if="task.description" class="bg-light p-3 rounded mb-4" style="white-space: pre-wrap;">{{ task.description }}</div>
                        <div v-else class="text-muted fst-italic mb-4">No description provided.</div>

                        <div v-if="task.attachments && task.attachments.length" class="mb-4">
                            <h6 class="fw-bold small mb-2 text-uppercase text-muted">Attachments</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <a v-for="att in task.attachments" :key="att.path"
                                   :href="'/storage/' + att.path" target="_blank"
                                   class="btn btn-sm btn-light border d-inline-flex align-items-center gap-2">
                                    📎 {{ att.name }} <small class="text-muted">({{ (att.size / 1024).toFixed(0) }} KB)</small>
                                </a>
                            </div>
                        </div>

                        <hr>

                        <!-- Comments Section -->
                        <h5 class="fw-bold mb-4">Progress & Comments</h5>
                        <div v-if="task.comments && task.comments.length" class="mb-4">
                            <div v-for="comment in task.comments" :key="comment.id" class="d-flex gap-3 mb-4 pb-3 border-bottom">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;font-size:14px;font-weight:600;">
                                    {{ (comment.author?.name ?? 'U').charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark">{{ comment.author?.name ?? 'Unknown' }}</strong>
                                        <small class="text-muted">{{ formatDate(comment.created_at) }}</small>
                                    </div>
                                    <p class="mb-2 text-dark">{{ comment.body }}</p>
                                    <div v-if="comment.attachment_path" class="mt-2 text-start">
                                        <a :href="'/storage/' + comment.attachment_path" target="_blank" class="badge bg-secondary text-decoration-none py-2 px-3">📎 {{ comment.attachment_name }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-muted py-3 text-center mb-4 italic">No updates posted yet.</div>

                        <!-- Add Comment -->
                        <form @submit.prevent="submitComment" class="bg-light p-3 rounded border">
                            <h6 class="fw-bold mb-2">Post an update</h6>
                            <div class="mb-3">
                                <textarea v-model="commentForm.body" class="form-control bg-white" rows="3" placeholder="Share progress or ask a question..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <input type="file" id="comment-attachment" @change="handleCommentAttachment" class="form-control form-control-sm w-50" accept="image/*,.pdf,.doc,.docx,.txt,zip">
                                <button type="submit" class="btn btn-primary btn-sm" :disabled="commentForm.processing">Post Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 fw-bold">Task Info</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                <tr class="border-bottom">
                                    <th class="ps-3 py-2 text-muted fw-normal">Project</th>
                                    <td class="pe-3 py-2 text-end fw-semibold">
                                        <Link v-if="task.build?.id" :href="route('builds.show', task.build.id)" class="text-decoration-none">
                                            {{ task.build?.project?.name ?? 'Unknown' }}
                                        </Link>
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="ps-3 py-2 text-muted fw-normal">Build Context</th>
                                    <td class="pe-3 py-2 text-end">v{{ task.build?.version_name }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="ps-3 py-2 text-muted fw-normal">Creator</th>
                                    <td class="pe-3 py-2 text-end">{{ task.creator?.name || 'System' }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="ps-3 py-2 text-muted fw-normal">Assignee</th>
                                    <td class="pe-3 py-2 text-end fw-bold text-primary">{{ task.assignee?.name || 'Unassigned' }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="ps-3 py-2 text-muted fw-normal">Created At</th>
                                    <td class="pe-3 py-2 text-end small">{{ formatDate(task.created_at) }}</td>
                                </tr>
                                <tr class="border-bottom" v-if="task.due_date">
                                    <th class="ps-3 py-2 text-muted fw-normal">Deadline</th>
                                    <td class="pe-3 py-2 text-end text-danger fw-bold">{{ (new Date(task.due_date)).toLocaleDateString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Task Modal -->
        <div class="modal fade" id="taskModal" tabindex="-1">
            <div class="modal-dialog">
                <form @submit.prevent="submitTask">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Task</h5>
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
                            <div class="col-6">
                                <label class="form-label">Due Date (optional)</label>
                                <input v-model="taskForm.due_date" type="date" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea v-model="taskForm.description" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <!-- Existing Task Attachments -->
                            <div class="col-12" v-if="taskKeepAttachments.length">
                                <label class="form-label">Existing Attachments</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <div v-for="att in task.attachments" :key="att.path"
                                         class="d-flex align-items-center gap-1 border rounded px-2 py-1 small"
                                         :class="taskKeepAttachments.includes(att.path) ? '' : 'text-decoration-line-through text-muted opacity-50'">
                                        <span class="text-truncate" style="max-width:150px;">📎 {{ att.name }}</span>
                                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-danger"
                                                @click="removeTaskKeepAttachment(att.path)"
                                                v-if="taskKeepAttachments.includes(att.path)"
                                                title="Remove">✕</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- New Task Attachments -->
                            <div class="col-12">
                                <label class="form-label">Add More Attachments</label>
                                <input type="file" class="form-control" multiple
                                       accept="image/*,.pdf,.doc,.docx,.txt,.zip"
                                       @change="handleTaskFiles">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning text-dark fw-bold" :disabled="taskForm.processing">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

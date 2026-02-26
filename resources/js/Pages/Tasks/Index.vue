<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import * as bootstrap from 'bootstrap';

const props = defineProps({
    tasks: Array,
    users: Array,
});

const editingTask = ref(null);
const taskAttachmentsInput = ref(null);
const taskNewFiles = ref([]);
const taskKeepAttachments = ref([]);

const taskForm = useForm({
    title: '', description: '', priority: 'Medium', status: 'Todo',
    assignee_id: '', due_date: ''
});

function openTaskModal(tk) {
    editingTask.value = tk;
    taskForm.title = tk.title;
    taskForm.description = tk.description || '';
    taskForm.priority = tk.priority || 'Medium';
    taskForm.status = tk.status || 'Todo';
    taskForm.assignee_id = tk.assignee_id || '';
    taskForm.due_date = tk.due_date || '';
    taskNewFiles.value = [];
    taskKeepAttachments.value = (tk.attachments || []).map(a => a.path);
    
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
    const url = route('tasks.update', editingTask.value.id);
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
            if (taskAttachmentsInput.value) taskAttachmentsInput.value.value = '';
        },
    });
}

function deleteTask(id) {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(route('tasks.destroy', id), { preserveScroll: true });
    }
}

function taskStatusBadge(status) {
    const map = { Todo: 'bg-secondary', 'In Progress': 'bg-primary', Done: 'bg-success' };
    return map[status] ?? 'bg-secondary';
}

function priorityBadge(priority) {
    const map = { Urgent: 'bg-danger', High: 'bg-warning text-dark', Medium: 'bg-info text-dark', Low: 'bg-secondary' };
    return map[priority] ?? 'bg-secondary';
}
const searchQuery = ref('');
const statusFilter = ref('All');

const filteredTasks = computed(() => {
    return props.tasks.filter(tk => {
        const matchesSearch = tk.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                             tk.build?.project?.name?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = statusFilter.value === 'All' || tk.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });
});
</script>

<template>
    <Head title="Global Tasks" />
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h2 class="h4 mb-0 fw-bold">All Tasks</h2>
                <div class="d-flex gap-2">
                    <input v-model="searchQuery" type="text" class="form-control form-control-sm" placeholder="Search tasks or project..." style="width: 250px;">
                    <select v-model="statusFilter" class="form-select form-select-sm" style="width: 150px;">
                        <option>All</option>
                        <option>Todo</option>
                        <option>In Progress</option>
                        <option>Done</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="card border-0 shadow-sm mt-1">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Title / Project</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assignee</th>
                                <th>Attachments</th>
                                <th>Due Date</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="task in filteredTasks" :key="task.id">
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ task.title }}</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="bg-light rounded p-1" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                            <img v-if="task.build?.project?.icon_url" :src="'/storage/' + task.build.project.icon_url" style="max-width:100%;max-height:100%;object-fit:contain;" />
                                            <i v-else class="bi bi-box text-muted" style="font-size: 10px;"></i>
                                        </div>
                                        <Link v-if="task.build?.id" :href="route('builds.show', task.build.id)" class="text-decoration-none small">
                                            {{ task.build?.project?.name || 'Unknown Project' }} v{{ task.build?.version_name }}
                                        </Link>
                                        <span v-else class="text-muted small">Unknown Project</span>
                                    </div>
                                </td>
                                <td><span class="badge" :class="taskStatusBadge(task.status)">{{ task.status }}</span></td>
                                <td><span class="badge" :class="priorityBadge(task.priority)">{{ task.priority }}</span></td>
                                <td>{{ task.assignee?.name || 'Unassigned' }}</td>
                                <td>
                                    <div v-if="task.attachments && task.attachments.length" class="d-flex flex-wrap gap-1">
                                        <a v-for="att in task.attachments" :key="att.path"
                                           :href="'/storage/' + att.path" target="_blank"
                                           class="badge bg-light text-dark border text-decoration-none small">
                                            📎 {{ att.name }}
                                        </a>
                                    </div>
                                    <span v-else class="text-muted small">—</span>
                                </td>
                                <td class="text-muted small">
                                    <span :class="{'text-danger': task.due_date}">
                                        {{ task.due_date ? new Date(task.due_date).toLocaleDateString() : '—' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-secondary me-2" @click="openTaskModal(task)">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" @click="deleteTask(task.id)">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="!filteredTasks.length">
                                <td colspan="7" class="text-center py-4 text-muted">No tasks have been created globally.</td>
                            </tr>
                        </tbody>
                    </table>
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
                            <div class="col-12" v-if="editingTask && editingTask.attachments && editingTask.attachments.length">
                                <label class="form-label">Existing Attachments</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <div v-for="att in editingTask.attachments" :key="att.path"
                                         class="d-flex align-items-center gap-1 border rounded px-2 py-1 small"
                                         :class="taskKeepAttachments.includes(att.path) ? '' : 'text-decoration-line-through text-muted opacity-50'">
                                        <a :href="'/storage/' + att.path" target="_blank" class="text-truncate" style="max-width:150px;">📎 {{ att.name }}</a>
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
                                <input ref="taskAttachmentsInput" type="file" class="form-control" multiple
                                       accept="image/*,.pdf,.doc,.docx,.txt,.zip"
                                       @change="handleTaskFiles">
                                <div v-if="taskNewFiles.length" class="text-muted small mt-1">
                                    {{ taskNewFiles.length }} file(s) selected
                                </div>
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

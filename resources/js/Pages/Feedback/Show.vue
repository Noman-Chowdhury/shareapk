<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import * as bootstrap from 'bootstrap';

const props = defineProps({
    feedback: Object,
    users: Array,
});

const editingFeedback = ref(null);
const feedbackAttachmentsInput = ref(null);
const feedbackNewFiles = ref([]);
const feedbackKeepAttachments = ref([]);

const feedbackForm = useForm({
    title: '', description: '', type: 'Bug', severity: 'Medium', status: 'Open',
    device_model: '', os_version: '', screen_size: '', assignee_id: '',
});

function openFeedbackModal() {
    editingFeedback.value = props.feedback;
    feedbackForm.title = props.feedback.title;
    feedbackForm.description = props.feedback.description;
    feedbackForm.type = props.feedback.type;
    feedbackForm.severity = props.feedback.severity || 'Medium';
    feedbackForm.status = props.feedback.status || 'Open';
    feedbackForm.device_model = props.feedback.device_model || '';
    feedbackForm.os_version = props.feedback.os_version || '';
    feedbackForm.screen_size = props.feedback.screen_size || '';
    feedbackForm.assignee_id = props.feedback.assignee_id || '';
    feedbackNewFiles.value = [];
    feedbackKeepAttachments.value = (props.feedback.attachments || []).map(a => a.path);

    const modalEl = document.getElementById('feedbackModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

function handleFeedbackFiles(e) {
    feedbackNewFiles.value = Array.from(e.target.files);
}

function removeKeepAttachment(path) {
    feedbackKeepAttachments.value = feedbackKeepAttachments.value.filter(p => p !== path);
}

function submitFeedback() {
    const url = route('feedback.update', props.feedback.id);
    const payload = feedbackForm.data();

    feedbackNewFiles.value.forEach((file, i) => {
        payload[`new_attachments[${i}]`] = file;
    });
    feedbackKeepAttachments.value.forEach((path, i) => {
        payload[`keep_attachments[${i}]`] = path;
    });
    payload['_method'] = 'PUT';

    feedbackForm.transform(() => payload).post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('feedbackModal'));
            if (modal) modal.hide();
            feedbackForm.reset();
            feedbackNewFiles.value = [];
            editingFeedback.value = null;
        },
    });
}

const commentForm = useForm({ body: '', commentable_id: props.feedback.id, commentable_type: 'App\\Models\\Feedback', attachment: null });

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

function severityBadge(sev) {
    const map = { Critical: 'bg-danger', High: 'bg-warning text-dark', Medium: 'bg-info text-dark', Low: 'bg-secondary' };
    return map[sev] ?? 'bg-secondary';
}

function feedbackStatusBadge(status) {
    const map = { Open: 'bg-danger', 'In Progress': 'bg-warning text-dark', Resolved: 'bg-success', Closed: 'bg-secondary' };
    return map[status] ?? 'bg-secondary';
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString();
}
</script>

<template>
    <Head :title="'Feedback: ' + feedback.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><Link :href="route('feedback.index')">Global Feedback</Link></li>
                        <li class="breadcrumb-item active">Feedback #{{ feedback.id }}</li>
                    </ol>
                </nav>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" @click="openFeedbackModal">Edit Feedback</button>
                </div>
            </div>
        </template>

        <div class="row pt-4">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h2 class="h4 fw-bold mb-0 text-dark">{{ feedback.title }}</h2>
                            <span class="badge" :class="feedbackStatusBadge(feedback.status)">{{ feedback.status }}</span>
                        </div>
                        
                        <div class="d-flex gap-2 mb-4">
                            <span class="badge bg-secondary">{{ feedback.type }}</span>
                            <span class="badge" :class="severityBadge(feedback.severity)">{{ feedback.severity || 'Medium' }}</span>
                        </div>

                        <div class="bg-light p-3 rounded mb-4" style="white-space: pre-wrap;">{{ feedback.description }}</div>

                        <div v-if="feedback.attachments && feedback.attachments.length" class="mb-4">
                            <h6 class="fw-bold small mb-2 text-uppercase text-muted">Attachments</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <a v-for="att in feedback.attachments" :key="att.path"
                                   :href="'/storage/' + att.path" target="_blank"
                                   class="btn btn-sm btn-light border d-inline-flex align-items-center gap-2">
                                    📎 {{ att.name }} <small class="text-muted">({{ (att.size / 1024).toFixed(0) }} KB)</small>
                                </a>
                            </div>
                        </div>

                        <hr>

                        <!-- Comments Section -->
                        <h5 class="fw-bold mb-4">Discussion</h5>
                        <div v-if="feedback.comments && feedback.comments.length" class="mb-4">
                            <div v-for="comment in feedback.comments" :key="comment.id" class="d-flex gap-3 mb-4 pb-3 border-bottom">
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
                        <div v-else class="text-muted py-3 text-center mb-4 italic">No comments yet. Be the first to start the discussion!</div>

                        <!-- Add Comment -->
                        <form @submit.prevent="submitComment" class="bg-light p-3 rounded border">
                            <h6 class="fw-bold mb-2">Post a comment</h6>
                            <div class="mb-3">
                                <textarea v-model="commentForm.body" class="form-control bg-white" rows="3" placeholder="Write your thoughts..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <input type="file" id="comment-attachment" @change="handleCommentAttachment" class="form-control form-control-sm w-50" accept="image/*,.pdf,.doc,.docx,.txt,zip">
                                <button type="submit" class="btn btn-primary btn-sm" :disabled="commentForm.processing">Post Comment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 fw-bold">Metadata</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                <tr class="border-bottom">
                                    <th class="ps-3 py-2 text-muted fw-normal">Project</th>
                                    <td class="pe-3 py-2 text-end fw-semibold">
                                        <Link v-if="feedback.build?.id" :href="route('builds.show', feedback.build.id)" class="text-decoration-none">
                                            {{ feedback.build?.project?.name ?? 'Unknown' }}
                                        </Link>
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="ps-3 py-2 text-muted fw-normal">Build</th>
                                    <td class="pe-3 py-2 text-end">v{{ feedback.build?.version_name }} ({{ feedback.build?.version_code }})</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="ps-3 py-2 text-muted fw-normal">Reported By</th>
                                    <td class="pe-3 py-2 text-end">{{ feedback.author?.name || 'Unknown' }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="ps-3 py-2 text-muted fw-normal">Assignee</th>
                                    <td class="pe-3 py-2 text-end fw-bold text-primary">{{ feedback.assignee?.name || 'Unassigned' }}</td>
                                </tr>
                                <tr class="border-bottom" v-if="feedback.device_model">
                                    <th class="ps-3 py-2 text-muted fw-normal">Device</th>
                                    <td class="pe-3 py-2 text-end">{{ feedback.device_model }}</td>
                                </tr>
                                <tr class="border-bottom" v-if="feedback.os_version">
                                    <th class="ps-3 py-2 text-muted fw-normal">OS Version</th>
                                    <td class="pe-3 py-2 text-end">{{ feedback.os_version }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="ps-3 py-2 text-muted fw-normal">Created</th>
                                    <td class="pe-3 py-2 text-end">{{ formatDate(feedback.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Feedback Modal -->
        <div class="modal fade" id="feedbackModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form @submit.prevent="submitFeedback">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Feedback</h5>
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
                                        <option>Bug</option><option>Feature</option><option>Improvement</option><option>Suggestion</option><option>Other</option>
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
                                <div class="col-md-4">
                                    <label class="form-label">Assign To</label>
                                    <select v-model="feedbackForm.assignee_id" class="form-select">
                                        <option value="">— Unassigned —</option>
                                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea v-model="feedbackForm.description" class="form-control" rows="4" required></textarea>
                                </div>
                                <!-- Existing Attachments -->
                                <div class="col-12" v-if="feedbackKeepAttachments.length">
                                    <label class="form-label">Existing Attachments</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <div v-for="att in feedback.attachments" :key="att.path"
                                             class="d-flex align-items-center gap-1 border rounded px-2 py-1 small"
                                             :class="feedbackKeepAttachments.includes(att.path) ? '' : 'text-decoration-line-through text-muted opacity-50'">
                                            <span class="text-truncate" style="max-width:150px;">📎 {{ att.name }}</span>
                                            <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-danger"
                                                    @click="removeKeepAttachment(att.path)"
                                                    v-if="feedbackKeepAttachments.includes(att.path)"
                                                    title="Remove">✕</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- New Attachments -->
                                <div class="col-12">
                                    <label class="form-label">Add More Attachments</label>
                                    <input type="file" class="form-control" multiple
                                           accept="image/*,.pdf,.doc,.docx,.txt,.zip"
                                           @change="handleFeedbackFiles">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" :disabled="feedbackForm.processing">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

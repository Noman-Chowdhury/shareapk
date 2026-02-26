<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import * as bootstrap from 'bootstrap';

const props = defineProps({
    feedbacks: Array
});

const editingFeedback = ref(null);
const feedbackForm = useForm({
    title: '', description: '', type: 'Bug', severity: 'Medium', status: 'Open',
    device_model: '', os_version: '', screen_size: ''
});

function openFeedbackModal(fb) {
    editingFeedback.value = fb;
    feedbackForm.title = fb.title;
    feedbackForm.description = fb.description;
    feedbackForm.type = fb.type;
    feedbackForm.severity = fb.severity || 'Medium';
    feedbackForm.status = fb.status || 'Open';
    feedbackForm.device_model = fb.device_model || '';
    feedbackForm.os_version = fb.os_version || '';
    feedbackForm.screen_size = fb.screen_size || '';
    
    const modalEl = document.getElementById('feedbackModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

function submitFeedback() {
    const url = route('feedback.update', editingFeedback.value.id);
    feedbackForm.put(url, {
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

function severityBadge(sev) {
    const map = { Critical: 'bg-danger', High: 'bg-warning text-dark', Medium: 'bg-info text-dark', Low: 'bg-secondary' };
    return map[sev] ?? 'bg-secondary';
}

function feedbackStatusBadge(status) {
    const map = { Open: 'bg-danger', 'In Progress': 'bg-warning text-dark', Resolved: 'bg-success', Closed: 'bg-secondary' };
    return map[status] ?? 'bg-secondary';
}

const searchQuery = ref('');
const statusFilter = ref('All');

const filteredFeedbacks = computed(() => {
    return props.feedbacks.filter(fb => {
        const matchesSearch = fb.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                             fb.build?.project?.name?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = statusFilter.value === 'All' || fb.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });
});
</script>

<template>
    <Head title="Global Feedback" />
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h2 class="h4 mb-0 fw-bold">All Feedback & Bug Reports</h2>
                <div class="d-flex gap-2">
                    <input v-model="searchQuery" type="text" class="form-control form-control-sm" placeholder="Search feedback or project..." style="width: 250px;">
                    <select v-model="statusFilter" class="form-select form-select-sm" style="width: 150px;">
                        <option>All</option>
                        <option>Open</option>
                        <option>In Progress</option>
                        <option>Resolved</option>
                        <option>Closed</option>
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
                                <th>Severity</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="fb in filteredFeedbacks" :key="fb.id">
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ fb.title }}</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="bg-light rounded p-1" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                            <img v-if="fb.build?.project?.icon_url" :src="'/storage/' + fb.build.project.icon_url" style="max-width:100%;max-height:100%;object-fit:contain;" />
                                            <i v-else class="bi bi-box text-muted" style="font-size: 10px;"></i>
                                        </div>
                                        <Link :href="route('builds.show', fb.build?.id)" class="text-decoration-none small">
                                            {{ fb.build?.project?.name || 'Unknown Project' }} v{{ fb.build?.version_name }}
                                        </Link>
                                    </div>
                                </td>
                                <td><span class="badge" :class="feedbackStatusBadge(fb.status)">{{ fb.status }}</span></td>
                                <td><span class="badge" :class="severityBadge(fb.severity)">{{ fb.severity }}</span></td>
                                <td>{{ fb.author?.name || 'Unknown' }}</td>
                                <td class="text-muted small">{{ new Date(fb.created_at).toLocaleDateString() }}</td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-secondary me-2" @click="openFeedbackModal(fb)">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" @click="deleteFeedback(fb.id)">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="!filteredFeedbacks.length">
                                <td colspan="6" class="text-center py-4 text-muted">No feedback has been reported globally.</td>
                            </tr>
                        </tbody>
                    </table>
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
                            <button type="submit" class="btn btn-danger" :disabled="feedbackForm.processing">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

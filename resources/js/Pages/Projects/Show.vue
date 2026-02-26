<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    project: Object,
    builds: Object,
});

function buildTypeBadge(type) {
    const map = { Production: 'bg-success', RC: 'bg-info text-dark', Beta: 'bg-primary', Alpha: 'bg-warning text-dark' };
    return map[type] ?? 'bg-secondary';
}



function formatBytes(bytes) {
    if (!bytes) return '—';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return bytes + ' B';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head :title="project.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary bg-opacity-10 rounded p-1" style="width:36px; height:36px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                    <img v-if="project?.icon_url" :src="'/storage/' + project.icon_url" alt="Icon" style="max-width:100%; max-height:100%; object-fit:contain;" />
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#4F46E5" class="bi bi-android2" viewBox="0 0 16 16">
                        <path d="m10.213 1.471.691-1.216a.104.104 0 0 0-.033-.142.106.106 0 0 0-.142.033l-.705 1.238A8 8 0 0 0 8 1.139a8 8 0 0 0-2.024.26L5.27.161a.105.105 0 0 0-.142-.033.104.104 0 0 0-.033.142l.691 1.216C3.906 2.502 2.146 4.606 1.838 7.21h12.324c-.308-2.604-2.068-4.708-3.949-5.739M5.4 5.378a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0m6.839 0a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0M1.6 8.21h12.8A1.6 1.6 0 0 1 16 9.81v3.2a1.6 1.6 0 0 1-1.6 1.6H1.6A1.6 1.6 0 0 1 0 13.01v-3.2A1.6 1.6 0 0 1 1.6 8.21Z"/>
                    </svg>
                </div>
                <Link :href="route('projects.index')" class="text-muted text-decoration-none">Projects</Link>
                <span class="text-muted">/</span>
                <h2 class="h4 mb-0 fw-bold">{{ project.name }}</h2>
                <span class="badge bg-secondary text-monospace fw-normal ms-1">{{ project.package_name }}</span>
            </div>
        </template>

        <!-- Project Stats Bar -->
        <div class="row g-3 mt-1 mb-4 text-center">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm py-3 h-100">
                    <div class="h3 mb-0 text-primary fw-bold">{{ project.builds_count }}</div>
                    <div class="text-muted small">Total Builds</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm py-3 h-100">
                    <div class="h3 mb-0 text-info fw-bold">{{ builds.total }}</div>
                    <div class="text-muted small">Available Versions</div>
                </div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <div class="card border-0 shadow-sm py-3 h-100 border-start border-4 border-warning">
                    <div class="h3 mb-0 text-warning fw-bold">{{ project.pending_tasks_count }}</div>
                    <div class="text-muted small">Open Tasks</div>
                </div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <div class="card border-0 shadow-sm py-3 h-100 border-start border-4 border-danger">
                    <div class="h3 mb-0 text-danger fw-bold">{{ project.pending_feedbacks_count }}</div>
                    <div class="text-muted small">Active Bugs/Issues</div>
                </div>
            </div>
        </div>

        <!-- Builds Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold">Build History</h5>
                <Link :href="route('builds.create')" class="btn btn-sm btn-primary">
                    Upload New Build
                </Link>
            </div>

            <div v-if="builds.data.length === 0" class="card-body text-center py-5">
                <p class="text-muted mb-0">No builds uploaded yet for this project.</p>
            </div>

            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Version</th>
                            <th>Type</th>

                            <th>Uploaded By</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th class="text-center">Feedback</th>
                            <th class="text-center">Tasks</th>
                            <th class="text-center">Downloads</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="build in builds.data" :key="build.id">
                            <td>
                                <div class="fw-semibold">v{{ build.version_name }}</div>
                                <small class="text-muted">#{{ build.version_code }}</small>
                            </td>
                            <td><span class="badge" :class="buildTypeBadge(build.build_type)">{{ build.build_type }}</span></td>

                            <td>
                                <span class="text-muted small">{{ build.uploader?.name ?? '—' }}</span>
                            </td>
                            <td class="text-muted small">{{ formatBytes(build.file_size) }}</td>
                            <td class="text-muted small text-nowrap">{{ formatDate(build.created_at) }}</td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center gap-1">
                                    <span v-if="build.pending_feedbacks_count > 0" class="badge bg-danger rounded-pill" title="Unresolved">
                                        {{ build.pending_feedbacks_count }} pending
                                    </span>
                                    <span class="badge bg-secondary bg-opacity-10 text-dark" title="Total Feedback">
                                        {{ build.feedbacks_count }} total
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center gap-1">
                                    <span v-if="build.pending_tasks_count > 0" class="badge bg-warning text-dark rounded-pill" title="Pending Tasks">
                                        {{ build.pending_tasks_count }} pending
                                    </span>
                                    <span class="badge bg-secondary bg-opacity-10 text-dark" title="Total Tasks">
                                        {{ build.tasks_count }} total
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ build.downloads_count }}</span>
                            </td>
                            <td>
                                <Link :href="route('builds.show', build.id)" class="btn btn-sm btn-outline-primary">
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="builds.last_page > 1" class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
                <Link v-if="builds.prev_page_url" :href="builds.prev_page_url" class="btn btn-sm btn-outline-secondary">← Prev</Link>
                <span class="btn btn-sm btn-light disabled">{{ builds.current_page }} / {{ builds.last_page }}</span>
                <Link v-if="builds.next_page_url" :href="builds.next_page_url" class="btn btn-sm btn-outline-secondary">Next →</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

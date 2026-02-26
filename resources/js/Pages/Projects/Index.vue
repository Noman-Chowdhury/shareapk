<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    projects: Array,
});

function buildTypeBadge(type) {
    const map = {
        Production: 'bg-success',
        RC: 'bg-info text-dark',
        Beta: 'bg-primary',
        Alpha: 'bg-warning text-dark',
    };
    return map[type] ?? 'bg-secondary';
}


</script>

<template>
    <Head title="Projects" />
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0 fw-bold">All Projects</h2>
                <Link :href="route('builds.create')" class="btn btn-primary btn-sm">
                    <i class="bi bi-upload me-1"></i> Upload APK
                </Link>
            </div>
        </template>

        <div v-if="projects.length === 0" class="text-center py-5">
            <div class="display-1 text-muted">📦</div>
            <h4 class="mt-3 text-muted">No projects yet</h4>
            <p class="text-muted">Upload your first APK to get started.</p>
            <Link :href="route('builds.create')" class="btn btn-primary">Upload APK</Link>
        </div>

        <div v-else class="row g-4 mt-1">
            <div v-for="project in projects" :key="project.id" class="col-md-6 col-lg-4">
                <Link :href="route('projects.show', project.id)" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-card" 
                         :class="project.pending_feedbacks_count > 0 ? 'border-danger border-2 bg-danger bg-opacity-10' : (project.pending_tasks_count > 0 ? 'border-warning border-2 bg-warning bg-opacity-10' : 'border-0')">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 rounded p-2 me-3" style="width:44px; height:44px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                    <img v-if="project.icon_url" :src="'/storage/' + project.icon_url" alt="App Icon" style="max-width:100%; max-height:100%; object-fit:contain;" />
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#4F46E5" class="bi bi-android2" viewBox="0 0 16 16">
                                        <path d="m10.213 1.471.691-1.216a.104.104 0 0 0-.033-.142.106.106 0 0 0-.142.033l-.705 1.238A8 8 0 0 0 8 1.139a8 8 0 0 0-2.024.26L5.27.161a.105.105 0 0 0-.142-.033.104.104 0 0 0-.033.142l.691 1.216C3.906 2.502 2.146 4.606 1.838 7.21h12.324c-.308-2.604-2.068-4.708-3.949-5.739M5.4 5.378a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0m6.839 0a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0M1.6 8.21h12.8A1.6 1.6 0 0 1 16 9.81v3.2a1.6 1.6 0 0 1-1.6 1.6H1.6A1.6 1.6 0 0 1 0 13.01v-3.2A1.6 1.6 0 0 1 1.6 8.21Z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark">{{ project.name }}</h5>
                                    <small class="text-muted font-monospace">{{ project.package_name }}</small>
                                </div>
                                <div class="ms-auto d-flex flex-column align-items-end gap-1">
                                    <span v-if="project.pending_feedbacks_count > 0" class="badge bg-danger rounded-pill" title="Unresolved Feedback">
                                        {{ project.pending_feedbacks_count }} Bugs
                                    </span>
                                    <span v-if="project.pending_tasks_count > 0" class="badge bg-warning text-dark rounded-pill" title="Pending Tasks">
                                        {{ project.pending_tasks_count }} Tasks
                                    </span>
                                </div>
                            </div>

                            <p class="text-muted small mb-3">{{ project.description || 'No description.' }}</p>

                            <div class="d-flex align-items-center justify-content-between">
                                <span class="badge bg-secondary bg-opacity-10 text-dark">
                                    {{ project.builds_count }} build{{ project.builds_count !== 1 ? 's' : '' }}
                                </span>
                                <div v-if="project.latest_build">
                                    <span class="badge me-1" :class="buildTypeBadge(project.latest_build.build_type)">
                                        {{ project.latest_build.build_type }}
                                    </span>

                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 text-muted small d-flex align-items-center">
                            <span v-if="project.latest_build">
                                Latest: <strong>v{{ project.latest_build.version_name }}</strong>
                            </span>
                            <span v-else>No builds yet</span>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.hover-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.hover-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
}
</style>

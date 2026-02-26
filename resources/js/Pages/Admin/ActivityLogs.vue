<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    logs: Object
});

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString();
}

function getActionBadge(action) {
    if (action.includes('Approved')) return 'bg-success';
    if (action.includes('Rejected')) return 'bg-danger';
    if (action.includes('Uploaded')) return 'bg-primary';
    if (action.includes('Created')) return 'bg-info text-dark';
    return 'bg-secondary';
}
</script>

<template>
    <Head title="Activity Logs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="h4 fw-bold mb-0">System Activity Logs</h2>
        </template>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in logs.data" :key="log.id">
                            <td class="small text-muted">{{ formatDate(log.created_at) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center fw-bold text-primary small" style="width: 24px; height: 24px;">
                                        {{ log.user.name.charAt(0) }}
                                    </div>
                                    <span class="fw-medium">{{ log.user.name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge" :class="getActionBadge(log.action)">{{ log.action }}</span>
                            </td>
                            <td>{{ log.description }}</td>
                            <td class="small text-muted font-monospace">{{ log.ip_address }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer bg-white border-0 py-3" v-if="logs.links.length > 3">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li v-for="(link, k) in logs.links" :key="k" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                            <Link class="page-link" :href="link.url" v-html="link.label" />
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

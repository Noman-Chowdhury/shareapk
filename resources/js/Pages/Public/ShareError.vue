<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    message: String
});
</script>

<template>
    <Head title="Link Expired" />
    
    <div class="min-h-screen bg-light d-flex align-items-center justify-content-center p-4">
        <div class="card shadow-sm border-0 text-center p-5" style="max-width: 450px;">
            <div class="text-danger mb-4">
                <i class="bi bi-exclamation-triangle display-1"></i>
            </div>
            <h2 class="fw-bold mb-3">Link Invalid</h2>
            <p class="text-muted mb-4">{{ message || 'This share link has expired, reached its download limit, or no longer exists.' }}</p>
            
            <div class="d-grid">
                <Link href="/" class="btn btn-primary py-3">Return to Portal</Link>
            </div>
        </div>
    </div>
</template>

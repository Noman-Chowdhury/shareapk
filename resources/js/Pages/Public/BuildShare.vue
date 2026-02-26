<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, computed, ref } from 'vue';
import QrcodeVue from 'qrcode.vue';

const props = defineProps({
    shareLink: Object,
    project: Object,
    build: Object,
});

const currentUrl = computed(() => window.location.href);
const showQr = ref(false);

function humanFileSize(bytes) {
    if (!bytes) return '0 B';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'KB', 'MB', 'GB', 'TB'][i];
}

const iconUrl = computed(() => {
    if (!props.project.icon_url) return null;
    // Use window.location.origin to ensure absolute path for images if needed, 
    // though /storage/ should work on most setups.
    return '/storage/' + props.project.icon_url;
});
</script>

<template>
    <Head :title="'Download ' + project.name" />
    
    <div class="min-h-screen bg-light d-flex align-items-center justify-content-center p-4">
        <div class="card shadow-lg border-0 overflow-hidden" style="max-width: 500px; width: 100%;">
            <div class="bg-primary p-5 text-center position-relative">
                <!-- Decorative background pattern -->
                <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" style="background-image: radial-gradient(#fff 2px, transparent 2px); background-size: 20px 20px;"></div>
                
                <div class="bg-white rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center p-3 mb-4 position-relative" style="width: 100px; height: 100px;">
                    <img v-if="iconUrl" :src="iconUrl" class="img-fluid" alt="App Icon" style="object-fit: contain; max-height: 100%;">
                    <i v-else class="bi bi-android2 text-primary display-4"></i>
                </div>
                
                <h2 class="text-white fw-bold mb-1">{{ project.name }}</h2>
                <p class="text-white-50 mb-0">Version {{ build.version_name }} ({{ build.version_code }})</p>
            </div>
            
            <div class="card-body p-5">
                <div class="d-flex justify-content-between mb-4 pb-4 border-bottom">
                    <div class="text-center flex-grow-1 border-end">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">File Size</small>
                        <span class="fw-bold">{{ humanFileSize(build.file_size) }}</span>
                    </div>
                    <div class="text-center flex-grow-1 border-end">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Build Type</small>
                        <span class="badge bg-info text-dark">{{ build.build_type }}</span>
                    </div>
                    <div class="text-center flex-grow-1">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Uploaded</small>
                        <span class="fw-bold">{{ new Date(build.created_at).toLocaleDateString() }}</span>
                    </div>
                </div>
                
                <div v-if="build.release_notes" class="mb-5">
                    <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size: 0.75rem;">Release Notes</h6>
                    <div class="bg-light p-3 rounded small border">
                        {{ build.release_notes }}
                    </div>
                </div>

                <div class="d-grid gap-3">
                    <a :href="route('public.share.download', shareLink.token)" class="btn btn-primary btn-lg py-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-download"></i>
                        Download APK
                    </a>
                    
                    <button @click="showQr = !showQr" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-qr-code"></i>
                        {{ showQr ? 'Hide' : 'Show' }} QR Code
                    </button>

                    <div v-if="showQr" class="text-center p-3 border rounded bg-white shadow-inner animate-fade-in">
                        <QrcodeVue :value="currentUrl" :size="200" level="H" class="mx-auto" />
                        <p class="text-muted small mt-2 mb-0">Scan with your phone to download directly.</p>
                    </div>
                    
                    <p class="text-center text-muted small mt-2">
                        <i class="bi bi-shield-check"></i> 
                        Scanned and ready for installation.
                    </p>
                </div>
            </div>
            
            <div class="card-footer bg-white border-top-0 text-center py-4">
                <small class="text-muted">Securely shared via <strong>In-house APK Portal</strong></small>
            </div>
        </div>
    </div>
</template>

<style scoped>
.bg-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%) !important;
}
</style>

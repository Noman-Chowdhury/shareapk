<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const form = useForm({
    apk_file: null,
    temp_path: null,
    build_type: 'Beta',
    release_notes: '',
});

const isAnalyzing = ref(false);
const apkDetails = ref(null);
const analysisError = ref(null);

const handleFileChange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    form.apk_file = file;
    form.temp_path = null;
    apkDetails.value = null;
    analysisError.value = null;
    isAnalyzing.value = true;

    // Create a form data for pre-analysis
    const formData = new FormData();
    formData.append('apk_file', file);

    try {
        const response = await axios.post(route('builds.pre-analyze'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            apkDetails.value = response.data;
            form.temp_path = response.data.temp_path;
        } else {
            analysisError.value = response.data.message || 'Failed to analyze APK.';
        }
    } catch (error) {
        analysisError.value = error.response?.data?.message || 'Error occurred during APK analysis.';
    } finally {
        isAnalyzing.value = false;
    }
};

const submit = () => {
    form.post(route('builds.store'), {
        preserveScroll: true,
        onSuccess: () => {
             // Reset if needed, but normally Inertia redirects
        }
    });
};

function formatBytes(bytes) {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>

<template>
    <Head title="Upload APK Build" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 font-weight-bold text-dark mb-0">Upload New Build</h2>
            </div>
        </template>

        <div class="row justify-content-center mt-4">
            <div class="col-lg-10 col-xl-8">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <!-- Left Side: Form -->
                            <div class="col-md-7 p-4 p-md-5">
                                <form @submit.prevent="submit">
                                    <h5 class="fw-bold mb-4">Select APK</h5>
                                    
                                    <div class="mb-4">
                                        <div 
                                            class="upload-zone border-2 border-dashed rounded-3 p-4 text-center cursor-pointer transition"
                                            :class="{'border-primary bg-primary bg-opacity-10': form.apk_file, 'border-light-subtle': !form.apk_file}"
                                            onclick="document.getElementById('apk_file').click()"
                                        >
                                            <input type="file" @change="handleFileChange" class="d-none" id="apk_file" accept=".apk">
                                            
                                            <div v-if="!form.apk_file">
                                                <i class="bi bi-cloud-arrow-up display-4 text-muted"></i>
                                                <p class="mt-2 mb-0 fw-medium">Click to browse or drag and drop APK</p>
                                                <small class="text-muted">Maximum file size: 500MB</small>
                                            </div>
                                            
                                            <div v-else class="d-flex align-items-center justify-content-center gap-3">
                                                <i class="bi bi-file-earmark-zip-fill text-primary display-6"></i>
                                                <div class="text-start">
                                                    <div class="fw-bold text-truncate" style="max-width: 200px;">{{ form.apk_file.name }}</div>
                                                    <small class="text-muted">{{ formatBytes(form.apk_file.size) }}</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0" @click.stop="form.apk_file = null; apkDetails = null; form.temp_path = null">
                                                    <i class="bi bi-x-circle-fill"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.apk_file" class="text-danger small mt-1">{{ form.errors.apk_file }}</div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Build Type</label>
                                            <select v-model="form.build_type" class="form-select bg-light border-0 py-2">
                                                <option value="Alpha">Alpha</option>
                                                <option value="Beta">Beta</option>
                                                <option value="RC">Release Candidate (RC)</option>
                                                <option value="Production">Production</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Release Notes</label>
                                        <textarea v-model="form.release_notes" class="form-control bg-light border-0" rows="4" placeholder="What's new in this version?"></textarea>
                                    </div>

                                    <div class="d-grid mt-5">
                                        <button type="submit" class="btn btn-primary btn-lg shadow-sm" :disabled="form.processing || isAnalyzing || !apkDetails">
                                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                                            {{ form.processing ? 'Finalizing...' : 'Upload & Publish' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Side: Preview -->
                            <div class="col-md-5 bg-light border-start p-4 p-md-5 d-flex flex-column justify-content-center">
                                <div v-if="isAnalyzing" class="text-center py-5">
                                    <div class="spinner-grow text-primary mb-3" role="status"></div>
                                    <h6 class="fw-bold">Analyzing APK...</h6>
                                    <p class="text-muted small">Please wait while we extract package details.</p>
                                </div>

                                <div v-else-if="apkDetails" class="text-center animate__animated animate__fadeIn">
                                    <div class="mb-4 d-inline-block position-relative">
                                        <img v-if="apkDetails.icon_data" :src="apkDetails.icon_data" class="rounded-4 shadow-sm" style="width: 96px; height: 96px; object-fit: cover;">
                                        <div v-else class="bg-primary bg-opacity-10 rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 96px; height: 96px;">
                                            <i class="bi bi-android2 text-primary display-4"></i>
                                        </div>
                                    </div>
                                    
                                    <h4 class="fw-bold mb-1">{{ apkDetails.app_name }}</h4>
                                    <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill mb-4 px-3">
                                        v{{ apkDetails.version_name }} ({{ apkDetails.version_code }})
                                    </div>

                                    <ul class="list-group list-group-flush bg-transparent text-start small">
                                        <li class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                                            <span class="text-muted">Package</span>
                                            <span class="fw-medium font-monospace">{{ apkDetails.package_name }}</span>
                                        </li>
                                        <li class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                                            <span class="text-muted">File Size</span>
                                            <span class="fw-medium">{{ formatBytes(apkDetails.file_size) }}</span>
                                        </li>
                                    </ul>
                                    
                                    <div class="alert alert-info border-0 small mt-4 text-start">
                                        <i class="bi bi-info-circle-fill me-2"></i>
                                        These details were extracted from the AndroidManifest.xml inside your APK.
                                    </div>
                                </div>

                                <div v-else-if="analysisError" class="text-center py-5">
                                    <i class="bi bi-exclamation-triangle-fill text-danger display-4 mb-3"></i>
                                    <h6 class="fw-bold text-danger">Analysis Failed</h6>
                                    <p class="small mb-0">{{ analysisError }}</p>
                                </div>

                                <div v-else class="text-center py-5 text-muted">
                                    <i class="bi bi-search display-1 opacity-25 mb-3"></i>
                                    <p class="small px-4">Select an APK file to see a preview of its metadata and icon before publishing.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.upload-zone {
    border-style: dashed !important;
}
.upload-zone:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
    border-color: var(--bs-primary) !important;
}
.cursor-pointer {
    cursor: pointer;
}
.transition {
    transition: all 0.2s ease-in-out;
}
</style>

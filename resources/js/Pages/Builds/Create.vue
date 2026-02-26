<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    apk_file: null,
    build_type: 'Beta',
    release_notes: '',
});

const submit = () => {
    form.post(route('builds.store'), {
        preserveScroll: true,
    });
};

const handleFileChange = (e) => {
    form.apk_file = e.target.files[0];
};
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
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form @submit.prevent="submit" enctype="multipart/form-data">
                            
                            <!-- File Upload Area -->
                            <div class="mb-4">
                                <label for="apk_file" class="form-label fw-bold">Select APK File <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="file" @change="handleFileChange" class="form-control" id="apk_file" accept=".apk" required :class="{ 'is-invalid': form.errors.apk_file }">
                                </div>
                                <div v-if="form.errors.apk_file" class="invalid-feedback d-block">{{ form.errors.apk_file }}</div>
                                <div class="form-text">The system will automatically extract the Version Name, Version Code, and Package Name.</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Build Type</label>
                                    <select v-model="form.build_type" class="form-select" :class="{ 'is-invalid': form.errors.build_type }">
                                        <option value="Alpha">Alpha</option>
                                        <option value="Beta">Beta</option>
                                        <option value="RC">Release Candidate (RC)</option>
                                        <option value="Production">Production</option>
                                    </select>
                                    <div v-if="form.errors.build_type" class="invalid-feedback d-block">{{ form.errors.build_type }}</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Release Notes</label>
                                <textarea v-model="form.release_notes" class="form-control" rows="4" placeholder="What's new in this version?" :class="{ 'is-invalid': form.errors.release_notes }"></textarea>
                                <div v-if="form.errors.release_notes" class="invalid-feedback d-block">{{ form.errors.release_notes }}</div>
                            </div>

                            <!-- Progress Bar -->
                            <div v-if="form.progress" class="progress mb-3" style="height: 20px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" :style="{ width: form.progress.percentage + '%' }" :aria-valuenow="form.progress.percentage" aria-valuemin="0" aria-valuemax="100">
                                    {{ form.progress.percentage }}%
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    {{ form.processing ? 'Uploading & Parsing...' : 'Upload Build' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

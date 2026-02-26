<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    token: String
});

const form = useForm({
    password: ''
});

function submit() {
    form.post(route('public.share.verify', props.token));
}
</script>

<template>
    <Head title="Password Required" />
    
    <div class="min-h-screen bg-light d-flex align-items-center justify-content-center p-4">
        <div class="card shadow-lg border-0 p-5 text-center" style="max-width: 450px; width: 100%;">
            <div class="mb-4">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="bi bi-lock-fill display-4"></i>
                </div>
            </div>
            
            <h3 class="fw-bold mb-2">Password Protected</h3>
            <p class="text-muted mb-4">This share link is private. Please enter the password to access the build.</p>
            
            <form @submit.prevent="submit">
                <div class="mb-3">
                    <input v-model="form.password" type="password" class="form-control form-control-lg text-center" placeholder="Enter password" required autofocus>
                    <div v-if="form.errors.password" class="text-danger small mt-1">{{ form.errors.password }}</div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm" :disabled="form.processing">
                        Unlock Build
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

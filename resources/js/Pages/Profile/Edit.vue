<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="My Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 50px; height: 50px;">
                    {{ $page.props.auth.user.name.charAt(0) }}
                </div>
                <div>
                    <h2 class="h4 fw-bold mb-0">My Profile</h2>
                    <p class="text-muted small mb-0">Manage your account settings and security</p>
                </div>
            </div>
        </template>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Profile Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </div>
                </div>

                <!-- Password Update -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <UpdatePasswordForm />
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- User Summary Card -->
                <div class="card border-0 shadow-sm mb-4 bg-primary text-white overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-3 opacity-10">
                        <i class="bi bi-person-fill" style="font-size: 8rem;"></i>
                    </div>
                    <div class="card-body position-relative p-4">
                        <h5 class="fw-bold mb-1">{{ $page.props.auth.user.name }}</h5>
                        <p class="opacity-75 small mb-3">{{ $page.props.auth.user.email }}</p>
                        <div class="mt-4">
                            <span class="badge bg-white text-primary rounded-pill px-3 py-2">
                                <i class="bi bi-shield-check me-1"></i>
                                {{ $page.props.auth.user.roles?.[0] || 'User' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="card border-0 shadow-sm border-start border-4 border-danger">
                    <div class="card-body p-4">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

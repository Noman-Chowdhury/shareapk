<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header class="mb-4">
            <h5 class="fw-bold text-dark">Profile Information</h5>
            <p class="text-muted small">
                Update your account's profile information and email address.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))">
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="name" class="form-label fw-semibold small">Name</label>
                    <input
                        id="name"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.name }"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <div v-if="form.errors.name" class="invalid-feedback">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div class="col-md-12">
                    <label for="email" class="form-label fw-semibold small">Email Address</label>
                    <input
                        id="email"
                        type="email"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.email }"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />
                    <div v-if="form.errors.email" class="invalid-feedback">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div v-if="mustVerifyEmail && user.email_verified_at === null" class="col-12 mt-3">
                    <div class="alert alert-warning border-0 small">
                        Your email address is unverified.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="btn btn-link btn-sm p-0 m-0 vertical-align-baseline"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </div>

                    <div v-show="status === 'verification-link-sent'" class="text-success small fw-medium mt-1">
                        A new verification link has been sent to your email address.
                    </div>
                </div>

                <div class="col-12 mt-4 d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary px-4" :disabled="form.processing">
                        Update Profile
                    </button>

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <span v-if="form.recentlySuccessful" class="text-success small fw-medium">
                            <i class="bi bi-check-circle me-1"></i> Changes saved.
                        </span>
                    </Transition>
                </div>
            </div>
        </form>
    </section>
</template>

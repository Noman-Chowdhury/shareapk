<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header class="mb-4">
            <h5 class="fw-bold text-dark">Update Password</h5>
            <p class="text-muted small">
                Ensure your account is using a long, random password to stay secure.
            </p>
        </header>

        <form @submit.prevent="updatePassword">
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="current_password" class="form-label fw-semibold small">Current Password</label>
                    <input
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.current_password }"
                        autocomplete="current-password"
                    />
                    <div v-if="form.errors.current_password" class="invalid-feedback">
                        {{ form.errors.current_password }}
                    </div>
                </div>

                <div class="col-md-12">
                    <label for="password" class="form-label fw-semibold small">New Password</label>
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.password }"
                        autocomplete="new-password"
                    />
                    <div v-if="form.errors.password" class="invalid-feedback">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div class="col-md-12">
                    <label for="password_confirmation" class="form-label fw-semibold small">Confirm New Password</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.password_confirmation }"
                        autocomplete="new-password"
                    />
                    <div v-if="form.errors.password_confirmation" class="invalid-feedback">
                        {{ form.errors.password_confirmation }}
                    </div>
                </div>

                <div class="col-12 mt-4 d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary px-4" :disabled="form.processing">
                        Change Password
                    </button>

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <span v-if="form.recentlySuccessful" class="text-success small fw-medium">
                            <i class="bi bi-shield-lock me-1"></i> Password updated.
                        </span>
                    </Transition>
                </div>
            </div>
        </form>
    </section>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <header class="mb-3">
            <h5 class="fw-bold text-danger">Delete Account</h5>
            <p class="text-muted small">
                Once your account is deleted, all of its resources and data will be permanently deleted.
            </p>
        </header>

        <button class="btn btn-outline-danger btn-sm" @click="confirmUserDeletion">
            Delete My Account
        </button>

        <!-- Bootstrap Modal Backdrop -->
        <div v-if="confirmingUserDeletion" class="modal-backdrop fade show"></div>

        <!-- Bootstrap Modal -->
        <div 
            class="modal fade" 
            :class="{ 'show d-block': confirmingUserDeletion }" 
            tabindex="-1" 
            role="dialog"
            @click.self="closeModal"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Are you sure?</h5>
                        <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-0">
                        <p class="text-muted small">
                            Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.
                        </p>

                        <div class="mt-3">
                            <label for="password" class="form-label fw-semibold small">Password</label>
                            <input
                                id="password"
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.password }"
                                placeholder="Enter your password"
                                @keyup.enter="deleteUser"
                            />
                            <div v-if="form.errors.password" class="invalid-feedback">
                                {{ form.errors.password }}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4">
                        <button type="button" class="btn btn-light px-4" @click="closeModal">Cancel</button>
                        <button 
                            type="button" 
                            class="btn btn-danger px-4" 
                            :disabled="form.processing"
                            @click="deleteUser"
                        >
                            Confirm Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.modal.show {
    background-color: rgba(0, 0, 0, 0.5);
}
</style>

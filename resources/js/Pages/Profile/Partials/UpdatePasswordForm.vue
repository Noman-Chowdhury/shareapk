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
    <section class="max-w-xl">
        <header class="mb-10">
            <h3 class="text-xl font-black text-slate-900 tracking-tight">Security Credentials</h3>
            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest mt-1">Access Key Rotation</p>
        </header>

        <form @submit.prevent="updatePassword" class="space-y-8">
            <div class="space-y-6">
                <!-- Current Password -->
                <div class="space-y-2 group">
                    <label for="current_password" class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Existing Cipher</label>
                    <div class="relative">
                        <i class="bi bi-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                        <input
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            type="password"
                            class="input-premium pl-11 py-3.5 text-sm font-bold"
                            :class="{ 'border-rose-300 bg-rose-50/10': form.errors.current_password }"
                            autocomplete="current-password"
                            placeholder="Current passphrase"
                        />
                    </div>
                    <p v-if="form.errors.current_password" class="text-[10px] font-black text-rose-500 uppercase tracking-tighter pl-1">{{ form.errors.current_password }}</p>
                </div>

                <!-- New Password -->
                <div class="space-y-2 group">
                    <label for="password" class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">New System Cipher</label>
                    <div class="relative">
                        <i class="bi bi-shield-plus absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                        <input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="input-premium pl-11 py-3.5 text-sm font-bold"
                            :class="{ 'border-rose-300 bg-rose-50/10': form.errors.password }"
                            autocomplete="new-password"
                            placeholder="Minimum 8 complex characters"
                        />
                    </div>
                    <p v-if="form.errors.password" class="text-[10px] font-black text-rose-500 uppercase tracking-tighter pl-1">{{ form.errors.password }}</p>
                </div>

                <!-- Confirm New Password -->
                <div class="space-y-2 group">
                    <label for="password_confirmation" class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Confirm Cipher Signature</label>
                    <div class="relative">
                        <i class="bi bi-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="input-premium pl-11 py-3.5 text-sm font-bold"
                            :class="{ 'border-rose-300 bg-rose-50/10': form.errors.password_confirmation }"
                            autocomplete="new-password"
                            placeholder="Re-enter for validation"
                        />
                    </div>
                    <p v-if="form.errors.password_confirmation" class="text-[10px] font-black text-rose-500 uppercase tracking-tighter pl-1">{{ form.errors.password_confirmation }}</p>
                </div>
            </div>

            <!-- Execution Interface -->
            <div class="flex items-center gap-6 pt-4">
                <button type="submit" class="btn-premium-primary px-8 py-3.5 text-sm" :disabled="form.processing">
                    <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin mr-2"></i>
                    {{ form.processing ? 'Re-calculating Hash...' : 'Rotate Security Cipher' }}
                </button>

                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-x-4"
                    leave-active-class="transition ease-in duration-200"
                    leave-to-class="opacity-0 translate-x-4"
                >
                    <div v-if="form.recentlySuccessful" class="flex items-center gap-2 text-indigo-600">
                        <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center">
                            <i class="bi bi-lock-fill text-[10px]"></i>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest">Protocol Encrypted</span>
                    </div>
                </Transition>
            </div>
        </form>
    </section>
</template>

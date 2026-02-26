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
        <button class="w-full btn-premium-secondary border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 font-black uppercase tracking-widest text-[10px] py-4 shadow-sm active:scale-95 transition-all" @click="confirmUserDeletion">
            Terminate Operator Identity
        </button>

        <Transition name="fade">
            <div v-if="confirmingUserDeletion" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-md shadow-2xl">
                <div class="premium-card max-w-lg w-full p-10 lg:p-12 animate-in zoom-in bg-white" @click.stop>
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 bg-rose-50 text-rose-600 rounded-[2rem] flex items-center justify-center text-3xl mx-auto mb-6 shadow-xl shadow-rose-100/50">
                            <i class="bi bi-shield-slash-fill"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Confirm Dissolution</h3>
                        <p class="text-xs font-medium text-slate-400 leading-relaxed max-w-xs mx-auto uppercase tracking-widest">
                            Permanent purge of all associated logs and access keys.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <p class="text-sm font-medium text-slate-500 text-center leading-relaxed">
                            To authorize this terminal action, please provide your secret access key below. This operation cannot be reversed.
                        </p>

                        <div class="space-y-2 group">
                            <label for="password" class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1 text-center">Authorization Key</label>
                            <div class="relative">
                                <i class="bi bi-shield-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-rose-500 transition-colors"></i>
                                <input
                                    id="password"
                                    ref="passwordInput"
                                    v-model="form.password"
                                    type="password"
                                    class="input-premium pl-11 py-4 text-center text-sm font-bold tracking-[0.3em]"
                                    :class="{ 'border-rose-300 ring-rose-50': form.errors.password }"
                                    placeholder="••••••••"
                                    @keyup.enter="deleteUser"
                                />
                            </div>
                            <p v-if="form.errors.password" class="text-[10px] font-black text-rose-500 uppercase tracking-tighter text-center">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            <button type="button" @click="closeModal" class="flex-grow btn-premium-secondary py-4 text-sm font-black uppercase tracking-widest">
                                Abort Purge
                            </button>
                            <button 
                                type="button" 
                                class="flex-[1.5] btn-premium-primary bg-rose-600 hover:bg-rose-700 shadow-rose-200 py-4 text-sm font-black uppercase tracking-widest" 
                                :disabled="form.processing"
                                @click="deleteUser"
                            >
                                <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                    <i class="bi bi-arrow-repeat animate-spin"></i>
                                    Purging...
                                </span>
                                <span v-else>Confirm Purge</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </section>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

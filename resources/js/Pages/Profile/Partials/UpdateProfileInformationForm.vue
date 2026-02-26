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
    <section class="max-w-xl">
        <header class="mb-10">
            <h3 class="text-xl font-black text-slate-900 tracking-tight">Identity Parameters</h3>
            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest mt-1">Registry Synchronization</p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-8">
            <div class="space-y-6">
                <!-- Name -->
                <div class="space-y-2 group">
                    <label for="name" class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Legal Designation</label>
                    <div class="relative">
                        <i class="bi bi-person absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                        <input
                            id="name"
                            type="text"
                            class="input-premium pl-11 py-3.5 text-sm font-bold"
                            :class="{ 'border-rose-300 bg-rose-50/10': form.errors.name }"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Operator Name"
                        />
                    </div>
                    <p v-if="form.errors.name" class="text-[10px] font-black text-rose-500 uppercase tracking-tighter pl-1">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div class="space-y-2 group">
                    <label for="email" class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Digital Comm Port (Email)</label>
                    <div class="relative">
                        <i class="bi bi-envelope-at absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                        <input
                            id="email"
                            type="email"
                            class="input-premium pl-11 py-3.5 text-sm font-bold"
                            :class="{ 'border-rose-300 bg-rose-50/10': form.errors.email }"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="operator@system.cloud"
                        />
                    </div>
                    <p v-if="form.errors.email" class="text-[10px] font-black text-rose-500 uppercase tracking-tighter pl-1">{{ form.errors.email }}</p>
                </div>

                <!-- Verification Logic -->
                <div v-if="mustVerifyEmail && user.email_verified_at === null" class="animate-in fade-in">
                    <div class="p-5 rounded-2xl bg-amber-50 border border-amber-100 flex items-start gap-4">
                        <i class="bi bi-exclamation-diamond-fill text-amber-500 text-lg"></i>
                        <div class="space-y-2">
                             <p class="text-xs font-bold text-amber-800">Your identity certificate is pending verification.</p>
                             <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-[10px] font-black uppercase text-amber-600 hover:text-amber-800 underline underline-offset-4 decoration-amber-200"
                            >
                                Re-transmit Verification Sequence
                            </Link>
                        </div>
                    </div>

                    <div v-show="status === 'verification-link-sent'" class="mt-3 px-4 py-2 bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase rounded-lg border border-emerald-100 animate-in slide-in-from-top-2">
                        Transmission successful. Check your comms receiver.
                    </div>
                </div>
            </div>

            <!-- Execution Interface -->
            <div class="flex items-center gap-6 pt-4">
                <button type="submit" class="btn-premium-primary px-8 py-3.5 text-sm" :disabled="form.processing">
                    <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin mr-2"></i>
                    {{ form.processing ? 'Synchronizing...' : 'Sychronize Cluster Identity' }}
                </button>

                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-x-4"
                    leave-active-class="transition ease-in duration-200"
                    leave-to-class="opacity-0 translate-x-4"
                >
                    <div v-if="form.recentlySuccessful" class="flex items-center gap-2 text-emerald-600">
                        <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center">
                            <i class="bi bi-check-lg text-xs"></i>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest">Protocol Success</span>
                    </div>
                </Transition>
            </div>
        </form>
    </section>
</template>

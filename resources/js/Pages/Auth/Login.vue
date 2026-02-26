<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="System Authentication" />

        <div v-if="status" class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-bold border border-emerald-100 flex items-center gap-3 animate-in fade-in">
            <i class="bi bi-check-circle-fill"></i>
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
                <h2 class="text-xl font-black text-slate-900 tracking-tight">System Login</h2>
                <p class="text-slate-400 text-xs font-medium">Identify yourself to access the deployment cluster.</p>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 tracking-widest pl-1">Registry Email</label>
                <div class="relative group">
                    <i class="bi bi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-indigo-500"></i>
                    <input
                        id="email"
                        type="email"
                        class="input-premium pl-11 py-3.5 text-sm"
                        :class="{ 'border-rose-300 ring-rose-50': form.errors.email }"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="operator@shareapk.dev"
                    />
                </div>
                <p v-if="form.errors.email" class="mt-1.5 text-[10px] font-black uppercase text-rose-500 tracking-tighter">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 tracking-widest pl-1">Secret Access Key</label>
                <div class="relative group">
                    <i class="bi bi-shield-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-indigo-500"></i>
                    <input
                        id="password"
                        type="password"
                        class="input-premium pl-11 py-3.5 text-sm"
                        :class="{ 'border-rose-300 ring-rose-50': form.errors.password }"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••••••"
                    />
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-[10px] font-black uppercase text-rose-500 tracking-tighter">{{ form.errors.password }}</p>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer group">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="hidden"
                    />
                    <div class="w-5 h-5 rounded-md border-2 border-slate-200 mr-2 flex items-center justify-center transition-all group-hover:border-indigo-400" :class="{ 'bg-indigo-600 border-indigo-600 shadow-lg shadow-indigo-100': form.remember }">
                        <i v-if="form.remember" class="bi bi-check-lg text-white text-xs"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-500">Keep session active</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-xs font-bold text-indigo-500 hover:text-indigo-700 transition-colors"
                >
                    Lost access?
                </Link>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full btn-premium-primary py-4 text-base tracking-tight"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Verifying Keys...
                    </span>
                    <span v-else>Initiate Sequence</span>
                </button>
            </div>
        </form>
    </GuestLayout>
</template>

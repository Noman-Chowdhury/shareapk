<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Establish Identity • Share Apk" />

        <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2 text-center mb-8">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Establish Identity</h2>
                <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Register new operator in the cloud registry.</p>
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 tracking-widest pl-1">Full Operator Name</label>
                <div class="relative group">
                    <i class="bi bi-person absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-indigo-500"></i>
                    <input
                        id="name"
                        type="text"
                        class="input-premium pl-11 py-3.5 text-sm"
                        :class="{ 'border-rose-300 ring-rose-50': form.errors.name }"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Commander Shepherd"
                    />
                </div>
                <p v-if="form.errors.name" class="mt-1.5 text-[10px] font-black uppercase text-rose-500 tracking-tighter">{{ form.errors.name }}</p>
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
                        autocomplete="username"
                        placeholder="operator@shareapk.dev"
                    />
                </div>
                <p v-if="form.errors.email" class="mt-1.5 text-[10px] font-black uppercase text-rose-500 tracking-tighter">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 tracking-widest pl-1">Access Key</label>
                    <div class="relative group">
                        <i class="bi bi-shield-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-indigo-500"></i>
                        <input
                            id="password"
                            type="password"
                            class="input-premium pl-11 py-3.5 text-sm"
                            :class="{ 'border-rose-300 ring-rose-50': form.errors.password }"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                    </div>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 tracking-widest pl-1">Confirm Key</label>
                    <div class="relative group">
                        <i class="bi bi-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-indigo-500"></i>
                        <input
                            id="password_confirmation"
                            type="password"
                            class="input-premium pl-11 py-3.5 text-sm"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                    </div>
                </div>
                <p v-if="form.errors.password" class="sm:col-span-2 mt-1.5 text-[10px] font-black uppercase text-rose-500 tracking-tighter">{{ form.errors.password }}</p>
            </div>

            <!-- Action Core -->
            <div class="pt-4 flex flex-col gap-4">
                <button
                    type="submit"
                    class="w-full btn-premium-primary py-4 text-base tracking-tight"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center justify-center gap-2">
                        <i class="bi bi-arrow-repeat animate-spin text-lg"></i>
                        Encrypting Identity...
                    </span>
                    <span v-else>Authorize & Create Identity</span>
                </button>

                <div class="flex items-center justify-center gap-2">
                     <span class="text-xs font-bold text-slate-400">Already in the registry?</span>
                     <Link
                        :href="route('login')"
                        class="text-xs font-black text-indigo-500 hover:text-indigo-700 transition-colors"
                    >
                        Return to Authentication
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>

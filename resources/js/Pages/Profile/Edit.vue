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
    <Head title="Operator Settings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 rounded-[2rem] bg-indigo-600 text-white flex items-center justify-center text-2xl font-black shadow-2xl shadow-indigo-200 border-4 border-white transition-transform hover:rotate-6">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Operator Profile</h2>
                        <p class="text-slate-400 text-sm font-medium">Manage your system credentials and access tokens.</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 pt-6">
            <div class="lg:col-span-8 space-y-10">
                <!-- Profile Information Sector -->
                <div class="premium-card p-10 lg:p-14 bg-white relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-10 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity pointer-events-none">
                        <i class="bi bi-person-lines-fill text-[120px]"></i>
                    </div>
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- Strategic Security: Password -->
                <div class="premium-card p-10 lg:p-14 bg-white relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-10 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity pointer-events-none text-indigo-600">
                        <i class="bi bi-shield-lock-fill text-[120px]"></i>
                    </div>
                    <UpdatePasswordForm />
                </div>
            </div>

            <div class="lg:col-span-4 space-y-8">
                <!-- User Identity Capsule -->
                <div class="premium-card bg-slate-900 overflow-hidden relative group">
                    <div class="absolute -top-12 -right-12 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl"></div>
                    <div class="p-8 relative z-10 flex flex-col items-center text-center">
                        <div class="w-24 h-24 rounded-[3rem] bg-white/5 border border-white/10 flex items-center justify-center text-4xl font-black text-white mb-6 p-1 backdrop-blur-md">
                            <div class="w-full h-full rounded-[2.8rem] bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-2xl">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                        </div>
                        <h3 class="text-xl font-black text-white mb-2">{{ $page.props.auth.user.name }}</h3>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em] mb-8">{{ $page.props.auth.user.email }}</p>
                        
                        <div class="flex items-center gap-3 px-6 py-3 bg-white/5 rounded-2xl border border-white/10 group-hover:border-indigo-400 transition-colors">
                            <i class="bi bi-shield-check text-indigo-400"></i>
                            <span class="text-[10px] font-black uppercase text-indigo-100 tracking-widest leading-none">
                                {{ $page.props.auth.user.roles?.[0] || 'Standard Operator' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Account Dissolution Sector -->
                <div class="premium-card bg-rose-50/50 border-rose-100 p-8 space-y-4">
                    <div class="flex items-center gap-3 text-rose-600 mb-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <h4 class="text-xs font-black uppercase tracking-widest">Zone of High Risk</h4>
                    </div>
                    <p class="text-[11px] font-bold text-slate-500 leading-relaxed mb-6">
                        Initiating account dissolution will permanently purge your identity and associated access keys from the cluster.
                    </p>
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

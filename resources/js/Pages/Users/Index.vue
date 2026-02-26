<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    users: Array,
    roles: Array,
});

const isModalOpen = ref(false);
const editingUser = ref(null);
const userForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'Viewer',
});

function openModal(user = null) {
    editingUser.value = user;
    if (user) {
        userForm.name = user.name;
        userForm.email = user.email;
        userForm.password = ''; 
        userForm.role = user.roles && user.roles.length > 0 ? user.roles[0].name : 'Viewer';
    } else {
        userForm.reset();
        userForm.role = 'Viewer';
    }
    isModalOpen.value = true;
}

function submitUser() {
    const url = editingUser.value 
        ? route('users.update', editingUser.value.id) 
        : route('users.store');
    const method = editingUser.value ? 'put' : 'post';

    userForm[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            userForm.reset();
            editingUser.value = null;
        },
    });
}

function deleteUser(id) {
    if (confirm('Permanently decommission this user?')) {
        router.delete(route('users.destroy', id), { preserveScroll: true });
    }
}

const currentUser = computed(() => usePage().props.auth.user);

const searchQuery = ref('');
const filteredUsers = computed(() => {
    return props.users.filter(u => 
        u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
        u.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

function roleColor(role) {
    return {
        Admin: 'bg-indigo-600 text-white',
        Tester: 'bg-emerald-50 text-emerald-700 border-emerald-100',
        Manager: 'bg-amber-50 text-amber-700 border-amber-100',
        Viewer: 'bg-slate-50 text-slate-700 border-slate-100'
    }[role] || 'bg-slate-50';
}
</script>

<template>
    <Head title="Access Control Registry" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest px-1 flex items-center gap-3">
                        <i class="bi bi-shield-lock-fill text-indigo-500"></i> Identity & Access Management
                    </h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Manage cluster operators and security protocols.</p>
                </div>
                <button @click="openModal()" class="btn-premium-indigo text-[10px] py-2 px-5 font-black uppercase tracking-widest">
                    <i class="bi bi-person-plus-fill mr-2"></i> Enroll Operator
                </button>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="premium-card p-5 bg-white border-b-4 border-indigo-500">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Total Enrolled</span>
                <span class="text-2xl font-black text-slate-900 tabular-nums">{{ users.length }}</span>
            </div>
            <div class="premium-card p-5 bg-slate-50 border-b-4 border-emerald-500">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Admins</span>
                <span class="text-2xl font-black text-slate-900 tabular-nums">{{ users.filter(u => u.roles.some(r => r.name === 'Admin')).length }}</span>
            </div>
            <div class="premium-card p-5 bg-slate-50 border-b-4 border-amber-500">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Managers</span>
                <span class="text-2xl font-black text-slate-900 tabular-nums">{{ users.filter(u => u.roles.some(r => r.name === 'Manager')).length }}</span>
            </div>
            <div class="premium-card p-5 bg-slate-50 border-b-4 border-slate-300">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Active Sessions</span>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-2xl font-black text-slate-900 tabular-nums">{{ users.length }}</span>
                </div>
            </div>
        </div>

        <div class="premium-card overflow-hidden bg-white shadow-xl/10">
            <div class="px-8 py-5 border-b border-slate-100 bg-slate-900 flex flex-col md:flex-row md:items-center justify-between gap-4">
                 <div class="relative max-w-sm w-full">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-white/40"></i>
                    <input v-model="searchQuery" type="text" class="w-full bg-white/5 border border-white/10 rounded-2xl py-2 pl-11 text-xs text-white placeholder-white/30 font-bold focus:bg-white/10 outline-none transition-all" placeholder="Search identities..." />
                 </div>
                 <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase text-indigo-400 tracking-widest">Protocol Registry</span>
                 </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-900 border-b border-white/10 text-white">
                        <tr class="text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-4">Operator Identity</th>
                            <th class="px-8 py-4">Access Tier</th>
                            <th class="px-8 py-4 text-center">Registry State</th>
                            <th class="px-8 py-4 text-center">Enrollment Date</th>
                            <th class="px-8 py-4 text-right pr-12">Control</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="user in filteredUsers" :key="user.id" class="group hover:bg-[#f8fafc] transition-colors">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-xs font-black text-slate-700 shadow-sm relative overflow-hidden group-hover:rotate-3 transition-transform">
                                        {{ user.name.charAt(0) }}
                                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent"></div>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors flex items-center gap-2">
                                            {{ user.name }}
                                            <span v-if="user.id === currentUser.id" class="text-[8px] font-black uppercase bg-indigo-600 text-white px-2 py-0.5 rounded-lg tracking-widest">Root</span>
                                        </span>
                                        <span class="text-[10px] font-black text-slate-400 truncate uppercase tracking-tighter">{{ user.email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span v-if="user.roles.length" class="text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-xl border" :class="roleColor(user.roles[0].name)">
                                    {{ user.roles[0].name }} Tier
                                </span>
                                <span v-else class="text-[9px] font-black text-slate-300 uppercase tracking-widest">No Protocol Assigned</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]"></span>
                                    <span class="text-[10px] font-black uppercase text-slate-600 tracking-widest">Authorized</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-xs font-black text-slate-500 tabular-nums">{{ new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</span>
                            </td>
                            <td class="px-8 py-4 text-right pr-10">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openModal(user)" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all border border-transparent hover:border-indigo-100"><i class="bi bi-pencil-square"></i></button>
                                    <button v-if="currentUser.roles && currentUser.roles.some(r => r.name === 'Admin')" 
                                            @click="deleteUser(user.id)" 
                                            :disabled="user.id === currentUser.id"
                                            class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all border border-transparent hover:border-rose-100 disabled:opacity-20"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- OPERATOR ENROLLMENT INTERFACE (MODAL) -->
        <Transition name="fade">
            <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/40 backdrop-blur-sm shadow-2xl overflow-y-auto">
                <div class="premium-card max-w-xl w-full p-8 lg:p-10 animate-in zoom-in bg-white h-fit">
                    <div class="space-y-2 mb-8 border-l-4 border-indigo-600 pl-6">
                        <h3 class="text-xl font-black text-slate-900 tracking-tight uppercase">{{ editingUser ? 'Update Credentials' : 'Enroll New Operator' }}</h3>
                        <p class="text-xs font-medium text-slate-400">Registry synchronization for identity services.</p>
                    </div>

                    <form @submit.prevent="submitUser" class="space-y-6">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Legal Name</label>
                            <input v-model="userForm.name" class="input-premium font-bold" placeholder="Registry name..." required />
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Communication Port (Email)</label>
                            <input v-model="userForm.email" type="email" class="input-premium font-bold" placeholder="operator@shareapk.dev" required />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Access Tier</label>
                                <select v-model="userForm.role" class="input-premium py-3 text-xs font-bold text-slate-700" required>
                                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                                </select>
                             </div>
                             <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Secret Access Key</label>
                                <input v-model="userForm.password" type="password" class="input-premium py-3 text-xs font-bold" :placeholder="editingUser ? 'Leave blank to retain' : 'Access Cipher'" :required="!editingUser" />
                             </div>
                        </div>

                        <div class="flex gap-4 pt-6">
                            <button type="button" @click="isModalOpen = false" class="flex-grow btn-premium-secondary py-4 text-base">Abort Sequence</button>
                            <button type="submit" class="flex-[2] btn-premium-primary py-4 text-base" :disabled="userForm.processing">
                                <span v-if="userForm.processing">Synchronizing...</span>
                                <span v-else>{{ editingUser ? 'Update Access Key' : 'Authorize Enrollment' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

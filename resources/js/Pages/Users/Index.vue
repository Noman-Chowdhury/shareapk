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
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Access Control</h2>
                    <p class="text-slate-500 text-sm font-medium">Manage operator permissions and cluster access levels.</p>
                </div>
                <button @click="openModal()" class="btn-premium-primary text-sm py-2.5">
                    <i class="bi bi-person-plus-fill mr-2"></i> Enroll Operator
                </button>
            </div>
        </template>

        <div class="premium-card overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                 <div class="relative max-w-sm w-full">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input v-model="searchQuery" type="text" class="input-premium py-2 pl-11 text-xs" placeholder="Search operators..." />
                 </div>
                 <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">{{ users.length }} Total Enrolled</span>
                 </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/30 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <tr>
                            <th class="px-8 py-4">Identity</th>
                            <th class="px-8 py-4">Permissions</th>
                            <th class="px-8 py-4">Status</th>
                            <th class="px-8 py-4">Enrolled On</th>
                            <th class="px-8 py-4 text-right pr-12">Control</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="user in filteredUsers" :key="user.id" class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-xs font-black text-slate-700">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-black text-slate-800 group-hover:text-indigo-600 transition-colors flex items-center gap-2">
                                            {{ user.name }}
                                            <span v-if="user.id === currentUser.id" class="text-[9px] font-black uppercase bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded border border-indigo-100">Self</span>
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-400 truncate">{{ user.email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span v-if="user.roles.length" class="badge-premium text-[9px] py-1 px-3 border-transparent" :class="roleColor(user.roles[0].name)">
                                    {{ user.roles[0].name }}
                                </span>
                                <span v-else class="text-[10px] font-black text-slate-300 uppercase">No Protocol</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)] animate-pulse"></span>
                                    <span class="text-[10px] font-black uppercase text-emerald-600 tracking-tighter">Verified</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-xs font-bold text-slate-500 tabular-nums">{{ new Date(user.created_at).toLocaleDateString() }}</span>
                            </td>
                            <td class="px-8 py-6 text-right pr-10">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openModal(user)" class="p-2 text-slate-300 hover:text-indigo-600 transition-colors tooltip" title="Edit Access"><i class="bi bi-pencil-square"></i></button>
                                    <button v-if="currentUser.roles && currentUser.roles.some(r => r.name === 'Admin')" 
                                            @click="deleteUser(user.id)" 
                                            :disabled="user.id === currentUser.id"
                                            class="p-2 text-slate-300 hover:text-rose-500 disabled:opacity-30 disabled:pointer-events-none transition-colors"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!filteredUsers.length">
                            <td colspan="5" class="py-20 text-center text-slate-400 opacity-40">
                                 <i class="bi bi-search text-5xl mb-4 block"></i>
                                 <p class="font-black uppercase tracking-[0.3em] text-xs">No matching identities identified</p>
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

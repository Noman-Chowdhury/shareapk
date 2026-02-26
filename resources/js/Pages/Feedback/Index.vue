<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ feedbacks: Array, users: Array });

const editingFeedback = ref(null);
const feedbackForm = useForm({
    title: '', description: '', type: 'Bug', severity: 'Medium', status: 'Open',
    device_model: '', os_version: '', screen_size: '', assignee_id: '',
});

const feedbackNewFiles = ref([]);
const feedbackKeepAttachments = ref([]);
const isModalOpen = ref(false);

function openModal(fb) {
    editingFeedback.value = fb;
    feedbackForm.title = fb.title;
    feedbackForm.description = fb.description;
    feedbackForm.type = fb.type;
    feedbackForm.severity = fb.severity || 'Medium';
    feedbackForm.status = fb.status || 'Open';
    feedbackForm.device_model = fb.device_model || '';
    feedbackForm.os_version = fb.os_version || '';
    feedbackForm.assignee_id = fb.assignee_id || '';
    feedbackNewFiles.value = [];
    feedbackKeepAttachments.value = (fb.attachments || []).map(a => a.path);
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    editingFeedback.value = null;
}

function handleFeedbackFiles(e) { feedbackNewFiles.value = Array.from(e.target.files); }

function submitFeedback() {
    const url = route('feedback.update', editingFeedback.value.id);
    const payload = feedbackForm.data();
    feedbackNewFiles.value.forEach((file, i) => payload[`new_attachments[${i}]`] = file);
    feedbackKeepAttachments.value.forEach((path, i) => payload[`keep_attachments[${i}]`] = path);
    payload['_method'] = 'PUT';

    feedbackForm.transform(() => payload).post(url, {
        forceFormData: true, preserveScroll: true,
        onSuccess: () => { closeModal(); feedbackForm.reset(); feedbackNewFiles.value = []; },
    });
}

function deleteFeedback(id) {
    if (confirm('Delete feedback report?')) router.delete(route('feedback.destroy', id), { preserveScroll: true });
}

function severityColor(sev) {
    return { 
        Critical: 'bg-rose-50 text-rose-700', 
        High: 'bg-orange-50 text-orange-700', 
        Medium: 'bg-amber-50 text-amber-700', 
        Low: 'bg-slate-50 text-slate-700' 
    }[sev] || 'bg-slate-50';
}
function statusColor(status) {
    return { Open: 'text-rose-600', 'In Progress': 'text-amber-600', Resolved: 'text-emerald-600', Closed: 'text-slate-500' }[status] || 'text-slate-500';
}

const searchQuery = ref('');
const statusFilter = ref('All');

const filteredFeedbacks = computed(() => {
    return props.feedbacks.filter(fb => {
        const matchesSearch = fb.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                             fb.build?.project?.name?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = statusFilter.value === 'All' || fb.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });
});
</script>

<template>
    <Head title="Quality Assurance" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Quality Control</h2>
                    <p class="text-slate-500 text-xs">Manage reported issues and feedback streams.</p>
                </div>
                <div class="flex gap-2">
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input v-model="searchQuery" type="text" class="input-premium py-1.5 pl-9 w-48 text-xs bg-white" placeholder="Search..." />
                    </div>
                    <select v-model="statusFilter" class="input-premium py-1.5 px-3 text-xs bg-white font-bold text-slate-600 w-32 border-slate-200">
                        <option>All</option>
                        <option>Open</option>
                        <option>In Progress</option>
                        <option>Resolved</option>
                        <option>Closed</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="premium-card overflow-hidden">
             <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr class="text-[10px] font-bold uppercase text-slate-400 tracking-widest">
                        <th class="px-6 py-3">Issue / Context</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3">Severity</th>
                        <th class="px-6 py-3">Assignee</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs">
                    <tr v-for="fb in filteredFeedbacks" :key="fb.id" class="group hover:bg-slate-50/30 transition-colors">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded bg-white border border-slate-200 flex items-center justify-center p-1 shrink-0">
                                    <img v-if="fb.build?.project?.icon_url" :src="'/storage/' + fb.build.project.icon_url" class="max-w-full max-h-full object-contain" />
                                    <i v-else class="bi bi-bug text-slate-300"></i>
                                </div>
                                <div class="min-w-0">
                                    <Link :href="route('feedback.show', fb.id)" class="font-bold text-slate-900 group-hover:text-indigo-600 truncate block">{{ fb.title }}</Link>
                                    <span v-if="fb.build" class="text-[10px] text-slate-400 font-medium truncate block">{{ fb.build.project?.name }} v{{ fb.build.version_name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="text-[10px] font-bold uppercase tracking-wider" :class="statusColor(fb.status)">{{ fb.status }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="badge-premium" :class="severityColor(fb.severity)">{{ fb.severity }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 bg-slate-100 rounded flex items-center justify-center text-[10px] text-slate-500 font-bold border border-slate-200">{{ fb.assignee?.name.charAt(0) || '?' }}</span>
                                <span class="font-medium text-slate-600">{{ fb.assignee?.name || 'Unassigned' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Link :href="route('feedback.show', fb.id)" class="p-1.5 text-slate-400 hover:text-indigo-600"><i class="bi bi-eye"></i></Link>
                                <button @click="openModal(fb)" class="p-1.5 text-slate-400 hover:text-indigo-600"><i class="bi bi-pencil-square"></i></button>
                                <button @click="deleteFeedback(fb.id)" class="p-1.5 text-slate-400 hover:text-rose-500"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
             </table>
        </div>

        <Transition name="fade">
            <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/10 backdrop-blur-sm">
                <div class="premium-card max-w-lg w-full p-6 animate-in zoom-in">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Update Report</h3>
                    <form @submit.prevent="submitFeedback" class="space-y-3">
                        <input v-model="feedbackForm.title" class="input-premium font-bold text-sm" placeholder="Title..." required />
                        <div class="grid grid-cols-2 gap-3">
                            <select v-model="feedbackForm.severity" class="input-premium text-xs py-1.5 bg-white">
                                <option>Critical</option><option>High</option><option>Medium</option><option>Low</option>
                            </select>
                            <select v-model="feedbackForm.status" class="input-premium text-xs py-1.5 bg-white">
                                <option>Open</option><option>In Progress</option><option>Resolved</option><option>Closed</option>
                            </select>
                        </div>
                        <select v-model="feedbackForm.assignee_id" class="input-premium text-xs py-1.5 bg-white">
                            <option value="">No Assignee</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <textarea v-model="feedbackForm.description" class="input-premium text-xs min-h-[100px]" placeholder="Summary..." required></textarea>
                        
                        <div class="flex gap-2 pt-2">
                            <button type="button" @click="closeModal" class="btn-premium-secondary py-1.5 text-xs">Cancel</button>
                            <button type="submit" class="btn-premium-indigo py-1.5 text-xs flex-grow">Update Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

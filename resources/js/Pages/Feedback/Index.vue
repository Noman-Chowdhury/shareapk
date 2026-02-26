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
    <Head title="Quality Assurance Echo" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Quality Assurance Archive</h2>
                    <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">Global quality protocol monitoring & trace analytics.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input v-model="searchQuery" type="text" class="input-premium py-2 pl-9 w-56 text-xs bg-white" placeholder="Registry Search..." />
                    </div>
                    <select v-model="statusFilter" class="input-premium py-2 px-4 text-xs bg-white font-black text-slate-600 w-36 border-slate-200 uppercase tracking-tighter">
                        <option>All Signals</option>
                        <option>Open</option>
                        <option>In Progress</option>
                        <option>Resolved</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="premium-card overflow-hidden animate-slide-up">
             <table class="w-full text-left">
                <thead class="bg-slate-900 border-b border-white/10 text-white">
                    <tr class="text-[10px] font-black uppercase tracking-[0.2em]">
                        <th class="px-8 py-4">Signal Identification</th>
                        <th class="px-8 py-4 text-center">Protocol Status</th>
                        <th class="px-8 py-4 text-center">Severity</th>
                        <th class="px-8 py-4">Assigned Agent</th>
                        <th class="px-8 py-4 text-right pr-12">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs">
                    <tr v-for="fb in filteredFeedbacks" :key="fb.id" class="group hover:bg-[#f8fafc] transition-colors">
                        <td class="px-8 py-2.5">
                            <div class="flex items-center gap-4">
                                <div class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center p-1 shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                                    <img v-if="fb.build?.project?.icon_url" :src="'/storage/' + fb.build.project.icon_url" class="max-w-full max-h-full object-contain" />
                                    <i v-else class="bi bi-bug text-slate-300 text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <Link :href="route('feedback.show', fb.id)" class="text-[13px] font-black text-slate-800 group-hover:text-indigo-600 truncate block transition-colors leading-tight">{{ fb.title }}</Link>
                                    <span v-if="fb.build" class="text-[9px] text-slate-400 font-black uppercase tracking-tighter opacity-70">{{ fb.build.project?.name }} v{{ fb.build.version_name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-2.5 text-center">
                            <span class="text-[9px] font-black uppercase tracking-widest" :class="statusColor(fb.status)">{{ fb.status }}</span>
                        </td>
                        <td class="px-8 py-2.5">
                            <span class="badge-premium text-[8px] px-2 py-0.5" :class="severityColor(fb.severity)">{{ fb.severity }}</span>
                        </td>
                        <td class="px-8 py-2.5">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-slate-50 rounded-lg flex items-center justify-center text-[9px] text-slate-500 font-black border border-slate-200">{{ fb.assignee?.name.charAt(0) || '?' }}</div>
                                <span class="text-[11px] font-black text-slate-600">{{ fb.assignee?.name || 'Pool Sync' }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-2.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Link :href="route('feedback.show', fb.id)" class="px-3 py-1.5 text-[9px] font-black uppercase tracking-widest bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-lg hover:bg-indigo-600 hover:text-white transition-all">Inspect</Link>
                                <button @click="openModal(fb)" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-white rounded-lg transition-all"><i class="bi bi-pencil-square"></i></button>
                                <button @click="deleteFeedback(fb.id)" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-white rounded-lg transition-all"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!filteredFeedbacks.length">
                        <td colspan="5" class="py-24 text-center">
                            <i class="bi bi-search text-5xl text-slate-100 block mb-4"></i>
                            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Protocol Search Silent. No matching signals.</p>
                        </td>
                    </tr>
                </tbody>
             </table>
        </div>

        <!-- Global Update Modal -->
        <Transition name="fade">
            <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-[#0f172a]/20 backdrop-blur-md">
                <div class="premium-card max-w-lg w-full p-8 animate-scale-in">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-6">Internal Protocol Re-Sync</h3>
                    <form @submit.prevent="submitFeedback" class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Signal Identification</label>
                            <input v-model="feedbackForm.title" class="input-premium font-bold text-sm" placeholder="Title..." required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Severity Tier</label>
                                <select v-model="feedbackForm.severity" class="input-premium text-[11px] font-bold bg-white">
                                    <option>Critical</option><option>High</option><option>Medium</option><option>Low</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Registry Status</label>
                                <select v-model="feedbackForm.status" class="input-premium text-[11px] font-bold bg-white">
                                    <option>Open</option><option>In Progress</option><option>Resolved</option><option>Closed</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Assigned Agent</label>
                            <select v-model="feedbackForm.assignee_id" class="input-premium text-[11px] font-bold bg-white">
                                <option value="">Pool Sync</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Protocol Trace Summary</label>
                            <textarea v-model="feedbackForm.description" class="input-premium text-xs min-h-[120px]" placeholder="Summary..." required></textarea>
                        </div>
                        
                        <div class="flex gap-3 pt-6">
                            <button type="button" @click="closeModal" class="flex-grow btn-premium-secondary text-[10px]">Abort</button>
                            <button type="submit" class="flex-[3] btn-premium-indigo text-[10px]">Initialize Protocol Update</button>
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

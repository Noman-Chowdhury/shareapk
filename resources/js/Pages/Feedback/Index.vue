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
function removeKeepAttachment(path) { feedbackKeepAttachments.value = feedbackKeepAttachments.value.filter(p => p !== path); }

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
    return { Critical: 'bg-rose-50 text-rose-700 border-rose-100', High: 'bg-orange-50 text-orange-700 border-orange-100', Medium: 'bg-amber-50 text-amber-700 border-amber-100', Low: 'bg-slate-50 text-slate-700 border-slate-100' }[sev] || 'bg-slate-50';
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
    <Head title="Quality Assurance Feed" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Quality Control</h2>
                    <p class="text-slate-500 text-sm font-medium">Monitoring bugs, features, and user feedback across all projects.</p>
                </div>
                <div class="flex gap-3">
                    <div class="relative group">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input v-model="searchQuery" type="text" class="input-premium py-2.5 pl-11 w-64 text-sm" placeholder="Search issues..." />
                    </div>
                    <select v-model="statusFilter" class="input-premium py-2.5 px-4 text-sm bg-white font-bold text-slate-700">
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
                    <tr>
                        <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest">Issue / Context</th>
                        <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest text-center">Status</th>
                        <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest">Severity</th>
                        <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest">Assigned To</th>
                        <th class="px-8 py-5 text-[11px] font-black uppercase text-slate-400 tracking-widest text-right pr-12">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="fb in filteredFeedbacks" :key="fb.id" class="group hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center p-1.5 shrink-0 overflow-hidden">
                                    <img v-if="fb.build?.project?.icon_url" :src="'/storage/' + fb.build.project.icon_url" class="w-full h-full object-contain" />
                                    <i v-else class="bi bi-bug text-slate-300"></i>
                                </div>
                                <div class="min-w-0">
                                    <Link :href="route('feedback.show', fb.id)" class="text-sm font-black text-slate-900 group-hover:text-indigo-600 truncate block">{{ fb.title }}</Link>
                                    <Link v-if="fb.build" :href="route('builds.show', fb.build.id)" class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter hover:text-indigo-500">{{ fb.build.project?.name }} v{{ fb.build.version_name }}</Link>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="text-[11px] font-black uppercase tracking-widest" :class="statusColor(fb.status)">{{ fb.status }}</span>
                        </td>
                        <td class="px-8 py-6">
                            <span class="badge-premium text-[9px]" :class="severityColor(fb.severity)">{{ fb.severity }}</span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 bg-slate-900 rounded-full flex items-center justify-center text-[8px] text-white font-black">{{ fb.assignee?.name.charAt(0) || '?' }}</span>
                                <span class="text-xs font-bold text-slate-600">{{ fb.assignee?.name || 'Unassigned' }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right pr-10">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Link :href="route('feedback.show', fb.id)" class="p-2 text-slate-300 hover:text-indigo-600 transition-colors"><i class="bi bi-eye"></i></Link>
                                <button @click="openModal(fb)" class="p-2 text-slate-300 hover:text-indigo-600 transition-colors"><i class="bi bi-pencil-square"></i></button>
                                <button @click="deleteFeedback(fb.id)" class="p-2 text-slate-300 hover:text-rose-500 transition-colors"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!filteredFeedbacks.length">
                        <td colspan="5" class="px-8 py-20 text-center">
                             <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl mx-auto flex items-center justify-center mb-4"><i class="bi bi-search text-slate-200 text-2xl"></i></div>
                             <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">No matching records found in registry</p>
                        </td>
                    </tr>
                </tbody>
             </table>
        </div>

        <!-- Custom Transition Modal for Editing -->
        <Transition name="fade">
            <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/40 backdrop-blur-sm shadow-2xl overflow-y-auto">
                <div class="premium-card max-w-2xl w-full p-8 animate-in zoom-in max-h-[90vh] overflow-y-auto">
                    <h3 class="text-xl font-black text-slate-900 mb-2">Edit Feedback Report</h3>
                    <form @submit.prevent="submitFeedback" class="space-y-4 pt-4">
                        <input v-model="feedbackForm.title" class="input-premium font-bold text-lg" placeholder="A short, descriptive title..." required />
                        <div class="grid grid-cols-3 gap-4">
                            <select v-model="feedbackForm.type" class="input-premium text-sm py-2">
                                <option>Bug</option><option>Feature</option><option>Improvement</option>
                            </select>
                            <select v-model="feedbackForm.severity" class="input-premium text-sm py-2">
                                <option>Critical</option><option>High</option><option>Medium</option><option>Low</option>
                            </select>
                            <select v-model="feedbackForm.status" class="input-premium text-sm py-2">
                                <option>Open</option><option>In Progress</option><option>Resolved</option><option>Closed</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <select v-model="feedbackForm.assignee_id" class="input-premium text-sm py-2">
                                <option value="">No Assignee</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                             <div class="flex gap-2">
                                <input v-model="feedbackForm.device_model" class="input-premium text-sm py-2" placeholder="Device" />
                                <input v-model="feedbackForm.os_version" class="input-premium text-sm py-2" placeholder="OS" />
                             </div>
                        </div>
                        <textarea v-model="feedbackForm.description" class="input-premium text-sm min-h-[120px]" placeholder="Detailed description..." required></textarea>
                        
                        <div class="p-6 border-2 border-dashed border-slate-200 rounded-3xl text-center">
                            <label class="block cursor-pointer">
                                <i class="bi bi-paperclip text-2xl text-slate-300"></i>
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest block mt-2">Add more attachments</span>
                                <input type="file" @change="handleFeedbackFiles" multiple class="hidden" />
                            </label>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="closeModal" class="btn-premium-secondary">Cancel</button>
                            <button type="submit" class="btn-premium-primary flex-grow text-rose-50 bg-rose-600 hover:bg-rose-700 shadow-rose-200">Commit Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

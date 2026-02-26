<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import QrcodeVue from 'qrcode.vue';

const props = defineProps({ build: Object, users: Array });

const activeTab = ref('overview');
const activeModal = ref(null);

const currentUrl = computed(() => route('builds.download', props.build.id));

const shareForm = useForm({ expires_at: '', download_limit: '', password: '' });
const feedbackForm = useForm({
    title: '', description: '', type: 'Bug', severity: 'Medium', status: 'Open',
    device_model: '', os_version: '', screen_size: '', assignee_id: '',
});
const taskForm = useForm({
    title: '', description: '', priority: 'Medium', status: 'Todo',
    assignee_id: '', due_date: ''
});
const commentForm = useForm({ body: '', commentable_id: '', commentable_type: '', attachment: null });

const editingFeedback = ref(null);
const feedbackNewFiles = ref([]);
const feedbackKeepAttachments = ref([]);
const editingTask = ref(null);
const taskNewFiles = ref([]);
const taskKeepAttachments = ref([]);

function openModal(name, data = null) {
    activeModal.value = name;
    if (name === 'feedback') {
        editingFeedback.value = data;
        if (data) {
            feedbackForm.title = data.title;
            feedbackForm.description = data.description;
            feedbackForm.type = data.type;
            feedbackForm.severity = data.severity || 'Medium';
            feedbackForm.status = data.status || 'Open';
            feedbackForm.device_model = data.device_model || '';
            feedbackForm.os_version = data.os_version || '';
            feedbackForm.assignee_id = data.assignee_id || '';
            feedbackKeepAttachments.value = (data.attachments || []).map(a => a.path);
        } else {
            feedbackForm.reset();
            feedbackKeepAttachments.value = [];
        }
    } else if (name === 'task') {
        editingTask.value = data;
        if (data) {
            taskForm.title = data.title;
            taskForm.description = data.description || '';
            taskForm.priority = data.priority || 'Medium';
            taskForm.status = data.status || 'Todo';
            taskForm.assignee_id = data.assignee_id || '';
            taskForm.due_date = data.due_date || '';
            taskKeepAttachments.value = (data.attachments || []).map(a => a.path);
        } else {
            taskForm.reset();
            taskKeepAttachments.value = [];
        }
    }
}

function closeModal() {
    activeModal.value = null;
    editingFeedback.value = null;
    editingTask.value = null;
}

function submitShare() {
    shareForm.post(route('share-links.store', props.build.id), {
        preserveScroll: true,
        onSuccess: () => {
            shareForm.reset();
            closeModal();
        },
    });
}

function deleteShare(id) {
    if (confirm('Delete this public link?')) router.delete(route('share-links.destroy', id), { preserveScroll: true });
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => alert('Copied to clipboard!'));
}

function handleFeedbackFiles(e) { feedbackNewFiles.value = Array.from(e.target.files); }
function handleTaskFiles(e) { taskNewFiles.value = Array.from(e.target.files); }

function submitFeedback() {
    const isEditing = !!editingFeedback.value;
    const url = isEditing ? route('feedback.update', editingFeedback.value.id) : route('feedback.store', props.build.id);
    const payload = feedbackForm.data();

    feedbackNewFiles.value.forEach((file, i) => payload[`new_attachments[${i}]`] = file);
    if (isEditing) {
        feedbackKeepAttachments.value.forEach((path, i) => payload[`keep_attachments[${i}]`] = path);
        payload['_method'] = 'PUT';
    }

    feedbackForm.transform(() => payload).post(url, {
        forceFormData: true, preserveScroll: true,
        onSuccess: () => { closeModal(); feedbackForm.reset(); feedbackNewFiles.value = []; },
    });
}

function submitTask() {
    const isEditing = !!editingTask.value;
    const url = isEditing ? route('tasks.update', editingTask.value.id) : route('tasks.store', props.build.id);
    const payload = taskForm.data();

    taskNewFiles.value.forEach((file, i) => payload[`new_attachments[${i}]`] = file);
    if (isEditing) {
        taskKeepAttachments.value.forEach((path, i) => payload[`keep_attachments[${i}]`] = path);
        payload['_method'] = 'PUT';
    }

    taskForm.transform(() => payload).post(url, {
        forceFormData: true, preserveScroll: true,
        onSuccess: () => { closeModal(); taskForm.reset(); taskNewFiles.value = []; },
    });
}

function deleteFeedback(id) { if (confirm('Delete feedback?')) router.delete(route('feedback.destroy', id), { preserveScroll: true }); }
function deleteTask(id) { if (confirm('Delete task?')) router.delete(route('tasks.destroy', id), { preserveScroll: true }); }
function deleteBuild() { if (confirm('Delete this build?')) router.delete(route('builds.destroy', props.build.id)); }

function severityColor(sev) {
    return { Critical: 'bg-rose-50 text-rose-700', High: 'bg-orange-50 text-orange-700', Medium: 'bg-amber-50 text-amber-700', Low: 'bg-slate-50 text-slate-700' }[sev] || 'bg-slate-50';
}
function statusColor(status) {
    return { Open: 'text-rose-600', 'In Progress': 'text-amber-600', Resolved: 'text-emerald-600', Closed: 'text-slate-500' }[status] || 'text-slate-500';
}
function formatDate(d) { return d ? new Date(d).toLocaleDateString() : '—'; }
function formatBytes(b) { return b >= 1048576 ? (b/1048576).toFixed(1)+' MB' : (b/1024).toFixed(1)+' KB'; }
</script>

<template>
    <Head :title="`Build detail: ${build.project?.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest">
                    <Link :href="route('projects.index')" class="text-slate-400 hover:text-indigo-600 transition-colors">Project Hub</Link>
                    <i class="bi bi-chevron-right text-[8px] text-slate-300"></i>
                    <Link :href="route('projects.show', build.project?.id)" class="text-slate-400 hover:text-indigo-600 transition-colors">{{ build.project?.name }}</Link>
                    <i class="bi bi-chevron-right text-[8px] text-slate-300"></i>
                    <span class="text-slate-800">v{{ build.version_name }}</span>
                </nav>
                <div class="flex items-center gap-3">
                    <button @click="openModal('qr')" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-all"><i class="bi bi-qr-code text-lg"></i></button>
                    <button @click="openModal('share')" class="btn-premium-secondary px-6">Public Sync</button>
                    <a :href="currentUrl" class="btn-premium-indigo px-8">
                        <i class="bi bi-download mr-2"></i> Download APK
                    </a>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Asset Information Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <div class="premium-card p-6 text-center animate-slide-up" style="animation-delay: 0.1s">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-slate-50 border border-slate-100 p-2 flex items-center justify-center shadow-inner overflow-hidden">
                        <img v-if="build.project?.icon_url" :src="'/storage/' + build.project.icon_url" class="max-w-full max-h-full object-contain" />
                        <i v-else class="bi bi-box-seam text-2xl text-slate-200"></i>
                    </div>
                    <h1 class="text-lg font-black text-slate-800 mb-1 truncate">{{ build.project?.name }}</h1>
                    <p class="text-[9px] font-mono text-slate-400 uppercase tracking-tighter mb-4">{{ build.project?.package_name }}</p>
                    
                    <div class="flex justify-center gap-2">
                        <span class="badge-premium bg-slate-900 text-white">{{ build.build_type }}</span>
                        <span class="badge-premium" :class="build.status === 'Approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">{{ build.status }}</span>
                    </div>
                </div>

                <div class="premium-card p-6 animate-slide-up" style="animation-delay: 0.2s">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 border-b border-slate-50 pb-2">Technical Metadata</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500">SIGNATURE</span>
                            <span class="text-xs font-black text-slate-800">{{ build.version_code }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500">PAYLOAD SIZE</span>
                            <span class="text-xs font-black text-slate-800">{{ formatBytes(build.file_size) }}</span>
                        </div>
                        <div class="pt-4 border-t border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 text-center">Protocol Agent</p>
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[9px] font-bold text-slate-600 border border-slate-200">{{ build.uploader?.name.charAt(0) }}</div>
                                <span class="text-[11px] font-bold text-slate-700">{{ build.uploader?.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button v-if="$page.props.auth.user.roles.includes('Admin')" @click="deleteBuild" class="w-full btn-premium-matte bg-rose-500 hover:bg-rose-600 text-white">
                    <i class="bi bi-trash-fill mr-2"></i> Wipe Build Trace
                </button>
            </div>

            <!-- Content Area -->
            <div class="lg:col-span-3 space-y-8">
                <!-- Data Streams Navigation -->
                <div class="flex items-center gap-8 border-b border-slate-200">
                    <button v-for="tab in ['overview', 'feedback', 'tasks', 'share', 'downloads']" :key="tab" @click="activeTab = tab" class="pb-4 text-xs font-black uppercase tracking-widest transition-all relative" :class="activeTab === tab ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'">
                        {{ tab === 'share' ? 'Public Sync' : tab }}
                        <span v-if="tab === 'feedback' && build.feedbacks_count" class="ml-1.5 px-1.5 py-0.5 rounded-lg bg-indigo-600 text-white text-[8px] tracking-normal">{{ build.feedbacks_count }}</span>
                        <div v-if="activeTab === tab" class="absolute bottom-0 left-0 right-0 h-1 bg-indigo-600 rounded-t-full animate-fade-in"></div>
                    </button>
                </div>

                <div class="animate-fade-in">
                    <!-- Overview -->
                    <div v-if="activeTab === 'overview'" class="space-y-6">
                        <section class="premium-card p-10 matte-surface animate-slide-up">
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                                <i class="bi bi-card-checklist text-indigo-500 text-lg"></i> Dispatch Log / Release Notes
                            </h3>
                            <div class="text-sm text-slate-600 leading-relaxed font-medium whitespace-pre-wrap">
                                {{ build.release_notes || 'No protocol documentation provided for this version.' }}
                            </div>
                        </section>
                    </div>

                    <!-- Feedback -->
                    <div v-if="activeTab === 'feedback'" class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">Quality Feedback Loop</h3>
                            <button @click="openModal('feedback')" class="btn-premium-indigo px-8 text-xs py-2">
                                <i class="bi bi-plus-lg mr-2"></i> Report Signal
                            </button>
                        </div>
                        
                        <div v-if="!build.feedbacks?.length" class="premium-card p-20 text-center text-slate-400 animate-slide-up">
                            <i class="bi bi-shield-check text-5xl mb-4 block opacity-10"></i>
                            <p class="font-bold uppercase tracking-[0.2em] text-[10px]">Registry is silent. System integrity verified.</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="fb in build.feedbacks" :key="fb.id" class="premium-card p-6 animate-slide-up border-l-4" :class="severityColor(fb.severity).replace('bg-', 'border-')">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-3 mb-1">
                                            <Link :href="route('feedback.show', fb.id)" class="text-base font-bold text-slate-900 hover:text-indigo-600 transition-colors">{{ fb.title }}</Link>
                                            <span class="badge-premium" :class="severityColor(fb.severity)">{{ fb.severity }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                            <span :class="statusColor(fb.status)">{{ fb.status }}</span>
                                            <span class="text-slate-200">/</span>
                                            <span>{{ fb.type }}</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <button @click="openModal('feedback', fb)" class="p-2 text-slate-400 hover:text-indigo-500 transition-colors"><i class="bi bi-pencil-square"></i></button>
                                        <button @click="deleteFeedback(fb.id)" class="p-2 text-slate-400 hover:text-rose-500 transition-colors"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-4">{{ fb.description }}</p>
                                <div class="flex justify-between items-center pt-4 border-t border-slate-50">
                                    <span class="text-[9px] font-black text-slate-400">LOGGED BY: {{ fb.user?.name || 'System Agent' }}</span>
                                    <Link :href="route('feedback.show', fb.id)" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 transition-colors">Full Protocol Trace</Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks -->
                    <div v-if="activeTab === 'tasks'" class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">Active Operations Queue</h3>
                            <button @click="openModal('task')" class="btn-premium-indigo bg-slate-900 hover:bg-black px-8 text-xs py-2">
                                <i class="bi bi-plus-lg mr-2"></i> Deploy Task
                            </button>
                        </div>
                        <div v-if="!build.tasks?.length" class="premium-card p-20 text-center text-slate-400 animate-slide-up">
                            <i class="bi bi-check-all text-5xl mb-4 block opacity-10"></i>
                            <p class="font-bold uppercase tracking-[0.2em] text-[10px]">Queue Clear. No active operations.</p>
                        </div>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="tk in build.tasks" :key="tk.id" class="premium-card p-5 animate-slide-up group">
                                <div class="flex justify-between mb-3">
                                    <span class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">{{ tk.title }}</span>
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="openModal('task', tk)" class="text-slate-300 hover:text-indigo-500"><i class="bi bi-pencil-square"></i></button>
                                        <button @click="deleteTask(tk.id) " class="text-slate-300 hover:text-rose-500"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-lg bg-slate-900 text-white">{{ tk.status }}</span>
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-lg" :class="tk.priority === 'Urgent' ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-500'">{{ tk.priority }}</span>
                                </div>
                                <div class="pt-3 border-t border-slate-50 flex items-center justify-between mt-auto">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-indigo-50 flex items-center justify-center text-[9px] font-bold text-indigo-600">{{ tk.assignee?.name.charAt(0) || '?' }}</div>
                                        <span class="text-[10px] font-bold text-slate-500">{{ tk.assignee?.name || 'Unassigned' }}</span>
                                    </div>
                                    <span v-if="tk.due_date" class="text-[9px] font-black text-rose-500 uppercase">DUE: {{ formatDate(tk.due_date) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Public Sync / Share -->
                    <div v-if="activeTab === 'share'" class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">Public Access Channels</h3>
                            <button @click="openModal('share')" class="btn-premium-indigo px-8 text-xs py-2">
                                <i class="bi bi-link-45deg mr-1"></i> New Protocol Link
                            </button>
                        </div>
                        <div class="premium-card overflow-hidden animate-slide-up">
                             <table class="w-full text-left">
                                <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-400 tracking-[0.2em]">
                                    <tr>
                                        <th class="px-6 py-4">Channel Token</th>
                                        <th class="px-6 py-4">Security Grade</th>
                                        <th class="px-6 py-4 text-center">Sync Count</th>
                                        <th class="px-6 py-4 text-right">Protocol</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                                    <tr v-for="link in build.share_links" :key="link.id" class="group hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <code class="text-[11px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg">{{ link.token }}</code>
                                                <button @click="copyToClipboard(route('public.share.show', link.token))" class="text-slate-300 hover:text-indigo-600 transition-colors"><i class="bi bi-clipboard"></i></button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <span v-if="link.password" class="badge-premium bg-slate-900 text-white"><i class="bi bi-lock-fill mr-1"></i> Encrypted</span>
                                            <span v-else class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Open Sync</span>
                                        </td>
                                        <td class="px-6 py-5 text-center font-bold text-slate-800">{{ link.download_count }}</td>
                                        <td class="px-6 py-5 text-right font-black">
                                            <button @click="deleteShare(link.id)" class="text-rose-500 hover:text-rose-700 transition-colors uppercase tracking-widest text-[10px]">Revoke Access</button>
                                        </td>
                                    </tr>
                                    <tr v-if="!build.share_links?.length">
                                        <td colspan="4" class="px-6 py-12 text-center text-slate-300 font-bold uppercase tracking-widest text-[9px]">No active sync channels found.</td>
                                    </tr>
                                </tbody>
                             </table>
                        </div>
                    </div>

                    <!-- Downloads -->
                    <div v-if="activeTab === 'downloads'" class="space-y-6">
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-4">Handshake Audit Stream</h3>
                        <div class="premium-card overflow-hidden animate-slide-up">
                             <table class="w-full text-left">
                                <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-400 tracking-[0.2em]">
                                    <tr>
                                        <th class="px-6 py-4">Target Agent</th>
                                        <th class="px-6 py-4">IP Protocol / Source</th>
                                        <th class="px-6 py-4 text-right">Timestamp Echo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 text-xs text-slate-600">
                                    <tr v-for="dl in build.downloads" :key="dl.id" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">{{ (dl.user?.name || 'G').charAt(0) }}</div>
                                                <span class="font-bold text-slate-800">{{ dl.user?.name || (dl.share_link ? 'Link Sync' : 'Guest Probe') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 font-mono text-[10px] text-slate-400">{{ dl.ip_address }}</td>
                                        <td class="px-6 py-5 text-right font-black text-[10px] text-slate-400 uppercase">{{ formatDate(dl.created_at) }}</td>
                                    </tr>
                                </tbody>
                             </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Modals -->
        <Transition name="fade">
            <div v-if="activeModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-[#0f172a]/20 backdrop-blur-md">
                
                <!-- QR Entry -->
                <div v-if="activeModal === 'qr'" class="premium-card max-w-xs w-full p-8 text-center animate-scale-in">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4">Direct Sync QR</h3>
                    <div class="bg-white p-4 border border-slate-100 rounded-2xl inline-block shadow-inner mb-6">
                        <QrcodeVue :value="currentUrl" :size="200" level="H" />
                    </div>
                    <button @click="closeModal" class="w-full btn-premium-matte text-xs">Acknowledge</button>
                </div>

                <!-- Sync Config Modal -->
                <div v-if="activeModal === 'share'" class="premium-card max-w-sm w-full p-8 animate-scale-in">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-6">Protocol Sync Config</h3>
                    <form @submit.prevent="submitShare" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest -mb-2">Expiry Echo</label>
                            <input v-model="shareForm.expires_at" type="datetime-local" class="input-premium" />
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Cap Limit</label>
                                    <input v-model="shareForm.download_limit" type="number" class="input-premium" placeholder="INF" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Pin Access</label>
                                    <input v-model="shareForm.password" type="password" class="input-premium" placeholder="None" />
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2 pt-4">
                            <button type="button" @click="closeModal" class="flex-grow btn-premium-secondary text-[10px]">Abort</button>
                            <button type="submit" class="flex-[2] btn-premium-indigo text-[10px]">Initialize Sync</button>
                        </div>
                    </form>
                </div>
                
                <!-- Quality Signal Modal -->
                <div v-if="activeModal === 'feedback'" class="premium-card max-w-lg w-full p-8 animate-scale-in">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-6">{{ editingFeedback ? 'Edit Protocol Signal' : 'Interface Performance Signal' }}</h3>
                    <form @submit.prevent="submitFeedback" class="space-y-4">
                        <input v-model="feedbackForm.title" class="input-premium font-bold text-sm" placeholder="Signal Identification..." required />
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Signal Type</label>
                                <select v-model="feedbackForm.type" class="input-premium text-[11px] font-bold">
                                    <option>Bug</option><option>Feature</option><option>UI/UX</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Severity Tier</label>
                                <select v-model="feedbackForm.severity" class="input-premium text-[11px] font-bold">
                                    <option>Critical</option><option>High</option><option>Medium</option><option>Low</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-1">
                             <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Target Agent</label>
                            <select v-model="feedbackForm.assignee_id" class="input-premium text-[11px] font-bold">
                                <option value="">Pool Sync</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                        </div>
                        <textarea v-model="feedbackForm.description" class="input-premium text-xs min-h-[100px]" placeholder="Signal summary echo..." required></textarea>
                        
                        <div class="flex gap-2 pt-4">
                            <button type="button" @click="closeModal" class="btn-premium-secondary text-[10px]">Dismiss</button>
                            <button type="submit" class="flex-grow btn-premium-indigo text-[10px]">{{ editingFeedback ? 'Commit Logic' : 'Broadcast Signal' }}</button>
                        </div>
                    </form>
                </div>

                <!-- Assignment Modal -->
                <div v-if="activeModal === 'task'" class="premium-card max-w-sm w-full p-8 animate-scale-in">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-6">Operational Assignment</h3>
                    <form @submit.prevent="submitTask" class="space-y-4">
                        <input v-model="taskForm.title" class="input-premium text-sm font-bold" placeholder="Operational Objective..." required />
                        <div class="grid grid-cols-2 gap-3">
                             <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Agent</label>
                                <select v-model="taskForm.assignee_id" class="input-premium text-[11px]">
                                    <option value="">Pool</option>
                                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </select>
                             </div>
                             <div class="space-y-1">
                                <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Tier</label>
                                <select v-model="taskForm.priority" class="input-premium text-[11px]">
                                    <option>Low</option><option>Medium</option><option>High</option><option>Urgent</option>
                                </select>
                             </div>
                        </div>
                        <div class="flex gap-2 pt-4">
                            <button type="button" @click="closeModal" class="btn-premium-secondary text-[10px]">Cancel</button>
                            <button type="submit" class="flex-grow btn-premium-indigo text-[10px]">Initialize Operation</button>
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

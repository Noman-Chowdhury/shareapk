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
function handleCommentAttachment(e) { commentForm.attachment = e.target.files[0]; }

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

const commentTarget = ref(null);
function openCommentBox(item, type) {
    const fullType = 'App\\Models\\' + type;
    if (commentTarget.value?.id === item.id && commentForm.commentable_type === fullType) {
        commentTarget.value = null;
    } else {
        commentTarget.value = item;
        commentForm.commentable_id = item.id;
        commentForm.commentable_type = fullType;
        commentForm.body = '';
        commentForm.attachment = null;
    }
}

function submitComment() {
    commentForm.post(route('comments.store'), {
        preserveScroll: true, forceFormData: true,
        onSuccess: () => { commentForm.reset(); commentTarget.value = null; },
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
function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'; }
function formatBytes(b) { return b >= 1048576 ? (b/1048576).toFixed(1)+' MB' : (b/1024).toFixed(1)+' KB'; }
</script>

<template>
    <Head :title="`Build: v${build.version_name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <nav class="flex items-center gap-2 text-xs font-medium">
                    <Link :href="route('projects.index')" class="text-slate-500 hover:text-indigo-600">Projects</Link>
                    <i class="bi bi-chevron-right text-[8px] text-slate-300"></i>
                    <Link :href="route('projects.show', build.project?.id)" class="text-slate-500 hover:text-indigo-600">{{ build.project?.name }}</Link>
                    <i class="bi bi-chevron-right text-[8px] text-slate-300"></i>
                    <span class="text-slate-900 font-bold">Version {{ build.version_name }}</span>
                </nav>
                <div class="flex items-center gap-2">
                    <button @click="openModal('qr')" class="p-1.5 text-slate-500 hover:bg-white rounded-lg border border-transparent hover:border-slate-200 transition-all"><i class="bi bi-qr-code"></i></button>
                    <button @click="openModal('share')" class="btn-premium-secondary py-1 text-xs">Share</button>
                    <a :href="route('builds.download', build.id)" class="btn-premium-indigo py-1 text-xs">Download APK</a>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar Info Column -->
            <div class="lg:col-span-1 space-y-4">
                <div class="premium-card p-5 text-center">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-xl bg-slate-50 border border-slate-100 p-2 flex items-center justify-center overflow-hidden">
                        <img v-if="build.project?.icon_url" :src="'/storage/' + build.project.icon_url" class="max-w-full max-h-full object-contain" />
                        <i v-else class="bi bi-box-seam text-2xl text-slate-200"></i>
                    </div>
                    <h1 class="text-base font-bold text-slate-900 mb-0.5">{{ build.project?.name }}</h1>
                    <p class="text-[9px] font-mono text-slate-400 uppercase mb-3">{{ build.project?.package_name }}</p>
                    
                    <div class="flex justify-center gap-2">
                        <span class="badge-premium bg-indigo-50 text-indigo-700">{{ build.build_type }}</span>
                        <span class="badge-premium" :class="build.status === 'Approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">{{ build.status }}</span>
                    </div>
                </div>

                <!-- Technical Specs -->
                <div class="premium-card p-5">
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-50 pb-2">Technical Metadata</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-500">Version Code</span>
                            <span class="font-bold text-slate-800">{{ build.version_code }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-500">File Size</span>
                            <span class="font-bold text-slate-800">{{ formatBytes(build.file_size) }}</span>
                        </div>
                        <div class="pt-3 border-t border-slate-50 text-[10px] text-slate-400">
                           By <span class="font-bold text-slate-600">{{ build.uploader?.name }}</span> • {{ formatDate(build.created_at) }}
                        </div>
                    </div>
                </div>

                <button v-if="$page.props.auth.user.roles.includes('Admin')" @click="deleteBuild" class="w-full py-2 rounded-lg border border-rose-100 text-rose-500 text-[11px] font-bold hover:bg-rose-50 transition-colors">
                    <i class="bi bi-trash mr-1"></i> Delete Build
                </button>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Navigation Tabs -->
                <div class="flex items-center gap-6 border-b border-slate-200">
                    <button v-for="tab in ['overview', 'feedback', 'tasks', 'share', 'downloads']" :key="tab" @click="activeTab = tab" class="pb-3 text-xs font-bold capitalize transition-all relative" :class="activeTab === tab ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'">
                        {{ tab === 'share' ? 'Public Links' : tab }}
                        <span v-if="tab === 'feedback' && build.feedbacks_count" class="ml-1 px-1 py-0.5 rounded-full bg-rose-500 text-white text-[8px]">{{ build.feedbacks_count }}</span>
                        <span v-if="tab === 'tasks' && build.tasks_count" class="ml-1 px-1 py-0.5 rounded-full bg-amber-500 text-white text-[8px]">{{ build.tasks_count }}</span>
                        <div v-if="activeTab === tab" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></div>
                    </button>
                </div>

                <div class="animate-in fade-in duration-200">
                    <!-- Overview Tab -->
                    <div v-if="activeTab === 'overview'" class="space-y-6">
                        <div class="premium-card p-6">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <i class="bi bi-journal-text"></i> Release Notes
                            </h3>
                            <div class="text-xs text-slate-600 leading-relaxed font-medium">
                                {{ build.release_notes || 'No release specification provided.' }}
                            </div>
                        </div>
                    </div>

                    <!-- Feedback Tab -->
                    <div v-if="activeTab === 'feedback'" class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-sm font-bold text-slate-900 uppercase">Quality Reports</h3>
                            <button @click="openModal('feedback')" class="btn-premium-indigo py-1 text-[10px]">Report Issue</button>
                        </div>
                        
                        <div v-if="!build.feedbacks?.length" class="premium-card py-16 text-center text-slate-300 italic text-xs">
                            No issues reported in this cycle.
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="fb in build.feedbacks" :key="fb.id" class="premium-card p-5">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <Link :href="route('feedback.show', fb.id)" class="text-sm font-bold text-slate-900 hover:text-indigo-600">{{ fb.title }}</Link>
                                            <span class="badge-premium" :class="severityColor(fb.severity)">{{ fb.severity }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] font-bold uppercase">
                                            <span :class="statusColor(fb.status)">{{ fb.status }}</span>
                                            <span class="text-slate-300">•</span>
                                            <span class="text-slate-500">{{ fb.type }}</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <button @click="openModal('feedback', fb)" class="text-slate-300 hover:text-indigo-500"><i class="bi bi-pencil-square"></i></button>
                                        <button @click="deleteFeedback(fb.id)" class="text-slate-300 hover:text-rose-500"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mb-4 line-clamp-2 leading-relaxed">{{ fb.description }}</p>
                                <div class="flex justify-between items-center">
                                    <div class="flex gap-2">
                                        <span v-for="att in fb.attachments" :key="att.path" class="text-[9px] font-bold text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-100">📎 Attached</span>
                                    </div>
                                    <button @click="openCommentBox(fb, 'Feedback')" class="text-[10px] font-bold text-indigo-600">Discussion ({{ fb.comments?.length || 0 }})</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks Tab -->
                    <div v-if="activeTab === 'tasks'" class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-sm font-bold text-slate-900 uppercase">Tasks & Actions</h3>
                            <button @click="openModal('task')" class="btn-premium-indigo py-1 text-[10px] bg-slate-900">New Task</button>
                        </div>
                        <div v-if="!build.tasks?.length" class="premium-card py-16 text-center text-slate-300 italic text-xs">
                            Execution queue is empty.
                        </div>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div v-for="tk in build.tasks" :key="tk.id" class="premium-card p-4">
                                <Link :href="route('tasks.show', tk.id)" class="text-xs font-bold text-slate-900 block mb-2">{{ tk.title }}</Link>
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded uppercase">{{ tk.status }}</span>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase" :class="tk.priority === 'Urgent' ? 'bg-rose-500 text-white' : 'bg-slate-50 text-slate-500'">{{ tk.priority }}</span>
                                </div>
                                <div class="pt-3 border-t border-slate-50 flex items-center justify-between text-[10px]">
                                    <span class="text-slate-600">{{ tk.assignee?.name || 'Unassigned' }}</span>
                                    <span v-if="tk.due_date" class="text-rose-500 font-bold">{{ formatDate(tk.due_date) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Public Links Tab -->
                    <div v-if="activeTab === 'share'" class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-sm font-bold text-slate-900 uppercase">Public Access</h3>
                            <button @click="openModal('share')" class="btn-premium-indigo py-1 text-[10px]">Generate link</button>
                        </div>
                        <div class="premium-card overflow-hidden">
                             <table class="w-full text-left">
                                <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                    <tr>
                                        <th class="px-5 py-3">Token</th>
                                        <th class="px-5 py-3">Status</th>
                                        <th class="px-5 py-3">Usage</th>
                                        <th class="px-5 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 text-xs text-slate-700">
                                    <tr v-for="link in build.share_links" :key="link.id" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-5 py-3 font-mono font-bold">{{ link.token }}</td>
                                        <td class="px-5 py-3">
                                            <span v-if="link.password" class="text-slate-400">Locked</span>
                                            <span v-else class="text-emerald-500">Public</span>
                                        </td>
                                        <td class="px-5 py-3">{{ link.download_count }} hits</td>
                                        <td class="px-5 py-3 text-right">
                                            <button @click="deleteShare(link.id)" class="text-rose-500 font-bold hover:underline">Revoke</button>
                                        </td>
                                    </tr>
                                    <tr v-if="!build.share_links?.length">
                                        <td colspan="4" class="px-5 py-10 text-center text-slate-300 italic">No links generated.</td>
                                    </tr>
                                </tbody>
                             </table>
                        </div>
                    </div>

                    <!-- Downloads Tab -->
                    <div v-if="activeTab === 'downloads'" class="space-y-4">
                         <h3 class="text-sm font-bold text-slate-900 uppercase">Audit Stream</h3>
                         <div class="premium-card overflow-hidden">
                             <table class="w-full text-left">
                                <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                    <tr>
                                        <th class="px-5 py-3">Initiator</th>
                                        <th class="px-5 py-3">Protocol</th>
                                        <th class="px-5 py-3 text-right">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 text-xs text-slate-600">
                                    <tr v-for="dl in build.downloads" :key="dl.id">
                                        <td class="px-5 py-3">
                                            <span class="font-bold text-slate-800">{{ dl.user?.name || (dl.share_link ? 'Public Link' : 'Guest') }}</span>
                                        </td>
                                        <td class="px-5 py-3 font-mono text-[10px]">{{ dl.ip_address }}</td>
                                        <td class="px-5 py-3 text-right text-slate-400 text-[10px]">{{ formatDate(dl.created_at) }}</td>
                                    </tr>
                                </tbody>
                             </table>
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <Transition name="fade">
            <div v-if="activeModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/10 backdrop-blur-sm">
                <!-- QR Modal -->
                <div v-if="activeModal === 'qr'" class="premium-card max-w-xs w-full p-6 text-center">
                    <h3 class="text-sm font-bold text-slate-900 mb-4">Scan to Download</h3>
                    <div class="bg-white p-3 border border-slate-100 rounded-lg inline-block mb-4">
                        <QrcodeVue :value="currentUrl" :size="180" level="M" />
                    </div>
                    <button @click="closeModal" class="w-full btn-premium-secondary py-1 text-xs">Dismiss</button>
                </div>

                <!-- Generic Form Modal -->
                <div v-if="activeModal === 'share'" class="premium-card max-w-sm w-full p-6">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Access Parameters</h3>
                    <form @submit.prevent="submitShare" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <input v-model="shareForm.expires_at" type="datetime-local" class="input-premium py-1.5 text-xs" />
                            <div class="grid grid-cols-2 gap-2">
                                <input v-model="shareForm.download_limit" type="number" class="input-premium py-1.5 text-xs" placeholder="Hits Limit" />
                                <input v-model="shareForm.password" type="password" class="input-premium py-1.5 text-xs" placeholder="PIN Code" />
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="closeModal" class="flex-grow btn-premium-secondary py-1.5 text-xs">Cancel</button>
                            <button type="submit" class="flex-[2] btn-premium-indigo py-1.5 text-xs">Generate Access Link</button>
                        </div>
                    </form>
                </div>
                
                <div v-if="activeModal === 'feedback'" class="premium-card max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">{{ editingFeedback ? 'Edit Report' : 'New Performance Report' }}</h3>
                    <form @submit.prevent="submitFeedback" class="space-y-3">
                        <input v-model="feedbackForm.title" class="input-premium font-bold text-sm" placeholder="Issue title..." required />
                        <div class="grid grid-cols-2 gap-3">
                            <select v-model="feedbackForm.type" class="input-premium text-[11px] py-1.5">
                                <option>Bug</option><option>Feature</option><option>UI/UX</option>
                            </select>
                            <select v-model="feedbackForm.severity" class="input-premium text-[11px] py-1.5">
                                <option>Critical</option><option>High</option><option>Medium</option><option>Low</option>
                            </select>
                        </div>
                        <textarea v-model="feedbackForm.description" class="input-premium text-xs min-h-[80px]" placeholder="Problem overview..." required></textarea>
                        <div class="flex gap-2">
                            <button type="button" @click="closeModal" class="btn-premium-secondary py-1.5 text-xs">Dismiss</button>
                            <button type="submit" class="flex-grow btn-premium-indigo py-1.5 text-xs">Commit Registry</button>
                        </div>
                    </form>
                </div>

                <div v-if="activeModal === 'task'" class="premium-card max-w-sm w-full p-6">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Internal Assignment</h3>
                    <form @submit.prevent="submitTask" class="space-y-3">
                        <input v-model="taskForm.title" class="input-premium text-sm font-bold" placeholder="Task objective..." required />
                        <div class="grid grid-cols-2 gap-3">
                            <select v-model="taskForm.assignee_id" class="input-premium text-[11px] py-1.5">
                                <option value="">Pool</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                            <select v-model="taskForm.priority" class="input-premium text-[11px] py-1.5">
                                <option>Low</option><option>Medium</option><option>High</option><option>Urgent</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="closeModal" class="btn-premium-secondary py-1.5 text-xs">Abort</button>
                            <button type="submit" class="flex-grow btn-premium-indigo py-1.5 text-xs">Create Assignment</button>
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

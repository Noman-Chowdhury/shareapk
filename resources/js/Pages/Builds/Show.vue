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
    return { Critical: 'bg-rose-100 text-rose-700 border-rose-200', High: 'bg-orange-100 text-orange-700 border-orange-200', Medium: 'bg-amber-100 text-amber-700 border-amber-200', Low: 'bg-slate-100 text-slate-700 border-slate-200' }[sev] || 'bg-slate-100';
}
function statusColor(status) {
    return { Open: 'text-rose-600', 'In Progress': 'text-amber-600', Resolved: 'text-emerald-600', Closed: 'text-slate-500' }[status] || 'text-slate-500';
}
function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'; }
function formatBytes(b) { return b >= 1048576 ? (b/1048576).toFixed(1)+' MB' : (b/1024).toFixed(1)+' KB'; }
</script>

<template>
    <Head :title="`Build detail: ${build.project?.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <nav class="flex items-center gap-2 text-sm font-medium">
                    <Link :href="route('projects.index')" class="text-slate-500 hover:text-indigo-600 transition-colors">Projects</Link>
                    <i class="bi bi-chevron-right text-[10px] text-slate-300"></i>
                    <Link :href="route('projects.show', build.project?.id)" class="text-slate-500 hover:text-indigo-600 transition-colors">{{ build.project?.name }}</Link>
                    <i class="bi bi-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-slate-900 font-bold">Version {{ build.version_name }}</span>
                </nav>
                <div class="flex items-center gap-2">
                    <button @click="openModal('qr')" class="p-2.5 text-slate-500 hover:bg-white rounded-xl border border-transparent hover:border-slate-200 hover:shadow-sm transition-all"><i class="bi bi-qr-code"></i></button>
                    <button @click="openModal('share')" class="btn-premium-secondary"><i class="bi bi-link-45deg mr-2"></i> Share</button>
                    <a :href="route('builds.download', build.id)" class="btn-premium-primary"><i class="bi bi-download mr-2"></i> Download APK</a>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar-ish Info Column -->
            <div class="lg:col-span-1 space-y-6">
                <!-- App Icon & Core Info -->
                <div class="premium-card p-6 text-center">
                    <div class="w-24 h-24 mx-auto mb-4 rounded-3xl bg-slate-50 border border-slate-100 p-3 shadow-inner flex items-center justify-center overflow-hidden">
                        <img v-if="build.project?.icon_url" :src="'/storage/' + build.project.icon_url" class="max-w-full max-h-full object-contain" />
                        <i v-else class="bi bi-box-seam text-4xl text-slate-200"></i>
                    </div>
                    <h1 class="text-xl font-black text-slate-900 mb-1 line-clamp-1">{{ build.project?.name }}</h1>
                    <p class="text-xs font-bold text-slate-400 font-mono tracking-tighter mb-4">{{ build.project?.package_name }}</p>
                    
                    <div class="flex flex-wrap justify-center gap-2">
                        <span class="badge-premium bg-indigo-50 text-indigo-700 border border-indigo-100">{{ build.build_type }}</span>
                        <span class="badge-premium" :class="build.status === 'Approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100'">{{ build.status }}</span>
                    </div>
                </div>

                <!-- Technical Specs Card -->
                <div class="premium-card p-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Architecture & Metadata</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center group">
                            <span class="text-sm font-medium text-slate-500">Version Code</span>
                            <span class="text-sm font-black text-slate-800">{{ build.version_code }}</span>
                        </div>
                        <div class="flex justify-between items-center group">
                            <span class="text-sm font-medium text-slate-500">File Size</span>
                            <span class="text-sm font-black text-slate-800">{{ formatBytes(build.file_size) }}</span>
                        </div>
                        <div class="flex justify-between items-center group">
                            <span class="text-sm font-medium text-slate-500">Min SDK</span>
                            <span class="text-sm font-black text-slate-800">Android 8.0</span>
                        </div>
                        <div class="pt-4 border-t border-slate-100 text-[11px] text-slate-400">
                            Uploaded by <span class="font-bold text-slate-600">{{ build.uploader?.name }}</span> on {{ formatDate(build.created_at) }}
                        </div>
                    </div>
                </div>

                <button v-if="$page.props.auth.user.roles.includes('Admin')" @click="deleteBuild" class="w-full py-3 rounded-xl border border-rose-200 text-rose-500 text-sm font-bold hover:bg-rose-50 transition-colors">
                    <i class="bi bi-trash mr-2"></i> Permanently Delete Build
                </button>
            </div>

            <!-- Main Content Area -->
            <div class="lg:col-span-3 space-y-8">
                <!-- Navigation Tabs -->
                <div class="flex items-center gap-8 border-b border-slate-200">
                    <button v-for="tab in ['overview', 'feedback', 'tasks', 'share', 'downloads']" :key="tab" @click="activeTab = tab" class="pb-4 text-sm font-bold capitalize transition-all relative" :class="activeTab === tab ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'">
                        {{ tab === 'share' ? 'Public Links' : tab }}
                        <span v-if="tab === 'feedback' && build.feedbacks_count" class="ml-1.5 px-1.5 py-0.5 rounded bg-rose-500 text-white text-[9px]">{{ build.feedbacks_count }}</span>
                        <span v-if="tab === 'tasks' && build.tasks_count" class="ml-1.5 px-1.5 py-0.5 rounded bg-amber-500 text-white text-[9px]">{{ build.tasks_count }}</span>
                        <div v-if="activeTab === tab" class="absolute bottom-0 left-0 right-0 h-1 bg-indigo-600 rounded-t-full"></div>
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="animate-in fade-in duration-300">
                    <!-- Overview Tab -->
                    <div v-if="activeTab === 'overview'" class="space-y-8">
                        <section class="premium-card p-10 bg-gradient-to-br from-indigo-500/5 to-transparent">
                            <h3 class="text-lg font-black text-slate-900 mb-4 flex items-center gap-2">
                                <i class="bi bi-megaphone text-indigo-500"></i> Release Notes
                            </h3>
                            <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                                {{ build.release_notes || 'No release notes provided for this version.' }}
                            </div>
                        </section>
                    </div>

                    <!-- Feedback Tab -->
                    <div v-if="activeTab === 'feedback'" class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-black text-slate-900">User Feedback & Bugs</h3>
                            <button @click="openModal('feedback')" class="btn-premium-primary py-2 text-sm">Report Issue</button>
                        </div>
                        
                        <div v-if="!build.feedbacks?.length" class="premium-card p-20 text-center text-slate-400">
                            <i class="bi bi-bug text-6xl mb-4 block opacity-10"></i>
                            <p class="font-bold uppercase tracking-widest text-xs">No feedback reported for this build</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="fb in build.feedbacks" :key="fb.id" class="premium-card overflow-hidden">
                                <div class="p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <div class="flex items-center gap-3 mb-2">
                                                <Link :href="route('feedback.show', fb.id)" class="text-lg font-bold text-slate-900 hover:text-indigo-600">{{ fb.title }}</Link>
                                                <span class="badge-premium text-[10px]" :class="severityColor(fb.severity)">{{ fb.severity }}</span>
                                            </div>
                                            <div class="flex items-center gap-3 text-xs font-bold text-slate-400 uppercase tracking-tight">
                                                <span :class="statusColor(fb.status)">{{ fb.status }}</span>
                                                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                                <span>{{ fb.type }}</span>
                                                <span v-if="fb.assignee" class="text-indigo-500">• Assigned to {{ fb.assignee.name }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button @click="openModal('feedback', fb)" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors"><i class="bi bi-pencil-square"></i></button>
                                            <button @click="deleteFeedback(fb.id)" class="p-2 text-slate-400 hover:text-rose-600 transition-colors"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                    <p class="text-slate-600 text-sm mb-4 leading-relaxed line-clamp-3">{{ fb.description }}</p>
                                    
                                    <div class="flex justify-between items-center">
                                        <div class="flex gap-2">
                                            <a v-for="att in fb.attachments" :key="att.path" :href="'/storage/' + att.path" target="_blank" class="px-3 py-1 bg-slate-100 rounded-lg text-[11px] font-bold text-slate-600 border border-slate-200 hover:bg-white transition-colors">📎 {{ att.name }}</a>
                                        </div>
                                        <button @click="openCommentBox(fb, 'Feedback')" class="text-xs font-black text-indigo-500 hover:underline">Discussion ({{ fb.comments?.length || 0 }})</button>
                                    </div>
                                </div>
                                <!-- Inline Discussion -->
                                <div v-if="commentTarget?.id === fb.id" class="bg-slate-50 p-6 border-t border-slate-100">
                                    <div class="space-y-4 mb-6">
                                        <div v-for="cmt in fb.comments" :key="cmt.id" class="flex gap-4">
                                            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[10px] font-bold shrink-0">{{ cmt.author?.name.charAt(0) }}</div>
                                            <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm flex-grow">
                                                <div class="flex justify-between mb-1">
                                                    <span class="text-xs font-bold text-slate-800">{{ cmt.author?.name }}</span>
                                                    <span class="text-[10px] text-slate-400">{{ formatDate(cmt.created_at) }}</span>
                                                </div>
                                                <p class="text-sm text-slate-600 leading-snug">{{ cmt.body }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <form @submit.prevent="submitComment" class="flex gap-3">
                                        <input v-model="commentForm.body" class="flex-grow px-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-4 focus:ring-indigo-100 outline-none" placeholder="Add to the discussion..." required />
                                        <button class="bg-indigo-600 text-white p-2 rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200"><i class="bi bi-send-fill px-1"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks Tab -->
                    <div v-if="activeTab === 'tasks'" class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-black text-slate-900">Action Items & Tasks</h3>
                            <button @click="openModal('task')" class="btn-premium-primary py-2 text-sm bg-amber-500 shadow-amber-200 hover:bg-amber-600">Create Task</button>
                        </div>
                        <div v-if="!build.tasks?.length" class="premium-card p-20 text-center text-slate-400">
                             <i class="bi bi-check2-circle text-6xl mb-4 block opacity-10"></i>
                             <p class="font-bold uppercase tracking-widest text-xs">All caught up!</p>
                        </div>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="tk in build.tasks" :key="tk.id" class="premium-card p-6 flex flex-col">
                                <div class="flex justify-between mb-4">
                                    <Link :href="route('tasks.show', tk.id)" class="font-bold text-slate-900 hover:text-indigo-600">{{ tk.title }}</Link>
                                    <div class="flex gap-1">
                                        <button @click="openModal('task', tk)" class="text-slate-300 hover:text-indigo-500"><i class="bi bi-pencil-square"></i></button>
                                        <button @click="deleteTask(tk.id)" class="text-slate-300 hover:text-rose-500"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-500 border border-slate-200">{{ tk.status }}</span>
                                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded" :class="tk.priority === 'Urgent' ? 'bg-rose-500 text-white' : 'bg-amber-50 text-amber-600 border border-amber-100'">{{ tk.priority }}</span>
                                    </div>
                                    <p class="text-[13px] text-slate-500 line-clamp-2 mb-4">{{ tk.description }}</p>
                                </div>
                                <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500 border border-slate-200">{{ tk.assignee?.name.charAt(0) || '?' }}</div>
                                        <span class="text-[11px] font-bold text-slate-700">{{ tk.assignee?.name || 'Unassigned' }}</span>
                                    </div>
                                    <span v-if="tk.due_date" class="text-[10px] font-black text-rose-500 uppercase tracking-tighter">Due {{ formatDate(tk.due_date) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Public Links Tab -->
                    <div v-if="activeTab === 'share'" class="space-y-6">
                        <div class="flex justify-between items-center px-4">
                            <h3 class="text-xl font-black text-slate-900">Public Access Links</h3>
                            <button @click="openModal('share')" class="btn-premium-primary py-2 text-sm">Generate Link</button>
                        </div>
                        <div class="premium-card overflow-hidden">
                             <table class="w-full text-left">
                                <thead class="bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4 text-[11px] font-black uppercase text-slate-400 tracking-widest">Share Token</th>
                                        <th class="px-6 py-4 text-[11px] font-black uppercase text-slate-400 tracking-widest">Security</th>
                                        <th class="px-6 py-4 text-[11px] font-black uppercase text-slate-400 tracking-widest">Usage</th>
                                        <th class="px-6 py-4 text-right pr-10 text-[11px] font-black uppercase text-slate-400 tracking-widest">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="link in build.share_links" :key="link.id" class="group hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-2">
                                                <code class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg border border-indigo-100">{{ link.token }}</code>
                                                <button @click="copyToClipboard(route('public.share.show', link.token))" class="text-slate-300 hover:text-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity"><i class="bi bi-clipboard"></i></button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <span v-if="link.password" class="text-[10px] px-2 py-0.5 rounded-full bg-slate-900 text-white font-black uppercase tracking-tighter"><i class="bi bi-lock-fill mr-1"></i> Locked</span>
                                            <span v-else class="text-[11px] font-bold text-emerald-500 uppercase tracking-tighter">Open Access</span>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-1.5 font-bold text-slate-700 text-sm">
                                                <i class="bi bi-download text-slate-300"></i> {{ link.download_count }} Downloads
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-right pr-8">
                                            <button @click="deleteShare(link.id)" class="btn-premium-secondary py-1 text-xs border-transparent shadow-none text-rose-500 hover:bg-rose-50 hover:border-rose-100">Revoke</button>
                                        </td>
                                    </tr>
                                    <tr v-if="!build.share_links?.length">
                                        <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">No active links</td>
                                    </tr>
                                </tbody>
                             </table>
                        </div>
                    </div>

                    <!-- Downloads Tab -->
                    <div v-if="activeTab === 'downloads'" class="space-y-6">
                        <h3 class="text-xl font-black text-slate-900 px-4">Download History</h3>
                        <div class="premium-card overflow-hidden">
                             <table class="w-full text-left">
                                <thead class="bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4 text-[11px] font-black uppercase text-slate-400 tracking-widest">Source / Initiator</th>
                                        <th class="px-6 py-4 text-[11px] font-black uppercase text-slate-400 tracking-widest text-center">Protocol / IP</th>
                                        <th class="px-6 py-4 text-right pr-10 text-[11px] font-black uppercase text-slate-400 tracking-widest">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="dl in build.downloads" :key="dl.id" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400"><i class="bi" :class="dl.share_link ? 'bi-link-45deg' : 'bi-person-badge'"></i></div>
                                                <div>
                                                    <span class="text-sm font-bold text-slate-800">{{ dl.share_link ? 'Public Collection' : (dl.user?.name || 'System User') }}</span>
                                                    <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-tight">{{ dl.share_link ? 'Token: ' + dl.share_link.token : 'Internal Direct' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <code class="text-[11px] font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded">{{ dl.ip_address }}</code>
                                        </td>
                                        <td class="px-6 py-5 text-right pr-8 text-xs font-bold text-slate-400 uppercase tabular-nums tracking-tighter">{{ formatDate(dl.created_at) }}</td>
                                    </tr>
                                    <tr v-if="!build.downloads?.length">
                                        <td colspan="3" class="px-6 py-12 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">Registry is empty</td>
                                    </tr>
                                </tbody>
                             </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CUSTOM PREMIUM MODALS -->
         <Transition name="fade">
            <div v-if="activeModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/40 backdrop-blur-sm shadow-2xl overflow-y-auto pt-24 pb-24">
                <div v-if="activeModal === 'qr'" class="premium-card max-w-sm w-full p-8 text-center animate-in zoom-in">
                    <h3 class="text-xl font-black text-slate-900 mb-2">Build QR Code</h3>
                    <p class="text-sm text-slate-500 mb-6">Scan to directly download APK to mobile.</p>
                    <div class="bg-white p-6 border-4 border-slate-100 rounded-3xl inline-block shadow-inner mb-6">
                        <QrcodeVue :value="currentUrl" :size="220" level="H" class="mx-auto" />
                    </div>
                    <button @click="closeModal" class="w-full btn-premium-secondary">Close</button>
                </div>

                <div v-if="activeModal === 'share'" class="premium-card max-w-md w-full p-8 animate-in zoom-in">
                    <h3 class="text-xl font-black text-slate-900 mb-2">New Public Link</h3>
                    <p class="text-sm text-slate-500 mb-6 font-medium">Configure temporary access to this build.</p>
                    <form @submit.prevent="submitShare" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 tracking-widest">Expiry Date</label>
                            <input v-model="shareForm.expires_at" type="datetime-local" class="input-premium py-2 text-sm" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 tracking-widest">Usage Limit</label>
                                <input v-model="shareForm.download_limit" type="number" class="input-premium py-2 text-sm" placeholder="e.g. 50" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 tracking-widest">Pin Access</label>
                                <input v-model="shareForm.password" type="password" class="input-premium py-2 text-sm" placeholder="Optional PIN" autocomplete="new-password" />
                            </div>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="closeModal" class="flex-grow btn-premium-secondary">Cancel</button>
                            <button type="submit" class="flex-[2] btn-premium-primary" :disabled="shareForm.processing">Create Access Link</button>
                        </div>
                    </form>
                </div>

                <!-- Feedback & Task Modals are similar... let's combine logic or separate -->
                <div v-if="activeModal === 'feedback'" class="premium-card max-w-2xl w-full p-8 animate-in zoom-in h-[600px] overflow-y-auto">
                    <h3 class="text-xl font-black text-slate-900 mb-2">{{ editingFeedback ? 'Review Issue' : 'New Bug Report' }}</h3>
                    <form @submit.prevent="submitFeedback" class="space-y-4 pt-4">
                        <input v-model="feedbackForm.title" class="input-premium font-bold text-lg" placeholder="A short, descriptive title..." required />
                        <div class="grid grid-cols-3 gap-4">
                            <select v-model="feedbackForm.type" class="input-premium text-sm py-2">
                                <option>Bug</option><option>Feature</option><option>Improvement</option>
                            </select>
                            <select v-model="feedbackForm.severity" class="input-premium text-sm py-2">
                                <option>Critical</option><option>High</option><option>Medium</option><option>Low</option>
                            </select>
                            <select v-model="feedbackForm.assignee_id" class="input-premium text-sm py-2">
                                <option value="">No Assignee</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                        </div>
                        <textarea v-model="feedbackForm.description" class="input-premium text-sm min-h-[120px]" placeholder="Detailed description of the issue..." required></textarea>
                         <div class="grid grid-cols-2 gap-4">
                            <input v-model="feedbackForm.device_model" class="input-premium text-sm py-2" placeholder="Device Model (optional)" />
                            <input v-model="feedbackForm.os_version" class="input-premium text-sm py-2" placeholder="OS Version (optional)" />
                        </div>
                        <div class="p-4 border-2 border-dashed border-slate-200 rounded-2xl text-center hover:bg-slate-50 transition-colors">
                            <label class="block cursor-pointer">
                                <i class="bi bi-cloud-arrow-up text-3xl text-slate-300 block mb-2"></i>
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Upload Attachments (.zip, .jpg, .png)</span>
                                <input type="file" @change="handleFeedbackFiles" multiple class="hidden" />
                            </label>
                            <div v-if="feedbackNewFiles.length" class="mt-4 flex flex-wrap gap-2 justify-center">
                                <span v-for="f in feedbackNewFiles" :key="f.name" class="text-[10px] font-black bg-indigo-50 text-indigo-700 px-2 py-1 rounded shadow-sm">{{ f.name }}</span>
                            </div>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="closeModal" class="btn-premium-secondary">Cancel</button>
                            <button type="submit" class="btn-premium-primary flex-grow">{{ editingFeedback ? 'Update Report' : 'Submit Feedback' }}</button>
                        </div>
                    </form>
                </div>

                <div v-if="activeModal === 'task'" class="premium-card max-w-xl w-full p-8 animate-in zoom-in">
                    <h3 class="text-xl font-black text-slate-900 mb-2">{{ editingTask ? 'Edit Task' : 'New Action Item' }}</h3>
                    <form @submit.prevent="submitTask" class="space-y-4 pt-4">
                        <input v-model="taskForm.title" class="input-premium font-bold" placeholder="What needs to be done?" required />
                        <div class="grid grid-cols-2 gap-4">
                             <div>
                                <label class="text-[10px] font-black uppercase text-slate-400 mb-1 block">Assignee</label>
                                <select v-model="taskForm.assignee_id" class="input-premium py-2 text-sm">
                                    <option value="">Unassigned</option>
                                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </select>
                             </div>
                             <div>
                                <label class="text-[10px] font-black uppercase text-slate-400 mb-1 block">Priority</label>
                                <select v-model="taskForm.priority" class="input-premium py-2 text-sm text-rose-600 font-black">
                                    <option>Low</option><option>Medium</option><option>High</option><option>Urgent</option>
                                </select>
                             </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                             <div>
                                <label class="text-[10px] font-black uppercase text-slate-400 mb-1 block">Status</label>
                                <select v-model="taskForm.status" class="input-premium py-2 text-sm font-black">
                                    <option>Todo</option><option>In Progress</option><option>Done</option>
                                </select>
                             </div>
                             <div>
                                <label class="text-[10px] font-black uppercase text-slate-400 mb-1 block">Deadline</label>
                                <input v-model="taskForm.due_date" type="date" class="input-premium py-2 text-sm tabular-nums" />
                             </div>
                        </div>
                        <textarea v-model="taskForm.description" class="input-premium text-sm min-h-[100px]" placeholder="Optional notes..."></textarea>
                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="closeModal" class="btn-premium-secondary">Discard</button>
                            <button type="submit" class="btn-premium-primary flex-grow">{{ editingTask ? 'Update Task' : 'Project Task' }}</button>
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

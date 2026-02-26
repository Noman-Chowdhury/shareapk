<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    feedback: Object,
    users: Array,
});

const isEditModalOpen = ref(false);
const feedbackNewFiles = ref([]);
const feedbackKeepAttachments = ref([]);

const feedbackForm = useForm({
    title: props.feedback.title,
    description: props.feedback.description,
    type: props.feedback.type,
    severity: props.feedback.severity || 'Medium',
    status: props.feedback.status || 'Open',
    device_model: props.feedback.device_model || '',
    os_version: props.feedback.os_version || '',
    assignee_id: props.feedback.assignee_id || '',
});

function openEditModal() {
    feedbackNewFiles.value = [];
    feedbackKeepAttachments.value = (props.feedback.attachments || []).map(a => a.path);
    isEditModalOpen.value = true;
}

function handleFeedbackFiles(e) {
    feedbackNewFiles.value = Array.from(e.target.files);
}

function removeKeepAttachment(path) {
    feedbackKeepAttachments.value = feedbackKeepAttachments.value.filter(p => p !== path);
}

function submitFeedback() {
    const url = route('feedback.update', props.feedback.id);
    const payload = feedbackForm.data();

    feedbackNewFiles.value.forEach((file, i) => {
        payload[`new_attachments[${i}]`] = file;
    });
    feedbackKeepAttachments.value.forEach((path, i) => {
        payload[`keep_attachments[${i}]`] = path;
    });
    payload['_method'] = 'PUT';

    feedbackForm.transform(() => payload).post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            isEditModalOpen.value = false;
            feedbackNewFiles.value = [];
        },
    });
}

const commentForm = useForm({ 
    body: '', 
    commentable_id: props.feedback.id, 
    commentable_type: 'App\\Models\\Feedback', 
    attachment: null 
});

function handleCommentAttachment(e) {
    commentForm.attachment = e.target.files[0];
}

function submitComment() {
    commentForm.post(route('comments.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            commentForm.reset();
            const fileInput = document.getElementById('comment-attachment');
            if (fileInput) fileInput.value = '';
        },
    });
}

function severityColor(sev) {
    return { 
        Critical: 'bg-rose-50 text-rose-700 border-rose-100', 
        High: 'bg-orange-50 text-orange-700 border-orange-100', 
        Medium: 'bg-amber-50 text-amber-700 border-amber-100', 
        Low: 'bg-slate-50 text-slate-700 border-slate-100' 
    }[sev] || 'bg-slate-50';
}

function statusColor(status) {
    return { 
        Open: 'text-rose-600', 
        'In Progress': 'text-amber-600', 
        Resolved: 'text-emerald-600', 
        Closed: 'text-slate-500' 
    }[status] || 'text-slate-500';
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<template>
    <Head :title="'Inspect Feedback: ' + feedback.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <nav class="flex items-center gap-2 text-sm font-medium text-slate-500">
                    <Link :href="route('feedback.index')" class="hover:text-indigo-600 transition-colors">Quality Control</Link>
                    <i class="bi bi-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-slate-900 font-bold tracking-tight uppercase text-xs">Report #{{ feedback.id }}</span>
                </nav>
                <button @click="openEditModal" class="btn-premium-secondary py-2 text-xs">
                    <i class="bi bi-pencil-square mr-2"></i> Refine Report
                </button>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-4">
            <!-- Left: Main Thread -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Core Issue Card -->
                <div class="premium-card p-8 lg:p-10">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-8">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <span class="badge-premium bg-slate-900 text-white border-transparent">{{ feedback.type }}</span>
                                <span class="badge-premium" :class="severityColor(feedback.severity)">{{ feedback.severity || 'Medium' }} Severity</span>
                            </div>
                            <h1 class="text-3xl font-black text-slate-900 leading-tight tracking-tight">{{ feedback.title }}</h1>
                        </div>
                        <div class="shrink-0 flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-2xl border border-slate-100">
                            <i class="bi bi-circle-fill text-[8px]" :class="statusColor(feedback.status)"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest" :class="statusColor(feedback.status)">{{ feedback.status }}</span>
                        </div>
                    </div>

                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium bg-slate-50/50 p-6 rounded-3xl border border-slate-100 mb-8 overflow-x-auto">
                        {{ feedback.description }}
                    </div>

                    <div v-if="feedback.attachments && feedback.attachments.length" class="space-y-4">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Evidence & Attachments</h4>
                        <div class="flex flex-wrap gap-3">
                            <a v-for="att in feedback.attachments" :key="att.path"
                               :href="'/storage/' + att.path" target="_blank"
                               class="group flex items-center gap-3 px-4 py-2.5 bg-white border border-slate-200 rounded-2xl hover:border-indigo-300 hover:shadow-md transition-all">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center transition-colors group-hover:bg-indigo-600 group-hover:text-white">
                                    <i class="bi bi-paperclip"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700 truncate max-w-[150px]">{{ att.name }}</span>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">{{ (att.size / 1024).toFixed(0) }} KB</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Discussion Engine -->
                <div class="space-y-6 pt-4">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-3 px-1">
                        <i class="bi bi-chat-left-dots-fill text-indigo-500"></i> Communication Log
                    </h3>
                    
                    <div class="space-y-6">
                        <div v-for="comment in feedback.comments" :key="comment.id" 
                             class="flex gap-5 animate-in fade-in"
                        >
                            <div class="shrink-0 pt-1">
                                <div class="w-10 h-10 rounded-2xl bg-slate-900 flex items-center justify-center text-white text-[12px] font-black border-2 border-white shadow-lg">
                                    {{ (comment.author?.name ?? 'U').charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex-grow space-y-2">
                                <div class="flex items-center justify-between px-1">
                                    <span class="text-xs font-black text-slate-900">{{ comment.author?.name ?? 'Anonymous System User' }}</span>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ formatDate(comment.created_at) }}</span>
                                </div>
                                <div class="premium-card p-5 bg-white shadow-sm ring-1 ring-slate-100/50">
                                    <p class="text-sm text-slate-600 leading-relaxed font-medium">{{ comment.body }}</p>
                                    <div v-if="comment.attachment_path" class="mt-4 pt-4 border-t border-slate-50 flex">
                                        <a :href="'/storage/' + comment.attachment_path" target="_blank" 
                                           class="text-[10px] font-black text-indigo-600 bg-indigo-50/50 px-3 py-1.5 rounded-xl border border-indigo-100 hover:bg-indigo-600 hover:text-white transition-all">
                                            <i class="bi bi-link-45deg mr-1"></i> {{ comment.attachment_name }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!feedback.comments?.length" class="text-center py-12 premium-card bg-slate-50/50 border-dashed">
                             <p class="text-[10px] font-black uppercase text-slate-300 tracking-[0.2em]">Silence in the logs. No discussion identified.</p>
                        </div>
                    </div>

                    <!-- Post Interface -->
                    <div class="premium-card p-8 bg-gradient-to-br from-white to-slate-50/50 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-8 opacity-[0.03] group-focus-within:opacity-[0.1] transition-opacity">
                            <i class="bi bi-send-fill text-[80px]"></i>
                        </div>
                        
                        <form @submit.prevent="submitComment" class="space-y-6 relative z-10">
                            <div class="relative">
                                <textarea v-model="commentForm.body" 
                                          class="input-premium py-4 text-sm font-medium min-h-[140px] focus:bg-white transition-colors" 
                                          placeholder="Collaborate on a resolution or provide further status updates..." required></textarea>
                            </div>
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="w-full sm:w-auto">
                                    <label class="px-4 py-2 border-2 border-dashed border-slate-200 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-white hover:border-indigo-300 transition-all text-xs font-bold text-slate-500">
                                        <i class="bi bi-paperclip text-lg"></i>
                                        <span>{{ commentForm.attachment ? commentForm.attachment.name : 'Include Evidence' }}</span>
                                        <input type="file" id="comment-attachment" @change="handleCommentAttachment" class="hidden" accept="image/*,.pdf,.doc,.docx,.txt,zip">
                                    </label>
                                </div>
                                <button type="submit" class="w-full sm:w-auto btn-premium-primary py-3 px-8" :disabled="commentForm.processing">
                                    <i class="bi bi-send-fill mr-2"></i> Commit Response
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Cluster Metadata -->
            <div class="space-y-6">
                <div class="sticky top-24 space-y-6">
                    <div class="premium-card overflow-hidden">
                        <div class="bg-slate-900 p-5 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center p-1.5 backdrop-blur-md">
                                <img v-if="feedback.build?.project?.icon_url" :src="'/storage/' + feedback.build.project.icon_url" class="w-full h-full object-contain" />
                                <i v-else class="bi bi-terminal text-white"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-black text-white/50 uppercase tracking-widest truncate">{{ feedback.build?.project?.name || 'Isolated Cluster' }}</p>
                                <p class="text-[11px] font-bold text-indigo-400 font-mono">Build v{{ feedback.build?.version_name }} ({{ feedback.build?.version_code }})</p>
                            </div>
                        </div>
                        
                        <div class="p-6 space-y-5">
                            <div class="flex justify-between items-center group">
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest group-hover:text-slate-600 transition-colors">Report Originator</span>
                                <span class="text-sm font-black text-slate-800">{{ feedback.author?.name || 'External Tracer' }}</span>
                            </div>
                            <div class="flex justify-between items-center group">
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest group-hover:text-slate-600 transition-colors">Current Handler</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center text-[9px] font-black text-white ring-2 ring-indigo-50">{{ feedback.assignee?.name.charAt(0) || '?' }}</div>
                                    <span class="text-sm font-black text-indigo-600">{{ feedback.assignee?.name || 'Unassigned Node' }}</span>
                                </div>
                            </div>
                            <div v-if="feedback.device_model" class="flex justify-between items-center group">
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest group-hover:text-slate-600 transition-colors">Execution Env</span>
                                <span class="text-xs font-bold text-slate-700 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">{{ feedback.device_model }}</span>
                            </div>
                            <div v-if="feedback.os_version" class="flex justify-between items-center group">
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest group-hover:text-slate-600 transition-colors">OS Signature</span>
                                <span class="text-xs font-bold text-slate-700 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">{{ feedback.os_version }}</span>
                            </div>
                            <div class="pt-4 border-t border-slate-50 flex justify-between items-center">
                                <span class="text-[10px] font-black uppercase text-slate-300 tracking-widest">Logged At</span>
                                <span class="text-[11px] font-bold text-slate-500">{{ formatDate(feedback.created_at) }}</span>
                            </div>
                        </div>

                        <!-- Quick Context Link -->
                        <div class="p-4 bg-indigo-50/50 border-t border-indigo-100/30">
                            <Link v-if="feedback.build" :href="route('builds.show', feedback.build.id)" 
                                  class="flex items-center justify-center gap-2 py-3 bg-white text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl border border-indigo-100 hover:bg-indigo-600 hover:text-white transition-all">
                                <i class="bi bi-box-arrow-up-right"></i> Navigate to Binary
                            </Link>
                        </div>
                    </div>

                    <!-- Performance Alert if Critical -->
                    <div v-if="feedback.severity === 'Critical'" class="premium-card p-6 bg-rose-600 text-white relative overflow-hidden group">
                        <i class="bi bi-lightning-fill absolute -bottom-4 -right-2 text-[80px] opacity-10 group-hover:scale-125 transition-transform"></i>
                        <h4 class="text-xs font-black uppercase tracking-[0.2em] mb-2 flex items-center gap-2">
                             Blocking Error <span class="animate-ping h-2 w-2 rounded-full bg-rose-200"></span>
                        </h4>
                        <p class="text-sm font-bold opacity-90 leading-relaxed">This record is flagged Critical. Escalated handling is required to prevent cluster disruption.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- REFINEMENT INTERFACE (MODAL) -->
        <Transition name="fade">
            <div v-if="isEditModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/40 backdrop-blur-sm shadow-2xl overflow-y-auto pt-24 pb-24">
                <div class="premium-card max-w-2xl w-full p-8 lg:p-10 animate-in zoom-in h-fit max-h-[90vh] overflow-y-auto">
                    <div class="space-y-2 mb-8">
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">System Report Refinement</h3>
                        <p class="text-xs font-medium text-slate-400">Updating metadata for Report #{{ feedback.id }}</p>
                    </div>

                    <form @submit.prevent="submitFeedback" class="space-y-6">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Primary Title</label>
                            <input v-model="feedbackForm.title" class="input-premium font-bold text-lg" placeholder="Identify the issue..." required />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Issue Strategy</label>
                                <select v-model="feedbackForm.type" class="input-premium text-sm font-bold text-slate-700">
                                    <option>Bug</option><option>Feature</option><option>Improvement</option><option>Suggestion</option><option>Other</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Alert Severity</label>
                                <select v-model="feedbackForm.severity" class="input-premium text-sm font-bold" :class="feedbackForm.severity === 'Critical' ? 'text-rose-600' : 'text-slate-700'">
                                    <option>Critical</option><option>High</option><option>Medium</option><option>Low</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Lifecycle State</label>
                                <select v-model="feedbackForm.status" class="input-premium text-sm font-bold text-slate-700">
                                    <option>Open</option><option>In Progress</option><option>Resolved</option><option>Closed</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Primary Handler</label>
                                <select v-model="feedbackForm.assignee_id" class="input-premium text-sm font-bold text-slate-700">
                                    <option value="">Unassigned Node</option>
                                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </select>
                            </div>
                             <div class="space-y-1 text-center md:text-left">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Execution Metrics</label>
                                <div class="flex gap-3">
                                    <input v-model="feedbackForm.device_model" class="input-premium text-sm font-bold" placeholder="Device" />
                                    <input v-model="feedbackForm.os_version" class="input-premium text-sm font-bold" placeholder="OS Ver" />
                                </div>
                             </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Deep Description</label>
                            <textarea v-model="feedbackForm.description" class="input-premium text-sm font-medium min-h-[160px]" required></textarea>
                        </div>

                        <div class="space-y-3">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Inventory Management (Attachments)</label>
                             <div v-if="feedbackKeepAttachments.length" class="flex flex-wrap gap-2 mb-4">
                                <div v-for="att in feedback.attachments" :key="att.path"
                                     class="flex items-center gap-3 px-3 py-2 bg-slate-50 border rounded-xl transition-all"
                                     :class="feedbackKeepAttachments.includes(att.path) ? 'border-slate-200' : 'opacity-30 line-through grayscale border-rose-100'">
                                    <span class="text-[10px] font-bold text-slate-600 truncate max-w-[120px]">📎 {{ att.name }}</span>
                                    <button type="button" class="text-rose-500 hover:scale-110 transition-transform"
                                            @click="removeKeepAttachment(att.path)"
                                            v-if="feedbackKeepAttachments.includes(att.path)">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </button>
                                </div>
                            </div>
                            <label class="w-full py-6 border-2 border-dashed border-slate-200 rounded-3xl flex flex-col items-center justify-center cursor-pointer hover:bg-slate-50 hover:border-indigo-300 transition-all group">
                                <i class="bi bi-cloud-arrow-up text-3xl text-slate-300 group-hover:text-indigo-400 transition-colors"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-2">Append supplementary files</span>
                                <input type="file" @change="handleFeedbackFiles" multiple class="hidden" accept="image/*,.pdf,.doc,.docx,.txt,.zip" />
                            </label>
                        </div>

                        <div class="flex gap-4 pt-6">
                            <button type="button" @click="isEditModalOpen = false" class="flex-grow btn-premium-secondary py-4 text-base">Abort Refinement</button>
                            <button type="submit" class="flex-[2] btn-premium-primary py-4 text-base" :disabled="feedbackForm.processing">
                                <span v-if="feedbackForm.processing">Synchronizing...</span>
                                <span v-else>Commit Updates</span>
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

.prose pre {
    @apply bg-slate-900 text-slate-100 p-4 rounded-xl font-mono text-xs overflow-x-auto my-4;
}
</style>

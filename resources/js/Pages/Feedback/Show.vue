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

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 pt-2">
            <!-- Left: Main Thread -->
            <div class="lg:col-span-3 space-y-5">
                <!-- Core Issue Card -->
                <div class="premium-card p-0 overflow-hidden border-slate-200/60 shadow-sm">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-100">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-slate-900 text-white rounded-md text-[8px] font-black uppercase tracking-widest">{{ feedback.type }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-widest border" :class="severityColor(feedback.severity)">{{ feedback.severity || 'Normal' }}</span>
                                </div>
                                <h1 class="text-xl font-black text-slate-800 tracking-tight uppercase leading-none">{{ feedback.title }}</h1>
                            </div>
                            <div class="shrink-0 flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl">
                                <i class="bi bi-circle-fill text-[6px]" :class="statusColor(feedback.status)"></i>
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-500">{{ feedback.status }} STATE</span>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-slate-50/50 rounded-2xl p-5 border border-slate-100 text-[13px] text-slate-600 leading-relaxed font-medium relative">
                                {{ feedback.description }}
                            </div>

                            <div v-if="feedback.attachments && feedback.attachments.length" class="space-y-3">
                                <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-1 flex items-center gap-2">
                                    <i class="bi bi-paperclip text-indigo-500"></i> Evidence trace
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2">
                                    <a v-for="att in feedback.attachments" :key="att.path"
                                       :href="'/storage/' + att.path" target="_blank"
                                       class="group flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-xl hover:border-indigo-300 hover:shadow-lg transition-all">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center transition-colors group-hover:bg-indigo-600 group-hover:text-white shrink-0 border border-indigo-100/50 text-sm">
                                            <i class="bi bi-link-45deg"></i>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-[10px] font-black text-slate-800 truncate">{{ att.name }}</span>
                                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">{{ (att.size / 1024).toFixed(0) }} KB Block</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discussion Engine -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between px-1">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span> Collaborative Logs
                        </h3>
                        <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">{{ feedback.comments?.length || 0 }} Entries</span>
                    </div>
                    
                    <div class="space-y-4">
                        <div v-for="comment in feedback.comments" :key="comment.id" 
                             class="flex gap-4 animate-in fade-in duration-300"
                        >
                            <div class="shrink-0">
                                <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-900 text-[10px] font-black shadow-sm transition-transform">
                                    {{ (comment.author?.name ?? 'U').charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex-grow space-y-1.5 pb-2">
                                <div class="flex items-center gap-2 px-1">
                                    <span class="text-[11px] font-black text-slate-800">{{ comment.author?.name || 'Node' }}</span>
                                    <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">{{ formatDate(comment.created_at) }}</span>
                                </div>
                                <div class="premium-card p-4 bg-white border-slate-100 shadow-sm transition-all group-hover:border-slate-200">
                                    <p class="text-xs text-slate-600 leading-normal font-medium">{{ comment.body }}</p>
                                    <div v-if="comment.attachment_path" class="mt-3 flex">
                                        <a :href="'/storage/' + comment.attachment_path" target="_blank" 
                                           class="inline-flex items-center gap-2 px-2.5 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg border border-indigo-100 text-[8px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all">
                                            <i class="bi bi-clipboard-pulse"></i> Trace fragment
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!feedback.comments?.length" class="text-center py-10 bg-slate-50/30 rounded-2xl border border-dashed border-slate-200">
                             <p class="text-[9px] font-black uppercase text-slate-300 tracking-widest">Awaiting tactical resolution logs...</p>
                        </div>
                    </div>

                    <!-- Post Interface -->
                    <div class="premium-card p-6 bg-slate-900 border-0 shadow-lg ml-12 overflow-hidden relative">
                        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-indigo-500/10 rounded-full blur-[40px] pointer-events-none"></div>
                        
                        <form @submit.prevent="submitComment" class="space-y-4 relative z-10">
                            <textarea v-model="commentForm.body" 
                                      class="w-full px-4 py-3 rounded-xl border border-slate-800 bg-slate-800/40 text-slate-100 text-xs font-medium min-h-[90px] focus:border-indigo-500 focus:bg-slate-800/80 outline-none transition-all placeholder-slate-600 shadow-inner" 
                                      placeholder="Log finding..." required></textarea>
                            
                            <div class="flex items-center justify-between gap-4">
                                <label class="flex-grow px-4 py-2 bg-white/5 border border-white/10 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-white/10 transition-all text-[8px] font-black text-slate-400 uppercase tracking-widest">
                                    <i class="bi bi-clipboard-plus text-indigo-400 text-sm"></i>
                                    <span>{{ commentForm.attachment ? commentForm.attachment.name : 'Evidence Link' }}</span>
                                    <input type="file" id="comment-attachment" @change="handleCommentAttachment" class="hidden" accept="image/*,.pdf,.doc,.docx,.txt,zip">
                                </label>
                                <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-indigo-900/40 hover:bg-indigo-500 transition-all" :disabled="commentForm.processing">
                                    Commit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Cluster Metadata -->
            <div class="space-y-5">
                <div class="sticky top-24 space-y-5">
                    <div class="premium-card p-0 shadow-sm border-slate-200/60">
                        <div class="bg-slate-900 p-5 flex flex-col items-center text-center">
                            <div class="w-12 h-12 rounded-2xl bg-white shadow-lg flex items-center justify-center mb-3 p-2 border border-slate-100">
                                <img v-if="feedback.build?.project?.icon_url" :src="'/storage/' + feedback.build.project.icon_url" class="w-full h-full object-contain" />
                                <i v-else class="bi bi-terminal text-slate-400 text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] font-black text-white/40 uppercase tracking-widest mb-0.5 truncate px-2">{{ feedback.build?.project?.name || 'Isolated' }}</p>
                                <p class="text-[11px] font-black text-indigo-400 font-mono tracking-tighter">v{{ feedback.build?.version_name }} [{{ feedback.build?.version_code }}]</p>
                            </div>
                        </div>
                        
                        <div class="p-5 space-y-6">
                            <div class="space-y-2">
                                <span class="text-[8px] font-black uppercase text-slate-400 tracking-widest block px-0.5">Reporter</span>
                                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-900 shadow-sm">{{ feedback.author?.name.charAt(0) || 'E' }}</div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-[11px] font-black text-slate-800 truncate uppercase tracking-tight">{{ feedback.author?.name || 'External Tracer' }}</span>
                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Origin</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <span class="text-[8px] font-black uppercase text-slate-400 tracking-widest block px-0.5">Assigned Agent</span>
                                <div class="flex items-center gap-3 p-3 bg-white border-2 border-indigo-50 rounded-xl shadow-sm">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-[10px] font-black text-white shadow-md ring-2 ring-white">{{ feedback.assignee?.name.charAt(0) || '?' }}</div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-[11px] font-black text-indigo-600 truncate uppercase tracking-tight">{{ feedback.assignee?.name || 'Unbound' }}</span>
                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Resolver</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3 px-0.5">
                                <div class="flex items-center justify-between text-[9px] font-black uppercase tracking-tight">
                                    <span class="text-slate-400">Environment</span>
                                    <span class="px-2 py-0.5 bg-slate-50 text-slate-700 rounded-md border border-slate-100">{{ feedback.device_model || 'Unknown' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[9px] font-black uppercase tracking-tight pt-2 border-t border-slate-50">
                                    <span class="text-slate-400">OS Signature</span>
                                    <span class="text-slate-500">{{ feedback.os_version || 'Generic' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[9px] font-black uppercase tracking-tight pt-2 border-t border-slate-50">
                                    <span class="text-slate-400">Timestamp</span>
                                    <span class="text-slate-500">{{ formatDate(feedback.created_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 bg-slate-50 border-t border-slate-100 text-center">
                            <Link v-if="feedback.build" :href="route('builds.show', feedback.build.id)" 
                                  class="flex items-center justify-center gap-2 py-3 bg-white text-indigo-600 text-[9px] font-black uppercase tracking-widest rounded-xl border border-indigo-100 shadow-sm hover:bg-slate-900 hover:text-white transition-all">
                                <i class="bi bi-cpu"></i> Inspect Trace
                            </Link>
                        </div>
                    </div>

                    <!-- Performance Alert if Critical -->
                    <div v-if="feedback.severity === 'Critical'" class="premium-card p-5 bg-rose-600 text-white relative overflow-hidden group shadow-lg shadow-rose-900/30">
                        <i class="bi bi-lightning-charge-fill absolute bottom-2 right-2 text-5xl opacity-10"></i>
                        <h1 class="text-[9px] font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                             System Lock <span class="animate-ping h-2 w-2 rounded-full bg-rose-200"></span>
                        </h1>
                        <p class="text-[10px] font-black tracking-tight opacity-90 leading-tight uppercase">Blocking issue recorded. Tier-1 escalation required.</p>
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

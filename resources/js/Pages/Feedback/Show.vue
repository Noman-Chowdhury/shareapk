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

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 pt-4">
            <!-- Left: Main Thread -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Core Issue Card -->
                <div class="premium-card p-0 overflow-hidden shadow-2xl shadow-slate-200/50">
                    <div class="p-8 lg:p-10">
                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-8 pb-8 border-b border-slate-100">
                            <div class="space-y-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-1 bg-slate-900 text-white rounded-lg text-[9px] font-black uppercase tracking-[0.2em]">{{ feedback.type }}</span>
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border" :class="severityColor(feedback.severity)">{{ feedback.severity || 'Medium' }} Severity Signal</span>
                                </div>
                                <h1 class="text-3xl font-black text-slate-900 leading-tight tracking-tight uppercase">{{ feedback.title }}</h1>
                            </div>
                            <div class="shrink-0 flex items-center gap-3 px-5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl shadow-inner">
                                <i class="bi bi-circle-fill text-[8px]" :class="statusColor(feedback.status)"></i>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em]" :class="statusColor(feedback.status)">{{ feedback.status }} STATE</span>
                            </div>
                        </div>

                        <div class="space-y-8">
                            <div class="bg-slate-50/50 rounded-3xl p-8 border border-slate-100 prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium overflow-x-auto shadow-inner relative">
                                <div class="absolute top-4 right-6 opacity-10">
                                    <i class="bi bi-quote text-4xl text-slate-400"></i>
                                </div>
                                {{ feedback.description }}
                            </div>

                            <div v-if="feedback.attachments && feedback.attachments.length" class="space-y-4">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1 flex items-center gap-3">
                                    <i class="bi bi-paperclip text-indigo-500"></i> Evidence & Binary Trace Registry
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                                    <a v-for="att in feedback.attachments" :key="att.path"
                                       :href="'/storage/' + att.path" target="_blank"
                                       class="group flex items-center gap-4 p-4 bg-white border border-slate-100 rounded-2xl hover:border-indigo-300 hover:shadow-xl transition-all">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center transition-colors group-hover:bg-indigo-600 group-hover:text-white shrink-0 shadow-sm border border-indigo-100/50">
                                            <i class="bi bi-link-45deg text-xl"></i>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-xs font-black text-slate-800 truncate">{{ att.name }}</span>
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">{{ (att.size / 1024).toFixed(0) }} KB Block</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discussion Engine -->
                <div class="space-y-8 pt-6">
                    <div class="flex items-center justify-between px-1">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.3em] flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span> Collaborative Protocol Logs
                        </h3>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ feedback.comments?.length || 0 }} Entries Recorded</span>
                    </div>
                    
                    <div class="space-y-8 relative before:absolute before:left-5 before:top-2 before:bottom-0 before:w-0.5 before:bg-slate-100">
                        <div v-for="comment in feedback.comments" :key="comment.id" 
                             class="flex gap-6 animate-in slide-in-from-left-4 duration-500 relative"
                        >
                            <div class="shrink-0 relative z-10">
                                <div class="w-10 h-10 rounded-2xl bg-white border-2 border-slate-100 flex items-center justify-center text-slate-900 text-[11px] font-black shadow-md rotate-3 group-hover:rotate-0 transition-transform">
                                    {{ (comment.author?.name ?? 'U').charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex-grow space-y-3 pb-8">
                                <div class="flex items-center justify-between px-1">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-black text-slate-900">{{ comment.author?.name || 'Anonymous Node' }}</span>
                                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ formatDate(comment.created_at) }}</span>
                                    </div>
                                </div>
                                <div class="premium-card p-6 bg-white border-slate-100 shadow-sm relative overflow-hidden group">
                                    <p class="text-[13px] text-slate-600 leading-relaxed font-medium relative z-10">{{ comment.body }}</p>
                                    <div v-if="comment.attachment_path" class="mt-5 pt-5 border-t border-slate-50 flex">
                                        <a :href="'/storage/' + comment.attachment_path" target="_blank" 
                                           class="inline-flex items-center gap-3 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 text-[9px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                            <i class="bi bi-clipboard-pulse text-xs"></i> View Trace Fragment: {{ comment.attachment_name }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!feedback.comments?.length" class="text-center py-16 bg-slate-50/30 rounded-[2.5rem] border-2 border-dashed border-slate-100 ml-10">
                             <i class="bi bi-chat-left-dots text-3xl text-slate-100 block mb-4"></i>
                             <p class="text-[10px] font-black uppercase text-slate-300 tracking-[0.3em]">Communication line silent. No resolution logs found.</p>
                        </div>
                    </div>

                    <!-- Post Interface -->
                    <div class="premium-card p-8 bg-gradient-to-br from-slate-900 to-slate-800 border-0 relative overflow-hidden group ml-10 shadow-2xl shadow-indigo-900/20">
                        <div class="absolute -bottom-10 -right-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-[80px] pointer-events-none"></div>
                        
                        <form @submit.prevent="submitComment" class="space-y-6 relative z-10">
                            <textarea v-model="commentForm.body" 
                                      class="w-full px-6 py-5 rounded-[2rem] border-2 border-slate-800 bg-slate-800/40 text-slate-100 text-sm font-medium min-h-[140px] focus:border-indigo-500 focus:bg-slate-800/80 outline-none transition-all placeholder-slate-500" 
                                      placeholder="Log tactical finding or broadcast resolution strategy..." required></textarea>
                            
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                                <label class="w-full sm:w-auto px-6 py-3 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-3 cursor-pointer hover:bg-white/10 hover:border-white/20 transition-all text-[9px] font-black text-slate-300 uppercase tracking-[0.2em]">
                                    <i class="bi bi-clipboard-plus text-lg text-indigo-400"></i>
                                    <span>{{ commentForm.attachment ? commentForm.attachment.name : 'Append Evidence' }}</span>
                                    <input type="file" id="comment-attachment" @change="handleCommentAttachment" class="hidden" accept="image/*,.pdf,.doc,.docx,.txt,zip">
                                </label>
                                <button type="submit" class="w-full sm:w-auto px-12 py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-indigo-900/60 hover:bg-indigo-500 active:scale-95 transition-all" :disabled="commentForm.processing">
                                    Commit Log Entry
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Cluster Metadata -->
            <div class="space-y-6">
                <div class="sticky top-24 space-y-6">
                    <div class="premium-card p-0 shadow-2xl shadow-slate-200/50">
                        <div class="bg-slate-900 p-8 flex flex-col items-center text-center relative overflow-hidden">
                            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-transparent via-indigo-500 to-transparent"></div>
                            <div class="relative w-16 h-16 rounded-[1.8rem] bg-white shadow-xl flex items-center justify-center mb-5 rotate-3 p-3">
                                <img v-if="feedback.build?.project?.icon_url" :src="'/storage/' + feedback.build.project.icon_url" class="w-full h-full object-contain" />
                                <i v-else class="bi bi-terminal text-slate-400 text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-black text-white/40 uppercase tracking-[0.3em] mb-1 truncate px-4">{{ feedback.build?.project?.name || 'Isolated Node' }}</p>
                                <p class="text-xs font-black text-indigo-400 font-mono">v{{ feedback.build?.version_name }} [{{ feedback.build?.version_code }}]</p>
                            </div>
                        </div>
                        
                        <div class="p-8 space-y-8">
                            <div class="space-y-3">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-[0.2em] block px-1">Signal Originator</span>
                                <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-[12px] font-black text-slate-900 shadow-sm">{{ feedback.author?.name.charAt(0) || 'E' }}</div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-black text-slate-800 truncate uppercase tracking-tight">{{ feedback.author?.name || 'External Tracer' }}</span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Deployment Reporter</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-[0.2em] block px-1">Active Handler</span>
                                <div class="flex items-center gap-4 p-4 bg-white border-2 border-indigo-50 rounded-2xl shadow-sm">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-[12px] font-black text-white shadow-lg ring-4 ring-white rotate-3">{{ feedback.assignee?.name.charAt(0) || '?' }}</div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-black text-indigo-600 truncate uppercase tracking-tight">{{ feedback.assignee?.name || 'Unassigned Node' }}</span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Primary Resolver</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4 px-1">
                                <div v-if="feedback.device_model" class="flex items-center justify-between text-[10px] font-black uppercase tracking-tight">
                                    <span class="text-slate-400">Execution Env</span>
                                    <span class="px-2.5 py-1 bg-slate-50 text-slate-700 rounded-lg border border-slate-100">{{ feedback.device_model }}</span>
                                </div>
                                <div v-if="feedback.os_version" class="flex items-center justify-between text-[10px] font-black uppercase tracking-tight pt-3 border-t border-slate-50">
                                    <span class="text-slate-400">OS Signature</span>
                                    <span class="px-2.5 py-1 bg-slate-50 text-slate-700 rounded-lg border border-slate-100">{{ feedback.os_version }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-tight pt-3 border-t border-slate-50">
                                    <span class="text-slate-400">Registry Stamp</span>
                                    <span class="text-slate-500">{{ formatDate(feedback.created_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Context Link -->
                        <div class="p-8 bg-slate-50 border-t border-slate-100 text-center">
                            <Link v-if="feedback.build" :href="route('builds.show', feedback.build.id)" 
                                  class="flex items-center justify-center gap-3 py-4 bg-white text-indigo-600 text-[10px] font-black uppercase tracking-[0.3em] rounded-2xl border border-indigo-100 shadow-sm hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all">
                                <i class="bi bi-cpu"></i> Inspect Deployment
                            </Link>
                        </div>
                    </div>

                    <!-- Performance Alert if Critical -->
                    <div v-if="feedback.severity === 'Critical'" class="premium-card p-8 bg-rose-600 text-white relative overflow-hidden group shadow-2xl shadow-rose-900/30">
                        <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full blur-[60px] pointer-events-none"></div>
                        <i class="bi bi-lightning-charge-fill absolute bottom-2 right-2 text-7xl opacity-10 group-hover:scale-125 transition-transform duration-700"></i>
                        <h4 class="text-[10px] font-black uppercase tracking-[0.3em] mb-3 flex items-center gap-3">
                             Operational Lock <span class="animate-ping h-2.5 w-2.5 rounded-full bg-rose-200"></span>
                        </h4>
                        <p class="text-xs font-black uppercase tracking-tight opacity-90 leading-relaxed italic">Blocking Error Identified. Tier-1 escalation recommended to prevent cluster de-sync.</p>
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

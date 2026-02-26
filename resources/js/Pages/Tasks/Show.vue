<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    task: Object,
    users: Array,
});

const isEditModalOpen = ref(false);
const taskNewFiles = ref([]);
const taskKeepAttachments = ref([]);

const taskForm = useForm({
    title: props.task.title,
    description: props.task.description || '',
    priority: props.task.priority || 'Medium',
    status: props.task.status || 'Todo',
    assignee_id: props.task.assignee_id || '',
    due_date: props.task.due_date || '',
});

function openEditModal() {
    taskNewFiles.value = [];
    taskKeepAttachments.value = (props.task.attachments || []).map(a => a.path);
    isEditModalOpen.value = true;
}

function handleTaskFiles(e) {
    taskNewFiles.value = Array.from(e.target.files);
}

function removeTaskKeepAttachment(path) {
    taskKeepAttachments.value = taskKeepAttachments.value.filter(p => p !== path);
}

function submitTask() {
    const url = route('tasks.update', props.task.id);
    const payload = taskForm.data();
    
    taskNewFiles.value.forEach((file, i) => {
        payload[`new_attachments[${i}]`] = file;
    });
    taskKeepAttachments.value.forEach((path, i) => {
        payload[`keep_attachments[${i}]`] = path;
    });
    payload['_method'] = 'PUT';

    taskForm.transform(() => payload).post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            isEditModalOpen.value = false;
            taskNewFiles.value = [];
        },
    });
}

const commentForm = useForm({ 
    body: '', 
    commentable_id: props.task.id, 
    commentable_type: 'App\\Models\\Task', 
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

function priorityColor(p) {
    return { 
        Urgent: 'bg-rose-50 text-rose-700 border-rose-100', 
        High: 'bg-orange-50 text-orange-700 border-orange-100', 
        Medium: 'bg-indigo-50 text-indigo-700 border-indigo-100', 
        Low: 'bg-slate-50 text-slate-700 border-slate-100' 
    }[p] || 'bg-slate-50';
}

function statusColor(s) {
    return { 
        Todo: 'text-slate-500', 
        'In Progress': 'text-indigo-600', 
        Done: 'text-emerald-600' 
    }[s] || 'text-slate-500';
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
    <Head :title="'Action Item: ' + task.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <nav class="flex items-center gap-2 text-sm font-medium text-slate-500">
                    <Link :href="route('tasks.index')" class="hover:text-indigo-600 transition-colors">Task Registry</Link>
                    <i class="bi bi-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-slate-900 font-bold tracking-tight uppercase text-xs">Action #{{ task.id }}</span>
                </nav>
                <button @click="openEditModal" class="btn-premium-primary py-2 text-xs bg-amber-500 hover:bg-amber-600 shadow-amber-200">
                    <i class="bi bi-pencil-square mr-2"></i> Modify Action
                </button>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 pt-4">
            <!-- Left: Main Task Details -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Task Core Card -->
                <div class="premium-card p-0 overflow-hidden">
                    <div class="p-8 lg:p-10">
                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-8 pb-8 border-b border-slate-100">
                            <div class="space-y-4 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest" :class="priorityColor(task.priority)">{{ task.priority }} Priority</span>
                                    <span v-if="task.due_date" class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 border border-rose-100">
                                        Execution Deadline: {{ (new Date(task.due_date)).toLocaleDateString() }}
                                    </span>
                                </div>
                                <h1 class="text-3xl font-black text-slate-900 leading-tight tracking-tight uppercase">
                                    {{ task.title }}
                                </h1>
                            </div>
                            <div class="shrink-0 flex items-center gap-3 px-5 py-2.5 bg-slate-900 rounded-2xl shadow-xl shadow-slate-200">
                                <span class="animate-pulse w-2 h-2 rounded-full bg-indigo-400"></span>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-white">{{ task.status }}</span>
                            </div>
                        </div>

                        <div class="space-y-8">
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                    <i class="bi bi-text-left text-indigo-500"></i> Operation Briefing
                                </h4>
                                <div class="bg-slate-50/50 rounded-3xl p-8 border border-slate-100 prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                                    {{ task.description || 'No detailed instructions provided for this action item.' }}
                                </div>
                            </div>

                            <div v-if="task.attachments && task.attachments.length" class="space-y-4">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1 flex items-center gap-2">
                                    <i class="bi bi-paperclip text-indigo-500"></i> Supplementary Assets
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                                    <a v-for="att in task.attachments" :key="att.path"
                                       :href="'/storage/' + att.path" target="_blank"
                                       class="group flex items-center gap-4 p-4 bg-white border border-slate-100 rounded-2xl hover:border-indigo-300 hover:shadow-xl transition-all">
                                        <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center transition-colors group-hover:bg-indigo-600 group-hover:text-white shrink-0">
                                            <i class="bi bi-file-earmark-binary text-xl"></i>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-xs font-black text-slate-800 truncate">{{ att.name }}</span>
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">{{ (att.size / 1024).toFixed(0) }} KB Registry</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discussion & Progress Feed -->
                <div class="space-y-8 pt-6">
                    <div class="flex items-center justify-between px-1">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.3em] flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Execution Metrics & Logs
                        </h3>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ task.comments?.length || 0 }} Signals Identified</span>
                    </div>
                    
                    <div class="space-y-6 relative before:absolute before:left-5 before:top-2 before:bottom-0 before:w-0.5 before:bg-slate-100">
                        <div v-for="comment in task.comments" :key="comment.id" 
                             class="flex gap-6 animate-in slide-in-from-left-4 duration-500 relative"
                        >
                            <div class="shrink-0 relative z-10">
                                <div class="w-10 h-10 rounded-2xl bg-white border-2 border-slate-100 flex items-center justify-center text-slate-900 text-[11px] font-black shadow-sm group-hover:rotate-6 transition-transform">
                                    {{ (comment.author?.name ?? 'U').charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex-grow space-y-2 pb-8">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-black text-slate-900">{{ comment.author?.name || 'Automated Agent' }}</span>
                                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ formatDate(comment.created_at) }}</span>
                                    </div>
                                </div>
                                <div class="premium-card p-6 bg-white border-slate-100 shadow-sm relative overflow-hidden group">
                                    <p class="text-[13px] text-slate-600 leading-relaxed font-medium relative z-10">{{ comment.body }}</p>
                                    <div v-if="comment.attachment_path" class="mt-4 flex">
                                        <a :href="'/storage/' + comment.attachment_path" target="_blank" 
                                           class="flex items-center gap-2 px-3 py-2 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 text-[9px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all">
                                            <i class="bi bi-paperclip"></i> View Fragment: {{ comment.attachment_name }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!task.comments?.length" class="text-center py-16 bg-slate-50/50 rounded-[2.5rem] border-2 border-dashed border-slate-100 ml-10">
                             <i class="bi bi-activity text-3xl text-slate-200 block mb-4"></i>
                             <p class="text-[10px] font-black uppercase text-slate-300 tracking-[0.3em]">Communication line idle. Awaiting tactical data.</p>
                        </div>
                    </div>

                    <!-- Input Interface -->
                    <div class="premium-card p-8 bg-slate-900 border-0 relative overflow-hidden group ml-10 shadow-2xl shadow-indigo-900/20">
                        <div class="absolute -top-12 -right-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none"></div>
                        
                        <form @submit.prevent="submitComment" class="space-y-6 relative z-10">
                            <textarea v-model="commentForm.body" 
                                      class="w-full px-6 py-5 rounded-[2rem] border-2 border-slate-800 bg-slate-800/40 text-slate-100 text-sm font-medium min-h-[140px] focus:border-indigo-500 focus:bg-slate-800/80 outline-none transition-all placeholder-slate-500" 
                                      placeholder="Log tactical update or broadcast resolution path..." required></textarea>
                            
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                                <label class="w-full sm:w-auto px-6 py-3 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-3 cursor-pointer hover:bg-white/10 hover:border-white/20 transition-all text-[9px] font-black text-slate-300 uppercase tracking-[0.2em]">
                                    <i class="bi bi-cloud-arrow-up text-lg text-indigo-400"></i>
                                    <span>{{ commentForm.attachment ? commentForm.attachment.name : 'Ingest Fragment' }}</span>
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

            <!-- Right: Object Information -->
            <div class="space-y-6">
                <div class="sticky top-24 space-y-6">
                    <div class="premium-card p-0 shadow-2xl shadow-slate-200/50">
                        <div class="bg-indigo-600 p-8 flex flex-col items-center text-center relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent"></div>
                            <div class="relative w-16 h-16 rounded-3xl bg-white shadow-xl flex items-center justify-center mb-4 rotate-3 group-hover:rotate-0 transition-transform">
                                <i class="bi bi-shield-check text-3xl text-indigo-600"></i>
                            </div>
                            <h4 class="text-white font-black uppercase tracking-[0.2em] text-[10px] relative z-10">Action Registry Meta</h4>
                        </div>
                        
                        <div class="p-8 space-y-8">
                            <div class="space-y-3">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-[0.2em] block px-1">Tactical Origin</span>
                                <Link v-if="task.build" :href="route('projects.show', task.build.project_id)" class="group block p-4 bg-slate-50 rounded-2xl border border-slate-100 hover:border-indigo-200 transition-all">
                                    <span class="text-xs font-black text-slate-900 group-hover:text-indigo-600 block uppercase tracking-tight">{{ task.build?.project?.name || 'Isolated Root' }}</span>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter block mt-1">Binary Hash: v{{ task.build?.version_name }}</span>
                                </Link>
                                <div v-else class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                                    <span class="text-[10px] font-black text-slate-400 uppercase italic tracking-widest">Global Protocol Scope</span>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-[0.2em] block px-1">Assigned Unit</span>
                                <div class="flex items-center gap-4 p-4 bg-white border-2 border-slate-50 rounded-2xl shadow-sm">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-[12px] font-black text-white shadow-lg rotate-3">{{ task.assignee?.name.charAt(0) || '?' }}</div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-black text-slate-800 truncate uppercase tracking-tight">{{ task.assignee?.name || 'System Unbound' }}</span>
                                        <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mt-0.5">Primary Handler</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4 px-1">
                                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-tight">
                                    <span class="text-slate-400">Dispatcher</span>
                                    <span class="text-slate-700">{{ task.creator?.name || 'System Orchestrator' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-tight pt-3 border-t border-slate-50">
                                    <span class="text-slate-400">Timestamp</span>
                                    <span class="text-slate-700">{{ formatDate(task.created_at) }}</span>
                                </div>
                                <div v-if="task.due_date" class="flex items-center justify-between text-[10px] font-black uppercase tracking-tight pt-3 border-t border-slate-50">
                                    <span class="text-rose-500">Deadline Signal</span>
                                    <span class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded-lg border border-rose-100">{{ (new Date(task.due_date)).toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 bg-slate-50 border-t border-slate-100">
                             <div class="p-5 bg-white rounded-3xl border border-slate-200 text-center shadow-inner">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mb-1.5">Operational Tier</p>
                                <div class="flex items-center justify-center gap-2">
                                    <span class="w-2 h-2 rounded-full" :class="task.priority === 'Urgent' ? 'bg-rose-500 animate-ping' : 'bg-slate-400'"></span>
                                    <p class="text-[13px] font-black uppercase tracking-widest" :class="task.priority === 'Urgent' ? 'text-rose-600' : 'text-slate-800'">{{ task.priority }} NODE</p>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTION REFINEMENT PANEL (MODAL) -->
        <Transition name="fade">
            <div v-if="isEditModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/40 backdrop-blur-sm shadow-2xl overflow-y-auto">
                <div class="premium-card max-w-xl w-full p-8 lg:p-10 animate-in zoom-in h-fit max-h-[90vh] overflow-y-auto">
                    <div class="space-y-2 mb-8 border-l-4 border-amber-500 pl-6">
                        <h3 class="text-xl font-black text-slate-900 tracking-tight uppercase">Action Modification</h3>
                        <p class="text-xs font-medium text-slate-400">Re-indexing parameters for Action #{{ task.id }}</p>
                    </div>

                    <form @submit.prevent="submitTask" class="space-y-6">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Operational Title</label>
                            <input v-model="taskForm.title" class="input-premium font-bold" placeholder="Define the objective..." required />
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                             <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Primary Node (Assignee)</label>
                                <select v-model="taskForm.assignee_id" class="input-premium py-2.5 text-xs font-bold text-slate-700">
                                    <option value="">System Unbound</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>
                             </div>
                             <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Urgency Vector</label>
                                <select v-model="taskForm.priority" class="input-premium py-2.5 text-xs font-bold text-slate-700">
                                    <option>Low</option><option>Medium</option><option>High</option><option>Urgent</option>
                                </select>
                             </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                             <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Lifecycle State</label>
                                <select v-model="taskForm.status" class="input-premium py-2.5 text-xs font-bold text-slate-700">
                                    <option>Todo</option><option>In Progress</option><option>Done</option>
                                </select>
                             </div>
                             <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Resolution Deadline</label>
                                <input v-model="taskForm.due_date" type="date" class="input-premium py-2.5 text-xs font-bold tabular-nums" />
                             </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Detailed Requirements</label>
                            <textarea v-model="taskForm.description" class="input-premium text-sm font-medium min-h-[140px]" placeholder="Specific implementation details..."></textarea>
                        </div>

                        <div class="space-y-4">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Documentation Registry</label>
                            <div v-if="taskKeepAttachments.length" class="flex flex-wrap gap-2 mb-4">
                                <div v-for="att in task.attachments" :key="att.path"
                                     class="flex items-center gap-3 px-3 py-1.5 bg-slate-50 border rounded-xl transition-all"
                                     :class="taskKeepAttachments.includes(att.path) ? 'border-slate-200' : 'opacity-30 line-through grayscale border-rose-200'">
                                    <span class="text-[10px] font-bold text-slate-600 truncate max-w-[120px]">📎 {{ att.name }}</span>
                                    <button type="button" class="text-rose-500 hover:scale-110 transition-transform"
                                            @click="removeTaskKeepAttachment(att.path)"
                                            v-if="taskKeepAttachments.includes(att.path)">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </button>
                                </div>
                            </div>
                            <label class="w-full py-8 border-2 border-dashed border-slate-200 rounded-3xl flex flex-col items-center justify-center cursor-pointer hover:bg-slate-50 hover:border-indigo-300 transition-all group">
                                <i class="bi bi-cloud-arrow-up text-3xl text-slate-300 group-hover:text-indigo-400 transition-colors"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-2">Ingest supplementary blueprints</span>
                                <input type="file" @change="handleTaskFiles" multiple class="hidden" accept="image/*,.pdf,.doc,.docx,.txt,.zip" />
                            </label>
                        </div>

                        <div class="flex gap-4 pt-6">
                            <button type="button" @click="isEditModalOpen = false" class="flex-grow btn-premium-secondary py-4 text-base">Abort</button>
                            <button type="submit" class="flex-[2] btn-premium-primary bg-amber-500 hover:bg-amber-600 text-amber-50 py-4 text-base shadow-amber-100" :disabled="taskForm.processing">Synchronize Changes</button>
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

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

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 pt-2">
            <!-- Left: Main Task Details -->
            <div class="lg:col-span-3 space-y-5">
                <!-- Task Core Card -->
                <div class="premium-card p-0 overflow-hidden border-slate-200/60 shadow-sm">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-100">
                            <div class="space-y-2 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-widest border" :class="priorityColor(task.priority)">{{ task.priority }}</span>
                                    <span v-if="task.due_date" class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 border border-rose-100">Due: {{ (new Date(task.due_date)).toLocaleDateString() }}</span>
                                </div>
                                <h1 class="text-xl font-black text-slate-900 tracking-tight uppercase leading-none">
                                    {{ task.title }}
                                </h1>
                            </div>
                            <div class="shrink-0 flex items-center gap-2 px-3 py-1.5 bg-slate-900 rounded-xl shadow-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                                <span class="text-[9px] font-black uppercase tracking-widest text-white">{{ task.status }}</span>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    <i class="bi bi-text-left text-indigo-500"></i> Operation briefing
                                </h4>
                                <div class="bg-slate-50/50 rounded-2xl p-5 border border-slate-100/80 text-[13px] text-slate-600 leading-relaxed font-medium">
                                    {{ task.description || 'No detailed instructions provided.' }}
                                </div>
                            </div>

                            <div v-if="task.attachments && task.attachments.length" class="space-y-3">
                                <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-1">Evidence Registry</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2">
                                    <a v-for="att in task.attachments" :key="att.path"
                                       :href="'/storage/' + att.path" target="_blank"
                                       class="group flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-xl hover:border-indigo-300 hover:shadow-lg transition-all">
                                        <div class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center transition-colors group-hover:bg-indigo-600 group-hover:text-white shrink-0 border border-slate-100">
                                            <i class="bi bi-file-earmark-binary text-lg"></i>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-[10px] font-black text-slate-800 truncate">{{ att.name }}</span>
                                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">{{ (att.size / 1024).toFixed(0) }} KB</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discussion & Progress Feed -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between px-1">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Communication logs
                        </h3>
                        <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">{{ task.comments?.length || 0 }} Entries</span>
                    </div>
                    
                    <div class="space-y-4">
                        <div v-for="comment in task.comments" :key="comment.id" 
                             class="flex gap-4 animate-in fade-in duration-300"
                        >
                            <div class="shrink-0">
                                <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-900 text-[10px] font-black shadow-sm">
                                    {{ (comment.author?.name ?? 'U').charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex-grow space-y-1.5">
                                <div class="flex items-center gap-2 px-1">
                                    <span class="text-[11px] font-black text-slate-800">{{ comment.author?.name || 'Agent' }}</span>
                                    <span class="text-[8px] font-black text-slate-300 uppercase tracking-tighter">{{ formatDate(comment.created_at) }}</span>
                                </div>
                                <div class="premium-card p-4 bg-white border-slate-100 shadow-sm">
                                    <p class="text-xs text-slate-600 leading-normal font-medium">{{ comment.body }}</p>
                                    <div v-if="comment.attachment_path" class="mt-3 flex">
                                        <a :href="'/storage/' + comment.attachment_path" target="_blank" 
                                           class="flex items-center gap-2 px-2.5 py-1.5 bg-slate-50 text-slate-500 rounded-lg border border-slate-100 text-[8px] font-black uppercase tracking-widest hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                            <i class="bi bi-paperclip"></i> Fragment Archive
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!task.comments?.length" class="text-center py-10 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                             <p class="text-[9px] font-black uppercase text-slate-300 tracking-widest">No terminal traffic detected.</p>
                        </div>
                    </div>

                    <!-- Input Interface -->
                    <div class="premium-card p-6 bg-slate-900 border-0 shadow-lg ml-12">
                        <form @submit.prevent="submitComment" class="space-y-4 overflow-hidden">
                            <textarea v-model="commentForm.body" 
                                      class="w-full px-4 py-3 rounded-xl border border-slate-800 bg-slate-800/40 text-slate-200 text-xs font-medium min-h-[90px] focus:border-indigo-500 focus:bg-slate-800/80 outline-none transition-all placeholder-slate-600 shadow-inner" 
                                      placeholder="Log broadcast..." required></textarea>
                            
                            <div class="flex items-center justify-between gap-4">
                                <label class="flex-grow px-4 py-2 bg-white/5 border border-white/10 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-white/10 transition-all text-[8px] font-black text-slate-400 uppercase tracking-widest">
                                    <i class="bi bi-cloud-arrow-up text-indigo-400"></i>
                                    <span>{{ commentForm.attachment ? commentForm.attachment.name : 'Ingest Fragment' }}</span>
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

            <!-- Right: Object Information -->
            <div class="space-y-5">
                <div class="sticky top-24 space-y-5">
                    <div class="premium-card p-0 shadow-sm border-slate-200/60">
                        <div class="bg-indigo-600 p-5 flex items-center gap-4 relative overflow-hidden">
                            <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 shrink-0">
                                <i class="bi bi-shield-check text-xl text-white"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-white font-black uppercase tracking-widest text-[10px]">Registry Meta</h4>
                                <p class="text-[8px] text-white/50 font-black tracking-widest uppercase">Action #{{ task.id }}</p>
                            </div>
                        </div>
                        
                        <div class="p-5 space-y-5">
                            <div class="space-y-2">
                                <span class="text-[8px] font-black uppercase text-slate-400 tracking-widest block px-0.5">Tactical Origin</span>
                                <Link v-if="task.build" :href="route('projects.show', task.build.project_id)" class="group block p-3 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-200 transition-all">
                                    <span class="text-[11px] font-black text-slate-800 group-hover:text-indigo-600 block uppercase tracking-tight truncate">{{ task.build?.project?.name || 'Root' }}</span>
                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-tighter block mt-0.5">v{{ task.build?.version_name }}</span>
                                </Link>
                                <div v-else class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-center">
                                    <span class="text-[8px] font-black text-slate-400 uppercase italic">Global Protocol Scope</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <span class="text-[8px] font-black uppercase text-slate-400 tracking-widest block px-0.5">Primary unit</span>
                                <div class="flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-xl">
                                    <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-[10px] font-black text-white shadow-md">{{ task.assignee?.name.charAt(0) || '?' }}</div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-[11px] font-black text-slate-800 truncate uppercase tracking-tight">{{ task.assignee?.name || 'Unbound' }}</span>
                                        <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest mt-0.5">Handler</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3 px-0.5">
                                <div class="flex items-center justify-between text-[9px] font-black uppercase tracking-tight">
                                    <span class="text-slate-400">Dispatcher</span>
                                    <span class="text-slate-700">{{ task.creator?.name || 'System' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[9px] font-black uppercase tracking-tight pt-2 border-t border-slate-100">
                                    <span class="text-slate-400">Timestamp</span>
                                    <span class="text-slate-500">{{ formatDate(task.created_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                             <div class="p-3 bg-white rounded-xl border border-slate-200/60 text-center">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Operational Tier</p>
                                <div class="flex items-center justify-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="task.priority === 'Urgent' ? 'bg-rose-500 animate-ping' : 'bg-slate-400'"></span>
                                    <p class="text-[11px] font-black uppercase tracking-widest" :class="task.priority === 'Urgent' ? 'text-rose-600' : 'text-slate-800'">{{ task.priority }}</p>
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

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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-4">
            <!-- Left: Main Task Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Task Core Card -->
                <div class="premium-card p-8 lg:p-10">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-8">
                        <div class="space-y-3 min-w-0">
                            <div class="flex items-center gap-3">
                                <span class="badge-premium" :class="priorityColor(task.priority)">{{ task.priority }} Priority</span>
                                <span v-if="task.due_date" class="badge-premium bg-rose-50 text-rose-600 border-rose-100">
                                    <i class="bi bi-calendar-event mr-1.5"></i> Deadline: {{ (new Date(task.due_date)).toLocaleDateString() }}
                                </span>
                            </div>
                            <h1 class="text-3xl font-black text-slate-900 leading-tight tracking-tight">
                                {{ task.title }}
                            </h1>
                        </div>
                        <div class="shrink-0 flex items-center gap-2 px-4 py-2 bg-slate-900 rounded-2xl border border-slate-700 shadow-lg">
                            <span class="animate-pulse w-2 h-2 rounded-full bg-indigo-400"></span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-indigo-100">{{ task.status }}</span>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 mb-8">
                         <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Functional Requirements & Notes</h4>
                         <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                            {{ task.description || 'No detailed instructions provided for this action item.' }}
                         </div>
                    </div>

                    <div v-if="task.attachments && task.attachments.length" class="space-y-4">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Attached Documentation</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <a v-for="att in task.attachments" :key="att.path"
                               :href="'/storage/' + att.path" target="_blank"
                               class="group flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-[1.5rem] hover:border-indigo-300 hover:shadow-xl transition-all">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center transition-colors group-hover:bg-indigo-600 group-hover:text-white">
                                    <i class="bi bi-file-earmark-text text-xl"></i>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-xs font-black text-slate-800 truncate">{{ att.name }}</span>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">{{ (att.size / 1024).toFixed(0) }} KB</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Discussion & Progress Feed -->
                <div class="space-y-6 pt-4">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-3 px-1">
                        <i class="bi bi-activity text-indigo-500"></i> Execution Feed
                    </h3>
                    
                    <div class="space-y-6 relative before:absolute before:left-5 before:top-2 before:bottom-0 before:w-px before:bg-slate-200">
                        <div v-for="comment in task.comments" :key="comment.id" 
                             class="flex gap-6 animate-in fade-in relative"
                        >
                            <div class="shrink-0 relative z-10">
                                <div class="w-10 h-10 rounded-2xl bg-white border-4 border-slate-50 flex items-center justify-center text-slate-900 text-[11px] font-black shadow-md">
                                    {{ (comment.author?.name ?? 'U').charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex-grow space-y-2 pb-6">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-black text-slate-800">{{ comment.author?.name || 'Automated Cluster Agent' }}</span>
                                    <span class="text-[9px] font-black text-slate-300 uppercase tracking-tighter">{{ formatDate(comment.created_at) }}</span>
                                </div>
                                <div class="premium-card p-5 bg-white">
                                    <p class="text-sm text-slate-600 leading-relaxed font-medium">{{ comment.body }}</p>
                                    <div v-if="comment.attachment_path" class="mt-4 flex">
                                        <a :href="'/storage/' + comment.attachment_path" target="_blank" 
                                           class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-xl border border-slate-100 text-[10px] font-black text-slate-500 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                            <i class="bi bi-paperclip"></i> {{ comment.attachment_name }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!task.comments?.length" class="text-center py-12 premium-card bg-slate-50/30 border-dashed ml-10">
                             <p class="text-[10px] font-black uppercase text-slate-300 tracking-[0.2em]">Deployment feed is idle.</p>
                        </div>
                    </div>

                    <!-- Input Interface -->
                    <div class="premium-card p-8 bg-slate-900 border-slate-800 relative overflow-hidden group ml-10">
                        <div class="absolute -bottom-6 -right-6 p-8 opacity-10 group-focus-within:opacity-20 transition-opacity">
                            <i class="bi bi-journal-text text-[100px] text-indigo-400"></i>
                        </div>
                        
                        <form @submit.prevent="submitComment" class="space-y-6 relative z-10">
                            <textarea v-model="commentForm.body" 
                                      class="w-full px-5 py-4 rounded-2xl border-2 border-slate-800 bg-slate-800/50 text-indigo-50 text-sm font-medium min-h-[120px] focus:border-indigo-500 focus:bg-slate-800 outline-none transition-all" 
                                      placeholder="Log an update or share findings..." required></textarea>
                            
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                <label class="px-4 py-2 border border-slate-700 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-slate-800 transition-all text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <i class="bi bi-cloud-arrow-up text-lg"></i>
                                    <span>{{ commentForm.attachment ? commentForm.attachment.name : 'Upload Report' }}</span>
                                    <input type="file" id="comment-attachment" @change="handleCommentAttachment" class="hidden" accept="image/*,.pdf,.doc,.docx,.txt,zip">
                                </label>
                                <button type="submit" class="w-full sm:w-auto px-10 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-xl shadow-indigo-900/40 hover:bg-indigo-500 active:scale-95 transition-all" :disabled="commentForm.processing">
                                    Publish Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Object Information -->
            <div class="space-y-6">
                <div class="sticky top-24 space-y-6">
                    <div class="premium-card overflow-hidden">
                        <div class="bg-indigo-600 p-6 flex flex-col items-center text-center">
                            <div class="w-16 h-16 rounded-[1.5rem] bg-white/10 backdrop-blur-xl border border-white/20 flex items-center justify-center mb-4">
                                <i class="bi bi-check2-circle text-3xl text-white"></i>
                            </div>
                            <h4 class="text-white font-black uppercase tracking-widest text-xs">Lifecycle Metadata</h4>
                        </div>
                        
                        <div class="p-8 space-y-6">
                            <div class="space-y-1">
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest block">Project Branch</span>
                                <Link v-if="task.build" :href="route('builds.show', task.build.id)" class="text-sm font-black text-slate-900 hover:text-indigo-600 transition-colors block">
                                    {{ task.build?.project?.name || 'Isolated Root' }} v{{ task.build?.version_name }}
                                </Link>
                                <span v-else class="text-sm font-bold text-slate-400">Universal Context</span>
                            </div>

                            <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-tighter">Current Assignee</span>
                                    <span class="text-xs font-black text-slate-800">{{ task.assignee?.name || 'System Unbound' }}</span>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-slate-900 border-2 border-white flex items-center justify-center text-[10px] font-black text-white shadow-sm">{{ task.assignee?.name.charAt(0) || '?' }}</div>
                            </div>

                            <div class="space-y-4 pt-2">
                                <div class="flex items-center justify-between text-[11px] font-bold">
                                    <span class="text-slate-400 uppercase tracking-tighter">Initiated By</span>
                                    <span class="text-slate-700">{{ task.creator?.name || 'Automated Routine' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] font-bold">
                                    <span class="text-slate-400 uppercase tracking-tighter">Creation Stamp</span>
                                    <span class="text-slate-700">{{ formatDate(task.created_at) }}</span>
                                </div>
                                <div v-if="task.due_date" class="flex items-center justify-between text-[11px] font-bold">
                                    <span class="text-rose-400 uppercase tracking-tighter">Hard Deadline</span>
                                    <span class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded-lg border border-rose-100">{{ (new Date(task.due_date)).toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-slate-50 border-t border-slate-100">
                             <div class="p-4 bg-white rounded-2xl border border-slate-200 text-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Action Priority</p>
                                <p class="text-sm font-black" :class="task.priority === 'Urgent' ? 'text-rose-600' : 'text-slate-800'">{{ task.priority }} Node</p>
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

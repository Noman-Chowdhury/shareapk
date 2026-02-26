<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ tasks: Array, users: Array });

const editingTask = ref(null);
const taskForm = useForm({
    title: '', description: '', priority: 'Medium', status: 'Todo',
    assignee_id: '', due_date: ''
});

const taskNewFiles = ref([]);
const taskKeepAttachments = ref([]);
const isModalOpen = ref(false);

function openModal(tk) {
    editingTask.value = tk;
    taskForm.title = tk.title;
    taskForm.description = tk.description || '';
    taskForm.priority = tk.priority || 'Medium';
    taskForm.status = tk.status || 'Todo';
    taskForm.assignee_id = tk.assignee_id || '';
    taskForm.due_date = tk.due_date || '';
    taskNewFiles.value = [];
    taskKeepAttachments.value = (tk.attachments || []).map(a => a.path);
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    editingTask.value = null;
}

function handleTaskFiles(e) { taskNewFiles.value = Array.from(e.target.files); }
function removeTaskKeepAttachment(path) { taskKeepAttachments.value = taskKeepAttachments.value.filter(p => p !== path); }

function submitTask() {
    const url = route('tasks.update', editingTask.value.id);
    const payload = taskForm.data();
    taskNewFiles.value.forEach((file, i) => payload[`new_attachments[${i}]`] = file);
    taskKeepAttachments.value.forEach((path, i) => payload[`keep_attachments[${i}]`] = path);
    payload['_method'] = 'PUT';

    taskForm.transform(() => payload).post(url, {
        forceFormData: true, preserveScroll: true,
        onSuccess: () => { closeModal(); taskForm.reset(); taskNewFiles.value = []; },
    });
}

function deleteTask(id) {
    if (confirm('Delete this task?')) router.delete(route('tasks.destroy', id), { preserveScroll: true });
}

function priorityColor(p) {
    return { Urgent: 'bg-rose-50 text-rose-700 border-rose-100', High: 'bg-orange-50 text-orange-700 border-orange-100', Medium: 'bg-indigo-50 text-indigo-700 border-indigo-100', Low: 'bg-slate-50 text-slate-700 border-slate-100' }[p] || 'bg-slate-50';
}
function statusColor(s) {
    return { Todo: 'bg-slate-100 text-slate-600', 'In Progress': 'bg-indigo-600 text-white', Done: 'bg-emerald-500 text-white' }[s] || 'bg-slate-100';
}

const searchQuery = ref('');
const statusFilter = ref('All');

const filteredTasks = computed(() => {
    return props.tasks.filter(tk => {
        const matchesSearch = tk.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                             tk.build?.project?.name?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = statusFilter.value === 'All' || tk.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });
});
</script>

<template>
    <Head title="Action Items registry" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">System Tasks</h2>
                    <p class="text-slate-500 text-sm font-medium">Coordinate deployments and resolution paths across the cluster.</p>
                </div>
                <div class="flex gap-3">
                    <div class="relative">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input v-model="searchQuery" type="text" class="input-premium py-2.5 pl-11 w-64 text-sm" placeholder="Search tasks..." />
                    </div>
                    <select v-model="statusFilter" class="input-premium py-2.5 px-4 text-sm font-bold text-slate-700">
                        <option>All</option>
                        <option>Todo</option>
                        <option>In Progress</option>
                        <option>Done</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="premium-card overflow-hidden">
             <table class="w-full text-left">
                <thead class="bg-slate-900 border-b border-white/10 text-white">
                    <tr class="text-[10px] font-black uppercase tracking-[0.2em]">
                        <th class="px-8 py-4">ID / Task Details</th>
                        <th class="px-8 py-4">Status</th>
                        <th class="px-8 py-4 text-center">Priority</th>
                        <th class="px-8 py-4">Assignee</th>
                        <th class="px-8 py-4 text-right pr-12">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="tk in filteredTasks" :key="tk.id" class="group hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-3">
                            <div class="flex items-center gap-4">
                                <span class="text-[10px] font-black text-slate-300 tabular-nums">#{{ tk.id }}</span>
                                <div class="min-w-0">
                                    <Link :href="route('tasks.show', tk.id)" class="text-xs font-black text-slate-900 group-hover:text-indigo-600 transition-colors block truncate leading-tight">{{ tk.title }}</Link>
                                    <span v-if="tk.build" class="text-[9px] font-black text-slate-400 uppercase tracking-tighter opacity-70">{{ tk.build.project?.name }} Cluster</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-3">
                            <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest" :class="statusColor(tk.status)">{{ tk.status }}</span>
                        </td>
                        <td class="px-8 py-3 text-center">
                            <span class="badge-premium text-[8px] px-2 py-0.5" :class="priorityColor(tk.priority)">{{ tk.priority }}</span>
                        </td>
                        <td class="px-8 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-[9px] font-black text-slate-500">{{ tk.assignee?.name.charAt(0) || '?' }}</div>
                                <div class="flex flex-col">
                                    <span class="text-[11px] font-black text-slate-700 leading-none">{{ tk.assignee?.name || 'Unassigned' }}</span>
                                    <span v-if="tk.due_date" class="text-[8px] font-black text-rose-500 uppercase tracking-tighter mt-0.5">Due {{ new Date(tk.due_date).toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-3 text-right pr-10">
                            <div class="flex items-center justify-end gap-2">
                                <Link :href="route('tasks.show', tk.id)" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all border border-transparent hover:border-indigo-100"><i class="bi bi-eye"></i></Link>
                                <button @click="openModal(tk)" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all border border-transparent hover:border-indigo-100"><i class="bi bi-pencil-square"></i></button>
                                <button @click="deleteTask(tk.id)" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all border border-transparent hover:border-rose-100"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!filteredTasks.length">
                        <td colspan="5" class="px-8 py-20 text-center text-slate-400">
                             <p class="font-bold uppercase tracking-widest text-xs">No active tasks identified</p>
                        </td>
                    </tr>
                </tbody>
             </table>
        </div>

        <!-- Custom Transition Modal for Editing -->
        <Transition name="fade">
            <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/40 backdrop-blur-sm shadow-2xl overflow-y-auto">
                <div class="premium-card max-w-xl w-full p-8 animate-in zoom-in h-fit">
                    <h3 class="text-xl font-black text-slate-900 mb-2">Modify Action Item</h3>
                    <form @submit.prevent="submitTask" class="space-y-4 pt-4">
                        <input v-model="taskForm.title" class="input-premium font-bold" placeholder="Task objective..." required />
                        <div class="grid grid-cols-2 gap-4">
                             <div>
                                <label class="text-[10px] font-black uppercase text-slate-400 mb-1 block">Assignee</label>
                                <select v-model="taskForm.assignee_id" class="input-premium py-2 text-sm font-bold text-slate-700">
                                    <option value="">Unassigned</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>
                             </div>
                             <div>
                                <label class="text-[10px] font-black uppercase text-slate-400 mb-1 block">Priority Level</label>
                                <select v-model="taskForm.priority" class="input-premium py-2 text-sm font-bold text-slate-700">
                                    <option>Low</option><option>Medium</option><option>High</option><option>Urgent</option>
                                </select>
                             </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                             <div>
                                <label class="text-[10px] font-black uppercase text-slate-400 mb-1 block">Lifecycle State</label>
                                <select v-model="taskForm.status" class="input-premium py-2 text-sm font-bold text-slate-700">
                                    <option>Todo</option><option>In Progress</option><option>Done</option>
                                </select>
                             </div>
                             <div>
                                <label class="text-[10px] font-black uppercase text-slate-400 mb-1 block">Resolution Deadline</label>
                                <input v-model="taskForm.due_date" type="date" class="input-premium py-2 text-sm tabular-nums" />
                             </div>
                        </div>
                        <textarea v-model="taskForm.description" class="input-premium text-sm min-h-[100px]" placeholder="Specific instructions..."></textarea>
                        
                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="closeModal" class="btn-premium-secondary">Abort</button>
                            <button type="submit" class="btn-premium-primary flex-grow">Update Repository</button>
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

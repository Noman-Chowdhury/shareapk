<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    totalBuilds: Number,
    openFeedback: Number,
    myTasksCount: Number,
    myTasks: Array,
    myFeedback: Array,
    downloads: Number,
    recentBuilds: Array,
    recentActivity: Array,
});

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Command Center" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        Command Center
                        <span class="text-[10px] font-black uppercase text-indigo-500 bg-indigo-50 border border-indigo-100 px-2 py-1 rounded-lg tracking-widest animate-pulse">Live</span>
                    </h2>
                    <p class="text-slate-500 text-sm font-medium">Synchronizing APK distribution and quality protocols.</p>
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="route('tasks.index')" class="hidden md:flex flex-col items-end">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Tasks</span>
                        <span class="text-lg font-black text-slate-800 tabular-nums">{{ myTasksCount }}</span>
                    </Link>
                    <Link :href="route('builds.create')" class="btn-premium-primary px-8 py-3.5 shadow-xl shadow-indigo-100">
                        <i class="bi bi-plus-lg mr-2"></i> Deploy New Build
                    </Link>
                </div>
            </div>
        </template>

        <!-- Dynamic Stats Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <div class="premium-card p-8 group hover:bg-slate-900 transition-all duration-500 border-b-4 border-indigo-500">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors border border-indigo-100 group-hover:border-indigo-400">
                        <i class="bi bi-box-seam text-2xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] group-hover:text-slate-500 transition-colors">Registry</span>
                </div>
                <h3 class="text-4xl font-black text-slate-900 mb-1 group-hover:text-white transition-colors tabular-nums">{{ totalBuilds }}</h3>
                <p class="text-slate-500 font-bold text-xs uppercase tracking-widest group-hover:text-slate-400 transition-colors">Total Deployments</p>
            </div>

            <div class="premium-card p-8 group hover:bg-slate-900 transition-all duration-500 border-b-4 border-rose-500">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors border border-rose-100 group-hover:border-rose-400">
                        <i class="bi bi-bug text-2xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] group-hover:text-slate-500 transition-colors">Quality</span>
                </div>
                <h3 class="text-4xl font-black text-slate-900 mb-1 group-hover:text-white transition-colors tabular-nums">{{ openFeedback }}</h3>
                <p class="text-slate-500 font-bold text-xs uppercase tracking-widest group-hover:text-slate-400 transition-colors">Alert Thresholds</p>
            </div>

            <div class="premium-card p-8 group hover:bg-slate-900 transition-all duration-500 border-b-4 border-amber-500">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-colors border border-amber-100 group-hover:border-amber-400">
                        <i class="bi bi-cpu text-2xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] group-hover:text-slate-500 transition-colors">Execution</span>
                </div>
                <h3 class="text-4xl font-black text-slate-900 mb-1 group-hover:text-white transition-colors tabular-nums">{{ myTasksCount }}</h3>
                <p class="text-slate-500 font-bold text-xs uppercase tracking-widest group-hover:text-slate-400 transition-colors">Assigned Operations</p>
            </div>

            <div class="premium-card p-8 group hover:bg-slate-900 transition-all duration-500 border-b-4 border-emerald-500">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors border border-emerald-100 group-hover:border-emerald-400">
                        <i class="bi bi-cloud-arrow-down text-2xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] group-hover:text-slate-500 transition-colors">Transfer</span>
                </div>
                <h3 class="text-4xl font-black text-slate-900 mb-1 group-hover:text-white transition-colors tabular-nums">{{ downloads }}</h3>
                <p class="text-slate-500 font-bold text-xs uppercase tracking-widest group-hover:text-slate-400 transition-colors">Successful Handshakes</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-10 items-start">
            <!-- Deployment Stream -->
            <div class="xl:col-span-8 space-y-10">
                <div class="premium-card overflow-hidden bg-white shadow-xl shadow-slate-200/50 relative">
                    <div class="absolute top-0 right-0 p-8 opacity-[0.03] pointer-events-none">
                        <i class="bi bi-terminal text-[160px]"></i>
                    </div>
                    
                    <div class="px-10 py-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                        <div class="flex items-center gap-4">
                            <div class="w-2 h-10 bg-indigo-600 rounded-full"></div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight uppercase">Deployment Log</h3>
                        </div>
                        <Link :href="route('projects.index')" class="text-[10px] font-black uppercase text-indigo-600 tracking-widest hover:text-indigo-800 transition-colors bg-indigo-50 px-4 py-2 rounded-xl border border-indigo-100">Project Directory</Link>
                    </div>
                    
                    <div class="divide-y divide-slate-100">
                        <template v-if="recentBuilds?.length">
                            <Link v-for="build in recentBuilds" :key="build.id" :href="route('builds.show', build.id)" class="block px-10 py-8 hover:bg-slate-50 transition-all duration-300 group">
                                <div class="flex items-center gap-8">
                                    <div class="w-20 h-20 rounded-[2rem] bg-white border-2 border-slate-100 flex items-center justify-center overflow-hidden group-hover:border-indigo-200 group-hover:shadow-2xl group-hover:shadow-indigo-100/50 transition-all duration-500 p-2">
                                        <img v-if="build.project?.icon_url" :src="'/storage/' + build.project.icon_url"  class="w-full h-full object-contain" />
                                        <i v-else class="bi bi-box-seam text-3xl text-slate-300"></i>
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <div class="flex items-center gap-3 mb-2 flex-wrap">
                                            <h4 class="text-xl font-black text-slate-900 group-hover:text-indigo-600 transition-colors">{{ build.project?.name }}</h4>
                                            <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded uppercase tracking-widest border border-slate-200">Ref: {{ build.version_name }}</span>
                                            <span class="badge-premium bg-indigo-50 text-indigo-700 border border-indigo-100 text-[9px] py-0.5 px-3">{{ build.build_type }} branch</span>
                                        </div>
                                        <div class="flex items-center gap-6 text-[11px] font-bold text-slate-400 uppercase tracking-tight">
                                            <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg group-hover:bg-white transition-colors border border-transparent group-hover:border-slate-100">
                                                <i class="bi bi-shield-check text-emerald-500"></i>
                                                <span class="text-slate-600">{{ build.uploader?.name }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-cpu"></i>
                                                <span class="tabular-nums">{{ (build.file_size / 1048576).toFixed(1) }} MB Payload</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-clock"></i>
                                                <span>{{ formatDate(build.created_at) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hidden md:flex items-center gap-3">
                                        <div v-if="build.pending_feedbacks_count" class="group/alert relative">
                                            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex flex-col items-center justify-center transition-transform hover:-translate-y-1">
                                                <span class="text-[11px] font-black text-rose-600 leading-none">{{ build.pending_feedbacks_count }}</span>
                                                <i class="bi bi-bug-fill text-[11px] text-rose-400/50"></i>
                                            </div>
                                        </div>
                                        <div v-if="build.pending_tasks_count" class="group/alert relative">
                                            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 flex flex-col items-center justify-center transition-transform hover:-translate-y-1">
                                                <span class="text-[11px] font-black text-amber-600 leading-none">{{ build.pending_tasks_count }}</span>
                                                <i class="bi bi-activity text-[11px] text-amber-400/50"></i>
                                            </div>
                                        </div>
                                        <i class="bi bi-chevron-right text-slate-200 group-hover:text-indigo-400 group-hover:translate-x-1 transition-all ml-4"></i>
                                    </div>
                                </div>
                            </Link>
                        </template>
                        <div v-else class="py-32 text-center">
                             <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-slate-100">
                                <i class="bi bi-terminal-dash text-3xl text-slate-200"></i>
                             </div>
                             <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.4em]">No deployment history detected in registry.</p>
                        </div>
                    </div>
                </div>

                <!-- Personal Context grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Action Items -->
                    <div class="premium-card overflow-hidden bg-white">
                         <div class="px-8 py-5 bg-slate-900 border-b border-slate-800 flex items-center justify-between">
                            <h3 class="font-black text-indigo-400 text-xs uppercase tracking-[0.2em]">Operational Stack</h3>
                            <Link :href="route('tasks.index')" class="text-[9px] font-black text-slate-400 uppercase tracking-widest hover:text-white transition-colors">Stack View</Link>
                        </div>
                        <div class="divide-y divide-slate-100">
                            <Link v-for="task in myTasks" :key="task.id" :href="route('tasks.show', task.id)" class="block px-8 py-6 hover:bg-slate-50 transition-colors group">
                                <div class="flex items-center gap-4 mb-3">
                                    <div class="w-1.5 h-6 rounded-full" :class="task.priority === 'Urgent' ? 'bg-rose-500 shadow-[0_0_12px_rgba(244,63,94,0.6)] animate-pulse' : 'bg-amber-400'"></div>
                                    <span class="font-black text-slate-800 group-hover:text-indigo-600 text-[13px] leading-tight transition-colors">{{ task.title }}</span>
                                </div>
                                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-slate-400 pl-5">
                                    <span class="truncate pr-4">{{ task.build?.project?.name }}</span>
                                    <span v-if="task.due_date" class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-100">{{ formatDate(task.due_date) }}</span>
                                </div>
                            </Link>
                            <div v-if="!myTasks?.length" class="py-20 text-center">
                                <i class="bi bi-check2-circle text-4xl text-slate-100 mb-4 block"></i>
                                <span class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">Execution Queue Idle</span>
                            </div>
                        </div>
                    </div>

                    <!-- Observation Data -->
                    <div class="premium-card overflow-hidden bg-white">
                         <div class="px-8 py-5 bg-slate-900 border-b border-slate-800 flex items-center justify-between">
                            <h3 class="font-black text-rose-400 text-xs uppercase tracking-[0.2em]">Quality Observation</h3>
                            <Link :href="route('feedback.index')" class="text-[9px] font-black text-slate-400 uppercase tracking-widest hover:text-white transition-colors">Audit log</Link>
                        </div>
                        <div class="divide-y divide-slate-100">
                            <Link v-for="fb in myFeedback" :key="fb.id" :href="route('feedback.show', fb.id)" class="block px-8 py-6 hover:bg-slate-50 transition-colors group">
                                <div class="flex flex-col mb-3">
                                    <span class="font-black text-slate-800 group-hover:text-rose-600 text-[13px] mb-2 leading-tight transition-colors">{{ fb.title }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-tighter border border-slate-200">{{ fb.type }}</span>
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded bg-rose-50 text-rose-600 uppercase tracking-tighter border border-rose-100">{{ fb.status }}</span>
                                    </div>
                                </div>
                            </Link>
                            <div v-if="!myFeedback?.length" class="py-20 text-center">
                                <i class="bi bi-shield-check text-4xl text-slate-100 mb-4 block"></i>
                                <span class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">Structural integrity verified</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Comm sidebar -->
            <div class="xl:col-span-4 space-y-8">
                <div class="premium-card overflow-hidden bg-slate-900 border-slate-800 shadow-2xl shadow-indigo-900/10">
                    <div class="px-8 py-6 bg-gradient-to-br from-indigo-600 to-indigo-800 border-b border-white/10 relative overflow-hidden">
                        <h3 class="font-black text-white flex items-center gap-3 tracking-tight relative z-10">
                            <i class="bi bi-lightning-charge-fill text-amber-300 animate-pulse"></i> Global Comms Feed
                        </h3>
                        <div class="absolute -right-4 -bottom-4 animate-spin-slow opacity-10">
                             <i class="bi bi-gear-wide-connected text-[120px] text-white"></i>
                        </div>
                    </div>
                    <div class="p-4 space-y-2">
                        <template v-if="recentActivity?.length">
                            <div v-for="activity in recentActivity" :key="activity.id" class="p-6 rounded-[2rem] bg-slate-800/40 border border-slate-700/50 hover:bg-slate-800 hover:border-indigo-500/50 transition-all duration-300 group">
                                <div class="flex gap-5">
                                    <div class="shrink-0">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm uppercase shadow-xl shadow-indigo-900/50 border-2 border-white/10">
                                            {{ activity.user?.name.charAt(0) }}
                                        </div>
                                    </div>
                                    <div class="min-w-0 space-y-2">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-black text-indigo-300">{{ activity.user?.name }}</span> 
                                            <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">{{ formatDate(activity.created_at) }}</span>
                                        </div>
                                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-tight leading-relaxed">
                                            Handshake: Internal Comm for <span class="text-indigo-400">{{ activity.commentable_type.split('\\').pop() }}</span>
                                        </p>
                                        <p class="text-slate-300 text-xs italic leading-relaxed border-l-2 border-slate-700 pl-4 py-1">"{{ activity.body }}"</p>
                                        <div v-if="activity.attachment_path" class="pt-2">
                                            <span class="text-[9px] bg-slate-700 text-indigo-300 px-2 py-1 rounded-lg font-black uppercase tracking-widest border border-indigo-400/20">📎 Ingested Asset</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div v-else class="py-24 text-center">
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-[0.4em]">Subspace communication idle.</span>
                        </div>
                    </div>
                </div>

                <!-- Support Matrix -->
                <div class="premium-card p-10 bg-gradient-to-br from-indigo-700 to-blue-900 text-white relative overflow-hidden group">
                    <div class="relative z-10 space-y-6">
                        <div class="w-16 h-16 rounded-[1.8rem] bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-3xl group-hover:rotate-12 transition-transform duration-500">
                             <i class="bi bi-hdd-network-fill"></i>
                        </div>
                        <div class="space-y-2">
                            <h4 class="font-black text-2xl tracking-tighter uppercase italic">Registry Docs</h4>
                            <p class="text-indigo-100/70 text-xs font-bold uppercase tracking-[0.2em] leading-relaxed">Strategic guidelines for deployment automation & protocol management.</p>
                        </div>
                        <button class="w-full bg-white text-indigo-700 font-black px-6 py-4 rounded-2xl text-[10px] uppercase tracking-[0.3em] transition-all hover:bg-slate-900 hover:text-white shadow-2xl shadow-indigo-900/40 active:scale-95">Open Knowledge Base</button>
                    </div>
                    <i class="bi bi-terminal absolute -bottom-10 -right-10 text-[180px] text-white opacity-[0.05] group-hover:scale-110 transition-transform duration-700"></i>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 12s linear infinite;
}
</style>

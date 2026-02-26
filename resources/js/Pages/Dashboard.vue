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
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString();
}
</script>

<template>
    <Head title="Command Center" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Command Center</h2>
                    <p class="text-slate-500 text-xs font-medium">Synchronizing distributed APK assets and quality protocols.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('builds.create')" class="btn-premium-indigo px-6">
                        <i class="bi bi-cloud-arrow-up mr-2"></i> Deploy New Build
                    </Link>
                </div>
            </div>
        </template>

        <!-- Dynamic Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="premium-card p-6 border-b-4 border-indigo-500 animate-slide-up" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-sm">
                        <i class="bi bi-box-seam text-xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Registries</span>
                </div>
                <h3 class="text-3xl font-black text-slate-800 tabular-nums">{{ totalBuilds }}</h3>
                <p class="text-slate-500 font-bold text-[10px] uppercase tracking-wider mt-1">Total Deployments</p>
            </div>

            <div class="premium-card p-6 border-b-4 border-rose-500 animate-slide-up" 
                 :class="{ 'animate-pulse-rose': openFeedback > 0 }"
                 style="animation-delay: 0.2s">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shadow-sm">
                        <i class="bi bi-bug text-xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Quality</span>
                </div>
                <h3 class="text-3xl font-black text-slate-800 tabular-nums">{{ openFeedback }}</h3>
                <p class="text-slate-500 font-bold text-[10px] uppercase tracking-wider mt-1">Pending Alerts</p>
            </div>

            <div class="premium-card p-6 border-b-4 border-amber-500 animate-slide-up" 
                 :class="{ 'animate-pulse-amber': myTasksCount > 0 }"
                 style="animation-delay: 0.3s">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-sm">
                        <i class="bi bi-activity text-xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Operations</span>
                </div>
                <h3 class="text-3xl font-black text-slate-800 tabular-nums">{{ myTasksCount }}</h3>
                <p class="text-slate-500 font-bold text-[10px] uppercase tracking-wider mt-1">Active Assignments</p>
            </div>

            <div class="premium-card p-6 border-b-4 border-emerald-500 animate-slide-up" style="animation-delay: 0.4s">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm">
                        <i class="bi bi-cloud-download text-xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Transfer</span>
                </div>
                <h3 class="text-3xl font-black text-slate-800 tabular-nums">{{ downloads }}</h3>
                <p class="text-slate-500 font-bold text-[10px] uppercase tracking-wider mt-1">Success Echoes</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
            <!-- Deployment Stream -->
            <div class="xl:col-span-8 space-y-8">
                <div class="premium-card overflow-hidden animate-slide-up" style="animation-delay: 0.5s">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">Deployment Intelligence</h3>
                        <Link :href="route('projects.index')" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 transition-colors uppercase tracking-widest">Protocol Index</Link>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-50">
                                    <th class="px-6 py-4">Identification</th>
                                    <th class="px-6 py-4">Release Path</th>
                                    <th class="px-6 py-4 text-center">Stability</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="build in recentBuilds" :key="build.id" 
                                    class="group transition-colors"
                                    :class="[
                                        build.pending_feedbacks_count > 0 ? 'animate-pulse-rose bg-rose-50/10' : 
                                        build.pending_tasks_count > 0 ? 'animate-pulse-amber bg-amber-50/10' : 
                                        'hover:bg-[#f1f5f9]/50'
                                    ]">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 shadow-sm p-1.5 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                                <img v-if="build.project?.icon_url" :src="'/storage/' + build.project.icon_url" class="max-w-full max-h-full object-contain" />
                                                <i v-else class="bi bi-android2 text-slate-300 text-xl"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <span class="text-sm font-bold text-slate-800 block truncate">{{ build.project?.name }}</span>
                                                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-tighter">REF: {{ build.version_name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-slate-600">v{{ build.version_name }}</span>
                                            <span class="text-[10px] text-slate-400">{{ formatDate(build.created_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="badge-premium" :class="{
                                            'bg-emerald-50 text-emerald-700': build.build_type === 'Production',
                                            'bg-indigo-50 text-indigo-700': build.build_type === 'RC',
                                            'bg-blue-50 text-blue-700': build.build_type === 'Beta',
                                            'bg-amber-50 text-amber-700': build.build_type === 'Alpha',
                                        }">{{ build.build_type }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('builds.show', build.id)" class="btn-premium-matte py-1 text-[10px] px-3">Inspect</Link>
                                    </td>
                                </tr>
                                <tr v-if="!recentBuilds?.length">
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic text-xs">No synchronization data available.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Action Items -->
                    <div class="premium-card overflow-hidden animate-slide-up" style="animation-delay: 0.6s">
                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-900 text-white">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-indigo-400">Operational Stack</h3>
                            <i class="bi bi-stack text-slate-500"></i>
                        </div>
                        <div class="divide-y divide-slate-50 matte-surface h-64 overflow-y-auto">
                            <Link v-for="task in myTasks" :key="task.id" :href="route('tasks.show', task.id)" class="block px-6 py-4 hover:bg-white transition-colors">
                                <p class="text-sm font-bold text-slate-800 truncate mb-1">{{ task.title }}</p>
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">{{ task.build?.project?.name }}</span>
                                    <span v-if="task.priority === 'Urgent'" class="text-[9px] font-black text-rose-500 bg-rose-50 px-1.5 py-0.5 rounded">URGENT</span>
                                </div>
                            </Link>
                            <div v-if="!myTasks?.length" class="py-16 text-center text-slate-300 italic text-xs">Queue Clear</div>
                        </div>
                    </div>

                    <!-- Observation Data -->
                    <div class="premium-card overflow-hidden animate-slide-up" style="animation-delay: 0.7s">
                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-900 text-white">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-rose-400">Quality Echo</h3>
                            <i class="bi bi-shield-radar text-slate-500"></i>
                        </div>
                        <div class="divide-y divide-slate-50 matte-surface h-64 overflow-y-auto">
                            <Link v-for="fb in myFeedback" :key="fb.id" :href="route('feedback.show', fb.id)" class="block px-6 py-4 hover:bg-white transition-colors">
                                <p class="text-sm font-bold text-slate-800 truncate mb-1">{{ fb.title }}</p>
                                <div class="flex gap-2">
                                    <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase">{{ fb.type }}</span>
                                    <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-rose-50 text-rose-600 uppercase">{{ fb.status }}</span>
                                </div>
                            </Link>
                            <div v-if="!myFeedback?.length" class="py-16 text-center text-slate-300 italic text-xs">No Observations</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Stream -->
            <div class="xl:col-span-4 space-y-8 animate-slide-up" style="animation-delay: 0.8s">
                <div class="premium-card overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-br from-indigo-600 to-indigo-800 text-white flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase tracking-widest">Protocol Signal</h3>
                        <i class="bi bi-lightning-charge animate-pulse"></i>
                    </div>
                    <div class="p-6 space-y-6 max-h-[600px] overflow-y-auto matte-surface">
                        <template v-if="recentActivity?.length">
                            <div v-for="activity in recentActivity" :key="activity.id" class="flex gap-4 group">
                                <div class="shrink-0">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-xs font-bold text-slate-500 group-hover:bg-indigo-500 group-hover:text-white group-hover:border-indigo-500 transition-all">
                                        {{ activity.user?.name.charAt(0) }}
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-800 mb-1 leading-snug">
                                        <span class="font-bold">{{ activity.user?.name }}</span>
                                        <span class="text-slate-400 mx-1">Interacted with</span>
                                        <span class="font-bold text-indigo-600">{{ activity.commentable_type.split('\\').pop() }}</span>
                                    </p>
                                    <div class="p-3 bg-white rounded-xl border border-slate-100 italic text-[11px] text-slate-500 mb-2 shadow-sm">
                                        "{{ activity.body }}"
                                    </div>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ formatDate(activity.created_at) }}</span>
                                </div>
                            </div>
                        </template>
                        <div v-else class="py-20 text-center text-slate-400 italic text-xs">Signal Silent</div>
                    </div>
                </div>

                <!-- Knowledge Hub -->
                <div class="premium-card p-8 bg-slate-900 text-white relative overflow-hidden group">
                    <div class="relative z-10">
                        <h4 class="text-sm font-black uppercase tracking-widest mb-3">Protocol Archive</h4>
                        <p class="text-xs text-slate-400 mb-6 leading-relaxed">Deep-dive into deployment schemas, API integration patterns, and quality assurance guidelines.</p>
                        <button class="w-full btn-premium-indigo py-3 text-xs bg-white text-slate-900 hover:bg-indigo-50">Explore Hub</button>
                    </div>
                    <i class="bi bi-hdd-network absolute -bottom-6 -right-6 text-8xl text-indigo-500/10 group-hover:scale-110 transition-transform"></i>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

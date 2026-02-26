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
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">System Dashboard</h2>
                    <p class="text-slate-500 text-xs">Overview of current application distribution and quality metrics.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('builds.create')" class="btn-premium-indigo">
                        <i class="bi bi-plus-lg mr-2"></i> Deploy Build
                    </Link>
                </div>
            </div>
        </template>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="premium-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Builds</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-900 tabular-nums">{{ totalBuilds }}</h3>
                    <span class="text-[10px] text-emerald-600 font-bold">+12%</span>
                </div>
            </div>

            <div class="premium-card p-5 border-l-4 border-l-rose-500">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Open Issues</span>
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                        <i class="bi bi-bug"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-900 tabular-nums">{{ openFeedback }}</h3>
                    <span class="text-[10px] text-rose-600 font-bold" v-if="openFeedback > 0">Attention Required</span>
                </div>
            </div>

            <div class="premium-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Active Tasks</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-900 tabular-nums">{{ myTasksCount }}</h3>
                    <span class="text-[10px] text-slate-400 font-medium">Assigned to you</span>
                </div>
            </div>

            <div class="premium-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Downloads</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="bi bi-download"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-900 tabular-nums">{{ downloads }}</h3>
                    <span class="text-[10px] text-emerald-600 font-bold">Live Traffic</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            <!-- Recent Deployments -->
            <div class="xl:col-span-8 space-y-6">
                <div class="premium-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Recent Deployments</h3>
                        <Link :href="route('projects.index')" class="text-[10px] font-bold text-indigo-600 hover:underline">View All Projects</Link>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-50">
                                    <th class="px-5 py-3">Project</th>
                                    <th class="px-5 py-3">Version</th>
                                    <th class="px-5 py-3">Stability</th>
                                    <th class="px-5 py-3">Date</th>
                                    <th class="px-5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="build in recentBuilds" :key="build.id" class="group hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded bg-white border border-slate-200 flex items-center justify-center p-1 shrink-0">
                                                <img v-if="build.project?.icon_url" :src="'/storage/' + build.project.icon_url" class="max-w-full max-h-full object-contain" />
                                                <i v-else class="bi bi-android2 text-slate-300"></i>
                                            </div>
                                            <span class="text-xs font-bold text-slate-900 truncate max-w-[150px]">{{ build.project?.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-xs font-mono text-slate-500">{{ build.version_name }}</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="badge-premium" :class="{
                                            'bg-emerald-50 text-emerald-700': build.build_type === 'Production',
                                            'bg-indigo-50 text-indigo-700': build.build_type === 'RC',
                                            'bg-blue-50 text-blue-700': build.build_type === 'Beta',
                                            'bg-amber-50 text-amber-700': build.build_type === 'Alpha',
                                        }">{{ build.build_type }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-500">
                                        {{ formatDate(build.created_at) }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <Link :href="route('builds.show', build.id)" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold">Details</Link>
                                    </td>
                                </tr>
                                <tr v-if="!recentBuilds?.length">
                                    <td colspan="5" class="py-10 text-center text-slate-400 italic text-xs">No recent activity detected.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- My Tasks -->
                    <div class="premium-card overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Active Assignments</h3>
                            <i class="bi bi-list-task text-slate-300"></i>
                        </div>
                        <div class="divide-y divide-slate-50 overflow-y-auto max-h-64">
                            <Link v-for="task in myTasks" :key="task.id" :href="route('tasks.show', task.id)" class="block px-5 py-3 hover:bg-slate-50 transition-colors">
                                <p class="text-[13px] font-bold text-slate-800 truncate mb-1">{{ task.title }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-slate-400 font-medium">{{ task.build?.project?.name }}</span>
                                    <span v-if="task.priority === 'Urgent'" class="text-[9px] font-bold text-rose-600 uppercase tracking-tighter">Urgent</span>
                                </div>
                            </Link>
                            <div v-if="!myTasks?.length" class="py-10 text-center text-slate-300 italic text-xs">Queue is clear.</div>
                        </div>
                    </div>

                    <!-- My Feedback -->
                    <div class="premium-card overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Observation Feed</h3>
                            <i class="bi bi-chat-text text-slate-300"></i>
                        </div>
                        <div class="divide-y divide-slate-50 overflow-y-auto max-h-64">
                            <Link v-for="fb in myFeedback" :key="fb.id" :href="route('feedback.show', fb.id)" class="block px-5 py-3 hover:bg-slate-50 transition-colors">
                                <p class="text-[13px] font-bold text-slate-800 truncate mb-1">{{ fb.title }}</p>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 uppercase">{{ fb.type }}</span>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 uppercase">{{ fb.status }}</span>
                                </div>
                            </Link>
                            <div v-if="!myFeedback?.length" class="py-10 text-center text-slate-300 italic text-xs">No pending feedback.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity / Global Stream -->
            <div class="xl:col-span-4 space-y-6">
                <div class="premium-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">System Activity</h3>
                    </div>
                    <div class="p-5 space-y-4 overflow-y-auto max-h-[600px]">
                        <template v-if="recentActivity?.length">
                            <div v-for="activity in recentActivity" :key="activity.id" class="flex gap-4 pb-4 border-b border-slate-50 last:border-0 last:pb-0">
                                <div class="shrink-0">
                                    <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                        {{ activity.user?.name.charAt(0) }}
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-800 mb-1">
                                        <span class="font-bold">{{ activity.user?.name }}</span>
                                        <span class="text-slate-500 mx-1">replied to</span>
                                        <span class="font-bold text-indigo-600">{{ activity.commentable_type.split('\\').pop() }}</span>
                                    </p>
                                    <p class="text-[11px] text-slate-500 italic line-clamp-2 mb-2">"{{ activity.body }}"</p>
                                    <span class="text-[9px] text-slate-400 font-medium uppercase tracking-wider">{{ formatDate(activity.created_at) }}</span>
                                </div>
                            </div>
                        </template>
                        <div v-else class="py-20 text-center text-slate-300 italic text-xs">No activity stream available.</div>
                    </div>
                </div>

                <!-- Support/Docs Info -->
                <div class="premium-card p-6 bg-indigo-600 text-white shadow-lg shadow-indigo-100">
                    <h4 class="text-sm font-bold uppercase tracking-wider mb-2">Knowledge Base</h4>
                    <p class="text-xs text-indigo-100 mb-4 leading-relaxed">Access documentation for API protocols, distribution rules, and build requirements.</p>
                    <button class="w-full bg-white text-indigo-600 font-bold py-2 rounded-lg text-xs hover:bg-indigo-50 transition-colors">Documentation</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    projects: Array,
});

function buildTypeColor(type) {
    return {
        Production: 'bg-emerald-50 text-emerald-700',
        RC: 'bg-indigo-50 text-indigo-700',
        Beta: 'bg-blue-50 text-blue-700',
        Alpha: 'bg-amber-50 text-amber-700',
    }[type] || 'bg-slate-50 text-slate-700';
}
</script>

<template>
    <Head title="App Directory" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">App Directory</h2>
                    <p class="text-slate-500 text-xs">Browse and manage registered application packages.</p>
                </div>
                <Link :href="route('builds.create')" class="btn-premium-indigo">
                    <i class="bi bi-upload mr-2"></i> Deploy APK
                </Link>
            </div>
        </template>

        <div v-if="projects.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-20 h-20 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center mb-4">
                <i class="bi bi-box-seam text-3xl text-slate-200"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-800 mb-1">No Projects Found</h4>
            <p class="text-slate-400 text-xs max-w-sm mx-auto mb-6">Initialize a new project by deploying its first build.</p>
            <Link :href="route('builds.create')" class="btn-premium-primary">Deploy First Build</Link>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div v-for="project in projects" :key="project.id" class="group">
                <Link :href="route('projects.show', project.id)" class="block h-full">
                    <div class="premium-card p-6 h-full flex flex-col hover-card"
                         :class="{ 
                             'animate-pulse-rose': project.pending_feedbacks_count > 0,
                             'animate-pulse-amber': project.pending_feedbacks_count === 0 && project.pending_tasks_count > 0 
                         }">
                        
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-white border border-slate-200 shadow-sm p-1.5 flex items-center justify-center overflow-hidden shrink-0">
                                <img v-if="project.icon_url" :src="'/storage/' + project.icon_url" class="max-w-full max-h-full object-contain" />
                                <i v-else class="bi bi-android2 text-2xl text-slate-300"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-slate-900 truncate mb-0.5 group-hover:text-indigo-600 transition-colors">{{ project.name }}</h3>
                                <p class="text-[10px] font-mono text-slate-400 uppercase truncate">{{ project.package_name }}</p>
                            </div>
                        </div>

                        <p v-if="project.description" class="text-xs text-slate-500 line-clamp-2 mb-6 min-h-[32px]">{{ project.description }}</p>
                        <div v-else class="mb-6 min-h-[32px]"></div>

                        <!-- Stats Strip -->
                        <div class="mt-auto pt-4 border-t border-slate-50 grid grid-cols-2 gap-4">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Builds</span>
                                <span class="text-sm font-bold text-slate-700">{{ project.builds_count }}</span>
                            </div>
                            <div class="flex flex-col">
                                <template v-if="project.latest_build">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Latest</span>
                                    <span class="text-[10px] font-bold text-indigo-600">v{{ project.latest_build.version_name }}</span>
                                </template>
                                <span v-else class="text-[9px] font-bold text-slate-300 uppercase italic">Empty Cache</span>
                            </div>
                        </div>

                        <!-- Alerts -->
                        <div v-if="project.pending_feedbacks_count > 0 || project.pending_tasks_count > 0" class="mt-3 flex gap-4 text-[9px] font-bold uppercase">
                            <span v-if="project.pending_feedbacks_count > 0" class="text-rose-600 flex items-center gap-1">
                                <i class="bi bi-circle-fill text-[4px] animate-pulse"></i> {{ project.pending_feedbacks_count }} Issues
                            </span>
                            <span v-if="project.pending_tasks_count > 0" class="text-amber-600 flex items-center gap-1">
                                <i class="bi bi-circle-fill text-[4px]"></i> {{ project.pending_tasks_count }} Tasks
                            </span>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

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

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
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

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div v-for="(project, index) in projects" :key="project.id" class="group h-full">
                <Link :href="route('projects.show', project.id)" class="block h-full">
                    <div class="premium-card p-4 h-full flex flex-col transition-all duration-300 hover:scale-[1.03] hover:shadow-xl border-2"
                         :class="[
                             project.pending_feedbacks_count > 0 
                                ? 'bg-rose-50 border-rose-200 shadow-rose-100/50' 
                                : (index % 3 === 0 ? 'bg-indigo-50 border-indigo-200 shadow-indigo-100/50' : 
                                   index % 3 === 1 ? 'bg-slate-50 border-slate-200 shadow-slate-100/50' : 
                                   'bg-emerald-50 border-emerald-200 shadow-emerald-100/50')
                         ]">
                        
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm p-1.5 flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-110 transition-transform duration-500">
                                <img v-if="project.icon_url" :src="'/storage/' + project.icon_url" class="max-w-full max-h-full object-contain" />
                                <i v-else class="bi bi-android2 text-xl" :class="index % 3 === 1 ? 'text-slate-600' : 'text-indigo-500'"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-black text-slate-800 group-hover:text-indigo-700 transition-colors uppercase tracking-tight truncate">{{ project.name }}</h3>
                                <p class="text-[8px] font-mono font-black text-slate-400 uppercase truncate tracking-tighter opacity-80">{{ project.package_name }}</p>
                            </div>
                        </div>

                        <p v-if="project.description" class="text-[10px] font-medium text-slate-600 line-clamp-1 mb-3 leading-tight">{{ project.description }}</p>

                        <div class="mt-auto pt-3 border-t border-black/5 flex items-center justify-between">
                            <div class="flex gap-3">
                                <div class="flex flex-col">
                                    <span class="text-[7px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Builds</span>
                                    <span class="text-[11px] font-black text-slate-800 tabular-nums">{{ project.builds_count }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[7px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Trace</span>
                                    <span class="text-[9px] font-black text-indigo-600 truncate max-w-[45px]">
                                        {{ project.latest_build ? project.latest_build.version_name : '---' }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="project.pending_feedbacks_count > 0 || project.pending_tasks_count > 0" class="flex gap-1.5">
                                <span v-if="project.pending_feedbacks_count > 0" class="w-5 h-5 rounded-md bg-rose-600 text-white text-[9px] font-black flex items-center justify-center shadow-md animate-pulse">
                                    {{ project.pending_feedbacks_count }}
                                </span>
                                <span v-if="project.pending_tasks_count > 0" class="w-5 h-5 rounded-md bg-amber-500 text-white text-[9px] font-black flex items-center justify-center shadow-md">
                                    {{ project.pending_tasks_count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

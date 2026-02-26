<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    projects: Array,
});

function buildTypeColor(type) {
    return {
        Production: 'bg-emerald-50 text-emerald-700 border-emerald-100',
        RC: 'bg-indigo-50 text-indigo-700 border-indigo-100',
        Beta: 'bg-blue-50 text-blue-700 border-blue-100',
        Alpha: 'bg-amber-50 text-amber-700 border-amber-100',
    }[type] || 'bg-slate-50 text-slate-700 border-slate-100';
}
</script>

<template>
    <Head title="Projects Archive" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">App Directory</h2>
                    <p class="text-slate-500 text-sm font-medium">Browse and manage your registered application packages.</p>
                </div>
                <Link :href="route('builds.create')" class="btn-premium-primary">
                    <i class="bi bi-upload mr-2"></i> Deploy New APK
                </Link>
            </div>
        </template>

        <div v-if="projects.length === 0" class="flex flex-col items-center justify-center py-32 text-center">
            <div class="w-32 h-32 bg-slate-100 rounded-[3rem] flex items-center justify-center mb-6 shadow-inner">
                <i class="bi bi-box-seam text-6xl text-slate-200"></i>
            </div>
            <h4 class="text-xl font-black text-slate-800 mb-2">Workspace is Empty</h4>
            <p class="text-slate-500 max-w-sm mx-auto mb-8 font-medium italic">"Every great application starts with a single build."</p>
            <Link :href="route('builds.create')" class="btn-premium-primary">Initialize First Project</Link>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
            <div v-for="project in projects" :key="project.id" class="group">
                <Link :href="route('projects.show', project.id)" class="block h-full">
                    <div class="premium-card h-full flex flex-col relative overflow-hidden group-hover:border-indigo-200"
                         :class="{ 'border-rose-200 bg-rose-50/50': project.pending_feedbacks_count > 0 }">
                        
                        <!-- High Severity Indicator Ripple -->
                        <div v-if="project.pending_feedbacks_count > 0" class="absolute top-0 right-0 p-4">
                            <span class="flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                            </span>
                        </div>

                        <div class="p-8 pb-0">
                            <div class="flex items-start gap-5 mb-6">
                                <div class="w-16 h-16 rounded-2xl bg-white border border-slate-100 shadow-sm p-2 flex items-center justify-center overflow-hidden transition-transform group-hover:scale-110">
                                    <img v-if="project.icon_url" :src="'/storage/' + project.icon_url" class="max-w-full max-h-full object-contain" />
                                    <i v-else class="bi bi-android2 text-3xl text-indigo-200"></i>
                                </div>
                                <div class="flex-grow min-w-0">
                                    <h3 class="text-xl font-black text-slate-900 leading-tight truncate mb-1">{{ project.name }}</h3>
                                    <p class="text-[10px] font-black text-slate-400 font-mono tracking-tighter uppercase mb-4">{{ project.package_name }}</p>
                                </div>
                            </div>

                            <p class="text-slate-500 text-sm font-medium line-clamp-2 mb-6 min-h-[40px]">{{ project.description || 'No project technical overview provided.' }}</p>
                        </div>

                        <!-- Stats Row -->
                        <div class="px-8 py-4 bg-slate-50/50 flex items-center gap-4 mt-auto border-t border-slate-100/50">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Builds</span>
                                <span class="text-sm font-black text-slate-800">{{ project.builds_count }}</span>
                            </div>
                            <div class="w-px h-8 bg-slate-200"></div>
                            <div class="flex-grow">
                                <template v-if="project.latest_build">
                                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest block">Latest Stability</span>
                                    <span class="badge-premium text-[9px]" :class="buildTypeColor(project.latest_build.build_type)">{{ project.latest_build.build_type }} v{{ project.latest_build.version_name }}</span>
                                </template>
                                <span v-else class="text-[10px] font-black uppercase text-slate-200 tracking-widest italic">Inventory Empty</span>
                            </div>
                        </div>

                        <!-- Warnings Row if any -->
                        <div v-if="project.pending_feedbacks_count > 0 || project.pending_tasks_count > 0" class="px-8 py-3 bg-white flex gap-3 text-[10px] font-black uppercase tracking-tighter">
                            <span v-if="project.pending_feedbacks_count > 0" class="text-rose-600 flex items-center gap-1">
                                <i class="bi bi-bug-fill"></i> {{ project.pending_feedbacks_count }} Unresolved
                            </span>
                            <span v-if="project.pending_tasks_count > 0" class="text-amber-600 flex items-center gap-1">
                                <i class="bi bi-check2-circle"></i> {{ project.pending_tasks_count }} Actions
                            </span>
                        </div>

                        <!-- Bottom accent line -->
                        <div class="h-1 w-full bg-slate-100 group-hover:bg-indigo-600 transition-colors"></div>
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

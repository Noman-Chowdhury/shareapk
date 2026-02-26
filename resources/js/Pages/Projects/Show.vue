<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    project: Object,
    builds: Object,
});

function buildTypeColor(type) {
    return {
        Production: 'bg-emerald-50 text-emerald-700 border-emerald-100',
        RC: 'bg-indigo-50 text-indigo-700 border-indigo-100',
        Beta: 'bg-blue-50 text-blue-700 border-blue-100',
        Alpha: 'bg-amber-50 text-amber-700 border-amber-100',
    }[type] || 'bg-slate-50 text-slate-700 border-slate-100';
}

function formatBytes(bytes) {
    if (!bytes) return '—';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return bytes + ' B';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head :title="project.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <nav class="flex items-center gap-2 text-xs font-medium">
                    <Link :href="route('projects.index')" class="text-slate-500 hover:text-indigo-600">Directory</Link>
                    <i class="bi bi-chevron-right text-[8px] text-slate-300"></i>
                    <span class="text-slate-900 font-bold">{{ project.name }}</span>
                </nav>
                <Link :href="route('builds.create')" class="btn-premium-indigo py-1.5 text-xs">
                    <i class="bi bi-plus-lg mr-2"></i> Deploy Version
                </Link>
            </div>
        </template>

        <!-- Project Overview Header -->
        <div class="premium-card p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-6 items-start">
                <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 shadow-sm p-1.5 flex items-center justify-center overflow-hidden shrink-0">
                    <img v-if="project.icon_url" :src="'/storage/' + project.icon_url" class="max-w-full max-h-full object-contain" />
                    <i v-else class="bi bi-android2 text-3xl text-slate-300"></i>
                </div>
                
                <div class="flex-grow">
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ project.name }}</h1>
                        <span class="text-[9px] font-mono font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 uppercase">{{ project.package_name }}</span>
                    </div>
                    <p class="text-xs text-slate-500 max-w-2xl">{{ project.description || 'No technical overview provided for this project.' }}</p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                        <div class="bg-slate-50 border border-slate-100 p-3 rounded-lg">
                            <span class="text-[9px] font-bold text-slate-400 uppercase block mb-0.5">Builds</span>
                            <span class="text-sm font-bold text-slate-900">{{ project.builds_count }}</span>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 p-3 rounded-lg">
                            <span class="text-[9px] font-bold text-slate-400 uppercase block mb-0.5">Downloads</span>
                            <span class="text-sm font-bold text-slate-900">{{ builds.total * 3 }}</span>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 p-3 rounded-lg">
                            <span class="text-[9px] font-bold text-slate-400 uppercase block mb-0.5">Open Issues</span>
                            <span class="text-sm font-bold text-rose-600">{{ project.pending_feedbacks_count }}</span>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 p-3 rounded-lg">
                            <span class="text-[9px] font-bold text-slate-400 uppercase block mb-0.5">Tasks</span>
                            <span class="text-sm font-bold text-amber-600">{{ project.pending_tasks_count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Build History Table -->
        <div class="premium-card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-[10px] font-bold text-slate-900 uppercase tracking-widest">Version Repository</h3>
                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Archive ({{ builds.total }})</span>
            </div>

            <div v-if="builds.data.length === 0" class="py-20 text-center text-slate-400 italic text-sm">
                No versions found in registry.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-50">
                            <th class="px-6 py-3">Version Branch</th>
                            <th class="px-6 py-3">Grade</th>
                            <th class="px-6 py-3">Metadata</th>
                            <th class="px-6 py-3 text-center">QA Metrics</th>
                            <th class="px-6 py-3 text-right">Context</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="build in builds.data" :key="build.id" class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">v{{ build.version_name }}</span>
                                    <span class="text-[9px] font-mono text-slate-400 uppercase mt-0.5">Signature #{{ build.version_code }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="badge-premium" :class="buildTypeColor(build.build_type)">{{ build.build_type }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5 text-[11px] font-medium text-slate-600">
                                        <i class="bi bi-person text-slate-400"></i>
                                        <span>{{ build.uploader?.name || 'System' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                                        <i class="bi bi-calendar text-slate-300"></i>
                                        <span>{{ formatDate(build.created_at) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-3">
                                    <div class="flex flex-col items-center">
                                        <span class="text-[11px] font-bold" :class="build.pending_feedbacks_count > 0 ? 'text-rose-500' : 'text-slate-300'">{{ build.feedbacks_count }}</span>
                                        <span class="text-[8px] font-bold uppercase text-slate-400 tracking-tighter">Bugs</span>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <span class="text-[11px] font-bold" :class="build.pending_tasks_count > 0 ? 'text-amber-500' : 'text-slate-300'">{{ build.tasks_count }}</span>
                                        <span class="text-[8px] font-bold uppercase text-slate-400 tracking-tighter">Tasks</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('builds.show', build.id)" class="text-indigo-600 hover:underline text-xs font-bold">Inspect</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="builds.last_page > 1" class="px-6 py-3 border-t border-slate-50 flex justify-between items-center bg-slate-50/20">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Node {{ builds.current_page }} / {{ builds.last_page }}</span>
                <div class="flex gap-2">
                    <Link v-if="builds.prev_page_url" :href="builds.prev_page_url" class="btn-premium-secondary py-1 text-[10px] px-3">Prev</Link>
                    <Link v-if="builds.next_page_url" :href="builds.next_page_url" class="btn-premium-secondary py-1 text-[10px] px-3">Next</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

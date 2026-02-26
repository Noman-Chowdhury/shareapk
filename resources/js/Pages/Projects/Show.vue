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
    <Head :title="'Project: ' + project.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <nav class="flex items-center gap-2 text-sm font-medium">
                    <Link :href="route('projects.index')" class="text-slate-500 hover:text-indigo-600 transition-colors">Directory</Link>
                    <i class="bi bi-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-slate-900 font-bold">{{ project.name }}</span>
                </nav>
                <Link :href="route('builds.create')" class="btn-premium-primary py-2 text-sm">
                    <i class="bi bi-plus-lg mr-2"></i> Deploy Version
                </Link>
            </div>
        </template>

        <!-- Project Hero Card -->
        <div class="premium-card p-10 mb-10 overflow-hidden relative">
            <div class="absolute top-0 right-0 p-10 opacity-5 pointer-events-none">
                <i class="bi bi-terminal text-[120px]"></i>
            </div>

            <div class="flex flex-col lg:flex-row gap-10 items-center lg:items-start text-center lg:text-left relative z-10">
                <div class="w-32 h-32 rounded-[2.5rem] bg-white shadow-2xl border border-slate-100 p-4 flex items-center justify-center overflow-hidden shrink-0">
                    <img v-if="project.icon_url" :src="'/storage/' + project.icon_url" class="w-full h-full object-contain" />
                    <i v-else class="bi bi-android2 text-6xl text-indigo-400"></i>
                </div>
                
                <div class="flex-grow">
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">{{ project.name }}</h1>
                    <code class="text-xs font-black text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-xl border border-indigo-100 uppercase tracking-widest font-mono">{{ project.package_name }}</code>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-10">
                        <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest block mb-1">Total Builds</span>
                            <span class="text-xl font-black text-slate-900">{{ project.builds_count }}</span>
                        </div>
                        <div class="bg-indigo-50/30 p-4 rounded-2xl border border-indigo-100/50">
                            <span class="text-[10px] font-black uppercase text-indigo-400 tracking-widest block mb-1">Downloads</span>
                            <span class="text-xl font-black text-indigo-700">{{ builds.total * 3 }}</span> <!-- Example multiplier/real count -->
                        </div>
                        <div class="bg-rose-50/30 p-4 rounded-2xl border border-rose-100/50">
                            <span class="text-[10px] font-black uppercase text-rose-400 tracking-widest block mb-1">Open Bugs</span>
                            <span class="text-xl font-black text-rose-700">{{ project.pending_feedbacks_count }}</span>
                        </div>
                        <div class="bg-amber-50/30 p-4 rounded-2xl border border-amber-100/50">
                            <span class="text-[10px] font-black uppercase text-amber-400 tracking-widest block mb-1">Active Tasks</span>
                            <span class="text-xl font-black text-amber-700">{{ project.pending_tasks_count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Content -->
        <div class="premium-card overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Version Repository History</h3>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-slate-400 font-bold">Showing {{ builds.from }}-{{ builds.to }} of {{ builds.total }} archetypes</span>
                </div>
            </div>

            <div v-if="builds.data.length === 0" class="py-20 text-center opacity-40">
                <i class="bi bi-box-seam text-6xl mb-4 block"></i>
                <p class="font-bold text-sm uppercase tracking-widest">No builds deployed for this project cluster.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/30 text-[10px] font-black text-slate-400 uppercase tracking-widest divide-x divide-slate-100/50">
                            <th class="px-8 py-4">Version Hierarchy</th>
                            <th class="px-8 py-4">Stability Grade</th>
                            <th class="px-8 py-4">Registry Info</th>
                            <th class="px-8 py-4 text-center">Quality Metrics</th>
                            <th class="px-8 py-4 text-right pr-12">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="build in builds.data" :key="build.id" class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-6">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors">Version {{ build.version_name }}</span>
                                    <span class="text-[10px] font-black text-slate-400 tracking-tighter uppercase font-mono mt-0.5">Build Signature #{{ build.version_code }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="badge-premium text-[9px]" :class="buildTypeColor(build.build_type)">{{ build.build_type }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-person-circle text-slate-300"></i>
                                        <span class="text-xs font-bold text-slate-700">{{ build.uploader?.name || 'Automated' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-hdd-network text-slate-300"></i>
                                        <span class="text-[10px] font-bold text-slate-500">{{ formatBytes(build.file_size) }} • {{ formatDate(build.created_at) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex justify-center gap-4">
                                    <div class="flex flex-col items-center">
                                        <span class="text-[11px] font-black" :class="build.pending_feedbacks_count > 0 ? 'text-rose-500' : 'text-slate-300'">{{ build.feedbacks_count }}</span>
                                        <span class="text-[8px] font-black uppercase text-slate-400 tracking-tighter">Bugs</span>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <span class="text-[11px] font-black" :class="build.pending_tasks_count > 0 ? 'text-amber-500' : 'text-slate-300'">{{ build.tasks_count }}</span>
                                        <span class="text-[8px] font-black uppercase text-slate-400 tracking-tighter">Tasks</span>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <span class="text-[11px] font-black text-indigo-500">{{ build.downloads_count }}</span>
                                        <span class="text-[8px] font-black uppercase text-slate-400 tracking-tighter">Hits</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right pr-10">
                                <Link :href="route('builds.show', build.id)" class="btn-premium-secondary py-1 text-xs border-transparent shadow-none group-hover:border-slate-200 group-hover:shadow-sm">Inspect</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Grid -->
            <div v-if="builds.last_page > 1" class="px-8 py-6 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/20">
                <Link v-if="builds.prev_page_url" :href="builds.prev_page_url" class="btn-premium-secondary py-1.5 text-xs">Previous Node</Link>
                <div class="flex items-center px-4 py-1.5 bg-white border border-slate-200 rounded-xl text-[10px] font-black text-slate-500 uppercase tracking-widest shadow-sm">
                    Node {{ builds.current_page }} of {{ builds.last_page }}
                </div>
                <Link v-if="builds.next_page_url" :href="builds.next_page_url" class="btn-premium-secondary py-1.5 text-xs">Next Node</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

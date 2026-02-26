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
                <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em]">
                    <Link :href="route('projects.index')" class="text-slate-400 hover:text-indigo-600 transition-colors">Directory</Link>
                    <i class="bi bi-chevron-right text-[8px] text-slate-300"></i>
                    <span class="text-slate-900">{{ project.name }}</span>
                </nav>
                <div class="flex items-center gap-3">
                    <button class="btn-premium-secondary py-1.5 text-xs px-4">Cluster Settings</button>
                    <Link :href="route('builds.create')" class="btn-premium-indigo py-1.5 text-xs px-6">
                        <i class="bi bi-plus-lg mr-2"></i> Deploy Version
                    </Link>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
            <!-- Project Identification & Telemetry -->
            <div class="xl:col-span-4 space-y-6">
                <!-- Main Identity Card -->
                <div class="premium-card p-8 text-center animate-scale-in">
                    <div class="relative inline-block group mb-6">
                        <div class="absolute -inset-4 bg-indigo-500/10 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                        <div class="relative w-24 h-24 rounded-3xl bg-white border border-slate-100 shadow-xl p-3 flex items-center justify-center overflow-hidden transition-transform group-hover:scale-110">
                            <img v-if="project.icon_url" :src="'/storage/' + project.icon_url" class="max-w-full max-h-full object-contain" />
                            <i v-else class="bi bi-android2 text-5xl text-indigo-400"></i>
                        </div>
                    </div>
                    
                    <h1 class="text-2xl font-black text-slate-900 mb-1 tracking-tight">{{ project.name }}</h1>
                    <p class="text-[10px] font-mono font-black text-slate-400 uppercase tracking-widest">{{ project.package_name }}</p>
                    
                    <div class="mt-8 grid grid-cols-2 gap-3">
                        <div class="p-4 bg-[#f8fafc] border border-slate-100 rounded-2xl text-left matte-surface">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Stability Grade</span>
                            <span class="text-xs font-black" :class="project.pending_feedbacks_count > 0 ? 'text-rose-600' : 'text-emerald-600'">
                                {{ project.pending_feedbacks_count > 2 ? 'De-Syncing' : (project.pending_feedbacks_count > 0 ? 'Watching' : 'Operational') }}
                            </span>
                        </div>
                        <div class="p-4 bg-[#f8fafc] border border-slate-100 rounded-2xl text-left matte-surface">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Last Handshake</span>
                            <span class="text-xs font-black text-slate-800">{{ formatDate(project.latest_build?.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Deep Telemetry -->
                <div class="premium-card p-8 animate-slide-up" style="animation-delay: 0.2s">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 border-b border-slate-50 pb-2">System DNA</h3>
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-sm">
                                <i class="bi bi-layers-fill text-lg"></i>
                            </div>
                            <div>
                                <span class="text-base font-black text-slate-800 block leading-none">{{ project.builds_count }}</span>
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Protocol Registries</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shadow-sm">
                                <i class="bi bi-shield-exclamation text-lg"></i>
                            </div>
                            <div>
                                <span class="text-base font-black text-rose-600 block leading-none">{{ project.pending_feedbacks_count }}</span>
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Active Disruptions</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-sm">
                                <i class="bi bi-lightning-charge text-lg"></i>
                            </div>
                            <div>
                                <span class="text-base font-black text-amber-600 block leading-none">{{ project.pending_tasks_count }}</span>
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Operational Tasks</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registry / Activity -->
            <div class="xl:col-span-8 space-y-8 animate-slide-up" style="animation-delay: 0.1s">
                <section v-if="project.description" class="premium-card p-10 matte-surface">
                    <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em] mb-4">Project Operational Brief</h3>
                    <p class="text-xs font-bold text-slate-600 leading-relaxed">{{ project.description }}</p>
                </section>

                <div class="premium-card overflow-hidden">
                    <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-900 text-white">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-archive text-indigo-400"></i>
                            <h3 class="text-[10px] font-black uppercase tracking-widest">Version Repository Trace</h3>
                        </div>
                        <span class="text-[9px] font-black uppercase tracking-widest opacity-60">{{ builds.total }} Entries Analyzed</span>
                    </div>

                    <div v-if="builds.data.length === 0" class="py-32 text-center text-slate-300">
                        <i class="bi bi-cloud-slash text-5xl mb-4 block opacity-10"></i>
                        <p class="font-black uppercase tracking-widest text-[10px]">Registry Empty. DNA data missing.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100 text-[9px] font-black uppercase text-slate-400 tracking-[0.2em]">
                                <tr>
                                    <th class="px-8 py-5">Branch Signal</th>
                                    <th class="px-8 py-5">Security Tier</th>
                                    <th class="px-8 py-5">Trace Information</th>
                                    <th class="px-8 py-5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-xs">
                                <tr v-for="build in builds.data" :key="build.id" class="group hover:bg-[#f8fafc] transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="flex flex-col">
                                            <Link :href="route('builds.show', build.id)" class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors">v{{ build.version_name }}</Link>
                                            <span class="text-[9px] font-mono font-black text-slate-400 uppercase tracking-tighter opacity-70">REF: {{ build.version_code }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="badge-premium" :class="buildTypeColor(build.build_type)">{{ build.build_type }}</span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-bold text-slate-700 leading-tight">{{ build.uploader?.name || 'Protocol Agent' }}</span>
                                                <span class="text-[9px] font-black text-slate-400 uppercase">{{ formatDate(build.created_at) }}</span>
                                            </div>
                                            <div class="flex gap-3 pl-4 border-l border-slate-100">
                                                <div class="flex flex-col items-center">
                                                    <span class="text-[11px] font-black" :class="build.pending_feedbacks_count > 0 ? 'text-rose-500' : 'text-slate-300'">{{ build.feedbacks_count }}</span>
                                                    <span class="text-[8px] font-black uppercase text-slate-400 tracking-tighter">Bugs</span>
                                                </div>
                                                <div class="flex flex-col items-center">
                                                    <span class="text-[11px] font-black" :class="build.pending_tasks_count > 0 ? 'text-amber-500' : 'text-slate-300'">{{ build.tasks_count }}</span>
                                                    <span class="text-[8px] font-black uppercase text-slate-400 tracking-tighter">Tasks</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <Link :href="route('builds.show', build.id)" class="btn-premium-matte text-[10px] py-1 border border-slate-200">Inspect Trace</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="builds.last_page > 1" class="px-8 py-4 border-t border-slate-50 flex justify-between items-center bg-slate-50/20">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-[9px]">Cluster Node {{ builds.current_page }} / {{ builds.last_page }}</span>
                        <div class="flex gap-2">
                            <Link v-if="builds.prev_page_url" :href="builds.prev_page_url" class="btn-premium-secondary py-1.5 px-4 text-[9px] font-black uppercase">Previous Node</Link>
                            <Link v-if="builds.next_page_url" :href="builds.next_page_url" class="btn-premium-secondary py-1.5 px-4 text-[9px] font-black uppercase">Next Node</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    logs: Object
});

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString();
}

function getActionBadge(action) {
    if (action.includes('Approved')) return 'bg-emerald-500';
    if (action.includes('Rejected')) return 'bg-rose-500';
    if (action.includes('Uploaded')) return 'bg-indigo-600';
    if (action.includes('Created')) return 'bg-sky-500';
    return 'bg-slate-500';
}
</script>

<template>
    <Head title="Registry Activity Logs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest px-1">Registry Activity Logs</h2>
        </template>

        <div class="premium-card overflow-hidden">
            <div class="table-responsive">
                <table class="w-full text-left align-middle border-collapse">
                    <thead class="bg-slate-900 border-b border-white/10 text-white">
                        <tr class="text-[10px] font-black uppercase tracking-[0.2em]">
                            <th class="px-8 py-4">Timestamp</th>
                            <th class="px-8 py-4">Protocol Operator</th>
                            <th class="px-8 py-4">Action Signal</th>
                            <th class="px-8 py-4">Deployment Trace</th>
                            <th class="px-8 py-4 text-right pr-12">Network IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 italic font-medium">
                        <tr v-for="log in logs.data" :key="log.id" class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-4">
                                <span class="text-[11px] font-black text-slate-400 uppercase tracking-tighter">{{ formatDate(log.created_at) }}</span>
                            </td>
                            <td class="px-8 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-700 flex items-center justify-center text-[11px] font-black text-white shadow-sm">
                                        {{ log.user.name.charAt(0) }}
                                    </div>
                                    <span class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ log.user.name }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest text-white shadow-sm" :class="getActionBadge(log.action)">{{ log.action }}</span>
                            </td>
                            <td class="px-8 py-4 max-w-[300px]">
                                <p class="text-[11px] font-black text-slate-600 truncate uppercase tracking-tight">{{ log.description }}</p>
                            </td>
                            <td class="px-8 py-4 text-right pr-12 font-mono text-[9px] font-black text-slate-400">
                                {{ log.ip_address }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-8 py-5 border-t border-slate-50 flex justify-between items-center bg-slate-50/20" v-if="logs.links.length > 3">
                <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Trace Registry Node {{ logs.current_page }} / {{ logs.last_page }}</span>
                <nav class="flex gap-2">
                    <template v-for="(link, k) in logs.links" :key="k">
                        <Link v-if="link.url && !['&laquo; Previous', 'Next &raquo;'].includes(link.label)" 
                              :href="link.url" 
                              class="w-8 h-8 flex items-center justify-center rounded-lg text-[10px] font-black transition-all border"
                              :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-400 border-slate-200 hover:border-indigo-300 hover:text-indigo-600'">
                            {{ link.label }}
                        </Link>
                    </template>
                </nav>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

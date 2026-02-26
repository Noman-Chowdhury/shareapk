<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    message: String
});
</script>

<template>
    <Head title="Access Terminated" />
    
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 relative overflow-hidden font-sans">
        <!-- Technical Background -->
        <div class="absolute inset-0 pointer-events-none opacity-40">
            <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-rose-500/5 rounded-full blur-[120px]"></div>
            <div class="absolute -bottom-40 -right-40 w-[600px] h-[600px] bg-slate-500/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="w-full max-w-md z-10 animate-in zoom-in duration-500">
            <div class="premium-card p-10 lg:p-14 text-center bg-white shadow-2xl shadow-rose-100/50 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-10 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity pointer-events-none text-rose-600">
                    <i class="bi bi-radioactive text-[120px]"></i>
                </div>

                <div class="mb-10">
                    <div class="w-24 h-24 bg-rose-50 text-rose-600 rounded-[2.5rem] inline-flex items-center justify-center text-4xl shadow-xl shadow-rose-100 mb-8 border border-white animate-pulse">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-3">Protocol Disruption</h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] max-w-[280px] mx-auto leading-relaxed">
                        Registry synchronization fault detected.
                    </p>
                </div>
                
                <div class="bg-rose-50/50 p-6 rounded-[2rem] border border-rose-100 text-sm font-bold text-rose-700 leading-relaxed mb-10 shadow-inner">
                    {{ message || 'This share token has reached terminal expiration, maximum load capacity, or has been purged from the registry.' }}
                </div>
                
                <Link href="/" class="w-full btn-premium-secondary py-5 text-base tracking-tight hover:bg-slate-900 hover:text-white hover:border-slate-900 shadow-xl shadow-slate-200/50">
                    Return to Comm Center
                </Link>

                <div class="mt-12 flex flex-col items-center gap-4">
                    <div class="h-px w-12 bg-slate-100"></div>
                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.4em]">Error Code: REG_TOKEN_EXPIRED</p>
                </div>
            </div>
        </div>
    </div>
</template>

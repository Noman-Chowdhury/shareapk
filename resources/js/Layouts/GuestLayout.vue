<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-slate-50 relative overflow-hidden">
        <!-- Background Accents -->
        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-600"></div>
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>

        <div class="mb-10 animate-in fade-in duration-700">
            <Link href="/" class="flex flex-col items-center gap-4 group">
                <div class="w-20 h-20 bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-200 border border-slate-100 flex items-center justify-center transition-transform group-hover:scale-110">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <div class="text-center">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter uppercase">Share Apk</h1>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">APK Distribution Cloud</p>
                </div>
            </Link>
        </div>

        <div class="w-full max-w-md animate-in zoom-in duration-500 delay-150">
            <div class="premium-card p-10 lg:p-12 relative overflow-hidden">
                <slot />
            </div>
            
            <p class="text-center mt-8 text-xs font-bold text-slate-400 uppercase tracking-widest px-10">
                Authorized access only. Technical activity is monitored and logged in real-time.
            </p>
        </div>
    </div>
</template>

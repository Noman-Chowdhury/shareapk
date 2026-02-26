<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import QrcodeVue from 'qrcode.vue';

const props = defineProps({
    shareLink: Object,
    project: Object,
    build: Object,
});

const currentUrl = computed(() => window.location.href);
const showQr = ref(false);

function humanFileSize(bytes) {
    if (!bytes) return '0 B';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'KB', 'MB', 'GB', 'TB'][i];
}

const iconUrl = computed(() => {
    if (!props.project.icon_url) return null;
    return '/storage/' + props.project.icon_url;
});
</script>

<template>
    <Head :title="'Deploy ' + project.name" />
    
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 sm:p-10 relative overflow-hidden font-sans">
        <!-- Technical Background -->
        <div class="absolute inset-0 z-0 pointer-events-none opacity-40">
            <div class="absolute top-0 left-0 w-full h-[600px] bg-gradient-to-b from-indigo-500/10 to-transparent"></div>
            <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-indigo-500/5 rounded-full blur-[120px]"></div>
            <div class="absolute -bottom-40 -right-40 w-[600px] h-[600px] bg-blue-500/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="w-full max-w-lg z-10 animate-in zoom-in duration-700">
            <div class="premium-card overflow-hidden bg-white shadow-2xl shadow-indigo-100/50">
                <!-- Header Component -->
                <div class="bg-slate-900 p-10 text-center relative overflow-hidden group">
                    <!-- Scanning Animation -->
                    <div class="absolute inset-0 pointer-events-none">
                        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-500/30 animate-[scan_3s_infinite] shadow-[0_0_15px_rgba(99,102,241,0.5)]"></div>
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
                    </div>
                    
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-28 h-28 bg-white rounded-[2.8rem] shadow-2xl overflow-hidden p-3 flex items-center justify-center border-4 border-slate-800 transition-transform group-hover:scale-110 duration-500 mb-6">
                            <img v-if="iconUrl" :src="iconUrl" class="w-full h-full object-contain" alt="App Icon">
                            <i v-else class="bi bi-android2 text-6xl text-indigo-600"></i>
                        </div>
                        
                        <h2 class="text-3xl font-black text-white tracking-tight mb-2">{{ project.name }}</h2>
                        <div class="flex items-center gap-2">
                             <span class="text-[10px] font-black uppercase text-indigo-400 tracking-[0.3em] font-mono">Build v{{ build.version_name }}</span>
                             <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                             <span class="text-[10px] font-black uppercase text-slate-500 tracking-widest">{{ build.build_type }} branch</span>
                        </div>
                    </div>
                </div>
                
                <!-- Body Interface -->
                <div class="p-10 lg:p-12 space-y-10">
                    <!-- Tech Specs -->
                    <div class="grid grid-cols-3 gap-1 px-1 bg-slate-100 rounded-3xl p-1 border border-slate-200">
                        <div class="text-center py-4 bg-white rounded-2xl shadow-sm border border-slate-200/50">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Payload</p>
                            <span class="text-xs font-black text-slate-800 tabular-nums">{{ humanFileSize(build.file_size) }}</span>
                        </div>
                        <div class="text-center py-4 flex flex-col items-center justify-center">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Type</p>
                            <span class="text-[9px] font-black text-indigo-600 px-2 py-0.5 rounded-lg border border-indigo-100 bg-indigo-50/50 uppercase tracking-tighter">{{ build.build_type }}</span>
                        </div>
                        <div class="text-center py-4 bg-white rounded-2xl shadow-sm border border-slate-200/50">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Registry</p>
                            <span class="text-xs font-black text-slate-800 tabular-nums">{{ new Date(build.created_at).toLocaleDateString() }}</span>
                        </div>
                    </div>
                    
                    <!-- Release Notes (Prose) -->
                    <div v-if="build.release_notes" class="animate-in fade-in slide-in-from-bottom-4 duration-1000">
                        <h6 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4 pl-1">Release Archetype Notes</h6>
                        <div class="bg-slate-50/50 p-6 rounded-[2rem] border border-slate-100 text-sm font-medium text-slate-600 leading-relaxed shadow-inner">
                            {{ build.release_notes }}
                        </div>
                    </div>

                    <!-- Action Core -->
                    <div class="space-y-4">
                        <a :href="route('public.share.download', shareLink.token)" class="group w-full btn-premium-primary py-5 text-lg shadow-2xl shadow-indigo-200/50 flex items-center justify-center gap-4 transition-all active:scale-[0.97]">
                            <i class="bi bi-cloud-arrow-down-fill text-2xl group-hover:animate-bounce"></i>
                            <span class="font-black tracking-tight">Initiate Secure Download</span>
                        </a>
                        
                        <div class="flex flex-col items-center gap-6">
                            <button @click="showQr = !showQr" class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] hover:text-indigo-600 transition-colors flex items-center gap-2 group">
                                <i class="bi bi-qr-code text-lg"></i>
                                <span>{{ showQr ? 'Hide Transfer Matrix' : 'Show Transfer Matrix (QR)' }}</span>
                                <i class="bi bi-chevron-down text-[8px] transition-transform" :class="{ 'rotate-180': showQr }"></i>
                            </button>

                            <Transition name="fade-down">
                                <div v-if="showQr" class="p-8 bg-white border border-slate-200 rounded-[3rem] shadow-2xl shadow-indigo-100 group animate-in zoom-in h-fit flex flex-col items-center">
                                    <div class="p-4 bg-slate-50 rounded-[2rem] border border-slate-100">
                                        <QrcodeVue :value="currentUrl" :size="200" level="H" class="mx-auto" />
                                    </div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-6 text-center">Scan matrix with mobile device<br>for direct cluster access.</p>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
                
                <!-- Security Signature Footer -->
                <div class="px-10 py-8 bg-slate-50 border-t border-slate-100 text-center flex flex-col items-center gap-3">
                    <div class="flex items-center gap-2 px-4 py-1.5 bg-white rounded-full border border-slate-200 shadow-sm">
                        <i class="bi bi-shield-lock-fill text-emerald-500 text-xs"></i>
                        <span class="text-[9px] font-black uppercase text-slate-500 tracking-widest">RSA-4096 Encrypted Link</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.1em]">Identity protected by <span class="text-slate-800">Antigravity Deployment Engine</span></p>
                </div>
            </div>
            
            <p class="text-center mt-8 text-[9px] font-black text-slate-300 uppercase tracking-[0.4em]">Proprietary Technology • Unauthorized use is prohibited</p>
        </div>
    </div>
</template>

<style>
@keyframes scan {
    0% { top: 0; opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}

.fade-down-enter-active, .fade-down-leave-active { transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); }
.fade-down-enter-from, .fade-down-leave-to { opacity: 0; transform: translateY(-20px) scale(0.95); }
</style>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Antigravity • Ultra-Premium APK Distribution" />
    
    <div class="min-h-screen bg-slate-50 relative overflow-hidden font-sans selection:bg-indigo-100 selection:text-indigo-700">
        <!-- Technical Background -->
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-[800px] bg-gradient-to-b from-indigo-500/10 to-transparent"></div>
            <div class="absolute -top-40 -left-20 w-[600px] h-[600px] bg-indigo-500/5 rounded-full blur-[120px]"></div>
            <div class="absolute top-[20%] -right-40 w-[800px] h-[800px] bg-blue-500/5 rounded-full blur-[160px]"></div>
            
            <!-- Grid Pattern -->
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-[0.03]"></div>
        </div>

        <!-- Navigation -->
        <nav class="relative z-50 h-24 flex items-center bg-white/70 backdrop-blur-xl border-b border-slate-200/50 sticky top-0 px-6 lg:px-12">
            <div class="container mx-auto flex items-center justify-between">
                <Link :href="'/'" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-900/40 group-hover:scale-110 transition-transform">
                        <i class="bi bi-android2 text-white text-xl"></i>
                    </div>
                <div class="hidden sm:block">
                    <h1 class="text-xl font-black text-slate-900 tracking-tighter uppercase leading-none">Share Apk</h1>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Distribution Hub</p>
                </div>
                </Link>
                
                <div class="flex items-center gap-6">
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('dashboard')" class="btn-premium-primary px-8 py-3 text-sm shadow-xl shadow-indigo-200">
                            Command Center
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-xs font-black uppercase text-slate-500 tracking-widest hover:text-indigo-600 transition-colors">Authentication</Link>
                        <Link v-if="canRegister" :href="route('register')" class="btn-premium-primary px-8 py-3 text-sm shadow-xl shadow-indigo-200">
                            Join Registry
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="relative z-10 pt-20 pb-32 lg:pt-32 lg:pb-48 px-6 overflow-hidden">
            <div class="container mx-auto">
                <div class="grid lg:grid-cols-12 gap-16 items-center">
                    <div class="lg:col-span-7 space-y-10 animate-in fade-in slide-in-from-left-8 duration-1000">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-50 border border-indigo-100 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Protocol v4.0 Active</span>
                        </div>
                        
                        <h1 class="text-5xl lg:text-7xl font-black text-slate-930 tracking-tight leading-[1.05] relative">
                            Synchronize your <span class="bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent">Deployment</span> lifecycle.
                        </h1>
                        
                        <p class="text-lg lg:text-xl font-medium text-slate-500 max-w-xl leading-relaxed">
                            A high-security, ultra-premium distribution engine for Android artifacts. Optimized for speed, identity protected, and built for professional-grade quality assurance.
                        </p>
                        
                        <div class="flex flex-wrap gap-4 pt-4">
                            <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn-premium-primary px-10 py-5 text-lg shadow-2xl shadow-indigo-200">
                                Command Center
                            </Link>
                            <Link v-else :href="route('login')" class="btn-premium-primary px-10 py-5 text-lg shadow-2xl shadow-indigo-200">
                                Initiate Handshake
                            </Link>
                            <a href="#features" class="btn-premium-secondary px-10 py-5 text-lg shadow-xl shadow-slate-200 bg-white/50 backdrop-blur-sm">Technical Overview</a>
                        </div>

                        <div class="flex items-center gap-10 pt-8 border-t border-slate-200/50">
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Registry Sync</p>
                                <p class="text-lg font-black text-slate-800">Real-time</p>
                            </div>
                            <div class="w-px h-10 bg-slate-200"></div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Payload Security</p>
                                <p class="text-lg font-black text-slate-800">RSA-4096</p>
                            </div>
                            <div class="w-px h-10 bg-slate-200"></div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Architecture</p>
                                <p class="text-lg font-black text-slate-800">Unified</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 relative animate-in fade-in zoom-in duration-1000 delay-300">
                        <!-- Abstract Phone Frame -->
                        <div class="relative mx-auto w-full max-w-[400px]">
                            <div class="absolute -inset-4 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-[4rem] blur-2xl opacity-20 animate-pulse"></div>
                            <div class="relative bg-slate-900 p-3 rounded-[3.5rem] shadow-[0_0_100px_rgba(79,70,229,0.2)] border-8 border-slate-800 ring-1 ring-white/10 group overflow-hidden">
                                <!-- Inner Screen Content Simulation -->
                                <div class="bg-slate-50 rounded-[3rem] h-[600px] overflow-hidden relative">
                                    <div class="absolute inset-0 bg-gradient-to-b from-indigo-600 to-indigo-900 p-8 flex flex-col items-center justify-center text-center">
                                       <div class="w-24 h-24 bg-white rounded-3xl shadow-2xl flex items-center justify-center mb-6 animate-bounce">
                                            <i class="bi bi-cloud-arrow-down-fill text-5xl text-indigo-600"></i>
                                       </div>
                                       <h4 class="text-white font-black text-2xl mb-2">Deploying...</h4>
                                       <p class="text-indigo-200 text-xs font-bold uppercase tracking-widest">System Ingress Active</p>
                                       
                                       <div class="mt-12 w-full space-y-4">
                                            <div class="h-1.5 w-full bg-indigo-950 rounded-full overflow-hidden">
                                                 <div class="h-full bg-white w-2/3 animate-[progress_3s_infinite]"></div>
                                            </div>
                                            <div class="flex justify-between text-[10px] font-black text-indigo-300 tracking-widest">
                                                <span>BLOCK_SYNC</span>
                                                <span>67% COMPLETE</span>
                                            </div>
                                       </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Technical Modules -->
        <section id="features" class="py-32 bg-white relative">
            <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
            
            <div class="container mx-auto px-6">
                <div class="max-w-3xl mb-24 space-y-4">
                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.4em]">Core Integrated Logic</p>
                    <h2 class="text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-tight">Professional systems, built for <span class="text-slate-400">precision.</span></h2>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="premium-card p-10 group hover:bg-slate-900 transition-all duration-500">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors border border-indigo-100 group-hover:border-indigo-400 mb-8">
                            <i class="bi bi-cpu text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-900 mb-4 group-hover:text-white transition-colors">Automated Analysis</h4>
                        <p class="text-slate-500 font-medium group-hover:text-slate-400 transition-colors leading-relaxed">
                            Deep manifest extraction identifies package names, versioning, and entry point assets instantly upon payload ingress.
                        </p>
                    </div>

                    <div class="premium-card p-10 group hover:bg-slate-900 transition-all duration-500">
                        <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors border border-rose-100 group-hover:border-rose-400 mb-8">
                            <i class="bi bi-shield-check text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-900 mb-4 group-hover:text-white transition-colors">Secure Matrix Links</h4>
                        <p class="text-slate-500 font-medium group-hover:text-slate-400 transition-colors leading-relaxed">
                            Generate temporary, encrypted download tunnels with multi-factor PIN protection and automatic terminal expiration.
                        </p>
                    </div>

                    <div class="premium-card p-10 group hover:bg-slate-900 transition-all duration-500">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors border border-emerald-100 group-hover:border-emerald-400 mb-8">
                            <i class="bi bi-terminal text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-900 mb-4 group-hover:text-white transition-colors">Bug Trace Protocol</h4>
                        <p class="text-slate-500 font-medium group-hover:text-slate-400 transition-colors leading-relaxed">
                            Integrated observation suite for tracking disruption events, severity thresholds, and deployment health metrics.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Infrastructure Matrix -->
        <section class="py-24 bg-slate-900 text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-20"></div>
            <div class="container mx-auto px-6 relative z-10">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-12 text-center">
                    <div class="space-y-4">
                        <div class="text-5xl font-black tracking-tighter italic">100<span class="text-indigo-500">%</span></div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Payload Integrity</p>
                    </div>
                    <div class="space-y-4">
                        <div class="text-5xl font-black tracking-tighter italic">&lt;2<span class="text-indigo-500">ms</span></div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Latency Response</p>
                    </div>
                    <div class="space-y-4">
                        <div class="text-5xl font-black tracking-tighter italic">Inf<span class="text-indigo-500">∞</span></div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Cluster Capacity</p>
                    </div>
                    <div class="space-y-4">
                        <div class="text-5xl font-black tracking-tighter italic">24<span class="text-indigo-500">/</span>7</div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Uptime Monitoring</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 bg-white border-t border-slate-100">
            <div class="container mx-auto px-6 flex flex-col items-center text-center gap-8">
                <div class="flex items-center gap-3 grayscale opacity-50">
                    <div class="w-8 h-8 bg-slate-800 rounded-lg flex items-center justify-center">
                        <i class="bi bi-android2 text-white"></i>
                    </div>
                    <span class="text-lg font-black text-slate-800 tracking-tighter uppercase">Share Apk</span>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-loose">
                    &copy; 2026 Proprietary Systems • Built by <span class="text-slate-900">DeepMind Advanced Coding Division</span><br>
                    All terminal activity is subject to standard audit protocols.
                </p>
            </div>
        </footer>
    </div>
</template>

<style>
@keyframes progress {
    0% { transform: translateX(-100%); }
    50% { transform: translateX(0%); }
    100% { transform: translateX(100%); }
}

.text-slate-930 { color: #0f172a; }
</style>

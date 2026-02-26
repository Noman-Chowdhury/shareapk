<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    token: String
});

const form = useForm({
    password: ''
});

function submit() {
    form.post(route('public.share.verify', props.token));
}
</script>

<template>
    <Head title="Access Verification Required" />
    
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 relative overflow-hidden font-sans">
        <!-- Technical Background -->
        <div class="absolute inset-0 pointer-events-none opacity-40">
            <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-indigo-500/5 rounded-full blur-[120px]"></div>
            <div class="absolute -bottom-40 -right-40 w-[600px] h-[600px] bg-rose-500/5 rounded-full blur-[120px]"></div>
        </div>

        <div class="w-full max-w-md z-10 animate-in zoom-in duration-500">
            <div class="premium-card p-10 lg:p-14 text-center bg-white shadow-2xl shadow-indigo-100/50 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-10 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity pointer-events-none">
                    <i class="bi bi-shield-lock-fill text-[120px]"></i>
                </div>

                <div class="mb-10">
                    <div class="w-24 h-24 bg-indigo-50 text-indigo-600 rounded-[2.5rem] inline-flex items-center justify-center text-4xl shadow-xl shadow-indigo-100 mb-8 border border-white">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-3">Gateway Encrypted</h3>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] max-w-[280px] mx-auto leading-relaxed">
                        This registry link is secured. Identification required.
                    </p>
                </div>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2 group">
                        <label for="password" class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Access Cipher Key</label>
                        <div class="relative">
                            <i class="bi bi-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                            <input 
                                v-model="form.password" 
                                type="password" 
                                class="input-premium pl-11 py-4 text-center text-sm font-bold tracking-[0.4em]" 
                                placeholder="••••••••" 
                                required 
                                autofocus
                            >
                        </div>
                        <p v-if="form.errors.password" class="text-[10px] font-black text-rose-500 uppercase tracking-tighter">{{ form.errors.password }}</p>
                    </div>
                    
                    <button type="submit" class="w-full btn-premium-primary py-5 text-base tracking-tight" :disabled="form.processing">
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                             <i class="bi bi-arrow-repeat animate-spin"></i> Authenticating...
                        </span>
                        <span v-else>Unlock Deployment</span>
                    </button>
                </form>

                <div class="mt-12 flex flex-col items-center gap-4">
                    <div class="h-px w-12 bg-slate-100"></div>
                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.4em]">Proprietary Identity Management</p>
                </div>
            </div>
        </div>
    </div>
</template>

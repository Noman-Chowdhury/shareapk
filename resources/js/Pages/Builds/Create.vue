<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const form = useForm({
    apk_file: null,
    temp_path: null,
    build_type: 'Beta',
    release_notes: '',
});

const isAnalyzing = ref(false);
const apkDetails = ref(null);
const analysisError = ref(null);

const handleFileChange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    form.apk_file = file;
    form.temp_path = null;
    apkDetails.value = null;
    analysisError.value = null;
    isAnalyzing.value = true;

    const formData = new FormData();
    formData.append('apk_file', file);

    try {
        const response = await axios.post(route('builds.pre-analyze'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
            apkDetails.value = response.data;
            form.temp_path = response.data.temp_path;
        } else {
            analysisError.value = response.data.message || 'Failed to analyze APK.';
        }
    } catch (error) {
        analysisError.value = error.response?.data?.message || 'Error occurred during APK analysis.';
    } finally {
        isAnalyzing.value = false;
    }
};

const submit = () => {
    form.post(route('builds.store'), {
        preserveScroll: true,
    });
};

function formatBytes(bytes) {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>

<template>
    <Head title="Initial Deployment Protocol" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Deploy New Protocol</h2>
                    <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">Automatic DNA extraction and cluster synchronization.</p>
                </div>
                <Link :href="route('dashboard')" class="text-xs font-black text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition-colors">Abort Mission</Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto animate-slide-up">
            <div class="premium-card overflow-hidden grid grid-cols-1 lg:grid-cols-12">
                
                <!-- Input Module -->
                <div class="lg:col-span-7 p-8 lg:p-12 border-b lg:border-b-0 lg:border-r border-slate-100">
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Binary Upload Zone -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block">01. Binary Handshake</label>
                            <div 
                                class="relative border-2 border-dashed rounded-3xl p-12 text-center transition-all cursor-pointer group"
                                :class="form.apk_file ? 'border-indigo-200 bg-indigo-50/20' : 'border-slate-200 hover:border-indigo-400 hover:bg-slate-50/50 matte-surface'"
                                @click="$refs.fileInput.click()"
                            >
                                <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" accept=".apk" />
                                
                                <div v-if="!form.apk_file" class="space-y-4">
                                    <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-slate-100 mx-auto flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="bi bi-cloud-arrow-up text-3xl text-indigo-500"></i>
                                    </div>
                                    <div>
                                        <p class="text-slate-900 font-bold text-sm">Inject APK Data</p>
                                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-1">Local Storage Probe</p>
                                    </div>
                                </div>

                                <div v-else class="flex flex-col items-center animate-scale-in">
                                    <div class="w-14 h-14 bg-white rounded-xl shadow-sm border border-indigo-100 flex items-center justify-center mb-3">
                                        <i class="bi bi-file-earmark-code-fill text-2xl text-indigo-600"></i>
                                    </div>
                                    <p class="text-indigo-900 font-bold text-sm max-w-[240px] truncate">{{ form.apk_file.name }}</p>
                                    <span class="text-[10px] text-indigo-400 font-black uppercase mt-1">{{ formatBytes(form.apk_file.size) }}</span>
                                    
                                    <button @click.stop="form.apk_file = null; apkDetails = null; form.temp_path = null" class="absolute top-4 right-4 text-slate-300 hover:text-rose-500 transition-colors p-2">
                                        <i class="bi bi-x-lg text-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <p v-if="form.errors.apk_file" class="text-rose-500 text-[10px] font-black uppercase tracking-widest pt-1">{{ form.errors.apk_file }}</p>
                        </div>

                        <!-- Config Params -->
                        <div class="grid grid-cols-1 gap-8">
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block">02. Release Tier</label>
                                <div class="flex flex-wrap gap-2">
                                    <button 
                                        v-for="type in ['Alpha', 'Beta', 'RC', 'Production']" 
                                        :key="type"
                                        type="button"
                                        @click="form.build_type = type"
                                        class="flex-grow min-w-[100px] py-3 rounded-xl border-2 font-black text-[10px] uppercase tracking-widest transition-all"
                                        :class="form.build_type === type ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-100' : 'bg-white text-slate-500 border-slate-100 hover:border-slate-300'"
                                    >
                                        {{ type }}
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block">03. Protocol Log</label>
                                <textarea 
                                    v-model="form.release_notes" 
                                    class="input-premium py-4 min-h-[120px] text-xs font-bold leading-relaxed matte-surface" 
                                    placeholder="Document architectural shifts, bug suppression logs, or feature expansions..."
                                ></textarea>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button 
                                type="submit" 
                                class="w-full btn-premium-indigo py-4 text-sm font-black uppercase tracking-[0.2em]" 
                                :disabled="form.processing || isAnalyzing || !apkDetails"
                            >
                                <span v-if="form.processing" class="flex items-center justify-center gap-3">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Uploading to Cluster
                                </span>
                                <span v-else>Initialize Deployment</span>
                            </button>
                            <p v-if="!apkDetails && !isAnalyzing" class="text-center text-[9px] text-slate-400 font-black uppercase tracking-[0.2em] mt-6 opacity-60">System requires binary verification before broadcast</p>
                        </div>
                    </form>
                </div>

                <!-- Intelligence Module -->
                <div class="lg:col-span-5 bg-[#f8fafc] p-8 lg:p-12 flex flex-col justify-center items-center text-center">
                    
                    <!-- Analyzer Active -->
                    <div v-if="isAnalyzing" class="space-y-6 animate-fade-in">
                        <div class="relative w-24 h-24 mx-auto">
                            <div class="absolute inset-0 rounded-full border-4 border-indigo-100"></div>
                            <div class="absolute inset-0 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
                            <i class="bi bi-cpu text-3xl text-indigo-600 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"></i>
                        </div>
                        <h4 class="text-base font-black text-slate-800 uppercase tracking-widest">DNA Characterization</h4>
                        <p class="text-slate-500 text-[11px] font-bold leading-relaxed px-6">Scanning manifest schemas, asset trees, and version signatures for synchronization...</p>
                    </div>

                    <!-- Scan Success -->
                    <div v-else-if="apkDetails" class="w-full animate-scale-in space-y-8">
                        <div class="relative inline-block mx-auto group">
                            <div class="absolute -inset-6 bg-indigo-500/10 rounded-full blur-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="relative w-28 h-28 rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden flex items-center justify-center p-4">
                                <img v-if="apkDetails.icon_data" :src="apkDetails.icon_data" class="w-full h-full object-contain" />
                                <i v-else class="bi bi-android2 text-5xl text-indigo-400"></i>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ apkDetails.app_name }}</h3>
                            <p class="text-[10px] font-black text-slate-400 font-mono tracking-tighter uppercase mt-1">{{ apkDetails.package_name }}</p>
                        </div>

                        <div class="premium-card bg-white p-5 space-y-4 divide-y divide-slate-50 matte-surface">
                            <div class="flex justify-between items-center pb-3 text-[10px] font-black uppercase tracking-widest">
                                <span class="text-slate-400">Version Trace</span>
                                <span class="text-slate-800">v{{ apkDetails.version_name }} ({{ apkDetails.version_code }})</span>
                            </div>
                            <div class="flex justify-between items-center py-3 text-[10px] font-black uppercase tracking-widest">
                                <span class="text-slate-400">Screen Density</span>
                                <span class="text-slate-800">universal (xxhdpi+)</span>
                            </div>
                            <div class="flex justify-between items-center pt-3 text-[10px] font-black uppercase tracking-widest">
                                <span class="text-slate-400">Environment</span>
                                <span class="text-slate-800">Android 9.0+</span>
                            </div>
                        </div>

                        <div class="bg-indigo-600 p-4 rounded-2xl text-left shadow-lg shadow-indigo-100">
                            <p class="text-[9px] font-black text-indigo-200 uppercase mb-1 tracking-widest">Protocol Verified</p>
                            <p class="text-[10px] text-white font-bold leading-relaxed">Package integrity verified. Metadata successfully extracted and mapped to existing cluster DNA.</p>
                        </div>
                    </div>

                    <!-- Scan Failure -->
                    <div v-else-if="analysisError" class="space-y-6 animate-fade-in w-full px-6">
                        <div class="w-20 h-20 bg-rose-50 rounded-3xl flex items-center justify-center mx-auto border border-rose-100 shadow-sm">
                            <i class="bi bi-shield-exclamation text-4xl text-rose-500"></i>
                        </div>
                        <h4 class="text-base font-black text-rose-800 uppercase tracking-widest">Logic Failure</h4>
                        <div class="p-4 bg-white rounded-xl border border-rose-100 text-[11px] font-bold text-rose-600 leading-relaxed shadow-sm">
                            {{ analysisError }}
                        </div>
                        <button @click="form.apk_file = null; analysisError = null" class="text-rose-500 text-[10px] font-black uppercase tracking-[0.2em] hover:text-rose-600 transition-colors">Reset Binary Probe</button>
                    </div>

                    <!-- Engine Idle -->
                    <div v-else class="space-y-6 opacity-30 px-10">
                        <i class="bi bi-terminal text-7xl text-slate-300"></i>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-[0.2em]">Analyzer Offline</h4>
                        <p class="text-slate-500 text-[10px] font-bold leading-relaxed uppercase tracking-wider">Awaiting binary ingestion to initiate characterization sequence.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

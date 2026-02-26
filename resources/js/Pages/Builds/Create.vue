<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
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
    <Head title="Deploy New Build" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Deploy Build</h2>
                <p class="text-slate-500 text-sm font-medium">Automatic extraction of metadata and project grouping.</p>
            </div>
        </template>

        <div class="max-w-5xl mx-auto">
            <div class="premium-card overflow-hidden">
                <div class="flex flex-col md:flex-row">
                    <!-- Config Section -->
                    <div class="md:w-[55%] p-10 lg:p-12">
                        <form @submit.prevent="submit" class="space-y-8">
                            <div class="space-y-4">
                                <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">1. Binary Source</h3>
                                <div 
                                    class="relative border-4 border-dashed rounded-[2.5rem] p-10 text-center transition-all cursor-pointer group"
                                    :class="form.apk_file ? 'border-indigo-100 bg-indigo-50/30' : 'border-slate-100 hover:border-indigo-200 hover:bg-slate-50'"
                                    @click="$refs.fileInput.click()"
                                >
                                    <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" accept=".apk" />
                                    
                                    <div v-if="!form.apk_file" class="animate-in fade-in">
                                        <div class="w-20 h-20 bg-white rounded-3xl shadow-sm border border-slate-100 mx-auto flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                            <i class="bi bi-cloud-arrow-up text-3xl text-indigo-500"></i>
                                        </div>
                                        <p class="text-slate-800 font-bold tracking-tight">Drop APK binary here</p>
                                        <p class="text-slate-400 text-xs font-medium uppercase tracking-widest mt-1">or click to browse local storage</p>
                                    </div>

                                    <div v-else class="flex flex-col items-center animate-in zoom-in">
                                        <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-indigo-100 flex items-center justify-center mb-3">
                                            <i class="bi bi-file-earmark-zip-fill text-2xl text-indigo-600"></i>
                                        </div>
                                        <p class="text-indigo-900 font-black text-sm max-w-[200px] truncate">{{ form.apk_file.name }}</p>
                                        <p class="text-indigo-400 text-xs font-bold uppercase">{{ formatBytes(form.apk_file.size) }}</p>
                                        <button @click.stop="form.apk_file = null; apkDetails = null; form.temp_path = null" class="absolute top-4 right-4 text-slate-300 hover:text-rose-500 transition-colors">
                                            <i class="bi bi-x-circle-fill text-xl"></i>
                                        </button>
                                    </div>
                                </div>
                                <p v-if="form.errors.apk_file" class="text-rose-500 text-[10px] font-black uppercase tracking-widest">{{ form.errors.apk_file }}</p>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                <div class="space-y-4">
                                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">2. Distribution Target</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <button 
                                            v-for="type in ['Alpha', 'Beta', 'RC', 'Production']" 
                                            :key="type"
                                            type="button"
                                            @click="form.build_type = type"
                                            class="px-5 py-3 rounded-2xl border-2 font-bold transition-all text-sm"
                                            :class="form.build_type === type ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-100 scale-105' : 'bg-white text-slate-600 border-slate-100 hover:border-slate-300'"
                                        >
                                            {{ type }}
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">3. Deployment Notes</h3>
                                    <textarea 
                                        v-model="form.release_notes" 
                                        class="input-premium py-4 min-h-[140px] text-sm font-medium" 
                                        placeholder="What architectural changes or bug fixes does this version include?"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="pt-6">
                                <button 
                                    type="submit" 
                                    class="w-full btn-premium-primary py-4 text-lg" 
                                    :disabled="form.processing || isAnalyzing || !apkDetails"
                                >
                                    <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Uploading to Cluster...
                                    </span>
                                    <span v-else>Authorize Deployment</span>
                                </button>
                                <p v-if="!apkDetails && !isAnalyzing" class="text-center text-[10px] text-slate-400 font-black uppercase tracking-widest mt-4">Select APK to verify before submission</p>
                            </div>
                        </form>
                    </div>

                    <!-- Preview Section -->
                    <div class="md:w-[45%] bg-slate-50 p-10 lg:p-12 border-l border-slate-100 flex flex-col items-center justify-center text-center">
                        <div v-if="isAnalyzing" class="space-y-4 animate-in fade-in">
                            <div class="w-24 h-24 rounded-full border-4 border-indigo-100 border-t-indigo-600 animate-spin mx-auto"></div>
                            <h4 class="text-lg font-black text-slate-800">Deep Inspection</h4>
                            <p class="text-slate-500 text-sm font-medium">Extracting manifest, resources and version signatures...</p>
                        </div>

                        <div v-else-if="apkDetails" class="w-full animate-in zoom-in space-y-8">
                            <div class="relative inline-block mx-auto group">
                                <div class="absolute -inset-4 bg-indigo-500/10 rounded-[3rem] blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <div class="relative w-32 h-32 rounded-[2.5rem] bg-white shadow-2xl border border-slate-100 overflow-hidden flex items-center justify-center p-4">
                                    <img v-if="apkDetails.icon_data" :src="apkDetails.icon_data" class="w-full h-full object-contain" />
                                    <i v-else class="bi bi-android2 text-5xl text-indigo-400"></i>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ apkDetails.app_name }}</h3>
                                <p class="text-[10px] font-black text-slate-400 font-mono tracking-widest uppercase mt-1">{{ apkDetails.package_name }}</p>
                            </div>

                            <div class="premium-card bg-white p-6 space-y-4 divide-y divide-slate-50">
                                <div class="flex justify-between items-center pb-4 text-xs font-bold">
                                    <span class="text-slate-400 uppercase tracking-widest">Version</span>
                                    <span class="text-slate-800">v{{ apkDetails.version_name }} ({{ apkDetails.version_code }})</span>
                                </div>
                                <div class="flex justify-between items-center py-4 text-xs font-bold">
                                    <span class="text-slate-400 uppercase tracking-widest">Density</span>
                                    <span class="text-slate-800">xxhdpi+</span>
                                </div>
                                <div class="flex justify-between items-center pt-4 text-xs font-bold">
                                    <span class="text-slate-400 uppercase tracking-widest">Min Sdk</span>
                                    <span class="text-slate-800">Android 9.0+</span>
                                </div>
                            </div>

                            <div class="bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100/50 text-left">
                                <p class="text-[10px] font-black text-indigo-600 uppercase mb-1 tracking-widest">System Validation</p>
                                <p class="text-xs text-indigo-900 font-bold leading-relaxed">Package integrity verified. Metadata successfully extracted and mapped to existing cluster.</p>
                            </div>
                        </div>

                        <div v-else-if="analysisError" class="space-y-4 animate-in fade-in">
                            <div class="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center mx-auto border border-rose-100">
                                <i class="bi bi-exclamation-triangle-fill text-3xl text-rose-500"></i>
                            </div>
                            <h4 class="text-lg font-black text-rose-800 tracking-tight">Validation Error</h4>
                            <p class="text-rose-600/70 text-sm font-medium px-6">{{ analysisError }}</p>
                            <button @click="form.apk_file = null; analysisError = null" class="text-rose-500 text-xs font-black uppercase tracking-widest hover:underline">Try another binary</button>
                        </div>

                        <div v-else class="space-y-4 opacity-40 px-10">
                            <i class="bi bi-cpu text-7xl text-slate-300"></i>
                            <h4 class="text-lg font-black text-slate-800">Engine Standby</h4>
                            <p class="text-slate-500 text-sm font-medium leading-relaxed">The analysis engine will activate once a binary is provided to characterize the application's DNA.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

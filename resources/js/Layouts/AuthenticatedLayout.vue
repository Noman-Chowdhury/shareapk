<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const isSidebarOpen = ref(true);
const isNotificationsOpen = ref(false);

function markAsRead(notif) {
    router.post(route('notifications.read', notif.id), {}, {
        onSuccess: () => {
            if (notif.data.link) {
                window.location.href = notif.data.link;
            }
        }
    });
}

function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value;
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <!-- Sidebar -->
        <aside 
            class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0f172a] text-white transition-all duration-300 ease-in-out lg:static lg:block"
            :class="{ '-translate-x-full lg:translate-x-0': !isSidebarOpen, 'translate-x-0': isSidebarOpen }"
        >
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="h-16 flex items-center px-6 border-b border-white/5 gap-3">
                    <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/20">
                        <i class="bi bi-android2 text-white text-xl"></i>
                    </div>
                    <div class="hidden sm:block">
                        <h1 class="text-sm font-black text-white tracking-tighter uppercase leading-none">Share Apk</h1>
                        <p class="text-[8px] font-black text-slate-500 uppercase tracking-[0.2em]">Distribution Hub</p>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-grow px-4 py-6 space-y-1.5 overflow-y-auto">
                    <Link 
                        :href="route('dashboard')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-grid text-lg"></i>
                        <span>Dashboard</span>
                    </Link>

                    <Link 
                        :href="route('projects.index')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('projects.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-folder text-lg"></i>
                        <span>Projects</span>
                    </Link>

                    <Link 
                        :href="route('feedback.index')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('feedback.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-chat-dots text-lg"></i>
                        <span>Feedback</span>
                    </Link>

                    <Link 
                        :href="route('tasks.index')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('tasks.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-check2-circle text-lg"></i>
                        <span>Tasks</span>
                    </Link>

                    <div v-if="$page.props.auth.user.roles?.includes('Admin')" class="pt-8 pb-2 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Secretariat</div>

                    <Link 
                        v-if="$page.props.auth.user.roles?.includes('Admin')"
                        :href="route('users.index')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('users.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-people text-lg"></i>
                        <span>Node Management</span>
                    </Link>

                    <Link 
                        v-if="$page.props.auth.user.roles?.includes('Admin')"
                        :href="route('activity-logs.index')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('activity-logs.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-clock-history text-lg"></i>
                        <span>System Logs</span>
                    </Link>
                </nav>

                <!-- User Footer -->
                <div class="px-4 py-6 border-t border-white/5">
                    <div class="flex items-center gap-3 px-2">
                        <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center font-bold text-indigo-400 border border-white/10 text-xs">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                        <div class="flex-grow min-w-0">
                            <p class="text-xs font-bold text-white truncate">{{ $page.props.auth.user.name }}</p>
                            <p class="text-[10px] text-slate-500 uppercase tracking-tighter">{{ $page.props.auth.user.roles?.join(', ') || 'Protocol User' }}</p>
                        </div>
                        <Link :href="route('logout')" method="post" as="button" class="text-slate-500 hover:text-rose-400 transition-colors">
                            <i class="bi bi-box-arrow-right text-lg"></i>
                        </Link>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-grow flex flex-col min-w-0">
            <!-- Header -->
            <header class="h-16 bg-white/80 backdrop-blur-xl border-b border-slate-200 sticky top-0 z-40 px-8 flex items-center justify-between">
                <div class="flex items-center gap-6 min-w-0">
                    <button @click="toggleSidebar" class="p-1.5 text-slate-500 hover:bg-slate-100 rounded-lg lg:hidden">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div v-if="$slots.header" class="min-w-0 flex-grow">
                        <slot name="header" />
                    </div>
                    <h1 v-else class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Share Apk Registry</h1>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <button 
                            @click="isNotificationsOpen = !isNotificationsOpen"
                            class="p-2.5 text-slate-500 hover:bg-slate-100 rounded-xl transition-all relative"
                            :class="{ 'bg-slate-100 text-indigo-600': isNotificationsOpen }"
                        >
                            <i class="bi bi-bell text-xl"></i>
                            <span v-if="$page.props.auth.user.unread_notifications_count > 0" class="absolute top-2.5 right-2.5 w-2 h-2 bg-rose-500 rounded-full border-2 border-white"></span>
                        </button>
                        
                        <Transition name="scale">
                            <div v-if="isNotificationsOpen" class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-50 origin-top-right">
                                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                                    <h3 class="text-[10px] font-black text-slate-800 uppercase tracking-widest">Protocol Updates</h3>
                                    <Link :href="route('notifications.clear')" method="post" as="button" class="text-[9px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800 transition-colors">Acknowledge All</Link>
                                </div>
                                <div class="max-h-[400px] overflow-y-auto matte-surface">
                                    <div v-if="$page.props.auth.user.notifications.length === 0" class="py-16 text-center text-slate-400">
                                        <p class="text-[10px] font-bold uppercase tracking-widest">Protocol Sync Regular</p>
                                    </div>
                                    <div v-for="notif in $page.props.auth.user.notifications" :key="notif.id" @click="markAsRead(notif)" class="px-6 py-5 border-b border-slate-100 hover:bg-slate-50 cursor-pointer transition-colors relative" :class="{ 'bg-indigo-50/30': !notif.read_at }">
                                        <h4 class="text-xs font-bold text-slate-900 mb-1">{{ notif.data.title }}</h4>
                                        <p class="text-[11px] text-slate-500 leading-tight mb-2">{{ notif.data.message }}</p>
                                        <span class="text-[9px] text-slate-400 font-black uppercase tracking-tighter">{{ new Date(notif.created_at).toLocaleDateString() }}</span>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                        <div v-if="isNotificationsOpen" @click="isNotificationsOpen = false" class="fixed inset-0 z-40"></div>
                    </div>

                    <div class="h-6 w-px bg-slate-200 mx-1"></div>

                    <Link :href="route('profile.edit')" class="flex items-center gap-3 pl-3 pr-1 py-1 rounded-xl hover:bg-slate-100 transition-all group">
                        <div class="flex flex-col items-end hidden sm:block">
                            <span class="text-xs font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">{{ $page.props.auth.user.name }}</span>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Registry Admin</span>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 group-hover:text-indigo-600 transition-all">
                            <i class="bi bi-person text-xl"></i>
                        </div>
                    </Link>
                </div>
            </header>

            <main class="flex-grow p-8 max-w-[1600px] w-full mx-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
.scale-enter-active, .scale-leave-active { transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.scale-enter-from, .scale-leave-to { opacity: 0; transform: scale(0.95) translateY(-10px); }
</style>

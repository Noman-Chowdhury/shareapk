<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
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
            class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-white transition-all duration-300 ease-in-out lg:static lg:block"
            :class="{ '-translate-x-full lg:translate-x-0': !isSidebarOpen, 'translate-x-0': isSidebarOpen }"
        >
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="p-6 flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-900/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight">Antigravity</span>
                </div>

                <!-- Navigation -->
                <nav class="flex-grow px-4 space-y-2 mt-4">
                    <Link 
                        :href="route('dashboard')" 
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group"
                        :class="route().current('dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-speedometer2 text-lg"></i>
                        <span class="font-medium">Dashboard</span>
                    </Link>

                    <Link 
                        :href="route('projects.index')" 
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group"
                        :class="route().current('projects.*') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-folder text-lg"></i>
                        <span class="font-medium">Projects</span>
                    </Link>

                    <Link 
                        :href="route('feedback.index')" 
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group"
                        :class="route().current('feedback.*') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-chat-left-dots text-lg"></i>
                        <span class="font-medium">Feedback</span>
                    </Link>

                    <Link 
                        :href="route('tasks.index')" 
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group"
                        :class="route().current('tasks.*') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-check2-square text-lg"></i>
                        <span class="font-medium">Tasks</span>
                    </Link>

                    <div v-if="$page.props.auth.user.roles?.includes('Admin')" class="pt-6 pb-2 px-4 uppercase text-[10px] font-bold text-slate-500 tracking-widest">Administration</div>

                    <Link 
                        v-if="$page.props.auth.user.roles?.includes('Admin')"
                        :href="route('users.index')" 
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group"
                        :class="route().current('users.*') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-people text-lg"></i>
                        <span class="font-medium">User Management</span>
                    </Link>

                    <Link 
                        v-if="$page.props.auth.user.roles?.includes('Admin')"
                        :href="route('activity-logs.index')" 
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group"
                        :class="route().current('activity-logs.*') ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-journal-text text-lg"></i>
                        <span class="font-medium">Activity Logs</span>
                    </Link>
                </nav>

                <!-- User Footer -->
                <div class="p-4 mt-auto">
                    <div class="p-4 bg-slate-800 rounded-2xl flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center font-bold text-indigo-400 border border-slate-600">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                        <div class="flex-grow min-w-0">
                            <p class="text-sm font-semibold truncate">{{ $page.props.auth.user.name }}</p>
                            <p class="text-[11px] text-slate-500 truncate capitalize">{{ $page.props.auth.user.roles?.join(', ') || 'User' }}</p>
                        </div>
                        <Link :href="route('logout')" method="post" as="button" class="text-slate-400 hover:text-rose-400 transition-colors">
                            <i class="bi bi-box-arrow-right text-lg"></i>
                        </Link>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-grow flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 px-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button @click="toggleSidebar" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <h1 v-if="!$slots.header" class="text-lg font-bold text-slate-800">APK Management</h1>
                    <div v-if="$slots.header" class="flex-grow">
                        <slot name="header" />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Notifications Dropdown -->
                    <div class="relative">
                        <button 
                            @click="isNotificationsOpen = !isNotificationsOpen"
                            class="p-2.5 text-slate-500 hover:bg-slate-100 rounded-xl transition-all relative"
                        >
                            <i class="bi bi-bell text-xl"></i>
                            <span v-if="$page.props.auth.user.unread_notifications_count > 0" class="absolute top-2 right-2 w-4 h-4 bg-rose-500 text-white rounded-full flex items-center justify-center text-[8px] font-bold border-2 border-white">
                                {{ $page.props.auth.user.unread_notifications_count }}
                            </span>
                        </button>

                        <div 
                            v-if="isNotificationsOpen" 
                            class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-50 animate-in fade-in zoom-in duration-200"
                        >
                            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                                <h3 class="font-bold text-slate-800">Notifications</h3>
                                <Link :href="route('notifications.clear')" method="post" as="button" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Clear all</Link>
                            </div>
                            <div class="max-h-[400px] overflow-y-auto">
                                <div v-if="$page.props.auth.user.notifications.length === 0" class="p-8 text-center text-slate-400">
                                    <i class="bi bi-bell-slash text-4xl block mb-2 opacity-20"></i>
                                    <p class="text-sm">No new notifications</p>
                                </div>
                                <div 
                                    v-for="notif in $page.props.auth.user.notifications" 
                                    :key="notif.id"
                                    @click="markAsRead(notif)"
                                    class="p-4 border-b border-slate-100 hover:bg-slate-50 cursor-pointer transition-colors group"
                                    :class="{ 'bg-indigo-50/30': !notif.read_at }"
                                >
                                    <div class="flex justify-between items-start mb-1">
                                        <h4 class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ notif.data.title }}</h4>
                                        <span v-if="!notif.read_at" class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                                    </div>
                                    <p class="text-[13px] text-slate-500 leading-snug">{{ notif.data.message }}</p>
                                    <p class="text-[10px] text-slate-400 mt-2 font-medium uppercase tracking-wider">{{ new Date(notif.created_at).toLocaleString() }}</p>
                                </div>
                            </div>
                        </div>
                        <!-- Close overlay -->
                        <div v-if="isNotificationsOpen" @click="isNotificationsOpen = false" class="fixed inset-0 z-40"></div>
                    </div>

                    <Link :href="route('profile.edit')" class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all">
                        <i class="bi bi-person text-xl"></i>
                    </Link>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-grow p-6 lg:p-10 max-w-[1600px]">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
/* Custom animations */
.animate-in {
    animation-duration: 0.2s;
    animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-in { animation-name: fade-in; }
.zoom-in { animation-name: zoom-in; }

@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes zoom-in { from { transform: scale(0.95); } to { transform: scale(1); } }
</style>

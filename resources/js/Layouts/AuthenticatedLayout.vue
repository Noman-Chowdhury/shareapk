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
            class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white transition-all duration-300 ease-in-out lg:static lg:block"
            :class="{ '-translate-x-full lg:translate-x-0': !isSidebarOpen, 'translate-x-0': isSidebarOpen }"
        >
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="h-14 flex items-center px-6 border-b border-slate-800">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-900/40">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                        </svg>
                    </div>
                    <span class="ml-3 text-lg font-bold tracking-tight">Antigravity</span>
                </div>

                <!-- Navigation -->
                <nav class="flex-grow px-3 py-4 space-y-1 overflow-y-auto">
                    <Link 
                        :href="route('dashboard')" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all text-sm font-medium"
                        :class="route().current('dashboard') ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-grid text-lg"></i>
                        <span>Dashboard</span>
                    </Link>

                    <Link 
                        :href="route('projects.index')" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all text-sm font-medium"
                        :class="route().current('projects.*') ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-folder text-lg"></i>
                        <span>Projects</span>
                    </Link>

                    <Link 
                        :href="route('feedback.index')" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all text-sm font-medium"
                        :class="route().current('feedback.*') ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-chat-dots text-lg"></i>
                        <span>Feedback</span>
                    </Link>

                    <Link 
                        :href="route('tasks.index')" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all text-sm font-medium"
                        :class="route().current('tasks.*') ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-check2-circle text-lg"></i>
                        <span>Tasks</span>
                    </Link>

                    <div v-if="$page.props.auth.user.roles?.includes('Admin')" class="pt-6 pb-2 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Management</div>

                    <Link 
                        v-if="$page.props.auth.user.roles?.includes('Admin')"
                        :href="route('users.index')" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all text-sm font-medium"
                        :class="route().current('users.*') ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-people text-lg"></i>
                        <span>Users</span>
                    </Link>

                    <Link 
                        v-if="$page.props.auth.user.roles?.includes('Admin')"
                        :href="route('activity-logs.index')" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all text-sm font-medium"
                        :class="route().current('activity-logs.*') ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                    >
                        <i class="bi bi-clock-history text-lg"></i>
                        <span>Logs</span>
                    </Link>
                </nav>

                <!-- User Footer -->
                <div class="p-4 border-t border-slate-800">
                    <div class="flex items-center gap-3 px-2">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center font-bold text-indigo-400 border border-slate-700 text-xs">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                        <div class="flex-grow min-w-0">
                            <p class="text-xs font-semibold text-white truncate">{{ $page.props.auth.user.name }}</p>
                            <p class="text-[10px] text-slate-500 truncate capitalize">{{ $page.props.auth.user.roles?.join(', ') || 'User' }}</p>
                        </div>
                        <Link :href="route('logout')" method="post" as="button" class="text-slate-500 hover:text-rose-400 transition-colors">
                            <i class="bi bi-box-arrow-right text-lg"></i>
                        </Link>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-grow flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="h-14 bg-white border-b border-slate-200 sticky top-0 z-40 px-6 flex items-center justify-between">
                <div class="flex items-center gap-4 min-w-0">
                    <button @click="toggleSidebar" class="p-1.5 text-slate-500 hover:bg-slate-100 rounded-md transition-colors lg:hidden">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <div v-if="$slots.header" class="min-w-0 flex-grow">
                        <slot name="header" />
                    </div>
                    <h1 v-else class="text-sm font-bold text-slate-900 uppercase tracking-wider">APK Management</h1>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Notifications -->
                    <div class="relative">
                        <button 
                            @click="isNotificationsOpen = !isNotificationsOpen"
                            class="p-2 text-slate-500 hover:bg-slate-50 rounded-lg transition-all relative"
                        >
                            <i class="bi bi-bell text-lg"></i>
                            <span v-if="$page.props.auth.user.unread_notifications_count > 0" class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full border border-white"></span>
                        </button>

                        <div 
                            v-if="isNotificationsOpen" 
                            class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-xl border border-slate-200 overflow-hidden z-50 animate-in fade-in zoom-in duration-150"
                        >
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                                <h3 class="text-xs font-bold text-slate-900 uppercase">Notifications</h3>
                                <Link :href="route('notifications.clear')" method="post" as="button" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider">Clear all</Link>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <div v-if="$page.props.auth.user.notifications.length === 0" class="py-10 text-center text-slate-400">
                                    <p class="text-xs">No new notifications</p>
                                </div>
                                <div 
                                    v-for="notif in $page.props.auth.user.notifications" 
                                    :key="notif.id"
                                    @click="markAsRead(notif)"
                                    class="p-4 border-b border-slate-50 hover:bg-slate-50 cursor-pointer transition-colors"
                                    :class="{ 'bg-indigo-50/20': !notif.read_at }"
                                >
                                    <div class="flex justify-between items-start mb-0.5">
                                        <h4 class="text-xs font-bold text-slate-900">{{ notif.data.title }}</h4>
                                        <span v-if="!notif.read_at" class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">{{ notif.data.message }}</p>
                                    <p class="text-[9px] text-slate-400 mt-1 uppercase font-semibold">{{ new Date(notif.created_at).toLocaleDateString() }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-if="isNotificationsOpen" @click="isNotificationsOpen = false" class="fixed inset-0 z-40"></div>
                    </div>

                    <div class="h-4 w-px bg-slate-200 mx-2"></div>

                    <Link :href="route('profile.edit')" class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-lg hover:bg-slate-50 transition-colors group">
                        <span class="text-xs font-semibold text-slate-700 group-hover:text-slate-900 hidden sm:block">{{ $page.props.auth.user.name }}</span>
                        <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 group-hover:text-indigo-600 transition-all">
                            <i class="bi bi-person text-lg"></i>
                        </div>
                    </Link>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-grow p-4 lg:p-8 max-w-[1600px] w-full">
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

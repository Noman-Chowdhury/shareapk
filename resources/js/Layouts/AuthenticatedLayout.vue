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
            class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0f172a] text-white transition-all duration-300 ease-in-out lg:static lg:block"
            :class="{ '-translate-x-full lg:translate-x-0': !isSidebarOpen, 'translate-x-0': isSidebarOpen }"
        >
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="h-16 flex items-center px-6 border-b border-white/5">
                    <div class="w-9 h-9 bg-indigo-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                        </svg>
                    </div>
                    <span class="ml-3 text-lg font-bold tracking-tight bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent">Antigravity</span>
                </div>

                <!-- Navigation -->
                <nav class="flex-grow px-4 py-6 space-y-1.5 overflow-y-auto">
                    <Link 
                        :href="route('dashboard')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-grid text-lg" :class="route().current('dashboard') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400'"></i>
                        <span>Dashboard</span>
                    </Link>

                    <Link 
                        :href="route('projects.index')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('projects.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-folder text-lg" :class="route().current('projects.*') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400'"></i>
                        <span>Projects</span>
                    </Link>

                    <Link 
                        :href="route('feedback.index')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('feedback.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-chat-dots text-lg" :class="route().current('feedback.*') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400'"></i>
                        <span>Feedback</span>
                    </Link>

                    <Link 
                        :href="route('tasks.index')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('tasks.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-check2-circle text-lg" :class="route().current('tasks.*') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400'"></i>
                        <span>Tasks</span>
                    </Link>

                    <div v-if="$page.props.auth.user.roles?.includes('Admin')" class="pt-8 pb-2 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Management</div>

                    <Link 
                        v-if="$page.props.auth.user.roles?.includes('Admin')"
                        :href="route('users.index')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('users.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-people text-lg" :class="route().current('users.*') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400'"></i>
                        <span>Users</span>
                    </Link>

                    <Link 
                        v-if="$page.props.auth.user.roles?.includes('Admin')"
                        :href="route('activity-logs.index')" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-sm font-semibold group"
                        :class="route().current('activity-logs.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <i class="bi bi-clock-history text-lg" :class="route().current('activity-logs.*') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400'"></i>
                        <span>Logs</span>
                    </Link>
                </nav>

                <!-- User Footer -->
                <div class="px-4 py-6 border-t border-white/5">
                    <div class="flex items-center gap-3 px-2">
                        <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center font-bold text-indigo-400 border border-white/10 text-xs shadow-inner">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                        <div class="flex-grow min-w-0">
                            <p class="text-xs font-bold text-white truncate">{{ $page.props.auth.user.name }}</p>
                            <p class="text-[10px] text-slate-500 truncate capitalize font-medium">{{ $page.props.auth.user.roles?.join(', ') || 'User' }}</p>
                        </div>
                        <Link :href="route('logout')" method="post" as="button" class="text-slate-500 hover:text-rose-400 transition-colors p-1.5 hover:bg-rose-400/10 rounded-lg">
                            <i class="bi bi-box-arrow-right text-lg"></i>
                        </Link>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-grow flex flex-col min-w-0 bg-[#f8fafc]">
            <!-- Topbar -->
            <header class="h-16 bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 px-8 flex items-center justify-between">
                <div class="flex items-center gap-6 min-w-0">
                    <button @click="toggleSidebar" class="p-1.5 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors lg:hidden">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div v-if="$slots.header" class="min-w-0 flex-grow animate-fade-in">
                        <slot name="header" />
                    </div>
                    <h1 v-else class="text-sm font-black text-slate-800 uppercase tracking-widest animate-fade-in">Registry Protocol</h1>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button 
                            @click="isNotificationsOpen = !isNotificationsOpen"
                            class="p-2.5 text-slate-500 hover:bg-slate-100 rounded-xl transition-all relative group"
                            :class="{ 'bg-slate-100 text-indigo-600': isNotificationsOpen }"
                        >
                            <i class="bi bi-bell text-xl"></i>
                            <span v-if="$page.props.auth.user.unread_notifications_count > 0" class="absolute top-2.5 right-2.5 w-2 h-2 bg-rose-500 rounded-full border-2 border-white ring-2 ring-rose-500/20"></span>
                        </button>

                        <Transition name="scale">
                            <div 
                                v-if="isNotificationsOpen" 
                                class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-50 origin-top-right transform transition-all"
                            >
                                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Updates</h3>
                                    <Link :href="route('notifications.clear')" method="post" as="button" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest">Mark All</Link>
                                </div>
                                <div class="max-h-[400px] overflow-y-auto matte-surface">
                                    <div v-if="$page.props.auth.user.notifications.length === 0" class="py-16 text-center text-slate-400">
                                        <i class="bi bi-cloud-check text-4xl mb-2 block opacity-20"></i>
                                        <p class="text-[10px] font-bold uppercase tracking-widest">Protocol Sync Regular</p>
                                    </div>
                                    <div 
                                        v-for="notif in $page.props.auth.user.notifications" 
                                        :key="notif.id"
                                        @click="markAsRead(notif)"
                                        class="px-6 py-5 border-b border-slate-100 hover:bg-slate-50 cursor-pointer transition-colors relative"
                                        :class="{ 'bg-indigo-50/30': !notif.read_at }"
                                    >
                                        <div class="flex justify-between items-start mb-1">
                                            <h4 class="text-xs font-bold text-slate-900 pr-4">{{ notif.data.title }}</h4>
                                            <span v-if="!notif.read_at" class="w-1.5 h-1.5 bg-indigo-500 rounded-full shadow-[0_0_8px_rgba(79,70,229,0.5)]"></span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ notif.data.message }}</p>
                                        <p class="text-[9px] text-slate-400 mt-2 font-black uppercase tracking-widest">{{ new Date(notif.created_at).toLocaleDateString() }}</p>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                        <div v-if="isNotificationsOpen" @click="isNotificationsOpen = false" class="fixed inset-0 z-40"></div>
                    </div>

                    <div class="h-6 w-px bg-slate-200 mx-1"></div>

                    <Link :href="route('profile.edit')" class="flex items-center gap-3 pl-3 pr-1 py-1 rounded-xl hover:bg-slate-100 transition-all group border border-transparent hover:border-slate-200">
                        <div class="flex flex-col items-end hidden sm:block">
                            <span class="text-xs font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">{{ $page.props.auth.user.name }}</span>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Profile Settings</span>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 group-hover:text-indigo-600 group-hover:bg-white group-hover:shadow-md transition-all">
                            <i class="bi bi-person text-xl"></i>
                        </div>
                    </Link>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-grow p-8 max-w-[1600px] w-full animate-slide-up">
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

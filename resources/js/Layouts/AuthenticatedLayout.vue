<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

function markAsRead(notif) {
    router.post(route('notifications.read', notif.id), {}, {
        onSuccess: () => {
            if (notif.data.link) {
                window.location.href = notif.data.link;
            }
        }
    });
}
</script>

<template>
    <div>
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top" style="z-index: 1050;">
            <div class="container">
                <Link :href="route('dashboard')" class="navbar-brand d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-android2 me-2" viewBox="0 0 16 16">
                        <path d="m10.213 1.471.691-1.216a.104.104 0 0 0-.033-.142.106.106 0 0 0-.142.033l-.705 1.238A8 8 0 0 0 8 1.139a8 8 0 0 0-2.024.26L5.27.161a.105.105 0 0 0-.142-.033.104.104 0 0 0-.033.142l.691 1.216C3.906 2.502 2.146 4.606 1.838 7.21h12.324c-.308-2.604-2.068-4.708-3.949-5.739M5.4 5.378a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0m6.839 0a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0M1.6 8.21h12.8A1.6 1.6 0 0 1 16 9.81v3.2a1.6 1.6 0 0 1-1.6 1.6H1.6A1.6 1.6 0 0 1 0 13.01v-3.2A1.6 1.6 0 0 1 1.6 8.21Z"/>
                    </svg>
                    APK Distribution
                </Link>
                
                <button
                    class="navbar-toggler"
                    type="button"
                    @click="showingNavigationDropdown = !showingNavigationDropdown"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" :class="{ show: showingNavigationDropdown }">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <Link :href="route('dashboard')" class="nav-link" :class="{ active: route().current('dashboard') }">
                                Dashboard
                            </Link>
                        </li>
                        <li class="nav-item">
                            <Link :href="route('projects.index')" class="nav-link" :class="{ active: route().current('projects.*') }">Projects</Link>
                        </li>
                        <li class="nav-item">
                            <Link :href="route('feedback.index')" class="nav-link" :class="{ active: route().current('feedback.*') }">Feedback</Link>
                        </li>
                        <li class="nav-item">
                            <Link :href="route('tasks.index')" class="nav-link" :class="{ active: route().current('tasks.*') }">Tasks</Link>
                        </li>
                        <li class="nav-item" v-if="$page.props.auth.user.roles?.includes('Admin')">
                            <Link :href="route('users.index')" class="nav-link" :class="{ active: route().current('users.*') }">Users</Link>
                        </li>
                        <li class="nav-item" v-if="$page.props.auth.user.roles?.includes('Admin')">
                            <Link :href="route('activity-logs.index')" class="nav-link" :class="{ active: route().current('activity-logs.*') }">Activity Log</Link>
                        </li>
                    </ul>

                    <ul class="navbar-nav ms-auto align-items-center">
                        <!-- Notifications -->
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link position-relative py-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5"></i>
                                <span v-if="$page.props.auth.user.unread_notifications_count > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    {{ $page.props.auth.user.unread_notifications_count }}
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-0 overflow-hidden" style="width: 300px;">
                                <li class="bg-light p-2 border-bottom">
                                    <span class="small fw-bold text-muted">Recent Notifications</span>
                                </li>
                                <li v-if="$page.props.auth.user.notifications.length === 0" class="p-3 text-center text-muted small">
                                    No new notifications
                                </li>
                                <li v-for="notif in $page.props.auth.user.notifications" :key="notif.id" class="border-bottom">
                                    <div @click="markAsRead(notif)" class="dropdown-item p-3 white-space-normal" style="cursor: pointer;" :class="{ 'bg-light': !notif.read_at }">
                                        <div class="fw-bold small d-flex justify-content-between">
                                            {{ notif.data.title }}
                                            <span v-if="!notif.read_at" class="badge rounded-pill bg-primary" style="font-size: 0.5rem; height: fit-content;">NEW</span>
                                        </div>
                                        <div class="text-muted small lh-sm">{{ notif.data.message }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ new Date(notif.created_at).toLocaleString() }}</div>
                                    </div>
                                </li>
                                <li class="bg-light text-center p-2">
                                    <Link :href="route('notifications.clear')" method="post" as="button" class="btn btn-link btn-sm p-0 m-0 small text-decoration-none border-0 box-shadow-none">Clear All</Link>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ $page.props.auth.user.name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <Link :href="route('profile.edit')" class="dropdown-item">Profile</Link>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <Link :href="route('logout')" method="post" as="button" class="dropdown-item">
                                        Log Out
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white shadow-sm py-3 mb-4" v-if="$slots.header">
            <div class="container">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="container">
            <slot />
        </main>
    </div>
</template>

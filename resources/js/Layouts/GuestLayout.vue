<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="d-flex min-vh-100 flex-column justify-content-center align-items-center bg-light pt-4">
        <div class="mb-4">
            <Link href="/" class="text-decoration-none">
                <div class="d-flex align-items-center text-primary fw-bold fs-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-android2 me-2" viewBox="0 0 16 16">
                        <path d="m10.213 1.471.691-1.216a.104.104 0 0 0-.033-.142.106.106 0 0 0-.142.033l-.705 1.238A8 8 0 0 0 8 1.139a8 8 0 0 0-2.024.26L5.27.161a.105.105 0 0 0-.142-.033.104.104 0 0 0-.033.142l.691 1.216C3.906 2.502 2.146 4.606 1.838 7.21h12.324c-.308-2.604-2.068-4.708-3.949-5.739M5.4 5.378a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0m6.839 0a.82.82 0 1 1-1.639 0 .82.82 0 0 1 1.639 0M1.6 8.21h12.8A1.6 1.6 0 0 1 16 9.81v3.2a1.6 1.6 0 0 1-1.6 1.6H1.6A1.6 1.6 0 0 1 0 13.01v-3.2A1.6 1.6 0 0 1 1.6 8.21Z"/>
                    </svg>
                    APK Distribution
                </div>
            </Link>
        </div>

        <div class="card shadow-sm border-0" style="width: 100%; max-width: 450px;">
            <div class="card-body p-4 p-sm-5">
                <slot />
            </div>
        </div>
    </div>
</template>

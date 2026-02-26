<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Premium APK Distribution Portal" />
    
    <div class="min-h-screen bg-white">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-3">
            <div class="container">
                <Link :href="'/'" class="navbar-brand d-flex align-items-center fw-bold text-primary">
                    <i class="bi bi-android2 me-2 fs-3"></i>
                    APK PORTAL
                </Link>
                
                <div class="ms-auto d-flex gap-2">
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('dashboard')" class="btn btn-primary shadow-sm">Go to Dashboard</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="btn btn-link text-decoration-none text-dark fw-medium">Log in</Link>
                        <Link v-if="canRegister" :href="route('register')" class="btn btn-primary shadow-sm">Get Started</Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="py-5 py-lg-10 bg-light position-relative overflow-hidden">
            <div class="container position-relative z-index-1">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-5 mb-lg-0">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3">v2.0 Now Live</span>
                        <h1 class="display-3 fw-bold mb-4">The Ultimate <span class="text-primary text-gradient">APK Distribution</span> Platform</h1>
                        <p class="lead text-muted mb-5">Seamlessly distribute, track, and manage your Android applications within your team. Automated APK parsing, real-time feedback, and secure public sharing.</p>
                        
                        <div class="d-flex flex-wrap gap-3">
                            <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn btn-primary btn-lg px-4 py-3 shadow">View Your Projects</Link>
                            <a href="#features" class="btn btn-outline-secondary btn-lg px-4 py-3">Learn More</a>
                        </div>

                        <div class="mt-5 d-flex align-items-center gap-4 text-muted small">
                            <span><i class="bi bi-check-circle-fill text-success me-1"></i> QR Code Ready</span>
                            <span><i class="bi bi-check-circle-fill text-success me-1"></i> APK Analysis</span>
                            <span><i class="bi bi-check-circle-fill text-success me-1"></i> Bug Tracking</span>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center">
                        <div class="position-relative d-inline-block">
                            <div class="position-absolute top-50 start-50 translate-middle bg-primary rounded-circle opacity-10 blur-3xl" style="width: 400px; height: 400px;"></div>
                            <img src="https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?auto=format&fit=crop&q=80&w=800" alt="Mobile App Development" class="img-fluid rounded-4 shadow-2xl position-relative border border-5 border-white" style="max-width: 500px;">
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Features -->
        <section id="features" class="py-5 py-lg-10">
            <div class="container">
                <div class="text-center mb-5 max-w-2xl mx-auto">
                    <h2 class="display-5 fw-bold mb-3">Enterprise-Grade Features</h2>
                    <p class="text-muted">Designed specifically for development teams who need speed, security, and reliability in their testing workflow.</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm p-4 hover-up">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 d-inline-flex mb-4" style="width: fit-content;">
                                <i class="bi bi-lightning-charge-fill fs-3"></i>
                            </div>
                            <h4 class="fw-bold">Smart Upload</h4>
                            <p class="text-muted">Simply drag and drop your APK. Our system automatically extracts the app name, package name, version codes, and even the app icon.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm p-4 hover-up">
                            <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 d-inline-flex mb-4" style="width: fit-content;">
                                <i class="bi bi-qr-code fs-3"></i>
                            </div>
                            <h4 class="fw-bold">QR Code Distribution</h4>
                            <p class="text-muted">Generate instant QR codes for any build. Testers can scan directly from their PC to start downloading on their Android device.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm p-4 hover-up">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 d-inline-flex mb-4" style="width: fit-content;">
                                <i class="bi bi-shield-lock-fill fs-3"></i>
                            </div>
                            <h4 class="fw-bold">Secure Sharing</h4>
                            <p class="text-muted">Create password-protected public links with download limits and expiry dates. Your builds are only accessible to those you trust.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm p-4 hover-up">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 d-inline-flex mb-4" style="width: fit-content;">
                                <i class="bi bi-chat-square-text-fill fs-3"></i>
                            </div>
                            <h4 class="fw-bold">Feedback Loop</h4>
                            <p class="text-muted">Testers can submit bugs and feature requests directly on build versions, complete with device info and severity levels.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm p-4 hover-up">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3 d-inline-flex mb-4" style="width: fit-content;">
                                <i class="bi bi-bell-fill fs-3"></i>
                            </div>
                            <h4 class="fw-bold">Real-time Notifications</h4>
                            <p class="text-muted">Stay updated with push notifications for build approvals, task assignments, and new bug reports.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm p-4 hover-up">
                            <div class="bg-dark bg-opacity-10 text-dark rounded-3 p-3 d-inline-flex mb-4" style="width: fit-content;">
                                <i class="bi bi-bar-chart-fill fs-3"></i>
                            </div>
                            <h4 class="fw-bold">Version History</h4>
                            <p class="text-muted">Keep a complete archive of every version uploaded. Compare build sizes, release notes, and approval status at a glance.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats -->
        <section class="py-5 bg-primary text-white">
            <div class="container py-4">
                <div class="row g-4 text-center">
                    <div class="col-6 col-md-3">
                        <div class="display-6 fw-bold mb-1">100%</div>
                        <div class="opacity-75">Private & Secure</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="display-6 fw-bold mb-1">Instant</div>
                        <div class="opacity-75">APK Analysis</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="display-6 fw-bold mb-1">No Limit</div>
                        <div class="opacity-75">Build Hosting</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="display-6 fw-bold mb-1">Mobile</div>
                        <div class="opacity-75">Optimized Portal</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-5 border-top">
            <div class="container text-center">
                <div class="d-flex align-items-center justify-content-center fw-bold text-primary mb-3">
                    <i class="bi bi-android2 me-2 fs-4"></i>
                    APK PORTAL
                </div>
                <p class="text-muted small mb-0">&copy; 2026 Internal Distribution System. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.text-gradient {
    background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hover-up {
    transition: transform 0.3s ease;
}
.hover-up:hover {
    transform: translateY(-10px);
}

.blur-3xl {
    filter: blur(60px);
}

.z-index-1 {
    z-index: 1;
}

header {
    background: radial-gradient(circle at 70% 30%, rgba(79, 70, 229, 0.05) 0%, rgba(255, 255, 255, 0) 50%);
}
</style>

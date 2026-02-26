<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import * as bootstrap from 'bootstrap';

const props = defineProps({
    users: Array,
    roles: Array,
});

const editingUser = ref(null);
const userForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'Viewer',
});

function openModal(user = null) {
    editingUser.value = user;
    if (user) {
        userForm.name = user.name;
        userForm.email = user.email;
        userForm.password = ''; // left blank to remain unchanged
        userForm.role = user.roles && user.roles.length > 0 ? user.roles[0].name : 'Viewer';
    } else {
        userForm.reset();
        userForm.role = 'Viewer';
    }
    const modalEl = document.getElementById('userModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

function submitUser() {
    const url = editingUser.value 
        ? route('users.update', editingUser.value.id) 
        : route('users.store');
    const method = editingUser.value ? 'put' : 'post';

    userForm[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            const modalEl = document.getElementById('userModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            userForm.reset();
            editingUser.value = null;
        },
    });
}

function deleteUser(id) {
    if (confirm('Are you sure you want to permanently delete this user?')) {
        router.delete(route('users.destroy', id), { preserveScroll: true });
    }
}

const currentUser = computed(() => usePage().props.auth.user);
</script>

<template>
    <Head title="Users & Permissions" />
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0 fw-bold">User Management</h2>
                <button class="btn btn-primary btn-sm" @click="openModal()">
                    + Invite User
                </button>
            </div>
        </template>

        <div class="card border-0 shadow-sm mt-1">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id">
                                <td class="ps-4 fw-semibold">
                                    {{ user.name }}
                                    <span v-if="user.id === currentUser.id" class="badge bg-secondary ms-2">You</span>
                                </td>
                                <td class="text-muted">{{ user.email }}</td>
                                <td>
                                    <span class="badge bg-info text-dark" v-if="user.roles.length > 0">
                                        {{ user.roles[0].name }}
                                    </span>
                                    <span class="badge bg-secondary" v-else>No Role</span>
                                </td>
                                <td class="text-muted small">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-secondary me-2" @click="openModal(user)">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            @click="deleteUser(user.id)" 
                                            v-if="currentUser.roles && currentUser.roles.includes('Admin')"
                                            :disabled="user.id === currentUser.id">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <form @submit.prevent="submitUser">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editingUser ? 'Edit User' : 'Add New User' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input v-model="userForm.name" type="text" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input v-model="userForm.email" type="email" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select v-model="userForm.role" class="form-select" required>
                                <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password <span class="text-danger" v-if="!editingUser">*</span></label>
                            <input v-model="userForm.password" type="password" class="form-control" :required="!editingUser">
                            <small class="text-muted" v-if="editingUser">Leave blank to keep the current password.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="userForm.processing">
                            {{ editingUser ? 'Save Changes' : 'Create User' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<template>
    <div class="users-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Users Management</h1>
                <p class="page-subtitle">Manage your users and their permissions</p>
            </div>
            <button 
                v-if="hasPermission('create_users')" 
                class="btn btn-primary" 
                @click="openCreateModal"
            >
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Add User
            </button>
        </div>

        <!-- Filters and Search -->
        <div class="filters-section">
            <div class="search-box">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                    <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Search users by name or email..."
                    @input="debouncedSearch"
                />
            </div>
            <select v-model="selectedRole" @change="fetchUsers" class="role-filter">
                <option value="">All Roles</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">
                    {{ role.name }}
                </option>
            </select>
        </div>
        
        <div class="content-card">
            <div v-if="loading" class="loading-state">
                <p>Loading users...</p>
            </div>
            
            <div v-else-if="users.length === 0" class="empty-state">
                <p>No users found</p>
            </div>
            
            <div v-else class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile Number</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id">
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">{{ user.name.charAt(0).toUpperCase() }}</div>
                                    <span>{{ user.name }}</span>
                                </div>
                            </td>
                            <td>{{ user.email }}</td>
                            <td>{{ user.mobile_number || 'N/A' }}</td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ user.role ? user.role.name : 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge" :class="user.status === 'active' ? 'status-active' : 'status-inactive'">
                                    {{ user.status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button 
                                        v-if="hasPermission('edit_users')" 
                                        class="icon-btn" 
                                        @click="openEditModal(user)" 
                                        title="Edit"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M11.3333 2.00001C11.5084 1.82489 11.7163 1.68603 11.9447 1.59128C12.1731 1.49654 12.4173 1.44775 12.6667 1.44775C12.916 1.44775 13.1602 1.49654 13.3886 1.59128C13.617 1.68603 13.8249 1.82489 14 2.00001C14.1751 2.17513 14.314 2.38305 14.4087 2.61143C14.5035 2.83981 14.5523 3.08405 14.5523 3.33334C14.5523 3.58263 14.5035 3.82687 14.4087 4.05525C14.314 4.28363 14.1751 4.49155 14 4.66667L5 13.6667L1.33333 14.6667L2.33333 11L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button 
                                        v-if="hasPermission('delete_users')" 
                                        class="icon-btn icon-btn-danger" 
                                        @click="confirmDelete(user)" 
                                        title="Delete"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M2 4H14M12.6667 4V13.3333C12.6667 13.687 12.5262 14.0263 12.2761 14.2764C12.026 14.5265 11.6867 14.6667 11.3333 14.6667H4.66667C4.31305 14.6667 3.97391 14.5265 3.72381 14.2764C3.47371 14.0263 3.33333 13.687 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2.31305 5.47371 1.97391 5.72381 1.72381C5.97391 1.47371 6.31305 1.33333 6.66667 1.33333H9.33333C9.68696 1.33333 10.0261 1.47371 10.2762 1.72381C10.5263 1.97391 10.6667 2.31305 10.6667 2.66667V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="pagination">
                <button 
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="page-btn"
                >
                    Previous
                </button>
                <span class="page-info">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </span>
                <button 
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="page-btn"
                >
                    Next
                </button>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingUser ? 'Edit User' : 'Create New User' }}</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="saveUser" class="modal-body">
                    <div class="form-group">
                        <label>Name *</label>
                        <input v-model="form.name" type="text" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input v-model="form.email" type="email" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input v-model="form.mobile_number" type="tel" class="form-control" placeholder="+1234567890" />
                    </div>
                    <div class="form-group">
                        <label>Password {{ editingUser ? '(leave blank to keep current)' : '*' }}</label>
                        <input v-model="form.password" type="password" class="form-control" :required="!editingUser" />
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select v-model="form.role_id" class="form-control" required>
                            <option value="">Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                {{ role.name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select v-model="form.status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
            <div class="modal-content modal-small" @click.stop>
                <div class="modal-header">
                    <h2>Delete User</h2>
                    <button class="modal-close" @click="closeDeleteModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>? This action cannot be undone.</p>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
                        <button type="button" class="btn btn-danger" @click="deleteUser" :disabled="deleting">
                            {{ deleting ? 'Deleting...' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAlert } from '../../composables/useAlert';
import { usePermissions } from '../../composables/usePermissions';

export default {
    name: 'Users',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const { hasPermission } = usePermissions();
        const users = ref([]);
        const roles = ref([]);
        const loading = ref(false);
        const saving = ref(false);
        const deleting = ref(false);
        const showModal = ref(false);
        const showDeleteModal = ref(false);
        const editingUser = ref(null);
        const userToDelete = ref(null);
        const searchQuery = ref('');
        const selectedRole = ref('');
        const pagination = ref(null);

        const form = ref({
            name: '',
            email: '',
            mobile_number: '',
            password: '',
            role_id: '',
            status: 'active'
        });

        // Fetch roles
        const fetchRoles = async () => {
            try {
                const response = await axios.get('/api/roles');
                
                if (Array.isArray(response.data)) {
                    roles.value = response.data;
                } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
                    roles.value = response.data.data;
                } else {
                    roles.value = [];
                }
            } catch (error) {
                console.error('Error fetching roles:', error);
                console.error('Error details:', error.response?.data || error.message);
                roles.value = [];
            }
        };

        // Fetch users
        const fetchUsers = async (page = 1) => {
            loading.value = true;
            try {
                const params = {
                    page,
                };
                
                if (searchQuery.value) {
                    params.search = searchQuery.value;
                }
                
                if (selectedRole.value) {
                    params.role_id = selectedRole.value;
                }

                const response = await axios.get('/api/users', { params });
                
                // Handle paginated response
                if (response.data && response.data.data) {
                    users.value = response.data.data;
                    pagination.value = response.data;
                } else if (Array.isArray(response.data)) {
                    users.value = response.data;
                    pagination.value = null;
                } else {
                    users.value = [];
                    pagination.value = null;
                }
            } catch (error) {
                console.error('Error fetching users:', error);
                console.error('Error details:', error.response?.data || error.message);
                users.value = [];
                pagination.value = null;
            } finally {
                loading.value = false;
            }
        };

        // Debounced search
        let searchTimeout = null;
        const debouncedSearch = () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchUsers(1);
            }, 500);
        };

        // Open create modal
        const openCreateModal = () => {
            editingUser.value = null;
            form.value = {
                name: '',
                email: '',
                mobile_number: '',
                password: '',
                role_id: '',
                status: 'active'
            };
            showModal.value = true;
        };

        // Open edit modal
        const openEditModal = (user) => {
            editingUser.value = user;
            form.value = {
                name: user.name,
                email: user.email,
                mobile_number: user.mobile_number || '',
                password: '',
                role_id: user.role_id,
                status: user.status
            };
            showModal.value = true;
        };

        // Close modal
        const closeModal = () => {
            showModal.value = false;
            editingUser.value = null;
            form.value = {
                name: '',
                email: '',
                mobile_number: '',
                password: '',
                role_id: '',
                status: 'active'
            };
        };

        // Save user
        const saveUser = async () => {
            saving.value = true;
            try {
                const payload = {
                    name: form.value.name,
                    email: form.value.email,
                    role_id: form.value.role_id,
                    status: form.value.status
                };

                if (form.value.password) {
                    payload.password = form.value.password;
                }

                if (editingUser.value) {
                    await axios.put(`/api/users/${editingUser.value.id}`, payload);
                    showSuccess('User updated successfully!');
                } else {
                    if (!payload.password) {
                        showError('Password is required for new users');
                        saving.value = false;
                        return;
                    }
                    await axios.post('/api/users', payload);
                    showSuccess('User created successfully!');
                }

                closeModal();
                fetchUsers(pagination.value?.current_page || 1);
            } catch (error) {
                console.error('Error saving user:', error);
                showError(error.response?.data?.message || 'Error saving user');
            } finally {
                saving.value = false;
            }
        };

        // Confirm delete
        const confirmDelete = (user) => {
            userToDelete.value = user;
            showDeleteModal.value = true;
        };

        // Close delete modal
        const closeDeleteModal = () => {
            showDeleteModal.value = false;
            userToDelete.value = null;
        };

        // Delete user
        const deleteUser = async () => {
            deleting.value = true;
            try {
                await axios.delete(`/api/users/${userToDelete.value.id}`);
                showSuccess('User deleted successfully!');
                closeDeleteModal();
                fetchUsers(pagination.value?.current_page || 1);
            } catch (error) {
                console.error('Error deleting user:', error);
                showError(error.response?.data?.message || 'Error deleting user');
            } finally {
                deleting.value = false;
            }
        };

        // Change page
        const changePage = (page) => {
            fetchUsers(page);
        };

        onMounted(() => {
            fetchRoles();
            fetchUsers();
        });

        return {
            users,
            roles,
            loading,
            saving,
            deleting,
            showModal,
            showDeleteModal,
            editingUser,
            userToDelete,
            form,
            searchQuery,
            selectedRole,
            pagination,
            fetchUsers,
            debouncedSearch,
            openCreateModal,
            openEditModal,
            closeModal,
            saveUser,
            confirmDelete,
            closeDeleteModal,
            deleteUser,
            changePage,
            hasPermission
        };
    }
}
</script>

<style scoped>
.users-page {
    max-width: 1400px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
}

.page-subtitle {
    font-size: 14px;
    color: #718096;
}

.filters-section {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
}

.search-box {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
}

.search-box svg {
    position: absolute;
    left: 12px;
    color: #95a5a6;
}

.search-box input {
    width: 100%;
    padding: 10px 12px 10px 40px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.role-filter {
    padding: 10px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    min-width: 200px;
    cursor: pointer;
}

.role-filter:focus {
    outline: none;
    border-color: #667eea;
}

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.loading-state,
.empty-state {
    padding: 40px;
    text-align: center;
    color: #718096;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f7fafc;
}

.data-table th {
    padding: 16px 24px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    padding: 16px 24px;
    border-top: 1px solid #e2e8f0;
    font-size: 14px;
    color: #2d3748;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 14px;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.badge-primary {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-active {
    background: rgba(67, 233, 123, 0.1);
    color: #43e97b;
}

.status-inactive {
    background: rgba(245, 101, 101, 0.1);
    color: #f56565;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.icon-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.2s;
}

.icon-btn:hover {
    background: #edf2f7;
    border-color: #667eea;
    color: #667eea;
}

.icon-btn-danger:hover {
    background: rgba(245, 101, 101, 0.1);
    border-color: #f56565;
    color: #f56565;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #5568d3;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    background: #ecf0f1;
    color: #2c3e50;
    border: 1px solid #bdc3c7;
}

.btn-secondary:hover {
    background: #d5dbdb;
}

.btn-danger {
    background: #f56565;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #e53e3e;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border-top: 1px solid #e2e8f0;
}

.page-btn {
    padding: 8px 16px;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
    border-color: #667eea;
    color: #667eea;
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    font-size: 14px;
    color: #718096;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-small {
    max-width: 400px;
}

.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    color: #718096;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #2c3e50;
}

.modal-body {
    padding: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #2d3748;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 16px;
    }
    
    .filters-section {
        flex-direction: column;
    }
    
    .table-container {
        overflow-x: scroll;
    }
}
</style>

<template>
    <div class="roles-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Roles & Permissions Management</h1>
                <p class="page-subtitle">Manage roles and their permissions</p>
            </div>
            <button 
                v-if="hasPermission('create_roles')" 
                class="btn btn-primary" 
                @click="openCreateModal"
            >
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Add Role
            </button>
        </div>

        <!-- Search -->
        <div class="filters-section">
            <div class="search-box">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                    <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Search roles..."
                    @input="debouncedSearch"
                />
            </div>
        </div>
        
        <div class="content-card">
            <div v-if="loading" class="loading-state">
                <p>Loading roles...</p>
            </div>
            
            <div v-else-if="roles.length === 0" class="empty-state">
                <p>No roles found</p>
            </div>
            
            <div v-else class="roles-grid">
                <div v-for="role in roles" :key="role.id" class="role-card">
                    <div class="role-header">
                        <div class="role-info">
                            <h3 class="role-name">{{ role.name }}</h3>
                            <p class="role-description">{{ role.description || 'No description' }}</p>
                        </div>
                        <div class="role-actions">
                            <button class="icon-btn" @click="openEditModal(role)" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M11.3333 2.00001C11.5084 1.82489 11.7163 1.68603 11.9447 1.59128C12.1731 1.49654 12.4173 1.44775 12.6667 1.44775C12.916 1.44775 13.1602 1.49654 13.3886 1.59128C13.617 1.68603 13.8249 1.82489 14 2.00001C14.1751 2.17513 14.314 2.38305 14.4087 2.61143C14.5035 2.83981 14.5523 3.08405 14.5523 3.33334C14.5523 3.58263 14.5035 3.82687 14.4087 4.05525C14.314 4.28363 14.1751 4.49155 14 4.66667L5 13.6667L1.33333 14.6667L2.33333 11L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <button class="icon-btn icon-btn-danger" @click="confirmDelete(role)" title="Delete">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M2 4H14M12.6667 4V13.3333C12.6667 13.687 12.5262 14.0263 12.2761 14.2764C12.026 14.5265 11.6867 14.6667 11.3333 14.6667H4.66667C4.31305 14.6667 3.97391 14.5265 3.72381 14.2764C3.47371 14.0263 3.33333 13.687 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2.31305 5.47371 1.97391 5.72381 1.72381C5.97391 1.47371 6.31305 1.33333 6.66667 1.33333H9.33333C9.68696 1.33333 10.0261 1.47371 10.2762 1.72381C10.5263 1.97391 10.6667 2.31305 10.6667 2.66667V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="role-stats">
                        <div class="stat-item">
                            <span class="stat-label">Users</span>
                            <span class="stat-value">{{ role.users_count || 0 }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Permissions</span>
                            <span class="stat-value">{{ role.permissions ? role.permissions.length : 0 }}</span>
                        </div>
                    </div>
                    <div class="role-permissions">
                        <h4>Permissions:</h4>
                        <div class="permissions-list">
                            <span 
                                v-for="permission in role.permissions" 
                                :key="permission.id" 
                                class="permission-badge"
                            >
                                {{ permission.name }}
                            </span>
                            <span v-if="!role.permissions || role.permissions.length === 0" class="no-permissions">
                                No permissions assigned
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content modal-large" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingRole ? 'Edit Role' : 'Create New Role' }}</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="saveRole" class="modal-body">
                    <div class="form-group">
                        <label>Role Name *</label>
                        <input v-model="form.name" type="text" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Slug *</label>
                        <input v-model="form.slug" type="text" class="form-control" required />
                        <small class="form-hint">Lowercase, use underscores (e.g., front_desk)</small>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Permissions *</label>
                        <div class="permissions-selector">
                            <div v-for="module in groupedPermissions" :key="module.name" class="permission-module">
                                <h4 class="module-title">{{ module.name }}</h4>
                                <div class="permissions-checkboxes">
                                    <label 
                                        v-for="permission in module.permissions" 
                                        :key="permission.id"
                                        class="checkbox-label"
                                    >
                                        <input 
                                            type="checkbox" 
                                            :value="permission.id"
                                            v-model="form.permissions"
                                        />
                                        <span>{{ permission.name }}</span>
                                        <small v-if="permission.description">{{ permission.description }}</small>
                                    </label>
                                </div>
                            </div>
                        </div>
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
                    <h2>Delete Role</h2>
                    <button class="modal-close" @click="closeDeleteModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ roleToDelete?.name }}</strong>? This action cannot be undone.</p>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
                        <button type="button" class="btn btn-danger" @click="deleteRole" :disabled="deleting">
                            {{ deleting ? 'Deleting...' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAlert } from '../../composables/useAlert';
import { usePermissions } from '../../composables/usePermissions';

export default {
    name: 'Roles',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const { hasPermission } = usePermissions();
        const roles = ref([]);
        const allPermissions = ref([]);
        const loading = ref(false);
        const saving = ref(false);
        const deleting = ref(false);
        const showModal = ref(false);
        const showDeleteModal = ref(false);
        const editingRole = ref(null);
        const roleToDelete = ref(null);
        const searchQuery = ref('');

        const form = ref({
            name: '',
            slug: '',
            description: '',
            permissions: []
        });

        // Group permissions by module
        const groupedPermissions = computed(() => {
            const grouped = {};
            allPermissions.value.forEach(perm => {
                if (!grouped[perm.module]) {
                    grouped[perm.module] = {
                        name: perm.module.charAt(0).toUpperCase() + perm.module.slice(1).replace('_', ' '),
                        permissions: []
                    };
                }
                grouped[perm.module].permissions.push(perm);
            });
            return Object.values(grouped);
        });

        // Fetch permissions
        const fetchPermissions = async () => {
            try {
                const response = await axios.get('/api/permissions');
                allPermissions.value = response.data;
            } catch (error) {
                console.error('Error fetching permissions:', error);
            }
        };

        // Fetch roles
        const fetchRoles = async () => {
            loading.value = true;
            try {
                const params = {};
                if (searchQuery.value) {
                    params.search = searchQuery.value;
                }
                const response = await axios.get('/api/roles', { params });
                
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
            } finally {
                loading.value = false;
            }
        };

        // Debounced search
        let searchTimeout = null;
        const debouncedSearch = () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchRoles();
            }, 500);
        };

        // Open create modal
        const openCreateModal = () => {
            editingRole.value = null;
            form.value = {
                name: '',
                slug: '',
                description: '',
                permissions: []
            };
            showModal.value = true;
        };

        // Open edit modal
        const openEditModal = (role) => {
            editingRole.value = role;
            form.value = {
                name: role.name,
                slug: role.slug,
                description: role.description || '',
                permissions: role.permissions ? role.permissions.map(p => p.id) : []
            };
            showModal.value = true;
        };

        // Close modal
        const closeModal = () => {
            showModal.value = false;
            editingRole.value = null;
            form.value = {
                name: '',
                slug: '',
                description: '',
                permissions: []
            };
        };

        // Save role
        const saveRole = async () => {
            saving.value = true;
            try {
                const payload = {
                    name: form.value.name,
                    slug: form.value.slug,
                    description: form.value.description,
                    permissions: form.value.permissions
                };

                if (editingRole.value) {
                    await axios.put(`/api/roles/${editingRole.value.id}`, payload);
                    showSuccess('Role updated successfully!');
                } else {
                    await axios.post('/api/roles', payload);
                    showSuccess('Role created successfully!');
                }

                closeModal();
                fetchRoles();
            } catch (error) {
                console.error('Error saving role:', error);
                showError(error.response?.data?.message || 'Error saving role');
            } finally {
                saving.value = false;
            }
        };

        // Confirm delete
        const confirmDelete = (role) => {
            roleToDelete.value = role;
            showDeleteModal.value = true;
        };

        // Close delete modal
        const closeDeleteModal = () => {
            showDeleteModal.value = false;
            roleToDelete.value = null;
        };

        // Delete role
        const deleteRole = async () => {
            deleting.value = true;
            try {
                await axios.delete(`/api/roles/${roleToDelete.value.id}`);
                showSuccess('Role deleted successfully!');
                closeDeleteModal();
                fetchRoles();
            } catch (error) {
                console.error('Error deleting role:', error);
                showError(error.response?.data?.message || 'Error deleting role');
            } finally {
                deleting.value = false;
            }
        };

        onMounted(() => {
            fetchPermissions();
            fetchRoles();
        });

        return {
            roles,
            allPermissions,
            loading,
            saving,
            deleting,
            showModal,
            showDeleteModal,
            editingRole,
            roleToDelete,
            form,
            searchQuery,
            groupedPermissions,
            fetchRoles,
            debouncedSearch,
            openCreateModal,
            openEditModal,
            closeModal,
            saveRole,
            confirmDelete,
            closeDeleteModal,
            deleteRole,
            hasPermission
        };
    }
}
</script>

<style scoped>
.roles-page {
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
    margin-bottom: 24px;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
    max-width: 400px;
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

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    padding: 24px;
}

.loading-state,
.empty-state {
    padding: 40px;
    text-align: center;
    color: #718096;
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.role-card {
    background: #f7f8fc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.2s;
}

.role-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.role-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.role-info {
    flex: 1;
}

.role-name {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
}

.role-description {
    font-size: 13px;
    color: #718096;
}

.role-actions {
    display: flex;
    gap: 8px;
}

.icon-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
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

.role-stats {
    display: flex;
    gap: 24px;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e2e8f0;
}

.stat-item {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 12px;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
}

.role-permissions h4 {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
}

.permissions-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.permission-badge {
    display: inline-block;
    padding: 4px 10px;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.no-permissions {
    color: #95a5a6;
    font-size: 13px;
    font-style: italic;
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

.modal-large {
    max-width: 800px;
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

.form-hint {
    display: block;
    font-size: 12px;
    color: #718096;
    margin-top: 4px;
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

.permissions-selector {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 16px;
}

.permission-module {
    margin-bottom: 24px;
}

.permission-module:last-child {
    margin-bottom: 0;
}

.module-title {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.permissions-checkboxes {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}

.checkbox-label:hover {
    background: #f7f8fc;
}

.checkbox-label input {
    margin-top: 2px;
    cursor: pointer;
}

.checkbox-label span {
    font-size: 14px;
    color: #2d3748;
    font-weight: 500;
}

.checkbox-label small {
    display: block;
    font-size: 12px;
    color: #718096;
    margin-top: 2px;
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
    
    .roles-grid {
        grid-template-columns: 1fr;
    }
}
</style>


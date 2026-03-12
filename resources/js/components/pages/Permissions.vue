<template>
    <div class="permissions-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Permissions Management</h1>
                <p class="page-subtitle">View and manage all system permissions</p>
            </div>
            <button 
                v-if="hasPermission('manage_system') || isSuperAdmin" 
                class="btn btn-primary" 
                @click="openCreateModal"
            >
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Add Permission
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="filters-section">
            <div class="search-box">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                    <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Search permissions..."
                    @input="filterPermissions"
                />
            </div>
            <select v-model="selectedModule" @change="filterPermissions" class="form-control module-filter">
                <option value="">All Modules</option>
                <option v-for="module in modules" :key="module" :value="module">{{ formatModuleName(module) }}</option>
            </select>
        </div>

        <div class="content-card">
            <div v-if="loading" class="loading-state">
                <p>Loading permissions...</p>
            </div>
            
            <div v-else-if="filteredPermissions.length === 0" class="empty-state">
                <p>No permissions found</p>
            </div>
            
            <div v-else>
                <!-- Grouped by Module -->
                <div v-for="module in groupedPermissions" :key="module.name" class="permission-module-section">
                    <div class="module-header">
                        <h3 class="module-title">{{ module.name }}</h3>
                        <div class="module-actions">
                            <label class="select-all-checkbox">
                                <input 
                                    type="checkbox" 
                                    :checked="isAllSelectedInModule(module.name)"
                                    @change="toggleModulePermissions(module.name, $event.target.checked)"
                                />
                                <span>Select All</span>
                            </label>
                        </div>
                    </div>
                    <div class="permissions-grid">
                        <div 
                            v-for="permission in module.permissions" 
                            :key="permission.id" 
                            class="permission-item"
                        >
                            <label class="permission-checkbox">
                                <input 
                                    type="checkbox" 
                                    :value="permission.id"
                                    v-model="selectedPermissions"
                                />
                                <div class="permission-info">
                                    <span class="permission-name">{{ permission.name }}</span>
                                    <span class="permission-slug">{{ permission.slug }}</span>
                                    <small v-if="permission.description" class="permission-description">
                                        {{ permission.description }}
                                    </small>
                                </div>
                            </label>
                            <div class="permission-actions">
                                <button 
                                    v-if="hasPermission('manage_system') || isSuperAdmin" 
                                    class="icon-btn" 
                                    @click="openEditModal(permission)" 
                                    title="Edit"
                                >
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M11.3333 2.00001C11.5084 1.82489 11.7163 1.68603 11.9447 1.59128C12.1731 1.49654 12.4173 1.44775 12.6667 1.44775C12.916 1.44775 13.1602 1.49654 13.3886 1.59128C13.617 1.68603 13.8249 1.82489 14 2.00001C14.1751 2.17513 14.314 2.38305 14.4087 2.61143C14.5035 2.83981 14.5523 3.08405 14.5523 3.33334C14.5523 3.58263 14.5035 3.82687 14.4087 4.05525C14.314 4.28363 14.1751 4.49155 14 4.66667L5 13.6667L1.33333 14.6667L2.33333 11L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <button 
                                    v-if="hasPermission('manage_system') || isSuperAdmin" 
                                    class="icon-btn icon-btn-danger" 
                                    @click="confirmDelete(permission)" 
                                    title="Delete"
                                >
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M2 4H14M12.6667 4V13.3333C12.6667 13.687 12.5262 14.0263 12.2761 14.2764C12.026 14.5265 11.6867 14.6667 11.3333 14.6667H4.66667C4.31305 14.6667 3.97391 14.5265 3.72381 14.2764C3.47371 14.0263 3.33333 13.687 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2.31305 5.47371 1.97391 5.72381 1.72381C5.97391 1.47371 6.31305 1.33333 6.66667 1.33333H9.33333C9.68696 1.33333 10.0261 1.47371 10.2762 1.72381C10.5263 1.97391 10.6667 2.31305 10.6667 2.66667V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingPermission ? 'Edit Permission' : 'Add Permission' }}</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="savePermission" class="modal-body">
                    <div class="form-group">
                        <label>Permission Name *</label>
                        <input 
                            type="text" 
                            v-model="form.name" 
                            class="form-control" 
                            placeholder="e.g., View Users"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label>Permission Slug *</label>
                        <input 
                            type="text" 
                            v-model="form.slug" 
                            class="form-control" 
                            placeholder="e.g., view_users"
                            required
                        />
                        <span class="form-hint">Used in code to check permissions (lowercase, underscores)</span>
                    </div>
                    <div class="form-group">
                        <label>Module *</label>
                        <input 
                            type="text" 
                            v-model="form.module" 
                            class="form-control" 
                            placeholder="e.g., users"
                            required
                        />
                        <span class="form-hint">Group permissions by module</span>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea 
                            v-model="form.description" 
                            class="form-control" 
                            rows="3"
                            placeholder="Describe what this permission allows..."
                        ></textarea>
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
            <div class="modal-content modal-sm" @click.stop>
                <div class="modal-header">
                    <h2>Delete Permission</h2>
                    <button class="modal-close" @click="closeDeleteModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the permission <strong>{{ permissionToDelete?.name }}</strong>?</p>
                    <p class="text-warning">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="deletePermission" :disabled="deleting">
                        {{ deleting ? 'Deleting...' : 'Delete' }}
                    </button>
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
    name: 'Permissions',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const { hasPermission, isSuperAdmin } = usePermissions();
        const permissions = ref([]);
        const loading = ref(false);
        const saving = ref(false);
        const deleting = ref(false);
        const showModal = ref(false);
        const showDeleteModal = ref(false);
        const editingPermission = ref(null);
        const permissionToDelete = ref(null);
        const searchQuery = ref('');
        const selectedModule = ref('');
        const selectedPermissions = ref([]);

        const form = ref({
            name: '',
            slug: '',
            module: '',
            description: ''
        });

        // Get unique modules
        const modules = computed(() => {
            const moduleSet = new Set();
            permissions.value.forEach(perm => {
                if (perm.module) {
                    moduleSet.add(perm.module);
                }
            });
            return Array.from(moduleSet).sort();
        });

        // Filter permissions based on search and module
        const filteredPermissions = computed(() => {
            let filtered = permissions.value;

            if (searchQuery.value) {
                const query = searchQuery.value.toLowerCase();
                filtered = filtered.filter(perm => 
                    perm.name.toLowerCase().includes(query) ||
                    perm.slug.toLowerCase().includes(query) ||
                    (perm.description && perm.description.toLowerCase().includes(query)) ||
                    perm.module.toLowerCase().includes(query)
                );
            }

            if (selectedModule.value) {
                filtered = filtered.filter(perm => perm.module === selectedModule.value);
            }

            return filtered;
        });

        // Group permissions by module
        const groupedPermissions = computed(() => {
            const grouped = {};
            filteredPermissions.value.forEach(perm => {
                const moduleName = perm.module || 'Other';
                if (!grouped[moduleName]) {
                    grouped[moduleName] = {
                        name: formatModuleName(moduleName),
                        permissions: []
                    };
                }
                grouped[moduleName].permissions.push(perm);
            });
            return Object.values(grouped);
        });

        const formatModuleName = (module) => {
            return module
                .split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        };

        const isAllSelectedInModule = (moduleName) => {
            const modulePerms = filteredPermissions.value.filter(p => 
                (p.module || 'Other') === moduleName
            );
            if (modulePerms.length === 0) return false;
            return modulePerms.every(perm => selectedPermissions.value.includes(perm.id));
        };

        const toggleModulePermissions = (moduleName, checked) => {
            const modulePerms = filteredPermissions.value.filter(p => 
                (p.module || 'Other') === moduleName
            );
            modulePerms.forEach(perm => {
                if (checked && !selectedPermissions.value.includes(perm.id)) {
                    selectedPermissions.value.push(perm.id);
                } else if (!checked) {
                    const index = selectedPermissions.value.indexOf(perm.id);
                    if (index > -1) {
                        selectedPermissions.value.splice(index, 1);
                    }
                }
            });
        };

        const filterPermissions = () => {
            // Reactive filtering is handled by computed properties
        };

        const fetchPermissions = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/api/permissions');
                permissions.value = response.data || [];
            } catch (error) {
                console.error('Error fetching permissions:', error);
                showError('Error loading permissions');
            } finally {
                loading.value = false;
            }
        };

        const openCreateModal = () => {
            editingPermission.value = null;
            form.value = {
                name: '',
                slug: '',
                module: '',
                description: ''
            };
            showModal.value = true;
        };

        const openEditModal = (permission) => {
            editingPermission.value = permission;
            form.value = {
                name: permission.name,
                slug: permission.slug,
                module: permission.module,
                description: permission.description || ''
            };
            showModal.value = true;
        };

        const closeModal = () => {
            showModal.value = false;
            editingPermission.value = null;
            form.value = {
                name: '',
                slug: '',
                module: '',
                description: ''
            };
        };

        const savePermission = async () => {
            saving.value = true;
            try {
                if (editingPermission.value) {
                    const response = await axios.put(`/api/permissions/${editingPermission.value.id}`, form.value);
                    if (response.data) {
                        showSuccess('Permission updated successfully!');
                        closeModal();
                        fetchPermissions();
                    }
                } else {
                    const response = await axios.post('/api/permissions', form.value);
                    if (response.data) {
                        showSuccess('Permission created successfully!');
                        closeModal();
                        fetchPermissions();
                    }
                }
            } catch (error) {
                console.error('Error saving permission:', error);
                showError(error.response?.data?.message || 'Error saving permission');
            } finally {
                saving.value = false;
            }
        };

        const confirmDelete = (permission) => {
            permissionToDelete.value = permission;
            showDeleteModal.value = true;
        };

        const closeDeleteModal = () => {
            showDeleteModal.value = false;
            permissionToDelete.value = null;
        };

        const deletePermission = async () => {
            if (!permissionToDelete.value) return;
            
            deleting.value = true;
            try {
                await axios.delete(`/api/permissions/${permissionToDelete.value.id}`);
                showSuccess('Permission deleted successfully!');
                closeDeleteModal();
                fetchPermissions();
            } catch (error) {
                console.error('Error deleting permission:', error);
                showError(error.response?.data?.message || 'Error deleting permission');
            } finally {
                deleting.value = false;
            }
        };

        onMounted(() => {
            fetchPermissions();
        });

        return {
            permissions,
            loading,
            saving,
            deleting,
            showModal,
            showDeleteModal,
            editingPermission,
            permissionToDelete,
            searchQuery,
            selectedModule,
            selectedPermissions,
            form,
            modules,
            filteredPermissions,
            groupedPermissions,
            formatModuleName,
            isAllSelectedInModule,
            toggleModulePermissions,
            filterPermissions,
            fetchPermissions,
            openCreateModal,
            openEditModal,
            closeModal,
            savePermission,
            confirmDelete,
            closeDeleteModal,
            deletePermission,
            hasPermission,
            isSuperAdmin
        };
    }
}
</script>

<style scoped>
.permissions-page {
    padding: 24px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
}

.page-title {
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 4px 0;
}

.page-subtitle {
    font-size: 14px;
    color: #718096;
    margin: 0;
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
    gap: 10px;
    padding: 10px 16px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.search-box svg {
    color: #a0aec0;
    flex-shrink: 0;
}

.search-box input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 14px;
    color: #2d3748;
}

.module-filter {
    min-width: 200px;
}

.content-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 24px;
}

.loading-state,
.empty-state {
    text-align: center;
    padding: 48px;
    color: #718096;
}

.permission-module-section {
    margin-bottom: 32px;
}

.permission-module-section:last-child {
    margin-bottom: 0;
}

.module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
}

.module-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.select-all-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
    color: #4a5568;
    font-weight: 500;
}

.select-all-checkbox input {
    cursor: pointer;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 12px;
}

.permission-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    transition: all 0.2s;
}

.permission-item:hover {
    background: #f7f8fc;
    border-color: #cbd5e0;
}

.permission-checkbox {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    flex: 1;
    cursor: pointer;
}

.permission-checkbox input {
    margin-top: 2px;
    cursor: pointer;
}

.permission-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.permission-name {
    font-size: 14px;
    font-weight: 500;
    color: #2d3748;
}

.permission-slug {
    font-size: 12px;
    color: #718096;
    font-family: 'Courier New', monospace;
}

.permission-description {
    font-size: 12px;
    color: #a0aec0;
    margin-top: 4px;
}

.permission-actions {
    display: flex;
    gap: 8px;
}

.icon-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #718096;
    border-radius: 4px;
    transition: all 0.2s;
}

.icon-btn:hover {
    background: #edf2f7;
    color: #4a5568;
}

.icon-btn-danger:hover {
    background: #fed7d7;
    color: #c53030;
}

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
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-sm {
    max-width: 400px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
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

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    margin-top: 24px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #5568d3;
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover:not(:disabled) {
    background: #cbd5e0;
}

.btn-danger {
    background: #e53e3e;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #c53030;
}

.text-warning {
    color: #d69e2e;
    font-size: 14px;
    margin-top: 8px;
}
</style>

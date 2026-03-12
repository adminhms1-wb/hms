<template>
    <div class="role-based-permissions-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Role-Based Permissions</h1>
                <p class="page-subtitle">Manage roles and their permissions</p>
            </div>
            <button class="btn btn-primary" @click="openCreateRoleModal">Create Role</button>
        </div>

        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading...</div>
            <div v-else class="roles-grid">
                <div v-for="role in roles" :key="role?.id || Math.random()" class="role-card">
                    <div class="role-header">
                        <h3>{{ role?.name || '—' }}</h3>
                        <div class="role-actions">
                            <button class="btn-icon" @click="editRole(role)" title="Edit" :disabled="!role?.id">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                    <path d="M11 3H4C3.46957 3 2.96086 3.21071 2.58579 3.58579C2.21071 3.96086 2 4.46957 2 5V16C2 16.5304 2.21071 17.0391 2.58579 17.4142C2.96086 17.7893 3.46957 18 4 18H15C15.5304 18 16.0391 17.7893 16.4142 17.4142C16.7893 17.0391 17 16.5304 17 16V9M14 1L19 6M19 6H14M19 6V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <button class="btn-icon" @click="deleteRole(role?.id)" title="Delete" v-if="role?.slug !== 'super_admin'" :disabled="!role?.id">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                    <path d="M3 5H17M8 5V3C8 2.44772 8.44772 2 9 2H11C11.5523 2 12 2.44772 12 3V5M15 5V17C15 18.1046 14.1046 19 13 19H7C5.89543 19 5 18.1046 5 17V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="role-description">{{ role?.description || 'No description' }}</p>
                    <div class="permissions-list">
                        <div v-for="permission in (role?.permissions || [])" :key="permission?.id || Math.random()" class="permission-tag">
                            {{ permission?.name || '—' }}
                        </div>
                        <div v-if="!role?.permissions || role.permissions.length === 0" class="no-permissions">No permissions assigned</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h3>{{ editingRole ? 'Edit Role' : 'Create Role' }}</h3>
                    <button class="btn-icon" @click="closeModal">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Role Name</label>
                        <input type="text" v-model="roleForm.name" class="form-input" />
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea v-model="roleForm.description" class="form-input" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Permissions</label>
                        <div class="permissions-grid">
                            <label v-for="permission in allPermissions" :key="permission?.id || Math.random()" class="permission-checkbox">
                                <input type="checkbox" :value="permission?.id" v-model="roleForm.permissions" :disabled="!permission?.id" />
                                {{ permission?.name || '—' }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="closeModal">Cancel</button>
                    <button class="btn btn-primary" @click="saveRole" :disabled="saving">
                        {{ saving ? 'Saving...' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAlert } from '@/composables/useAlert';

const { success: showSuccess, error: showError } = useAlert();

const roles = ref([]);
const allPermissions = ref([]);
const loading = ref(false);
const saving = ref(false);
const showModal = ref(false);
const editingRole = ref(null);
const roleForm = ref({
    name: '',
    description: '',
    permissions: []
});

const loadRoles = async () => {
    loading.value = true;
    try {
        const response = await window.axios.get('/api/roles');
        // Filter out null entries and ensure all roles have an id
        roles.value = (response.data || []).filter(r => r != null && r.id != null);
    } catch (error) {
        if (error.response && error.response.status === 403) {
            showError('You do not have permission to view roles. Please contact your administrator.');
        } else {
            showError('Failed to load roles');
        }
        console.error('Error loading roles:', error);
        roles.value = [];
    } finally {
        loading.value = false;
    }
};

const loadPermissions = async () => {
    try {
        const response = await window.axios.get('/api/permissions');
        // Filter out null entries and ensure all permissions have an id
        allPermissions.value = (response.data || []).filter(p => p != null && p.id != null);
    } catch (error) {
        if (error.response && error.response.status === 403) {
            console.error('Permission denied to view permissions:', error);
            // Don't show error to user for permissions as it's loaded in background
        } else {
            console.error('Error loading permissions:', error);
        }
        allPermissions.value = [];
    }
};

const openCreateRoleModal = () => {
    editingRole.value = null;
    roleForm.value = {
        name: '',
        description: '',
        permissions: []
    };
    showModal.value = true;
};

const editRole = (role) => {
    if (!role || !role.id) return;
    editingRole.value = role;
    roleForm.value = {
        name: role.name || '',
        description: role.description || '',
        permissions: (role.permissions || []).filter(p => p != null && p.id != null).map(p => p.id)
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingRole.value = null;
    roleForm.value = {
        name: '',
        description: '',
        permissions: []
    };
};

const saveRole = async () => {
    if (!roleForm.value.name) {
        showError('Role name is required');
        return;
    }

    saving.value = true;
    try {
        if (editingRole.value) {
            await window.axios.put(`/api/roles/${editingRole.value.id}`, roleForm.value);
            showSuccess('Role updated successfully');
        } else {
            await window.axios.post('/api/roles', roleForm.value);
            showSuccess('Role created successfully');
        }
        closeModal();
        loadRoles();
    } catch (error) {
        showError('Failed to save role');
        console.error('Error saving role:', error);
    } finally {
        saving.value = false;
    }
};

const deleteRole = async (id) => {
    if (!confirm('Are you sure you want to delete this role?')) return;
    
    try {
        await window.axios.delete(`/api/roles/${id}`);
        showSuccess('Role deleted successfully');
        loadRoles();
    } catch (error) {
        showError('Failed to delete role');
        console.error('Error deleting role:', error);
    }
};

onMounted(() => {
    loadRoles();
    loadPermissions();
});
</script>

<style scoped>
.role-based-permissions-page { padding: 24px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-primary { background: #3498db; color: white; }
.btn-primary:hover:not(:disabled) { background: #2980b9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.roles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
.role-card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; }
.role-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.role-header h3 { margin: 0; font-size: 18px; }
.role-actions { display: flex; gap: 8px; }
.btn-icon { background: none; border: none; cursor: pointer; padding: 4px; color: #666; }
.btn-icon:hover { color: #333; }
.role-description { color: #666; margin-bottom: 12px; font-size: 14px; }
.permissions-list { display: flex; flex-wrap: wrap; gap: 6px; }
.permission-tag { background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.no-permissions { color: #999; font-size: 12px; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: white; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #e0e0e0; }
.modal-header h3 { margin: 0; }
.modal-body { padding: 20px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
.form-input { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.permissions-grid { max-height: 300px; overflow-y: auto; border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px; }
.permission-checkbox { display: block; padding: 8px; cursor: pointer; }
.permission-checkbox:hover { background: #f8f9fa; }
.permission-checkbox input { margin-right: 8px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding: 20px; border-top: 1px solid #e0e0e0; }
.btn-secondary { background: #95a5a6; color: white; }
.btn-secondary:hover { background: #7f8c8d; }
.loading-state { text-align: center; padding: 40px; color: #999; }
</style>

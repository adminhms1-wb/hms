<template>
    <div class="role-assignment-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Role Assignment</h1>
                <p class="page-subtitle">Assign and manage roles for staff members</p>
            </div>
        </div>

        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading...</div>
            <div v-else>
                <div class="inventory-table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Current Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id">
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.role?.name || 'No Role' }}</td>
                                <td>
                                    <select v-model="user.role_id" @change="assignRole(user)" class="form-control" style="width: 200px;">
                                        <option value="">Select Role</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useAlert } from '../../composables/useAlert';

export default {
    name: 'RoleAssignment',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const loading = ref(false);
        const users = ref([]);
        const roles = ref([]);

        const fetchData = async () => {
            loading.value = true;
            try {
                const response = await window.axios.get('/api/role-assignment');
                users.value = response.data.users || [];
                roles.value = response.data.roles || [];
            } catch (error) {
                showError('Failed to load data');
            } finally {
                loading.value = false;
            }
        };

        const assignRole = async (user) => {
            try {
                await window.axios.post(`/api/role-assignment/${user.id}`, { role_id: user.role_id });
                showSuccess('Role assigned successfully!');
                window.location.reload();
            } catch (error) {
                showError('Failed to assign role');
            }
        };

        onMounted(() => fetchData());

        return { loading, users, roles, assignRole };
    }
}
</script>

<style scoped>
.role-assignment-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #718096; margin: 0; }
.content-card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px; }
.loading-state { text-align: center; padding: 48px; }
.inventory-table-wrapper { overflow-x: auto; }
.inventory-table { width: 100%; border-collapse: collapse; }
.inventory-table th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
.inventory-table td { padding: 16px; border-bottom: 1px solid #e2e8f0; }
.form-control { padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px; }
</style>

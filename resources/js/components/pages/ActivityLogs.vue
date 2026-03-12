<template>
    <div class="activity-logs-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Activity Logs</h1>
                <p class="page-subtitle">View system activity and user actions</p>
            </div>
        </div>

        <div class="filters-section">
            <div class="filter-group">
                <label>User</label>
                <select v-model="filters.user_id" class="filter-input">
                    <option value="">All Users</option>
                    <option v-for="user in users" :key="user?.id || Math.random()" :value="user?.id">{{ user?.name || '—' }}</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Action</label>
                <select v-model="filters.action" class="filter-input">
                    <option value="">All Actions</option>
                    <option value="create">Create</option>
                    <option value="update">Update</option>
                    <option value="delete">Delete</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                </select>
            </div>
            <div class="filter-group">
                <label>From Date</label>
                <input type="date" v-model="filters.from_date" class="filter-input" />
            </div>
            <div class="filter-group">
                <label>To Date</label>
                <input type="date" v-model="filters.to_date" class="filter-input" />
            </div>
            <button class="btn btn-primary" @click="loadLogs">Filter</button>
            <button class="btn btn-secondary" @click="clearFilters">Clear</button>
        </div>

        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading...</div>
            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                        <tr v-for="log in logs" :key="log?.id || Math.random()">
                            <td>{{ formatDateTime(log?.created_at) }}</td>
                            <td>{{ log?.user?.name || 'System' }}</td>
                            <td>
                                <span :class="['action-badge', log?.action || '']">{{ log?.action || '—' }}</span>
                            </td>
                            <td>{{ log?.module || '—' }}</td>
                            <td>{{ log?.description || log?.action || '—' }}</td>
                            <td>{{ log?.ip_address || '—' }}</td>
                        </tr>
                    <tr v-if="logs.length === 0">
                        <td colspan="6" class="empty-state">No activity logs found</td>
                    </tr>
                </tbody>
            </table>

            <div class="pagination" v-if="pagination.total > pagination.per_page">
                <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1">Previous</button>
                <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
                <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page">Next</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAlert } from '@/composables/useAlert';

const { success: showSuccess, error: showError } = useAlert();

const logs = ref([]);
const users = ref([]);
const loading = ref(false);
const filters = ref({
    user_id: '',
    action: '',
    from_date: '',
    to_date: ''
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0
});

const loadUsers = async () => {
    try {
        const response = await window.axios.get('/api/users');
        // Handle paginated response (data property) or direct array
        let usersData = response.data;
        if (usersData && usersData.data) {
            usersData = usersData.data; // Paginated response
        }
        // Ensure it's an array before filtering
        if (!Array.isArray(usersData)) {
            usersData = [];
        }
        // Filter out null entries and ensure all users have an id
        users.value = usersData.filter(u => u != null && u.id != null);
    } catch (error) {
        console.error('Error loading users:', error);
        users.value = [];
    }
};

const loadLogs = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            page,
            ...filters.value
        };
        // Remove empty filters
        Object.keys(params).forEach(key => {
            if (params[key] === '') delete params[key];
        });
        const response = await window.axios.get('/api/activity-logs', { params });
        // Filter out null entries and ensure all logs have an id
        const logsData = response.data.data || response.data || [];
        logs.value = logsData.filter(l => l != null && l.id != null);
        pagination.value = {
            current_page: response.data.current_page || 1,
            last_page: response.data.last_page || 1,
            per_page: response.data.per_page || 20,
            total: response.data.total || 0
        };
    } catch (error) {
        showError('Failed to load activity logs');
        console.error('Error loading logs:', error);
        logs.value = [];
    } finally {
        loading.value = false;
    }
};

const clearFilters = () => {
    filters.value = {
        user_id: '',
        action: '',
        from_date: '',
        to_date: ''
    };
    loadLogs();
};

const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        loadLogs(page);
    }
};

const formatDateTime = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleString();
};

onMounted(() => {
    loadUsers();
    loadLogs();
});
</script>

<style scoped>
.activity-logs-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.filters-section { display: flex; gap: 12px; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; }
.filter-group { display: flex; flex-direction: column; gap: 4px; }
.filter-group label { font-size: 14px; font-weight: 500; }
.filter-input { padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-primary { background: #3498db; color: white; }
.btn-primary:hover { background: #2980b9; }
.btn-secondary { background: #95a5a6; color: white; }
.btn-secondary:hover { background: #7f8c8d; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
.data-table th { background: #f8f9fa; font-weight: 600; }
.action-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
.action-badge.create { background: #d4edda; color: #155724; }
.action-badge.update { background: #d1ecf1; color: #0c5460; }
.action-badge.delete { background: #f8d7da; color: #721c24; }
.action-badge.login { background: #d4edda; color: #155724; }
.action-badge.logout { background: #fff3cd; color: #856404; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 16px; margin-top: 24px; }
.pagination button { padding: 8px 16px; border: 1px solid #e0e0e0; border-radius: 6px; background: white; cursor: pointer; }
.pagination button:hover:not(:disabled) { background: #f8f9fa; }
.pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
.loading-state, .empty-state { text-align: center; padding: 40px; color: #999; }
</style>

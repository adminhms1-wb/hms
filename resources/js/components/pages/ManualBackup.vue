<template>
    <div class="manual-backup-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Manual Backup</h1>
                <p class="page-subtitle">Create manual database backups</p>
            </div>
        </div>

        <div class="content-card">
            <div class="backup-section">
                <h3>Create Backup</h3>
                <div class="form-group">
                    <label>Backup Type</label>
                    <select v-model="backupType" class="form-input">
                        <option value="full">Full Database Backup</option>
                        <option value="tables">Selected Tables</option>
                    </select>
                </div>
                <div class="form-group" v-if="backupType === 'tables'">
                    <label>Select Tables</label>
                    <div class="tables-list">
                        <label v-for="table in tables" :key="table" class="table-checkbox">
                            <input type="checkbox" :value="table" v-model="selectedTables" />
                            {{ table }}
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Backup Name (Optional)</label>
                    <input type="text" v-model="backupName" class="form-input" placeholder="backup-2026-01-15" />
                </div>
                <button class="btn btn-primary" @click="createBackup" :disabled="creating">
                    {{ creating ? 'Creating Backup...' : 'Create Backup' }}
                </button>
            </div>

            <div class="backup-history-section">
                <h3>Backup History</h3>
                <div v-if="loading" class="loading-state">Loading...</div>
                <table v-else class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="backup in backups" :key="backup?.id || Math.random()">
                            <td>{{ backup?.name || 'Manual Backup' }}</td>
                            <td>{{ formatDateTime(backup?.created_at) }}</td>
                            <td>{{ backup?.type || '—' }}</td>
                            <td>{{ formatSize(backup?.size) }}</td>
                            <td>
                                <span :class="['status-badge', backup?.status || 'pending']">{{ backup?.status || 'pending' }}</span>
                            </td>
                            <td>
                                <button class="btn-icon" @click="downloadBackup(backup?.id)" title="Download" :disabled="!backup?.id">
                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                        <path d="M10 2V12M10 12L6 8M10 12L14 8M2 14V16C2 17.1046 2.89543 18 4 18H16C17.1046 18 18 17.1046 18 16V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <button class="btn-icon" @click="deleteBackup(backup?.id)" title="Delete" :disabled="!backup?.id">
                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                        <path d="M3 5H17M8 5V3C8 2.44772 8.44772 2 9 2H11C11.5523 2 12 2.44772 12 3V5M15 5V17C15 18.1046 14.1046 19 13 19H7C5.89543 19 5 18.1046 5 17V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="backups.length === 0">
                            <td colspan="6" class="empty-state">No backups found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAlert } from '@/composables/useAlert';

const { success: showSuccess, error: showError } = useAlert();

const backupType = ref('full');
const selectedTables = ref([]);
const backupName = ref('');
const backups = ref([]);
const tables = ref([]);
const loading = ref(false);
const creating = ref(false);

const loadBackups = async () => {
    loading.value = true;
    try {
        const response = await window.axios.get('/api/backups/manual');
        // Filter out null entries and ensure all backups have an id
        backups.value = (response.data.backups || []).filter(b => b != null && b.id != null);
    } catch (error) {
        console.error('Error loading backups:', error);
        backups.value = [];
    } finally {
        loading.value = false;
    }
};

const loadTables = async () => {
    try {
        const response = await window.axios.get('/api/backups/tables');
        tables.value = response.data.tables || [];
    } catch (error) {
        console.error('Error loading tables:', error);
    }
};

const createBackup = async () => {
    if (backupType.value === 'tables' && selectedTables.value.length === 0) {
        showError('Please select at least one table');
        return;
    }

    creating.value = true;
    try {
        const payload = {
            type: backupType.value,
            name: backupName.value || null,
            tables: backupType.value === 'tables' ? selectedTables.value : null
        };
        await window.axios.post('/api/backups/manual', payload);
        showSuccess('Backup created successfully');
        backupName.value = '';
        selectedTables.value = [];
        loadBackups();
    } catch (error) {
        showError('Failed to create backup');
        console.error('Error creating backup:', error);
    } finally {
        creating.value = false;
    }
};

const downloadBackup = async (id) => {
    try {
        const response = await window.axios.get(`/api/backups/${id}/download`, {
            responseType: 'blob'
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `backup-${id}.sql`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        showSuccess('Backup downloaded successfully');
    } catch (error) {
        showError('Failed to download backup');
        console.error('Error downloading backup:', error);
    }
};

const deleteBackup = async (id) => {
    if (!confirm('Are you sure you want to delete this backup?')) return;
    
    try {
        await window.axios.delete(`/api/backups/${id}`);
        showSuccess('Backup deleted successfully');
        loadBackups();
    } catch (error) {
        showError('Failed to delete backup');
        console.error('Error deleting backup:', error);
    }
};

const formatDateTime = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleString();
};

const formatSize = (bytes) => {
    if (!bytes) return '—';
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 Bytes';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    loadBackups();
    loadTables();
});
</script>

<style scoped>
.manual-backup-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.backup-section { margin-bottom: 32px; }
.backup-section h3 { font-size: 18px; margin-bottom: 16px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
.form-input { width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.tables-list { max-height: 200px; overflow-y: auto; border: 1px solid #e0e0e0; border-radius: 6px; padding: 8px; }
.table-checkbox { display: block; padding: 8px; cursor: pointer; }
.table-checkbox:hover { background: #f8f9fa; }
.table-checkbox input { margin-right: 8px; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-primary { background: #3498db; color: white; }
.btn-primary:hover:not(:disabled) { background: #2980b9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.backup-history-section h3 { font-size: 18px; margin-bottom: 16px; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
.data-table th { background: #f8f9fa; font-weight: 600; }
.status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
.status-badge.success { background: #d4edda; color: #155724; }
.status-badge.failed { background: #f8d7da; color: #721c24; }
.status-badge.pending { background: #fff3cd; color: #856404; }
.btn-icon { background: none; border: none; cursor: pointer; padding: 4px; color: #666; }
.btn-icon:hover { color: #333; }
.loading-state, .empty-state { text-align: center; padding: 40px; color: #999; }
</style>

<template>
    <div class="performance-logs-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Performance Logs</h1>
                <p class="page-subtitle">Track and review staff performance</p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">Add Log</button>
        </div>

        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading...</div>
            <div v-else>
                <div class="inventory-table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Review Date</th>
                                <th>Category</th>
                                <th>Title</th>
                                <th>Rating</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in logs" :key="log.id">
                                <td>{{ log.staff?.name || '—' }}</td>
                                <td>{{ formatDate(log.review_date) }}</td>
                                <td>{{ log.category }}</td>
                                <td>{{ log.title }}</td>
                                <td>{{ log.rating }}/5</td>
                                <td>
                                    <span :class="['status-badge', getTypeClass(log.type)]">{{ log.type }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-btn" @click="openEditModal(log)">Edit</button>
                                        <button class="icon-btn icon-btn-danger" @click="deleteLog(log.id)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingLog ? 'Edit Log' : 'Add Performance Log' }}</h2>
                    <button class="modal-close" @click="closeModal">×</button>
                </div>
                <form @submit.prevent="saveLog" class="modal-body">
                    <div class="form-group">
                        <label>Staff *</label>
                        <select v-model="form.staff_id" class="form-control" required>
                            <option value="">Select Staff</option>
                            <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Review Date *</label>
                            <input type="date" v-model="form.review_date" class="form-control" required />
                        </div>
                        <div class="form-group">
                            <label>Category *</label>
                            <select v-model="form.category" class="form-control" required>
                                <option value="attendance">Attendance</option>
                                <option value="task_completion">Task Completion</option>
                                <option value="customer_service">Customer Service</option>
                                <option value="teamwork">Teamwork</option>
                                <option value="punctuality">Punctuality</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" v-model="form.title" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea v-model="form.description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Rating (1-5) *</label>
                            <input type="number" v-model="form.rating" class="form-control" min="1" max="5" required />
                        </div>
                        <div class="form-group">
                            <label>Type *</label>
                            <select v-model="form.type" class="form-control" required>
                                <option value="positive">Positive</option>
                                <option value="negative">Negative</option>
                                <option value="neutral">Neutral</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Action Items</label>
                        <textarea v-model="form.action_items" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useAlert } from '../../composables/useAlert';
import { formatDate, formatDateForInput } from '../../utils/dateFormatter';

export default {
    name: 'PerformanceLogs',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const loading = ref(false);
        const saving = ref(false);
        const logs = ref([]);
        const staff = ref([]);
        const showModal = ref(false);
        const editingLog = ref(null);
        const form = ref({ staff_id: '', reviewed_by: '', review_date: '', category: 'other', title: '', description: '', rating: 3, type: 'neutral', action_items: '' });

        const getTypeClass = (type) => {
            const classes = { positive: 'status-in', negative: 'status-out', neutral: 'status-low' };
            return classes[type] || '';
        };

        const fetchData = async () => {
            loading.value = true;
            try {
                const response = await window.axios.get('/api/performance-logs');
                logs.value = response.data.logs || [];
                staff.value = response.data.staff || [];
            } catch (error) {
                showError('Failed to load data');
            } finally {
                loading.value = false;
            }
        };

        const openCreateModal = () => {
            editingLog.value = null;
            form.value = { staff_id: '', reviewed_by: '', review_date: '', category: 'other', title: '', description: '', rating: 3, type: 'neutral', action_items: '' };
            showModal.value = true;
        };

        const openEditModal = (log) => {
            editingLog.value = log;
            // Format the date properly for the input field
            form.value = {
                ...log,
                review_date: formatDateForInput(log.review_date)
            };
            showModal.value = true;
        };

        const closeModal = () => {
            showModal.value = false;
            editingLog.value = null;
        };

        const saveLog = async () => {
            saving.value = true;
            try {
                // Ensure date is in correct format (YYYY-MM-DD)
                const dataToSend = {
                    ...form.value,
                    review_date: form.value.review_date ? formatDateForInput(form.value.review_date) : null
                };
                
                // Remove empty strings and convert to null for optional fields
                if (!dataToSend.reviewed_by) dataToSend.reviewed_by = null;
                if (!dataToSend.action_items) dataToSend.action_items = null;

                if (editingLog.value) {
                    await window.axios.post(`/api/performance-logs/${editingLog.value.id}`, dataToSend);
                    showSuccess('Log updated!');
                } else {
                    await window.axios.post('/api/performance-logs', dataToSend);
                    showSuccess('Log created!');
                }
                window.location.reload();
            } catch (error) {
                console.error('Save error:', error);
                showError(error.response?.data?.message || 'Failed to save log');
                saving.value = false;
            }
        };

        const deleteLog = async (id) => {
            if (!confirm('Delete this log?')) return;
            try {
                await window.axios.post(`/api/performance-logs/${id}/delete`);
                showSuccess('Log deleted!');
                window.location.reload();
            } catch (error) {
                showError('Failed to delete log');
            }
        };

        onMounted(() => fetchData());

        return { loading, saving, logs, staff, showModal, editingLog, form, getTypeClass, formatDate, openCreateModal, openEditModal, closeModal, saveLog, deleteLog };
    }
}
</script>

<style scoped>
.performance-logs-page { padding: 24px; }
.page-header { display: flex; justify-content: space-between; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #718096; margin: 0; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
.btn-primary { background: #667eea; color: white; }
.btn-secondary { background: #e2e8f0; }
.content-card { background: white; border-radius: 8px; padding: 24px; }
.loading-state { text-align: center; padding: 48px; }
.inventory-table-wrapper { overflow-x: auto; }
.inventory-table { width: 100%; border-collapse: collapse; }
.inventory-table th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
.inventory-table td { padding: 16px; border-bottom: 1px solid #e2e8f0; }
.status-badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.status-in { background: #c6f6d5; color: #22543d; }
.status-out { background: #fed7d7; color: #742a2a; }
.status-low { background: #feebc8; color: #7c2d12; }
.action-buttons { display: flex; gap: 8px; }
.icon-btn { background: none; border: none; cursor: pointer; padding: 6px; }
.icon-btn-danger:hover { background: #fed7d7; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: white; border-radius: 8px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
.modal-header { display: flex; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #e2e8f0; }
.modal-close { background: none; border: none; cursor: pointer; font-size: 24px; }
.modal-body { padding: 24px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; }
.form-control { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid #e2e8f0; margin-top: 24px; }
</style>

<template>
    <div class="shift-scheduling-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Shift Scheduling</h1>
                <p class="page-subtitle">Schedule shifts for staff members</p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">Add Schedule</button>
        </div>

        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading...</div>
            <div v-else>
                <div class="inventory-table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Shift</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="schedule in schedules" :key="schedule.id">
                                <td>{{ schedule.staff?.name || '—' }}</td>
                                <td>{{ schedule.shift?.name || '—' }}</td>
                                <td>{{ formatDate(schedule.schedule_date) }}</td>
                                <td>
                                    <span :class="['status-badge', getStatusClass(schedule.status)]">{{ schedule.status }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-btn" @click="openEditModal(schedule)">Edit</button>
                                        <button class="icon-btn icon-btn-danger" @click="deleteSchedule(schedule.id)">Delete</button>
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
                    <h2>{{ editingSchedule ? 'Edit Schedule' : 'Add Schedule' }}</h2>
                    <button class="modal-close" @click="closeModal">×</button>
                </div>
                <form @submit.prevent="saveSchedule" class="modal-body">
                    <div class="form-group">
                        <label>Staff *</label>
                        <select v-model="form.staff_id" class="form-control" required>
                            <option value="">Select Staff</option>
                            <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Shift *</label>
                        <select v-model="form.shift_id" class="form-control" required>
                            <option value="">Select Shift</option>
                            <option v-for="s in shifts" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" v-model="form.schedule_date" class="form-control" required />
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
    name: 'ShiftScheduling',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const loading = ref(false);
        const saving = ref(false);
        const schedules = ref([]);
        const staff = ref([]);
        const shifts = ref([]);
        const showModal = ref(false);
        const editingSchedule = ref(null);
        const form = ref({ staff_id: '', shift_id: '', schedule_date: '', status: 'scheduled' });

        const getStatusClass = (status) => {
            const classes = { scheduled: 'status-in', completed: 'status-in', cancelled: 'status-out', absent: 'status-out' };
            return classes[status] || '';
        };

        const fetchData = async () => {
            loading.value = true;
            try {
                const response = await window.axios.get('/api/shift-scheduling');
                schedules.value = response.data.schedules || [];
                staff.value = response.data.staff || [];
                shifts.value = response.data.shifts || [];
            } catch (error) {
                showError('Failed to load data');
            } finally {
                loading.value = false;
            }
        };

        const openCreateModal = () => {
            editingSchedule.value = null;
            form.value = { staff_id: '', shift_id: '', schedule_date: '', status: 'scheduled' };
            showModal.value = true;
        };

        const openEditModal = (schedule) => {
            editingSchedule.value = schedule;
            // Format the date properly for the input field
            form.value = {
                ...schedule,
                schedule_date: formatDateForInput(schedule.schedule_date)
            };
            showModal.value = true;
        };

        const closeModal = () => {
            showModal.value = false;
            editingSchedule.value = null;
        };

        const saveSchedule = async () => {
            saving.value = true;
            try {
                // Ensure date is in correct format (YYYY-MM-DD)
                const dataToSend = {
                    ...form.value,
                    schedule_date: form.value.schedule_date ? formatDateForInput(form.value.schedule_date) : null
                };

                if (editingSchedule.value) {
                    await window.axios.post(`/api/shift-scheduling/${editingSchedule.value.id}`, dataToSend);
                    showSuccess('Schedule updated!');
                } else {
                    await window.axios.post('/api/shift-scheduling', dataToSend);
                    showSuccess('Schedule created!');
                }
                window.location.reload();
            } catch (error) {
                console.error('Save error:', error);
                
                // Handle validation errors
                if (error.response?.data?.errors) {
                    const errors = error.response.data.errors;
                    const errorMessages = Object.values(errors).flat();
                    showError(errorMessages.join(', ') || 'Validation failed');
                } else {
                    showError(error.response?.data?.message || 'Failed to save schedule');
                }
                
                saving.value = false;
            }
        };

        const deleteSchedule = async (id) => {
            if (!confirm('Delete this schedule?')) return;
            try {
                await window.axios.post(`/api/shift-scheduling/${id}/delete`);
                showSuccess('Schedule deleted!');
                window.location.reload();
            } catch (error) {
                showError('Failed to delete schedule');
            }
        };

        onMounted(() => fetchData());

        return { loading, saving, schedules, staff, shifts, showModal, editingSchedule, form, getStatusClass, formatDate, openCreateModal, openEditModal, closeModal, saveSchedule, deleteSchedule };
    }
}
</script>

<style scoped>
.shift-scheduling-page { padding: 24px; }
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
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid #e2e8f0; margin-top: 24px; }
</style>

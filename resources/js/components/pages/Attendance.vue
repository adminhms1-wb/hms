<template>
    <div class="attendance-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Attendance</h1>
                <p class="page-subtitle">Track staff attendance (manual / biometric integration later)</p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">Record Attendance</button>
        </div>

        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading...</div>
            <div v-else>
                <div class="inventory-table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="record in attendance" :key="record.id">
                                <td>{{ record.staff?.name || '—' }}</td>
                                <td>{{ formatDate(record.attendance_date || record.date) }}</td>
                                <td>{{ formatTime(record.check_in_time) || '—' }}</td>
                                <td>{{ formatTime(record.check_out_time) || '—' }}</td>
                                <td>
                                    <span :class="['status-badge', getStatusClass(record.status)]">{{ record.status }}</span>
                                </td>
                                <td>{{ record.check_in_type || '—' }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-btn" @click="openEditModal(record)">Edit</button>
                                        <button class="icon-btn icon-btn-danger" @click="deleteRecord(record.id)">Delete</button>
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
                    <h2>{{ editingRecord ? 'Edit Attendance' : 'Record Attendance' }}</h2>
                    <button class="modal-close" @click="closeModal">×</button>
                </div>
                <form @submit.prevent="saveRecord" class="modal-body">
                    <div class="form-group">
                        <label>Staff *</label>
                        <select v-model="form.staff_id" class="form-control" required>
                            <option value="">Select Staff</option>
                            <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" v-model="form.attendance_date" class="form-control" required />
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Check In Time</label>
                            <input type="time" v-model="form.check_in_time" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Check Out Time</label>
                            <input type="time" v-model="form.check_out_time" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select v-model="form.status" class="form-control" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="half_day">Half Day</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Check In Type</label>
                        <select v-model="form.check_in_type" class="form-control">
                            <option value="manual">Manual</option>
                            <option value="biometric">Biometric</option>
                        </select>
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
import { formatDate, formatDateForInput, formatTime } from '../../utils/dateFormatter';

export default {
    name: 'Attendance',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const loading = ref(false);
        const saving = ref(false);
        const attendance = ref([]);
        const staff = ref([]);
        const showModal = ref(false);
        const editingRecord = ref(null);
        const form = ref({ staff_id: '', attendance_date: '', check_in_time: '', check_out_time: '', status: 'present', check_in_type: 'manual' });

        const getStatusClass = (status) => {
            const classes = { present: 'status-in', absent: 'status-out', late: 'status-low', half_day: 'status-low', on_leave: 'status-out' };
            return classes[status] || '';
        };

        const fetchData = async () => {
            loading.value = true;
            try {
                const response = await window.axios.get('/api/attendance');
                attendance.value = response.data.attendance || [];
                staff.value = response.data.staff || [];
            } catch (error) {
                showError('Failed to load data');
            } finally {
                loading.value = false;
            }
        };

        const openCreateModal = () => {
            editingRecord.value = null;
            form.value = { staff_id: '', attendance_date: '', check_in_time: '', check_out_time: '', status: 'present', check_in_type: 'manual' };
            showModal.value = true;
        };


        const openEditModal = (record) => {
            editingRecord.value = record;
            // Format the date properly for the input field
            form.value = {
                staff_id: record.staff_id || '',
                attendance_date: formatDateForInput(record.attendance_date || record.date),
                check_in_time: formatTime(record.check_in_time) || '',
                check_out_time: formatTime(record.check_out_time) || '',
                status: record.status || 'present',
                check_in_type: record.check_in_type || 'manual',
                shift_schedule_id: record.shift_schedule_id || null,
                notes: record.notes || ''
            };
            showModal.value = true;
        };

        const closeModal = () => {
            showModal.value = false;
            editingRecord.value = null;
        };

        const saveRecord = async () => {
            saving.value = true;
            try {
                // Ensure date is in correct format (YYYY-MM-DD)
                const dataToSend = {
                    ...form.value,
                    attendance_date: form.value.attendance_date ? formatDateForInput(form.value.attendance_date) : null
                };
                
                // Remove empty strings and convert to null for optional fields
                if (!dataToSend.check_in_time) dataToSend.check_in_time = null;
                if (!dataToSend.check_out_time) dataToSend.check_out_time = null;
                if (!dataToSend.check_in_type) dataToSend.check_in_type = null;
                if (!dataToSend.shift_schedule_id) dataToSend.shift_schedule_id = null;
                if (!dataToSend.notes) dataToSend.notes = null;

                if (editingRecord.value) {
                    await window.axios.post(`/api/attendance/${editingRecord.value.id}`, dataToSend);
                    showSuccess('Attendance updated!');
                } else {
                    await window.axios.post('/api/attendance', dataToSend);
                    showSuccess('Attendance recorded!');
                }
                window.location.reload();
            } catch (error) {
                console.error('Save error:', error);
                showError(error.response?.data?.message || 'Failed to save attendance');
                saving.value = false;
            }
        };

        const deleteRecord = async (id) => {
            if (!confirm('Delete this record?')) return;
            try {
                await window.axios.post(`/api/attendance/${id}/delete`);
                showSuccess('Record deleted!');
                window.location.reload();
            } catch (error) {
                showError('Failed to delete record');
            }
        };

        onMounted(() => fetchData());

        return { loading, saving, attendance, staff, showModal, editingRecord, form, getStatusClass, formatDate, formatTime, openCreateModal, openEditModal, closeModal, saveRecord, deleteRecord };
    }
}
</script>

<style scoped>
.attendance-page { padding: 24px; }
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

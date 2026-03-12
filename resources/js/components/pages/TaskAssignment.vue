<template>
    <div class="task-assignment-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Task Assignment</h1>
                <p class="page-subtitle">Assign tasks for housekeeping and room service</p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">Assign Task</button>
        </div>

        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading...</div>
            <div v-else>
                <div class="inventory-table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Task Type</th>
                                <th>Title</th>
                                <th>Assigned Date</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="task in tasks" :key="task.id">
                                <td>{{ task.staff?.name || '—' }}</td>
                                <td>{{ task.task_type }}</td>
                                <td>{{ task.title }}</td>
                                <td>{{ task.assigned_date }}</td>
                                <td>
                                    <span :class="['status-badge', getPriorityClass(task.priority)]">{{ task.priority }}</span>
                                </td>
                                <td>
                                    <span :class="['status-badge', getStatusClass(task.status)]">{{ task.status }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-btn" @click="openEditModal(task)">Edit</button>
                                        <button class="icon-btn icon-btn-danger" @click="deleteTask(task.id)">Delete</button>
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
                    <h2>{{ editingTask ? 'Edit Task' : 'Assign Task' }}</h2>
                    <button class="modal-close" @click="closeModal">×</button>
                </div>
                <form @submit.prevent="saveTask" class="modal-body">
                    <div class="form-group">
                        <label>Staff *</label>
                        <select v-model="form.staff_id" class="form-control" required>
                            <option value="">Select Staff</option>
                            <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Task Type *</label>
                        <select v-model="form.task_type" class="form-control" required>
                            <option value="housekeeping">Housekeeping</option>
                            <option value="room_service">Room Service</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" v-model="form.title" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Assigned Date *</label>
                            <input type="date" v-model="form.assigned_date" class="form-control" required />
                        </div>
                        <div class="form-group">
                            <label>Due Date</label>
                            <input type="date" v-model="form.due_date" class="form-control" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Priority *</label>
                            <select v-model="form.priority" class="form-control" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select v-model="form.status" class="form-control">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
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

export default {
    name: 'TaskAssignment',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const loading = ref(false);
        const saving = ref(false);
        const tasks = ref([]);
        const staff = ref([]);
        const rooms = ref([]);
        const showModal = ref(false);
        const editingTask = ref(null);
        const form = ref({ staff_id: '', task_type: 'housekeeping', title: '', description: '', assigned_date: '', due_date: '', priority: 'medium', status: 'pending', room_id: '' });

        const getPriorityClass = (priority) => {
            const classes = { low: 'status-in', medium: 'status-low', high: 'status-out', urgent: 'status-out' };
            return classes[priority] || '';
        };

        const getStatusClass = (status) => {
            const classes = { pending: 'status-low', in_progress: 'status-in', completed: 'status-in', cancelled: 'status-out' };
            return classes[status] || '';
        };

        const fetchData = async () => {
            loading.value = true;
            try {
                const response = await window.axios.get('/api/task-assignment');
                tasks.value = response.data.tasks || [];
                staff.value = response.data.staff || [];
                rooms.value = response.data.rooms || [];
            } catch (error) {
                showError('Failed to load data');
            } finally {
                loading.value = false;
            }
        };

        const openCreateModal = () => {
            editingTask.value = null;
            form.value = { staff_id: '', task_type: 'housekeeping', title: '', description: '', assigned_date: '', due_date: '', priority: 'medium', status: 'pending', room_id: '' };
            showModal.value = true;
        };

        const openEditModal = (task) => {
            editingTask.value = task;
            form.value = { ...task };
            showModal.value = true;
        };

        const closeModal = () => {
            showModal.value = false;
            editingTask.value = null;
        };

        const saveTask = async () => {
            saving.value = true;
            try {
                if (editingTask.value) {
                    await window.axios.post(`/api/task-assignment/${editingTask.value.id}`, form.value);
                    showSuccess('Task updated!');
                } else {
                    await window.axios.post('/api/task-assignment', form.value);
                    showSuccess('Task assigned!');
                }
                window.location.reload();
            } catch (error) {
                showError('Failed to save task');
                saving.value = false;
            }
        };

        const deleteTask = async (id) => {
            if (!confirm('Delete this task?')) return;
            try {
                await window.axios.post(`/api/task-assignment/${id}/delete`);
                showSuccess('Task deleted!');
                window.location.reload();
            } catch (error) {
                showError('Failed to delete task');
            }
        };

        onMounted(() => fetchData());

        return { loading, saving, tasks, staff, rooms, showModal, editingTask, form, getPriorityClass, getStatusClass, openCreateModal, openEditModal, closeModal, saveTask, deleteTask };
    }
}
</script>

<style scoped>
.task-assignment-page { padding: 24px; }
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

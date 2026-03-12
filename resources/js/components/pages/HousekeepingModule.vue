<template>
    <div class="housekeeping-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Housekeeping Module</h1>
                <p class="page-subtitle">
                    Manage housekeeping tasks, room cleaning status, and staff assignments.
                </p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                :class="['tab-btn', { active: activeTab === tab.id }]"
                @click="activeTab = tab.id"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Cleaning Schedule Tab -->
        <div v-if="activeTab === 'cleaning-schedule'" class="tab-content">
            <div class="content-grid">
                <section class="content-card">
                    <div class="card-header">
                        <h2>Cleaning Schedule</h2>
                        <p>Manage housekeeping tasks and assign staff</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Room *</label>
                                <select v-model="taskForm.room_id">
                                    <option value="">Select a room</option>
                                    <option v-for="room in rooms" :key="room?.id || room" :value="room?.id">
                                        {{ room?.room_number }} - {{ room?.room_type?.name || 'N/A' }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Task Type *</label>
                                <select v-model="taskForm.task_type">
                                    <option value="cleaning">Cleaning</option>
                                    <option value="inspection">Inspection</option>
                                    <option value="linen_change">Linen Change</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="deep_cleaning">Deep Cleaning</option>
                                    <option value="turn_down_service">Turn-down Service</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date *</label>
                                <input v-model="taskForm.date" type="date" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Assign Staff</label>
                                <select v-model="taskForm.staff_id">
                                    <option value="">Select staff</option>
                                    <option v-for="staff in staffList" :key="staff?.id || staff" :value="staff?.id">
                                        {{ staff?.name || 'Unknown' }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select v-model="taskForm.status">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea v-model="taskForm.notes" rows="2" placeholder="Additional notes"></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-secondary" @click="resetTaskForm">Clear</button>
                            <button class="btn btn-primary" @click="saveTask" :disabled="!taskForm.room_id || !taskForm.task_type || !taskForm.date || saving">
                                {{ saving ? 'Saving...' : (taskForm.id ? 'Update Task' : 'Create Task') }}
                            </button>
                        </div>

                        <div class="filter-chips" style="margin-top: 20px;">
                            <button
                                v-for="status in taskStatuses"
                                :key="status.value"
                                :class="['chip', { active: activeTaskStatus === status.value }]"
                                @click="activeTaskStatus = status.value"
                            >
                                {{ status.label }}
                            </button>
                        </div>

                        <div v-if="loadingTasks" class="loading-state">
                            <p>Loading tasks...</p>
                        </div>
                        <div v-else-if="filteredTasks.length === 0" class="empty-state">
                            <p>No tasks found.</p>
                        </div>
                        <div v-else class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Room</th>
                                        <th>Task Type</th>
                                        <th>Date</th>
                                        <th>Assigned Staff</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="task in filteredTasks" :key="task?.id || task">
                                        <td>
                                            <strong>{{ task?.room?.room_number || 'N/A' }}</strong>
                                            <small>{{ task?.room?.room_type?.name || '' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ formatTaskType(task?.task_type) }}</span>
                                        </td>
                                        <td>{{ formatDate(task?.date) }}</td>
                                        <td>{{ task?.staff?.name || 'Unassigned' }}</td>
                                        <td>
                                            <span :class="['badge', getTaskStatusBadge(task?.status)]">
                                                {{ task?.status || 'pending' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline" @click="editTask(task)">Edit</button>
                                                <button v-if="task?.status === 'pending'" class="btn btn-sm btn-success" @click="startTask(task)" :disabled="saving">
                                                    Start
                                                </button>
                                                <button v-if="task?.status === 'in_progress'" class="btn btn-sm btn-primary" @click="completeTask(task)" :disabled="saving">
                                                    Complete
                                                </button>
                                                <button class="btn btn-sm btn-danger" @click="deleteTask(task?.id)" :disabled="saving">Remove</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Room Cleaning Status Tab -->
        <div v-if="activeTab === 'room-status'" class="tab-content">
            <div class="content-grid">
                <section class="content-card">
                    <div class="card-header">
                        <h2>Room Cleaning Status</h2>
                        <p>View and update room cleaning status</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Filter by Status</label>
                                <select v-model="roomStatusFilter" @change="fetchRoomsByStatus">
                                    <option value="">All Rooms</option>
                                    <option value="available">Available</option>
                                    <option value="under_cleaning">Under Cleaning</option>
                                    <option value="checked_out">Checked Out</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="loadingRooms" class="loading-state">
                            <p>Loading rooms...</p>
                        </div>
                        <div v-else-if="roomsForCleaning.length === 0" class="empty-state">
                            <p>No rooms found.</p>
                        </div>
                        <div v-else class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Room Number</th>
                                        <th>Room Type</th>
                                        <th>Current Status</th>
                                        <th>Last Cleaned</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="room in roomsForCleaning" :key="room?.id || room">
                                        <td><strong>{{ room?.room_number || 'N/A' }}</strong></td>
                                        <td>{{ room?.room_type?.name || 'N/A' }}</td>
                                        <td>
                                            <span :class="['badge', getRoomStatusBadge(room?.status)]">
                                                {{ formatRoomStatus(room?.status) }}
                                            </span>
                                        </td>
                                        <td>{{ room?.last_cleaned ? formatDate(room.last_cleaned) : 'Never' }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <button v-if="room?.status === 'checked_out'" class="btn btn-sm btn-success" @click="markRoomCleaning(room)" :disabled="saving">
                                                    Mark for Cleaning
                                                </button>
                                                <button v-if="room?.status === 'under_cleaning'" class="btn btn-sm btn-primary" @click="markRoomAvailable(room)" :disabled="saving">
                                                    Mark Available
                                                </button>
                                                <button class="btn btn-sm btn-outline" @click="createTaskForRoom(room)">Create Task</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Maintenance Issues Tab -->
        <div v-if="activeTab === 'maintenance'" class="tab-content">
            <div class="content-grid">
                <section class="content-card">
                    <div class="card-header">
                        <h2>Maintenance Issue Reporting</h2>
                        <p>Report and track maintenance issues</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Room *</label>
                                <select v-model="maintenanceForm.room_id">
                                    <option value="">Select a room</option>
                                    <option v-for="room in rooms" :key="room?.id || room" :value="room?.id">
                                        {{ room?.room_number }} - {{ room?.room_type?.name || 'N/A' }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Priority</label>
                                <select v-model="maintenanceForm.priority">
                                    <option value="low">Low</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Assign To</label>
                                <select v-model="maintenanceForm.assigned_to">
                                    <option value="">Select staff</option>
                                    <option v-for="staff in staffList" :key="staff?.id || staff" :value="staff?.id">
                                        {{ staff?.name || 'Unknown' }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label>Issue Description *</label>
                                <textarea v-model="maintenanceForm.issue" rows="3" placeholder="Describe the maintenance issue"></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label>Notes</label>
                                <textarea v-model="maintenanceForm.notes" rows="2" placeholder="Additional notes"></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-secondary" @click="resetMaintenanceForm">Clear</button>
                            <button class="btn btn-primary" @click="saveMaintenance" :disabled="!maintenanceForm.room_id || !maintenanceForm.issue || saving">
                                {{ saving ? 'Saving...' : (maintenanceForm.id ? 'Update Request' : 'Report Issue') }}
                            </button>
                        </div>

                        <div class="filter-chips" style="margin-top: 20px;">
                            <button
                                v-for="status in maintenanceStatuses"
                                :key="status.value"
                                :class="['chip', { active: activeMaintenanceStatus === status.value }]"
                                @click="activeMaintenanceStatus = status.value"
                            >
                                {{ status.label }}
                            </button>
                        </div>

                        <div v-if="loadingMaintenance" class="loading-state">
                            <p>Loading maintenance requests...</p>
                        </div>
                        <div v-else-if="filteredMaintenance.length === 0" class="empty-state">
                            <p>No maintenance requests found.</p>
                        </div>
                        <div v-else class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Room</th>
                                        <th>Issue</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Reported By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="req in filteredMaintenance" :key="req?.id || req">
                                        <td>
                                            <strong>{{ req?.room?.room_number || 'N/A' }}</strong>
                                            <small>{{ req?.room?.room_type?.name || '' }}</small>
                                        </td>
                                        <td>{{ req?.issue || 'N/A' }}</td>
                                        <td>
                                            <span :class="['badge', getPriorityBadge(req?.priority)]">
                                                {{ req?.priority || 'normal' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span :class="['badge', getMaintenanceStatusBadge(req?.status)]">
                                                {{ req?.status || 'reported' }}
                                            </span>
                                        </td>
                                        <td>{{ req?.assigned_to?.name || 'Unassigned' }}</td>
                                        <td>{{ req?.reported_by?.name || 'N/A' }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline" @click="editMaintenance(req)">Edit</button>
                                                <button v-if="req?.status === 'reported'" class="btn btn-sm btn-info" @click="startMaintenance(req)" :disabled="saving">
                                                    Start
                                                </button>
                                                <button v-if="req?.status === 'in_progress'" class="btn btn-sm btn-success" @click="resolveMaintenance(req)" :disabled="saving">
                                                    Resolve
                                                </button>
                                                <button class="btn btn-sm btn-danger" @click="deleteMaintenance(req?.id)" :disabled="saving">Remove</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Lost & Found Tab -->
        <div v-if="activeTab === 'lost-found'" class="tab-content">
            <div class="content-grid">
                <section class="content-card">
                    <div class="card-header">
                        <h2>Lost & Found Tracking</h2>
                        <p>Track lost and found items</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Room</label>
                                <select v-model="lostFoundForm.room_id">
                                    <option value="">Select a room (optional)</option>
                                    <option v-for="room in rooms" :key="room?.id || room" :value="room?.id">
                                        {{ room?.room_number }} - {{ room?.room_type?.name || 'N/A' }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Item *</label>
                                <input v-model="lostFoundForm.item" type="text" placeholder="Item description" />
                            </div>
                            <div class="form-group">
                                <label>Found Date *</label>
                                <input v-model="lostFoundForm.found_date" type="date" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label>Description</label>
                                <textarea v-model="lostFoundForm.description" rows="2" placeholder="Detailed description"></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Location</label>
                                <input v-model="lostFoundForm.location" type="text" placeholder="Where was it found?" />
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select v-model="lostFoundForm.status">
                                    <option value="found">Found</option>
                                    <option value="claimed">Claimed</option>
                                    <option value="unclaimed">Unclaimed</option>
                                    <option value="discarded">Discarded</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-secondary" @click="resetLostFoundForm">Clear</button>
                            <button class="btn btn-primary" @click="saveLostFound" :disabled="!lostFoundForm.item || !lostFoundForm.found_date || saving">
                                {{ saving ? 'Saving...' : (lostFoundForm.id ? 'Update Item' : 'Add Item') }}
                            </button>
                        </div>

                        <div class="filter-chips" style="margin-top: 20px;">
                            <button
                                v-for="status in lostFoundStatuses"
                                :key="status.value"
                                :class="['chip', { active: activeLostFoundStatus === status.value }]"
                                @click="activeLostFoundStatus = status.value"
                            >
                                {{ status.label }}
                            </button>
                        </div>

                        <div v-if="loadingLostFound" class="loading-state">
                            <p>Loading lost and found items...</p>
                        </div>
                        <div v-else-if="filteredLostFound.length === 0" class="empty-state">
                            <p>No lost and found items found.</p>
                        </div>
                        <div v-else class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Room</th>
                                        <th>Found Date</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in filteredLostFound" :key="item?.id || item">
                                        <td>
                                            <strong>{{ item?.item || 'N/A' }}</strong>
                                            <small v-if="item?.description">{{ item.description }}</small>
                                        </td>
                                        <td>{{ item?.room?.room_number || 'N/A' }}</td>
                                        <td>{{ formatDate(item?.found_date) }}</td>
                                        <td>{{ item?.location || 'N/A' }}</td>
                                        <td>
                                            <span :class="['badge', getLostFoundStatusBadge(item?.status)]">
                                                {{ item?.status || 'found' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline" @click="editLostFound(item)">Edit</button>
                                                <button v-if="item?.status === 'found'" class="btn btn-sm btn-success" @click="claimLostFound(item)" :disabled="saving">
                                                    Mark Claimed
                                                </button>
                                                <button class="btn btn-sm btn-danger" @click="deleteLostFound(item?.id)" :disabled="saving">Remove</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'HousekeepingModule',
    setup() {
        const tabs = [
            { id: 'cleaning-schedule', label: 'Cleaning Schedule' },
            { id: 'room-status', label: 'Room Cleaning Status' },
            { id: 'maintenance', label: 'Maintenance Issues' },
            { id: 'lost-found', label: 'Lost & Found' },
        ];
        const activeTab = ref('cleaning-schedule');
        const saving = ref(false);
        const loadingTasks = ref(false);
        const loadingRooms = ref(false);
        const loadingMaintenance = ref(false);
        const loadingLostFound = ref(false);

        // Housekeeping Tasks
        const tasks = ref([]);
        const activeTaskStatus = ref('all');
        const taskStatuses = [
            { value: 'all', label: 'All' },
            { value: 'pending', label: 'Pending' },
            { value: 'in_progress', label: 'In Progress' },
            { value: 'completed', label: 'Completed' },
            { value: 'cancelled', label: 'Cancelled' },
        ];
        const taskForm = ref({
            id: null,
            room_id: '',
            staff_id: null,
            task_type: 'cleaning',
            status: 'pending',
            date: new Date().toISOString().split('T')[0],
            notes: '',
        });

        const filteredTasks = computed(() => {
            const validTasks = tasks.value.filter(t => t && t.id);
            if (activeTaskStatus.value === 'all') {
                return validTasks;
            }
            return validTasks.filter(t => t.status === activeTaskStatus.value);
        });

        // Rooms
        const rooms = ref([]);
        const roomsForCleaning = ref([]);
        const roomStatusFilter = ref('');
        const staffList = ref([]);

        // Maintenance Requests
        const maintenanceRequests = ref([]);
        const activeMaintenanceStatus = ref('all');
        const maintenanceStatuses = [
            { value: 'all', label: 'All' },
            { value: 'reported', label: 'Reported' },
            { value: 'in_progress', label: 'In Progress' },
            { value: 'resolved', label: 'Resolved' },
            { value: 'cancelled', label: 'Cancelled' },
        ];
        const maintenanceForm = ref({
            id: null,
            room_id: '',
            issue: '',
            priority: 'normal',
            assigned_to: null,
            notes: '',
        });

        const filteredMaintenance = computed(() => {
            const validRequests = maintenanceRequests.value.filter(r => r && r.id);
            if (activeMaintenanceStatus.value === 'all') {
                return validRequests;
            }
            return validRequests.filter(r => r.status === activeMaintenanceStatus.value);
        });

        // Lost & Found
        const lostFoundItems = ref([]);
        const activeLostFoundStatus = ref('all');
        const lostFoundStatuses = [
            { value: 'all', label: 'All' },
            { value: 'found', label: 'Found' },
            { value: 'claimed', label: 'Claimed' },
            { value: 'unclaimed', label: 'Unclaimed' },
            { value: 'discarded', label: 'Discarded' },
        ];
        const lostFoundForm = ref({
            id: null,
            room_id: null,
            item: '',
            description: '',
            found_date: new Date().toISOString().split('T')[0],
            location: '',
            status: 'found',
        });

        const filteredLostFound = computed(() => {
            const validItems = lostFoundItems.value.filter(i => i && i.id);
            if (activeLostFoundStatus.value === 'all') {
                return validItems;
            }
            return validItems.filter(i => i.status === activeLostFoundStatus.value);
        });

        // Fetch housekeeping tasks
        const fetchTasks = async () => {
            try {
                loadingTasks.value = true;
                const response = await axios.get('/api/housekeeping-tasks');
                const rawData = response.data.data || [];
                const newData = rawData.filter(t => t && t.id);
                if (newData.length > 0 || tasks.value.length === 0) {
                    tasks.value = newData;
                }
            } catch (error) {
                console.error('Error fetching tasks:', error);
                if (tasks.value.length === 0) {
                    tasks.value = [];
                }
            } finally {
                loadingTasks.value = false;
            }
        };

        const saveTask = async () => {
            if (!taskForm.value.room_id || !taskForm.value.task_type || !taskForm.value.date) return;
            
            try {
                saving.value = true;
                
                if (taskForm.value.id) {
                    await axios.put(`/api/housekeeping-tasks/${taskForm.value.id}`, taskForm.value);
                } else {
                    await axios.post('/api/housekeeping-tasks', taskForm.value);
                }
                
                await fetchTasks();
                resetTaskForm();
            } catch (error) {
                console.error('Error saving task:', error);
                alert('Failed to save task: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const editTask = (task) => {
            taskForm.value = {
                id: task.id,
                room_id: task.room_id,
                staff_id: task.staff_id,
                task_type: task.task_type || 'cleaning',
                status: task.status || 'pending',
                date: task.date ? task.date.split('T')[0] : new Date().toISOString().split('T')[0],
                notes: task.notes || '',
            };
        };

        const deleteTask = async (id) => {
            if (!confirm('Are you sure you want to delete this task?')) return;
            
            try {
                saving.value = true;
                await axios.delete(`/api/housekeeping-tasks/${id}`);
                await fetchTasks();
            } catch (error) {
                console.error('Error deleting task:', error);
                alert('Failed to delete task: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const startTask = async (task) => {
            try {
                saving.value = true;
                await axios.put(`/api/housekeeping-tasks/${task.id}`, {
                    ...task,
                    status: 'in_progress',
                });
                await fetchTasks();
            } catch (error) {
                console.error('Error starting task:', error);
                alert('Failed to start task: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const completeTask = async (task) => {
            try {
                saving.value = true;
                await axios.put(`/api/housekeeping-tasks/${task.id}`, {
                    ...task,
                    status: 'completed',
                });
                await fetchTasks();
            } catch (error) {
                console.error('Error completing task:', error);
                alert('Failed to complete task: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const resetTaskForm = () => {
            taskForm.value = {
                id: null,
                room_id: '',
                staff_id: null,
                task_type: 'cleaning',
                status: 'pending',
                date: new Date().toISOString().split('T')[0],
                notes: '',
            };
        };

        const formatTaskType = (type) => {
            const types = {
                cleaning: 'Cleaning',
                inspection: 'Inspection',
                linen_change: 'Linen Change',
                maintenance: 'Maintenance',
                deep_cleaning: 'Deep Cleaning',
                turn_down_service: 'Turn-down Service',
            };
            return types[type] || type;
        };

        const getTaskStatusBadge = (status) => {
            const badges = {
                pending: 'badge-warning',
                in_progress: 'badge-info',
                completed: 'badge-success',
                cancelled: 'badge-secondary',
            };
            return badges[status] || 'badge-secondary';
        };

        // Fetch rooms
        const fetchRooms = async () => {
            try {
                const response = await axios.get('/api/rooms');
                const rawData = response.data.data || [];
                rooms.value = rawData.filter(r => r && r.id);
            } catch (error) {
                console.error('Error fetching rooms:', error);
                rooms.value = [];
            }
        };

        const fetchRoomsByStatus = async () => {
            try {
                loadingRooms.value = true;
                const params = {};
                if (roomStatusFilter.value) {
                    params.status = roomStatusFilter.value;
                }
                const response = await axios.get('/api/rooms', { params });
                const rawData = response.data.data || [];
                roomsForCleaning.value = rawData.filter(r => r && r.id);
            } catch (error) {
                console.error('Error fetching rooms:', error);
                roomsForCleaning.value = [];
            } finally {
                loadingRooms.value = false;
            }
        };

        const fetchStaff = async () => {
            try {
                const response = await axios.get('/api/users');
                const allUsers = response.data.data || response.data || [];
                const newData = allUsers.filter(u => u && u.id && u.status === 'active');
                if (newData.length > 0 || staffList.value.length === 0) {
                    staffList.value = newData;
                }
            } catch (error) {
                console.error('Error fetching staff:', error);
                if (staffList.value.length === 0) {
                    staffList.value = [];
                }
            }
        };

        const markRoomCleaning = async (room) => {
            try {
                saving.value = true;
                await axios.post(`/api/rooms/${room.id}/status`, {
                    status: 'under_cleaning',
                });
                await fetchRoomsByStatus();
            } catch (error) {
                console.error('Error updating room status:', error);
                alert('Failed to update room status: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const markRoomAvailable = async (room) => {
            try {
                saving.value = true;
                await axios.post(`/api/rooms/${room.id}/status`, {
                    status: 'available',
                });
                await fetchRoomsByStatus();
            } catch (error) {
                console.error('Error updating room status:', error);
                alert('Failed to update room status: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const createTaskForRoom = (room) => {
            taskForm.value.room_id = room.id;
            taskForm.value.date = new Date().toISOString().split('T')[0];
            activeTab.value = 'cleaning-schedule';
        };

        const getRoomStatusBadge = (status) => {
            const badges = {
                available: 'badge-success',
                reserved: 'badge-info',
                checked_in: 'badge-primary',
                checked_out: 'badge-warning',
                under_cleaning: 'badge-warning',
                maintenance: 'badge-danger',
            };
            return badges[status] || 'badge-secondary';
        };

        const formatRoomStatus = (status) => {
            const statuses = {
                available: 'Available',
                reserved: 'Reserved',
                checked_in: 'Checked In',
                checked_out: 'Checked Out',
                under_cleaning: 'Under Cleaning',
                maintenance: 'Maintenance',
            };
            return statuses[status] || status;
        };

        // Maintenance Requests
        const fetchMaintenance = async () => {
            try {
                loadingMaintenance.value = true;
                const response = await axios.get('/api/maintenance-requests');
                const rawData = response.data.data || [];
                const newData = rawData.filter(r => r && r.id);
                if (newData.length > 0 || maintenanceRequests.value.length === 0) {
                    maintenanceRequests.value = newData;
                }
            } catch (error) {
                console.error('Error fetching maintenance requests:', error);
                if (maintenanceRequests.value.length === 0) {
                    maintenanceRequests.value = [];
                }
            } finally {
                loadingMaintenance.value = false;
            }
        };

        const saveMaintenance = async () => {
            if (!maintenanceForm.value.room_id || !maintenanceForm.value.issue) return;
            
            try {
                saving.value = true;
                
                if (maintenanceForm.value.id) {
                    await axios.put(`/api/maintenance-requests/${maintenanceForm.value.id}`, maintenanceForm.value);
                } else {
                    await axios.post('/api/maintenance-requests', maintenanceForm.value);
                }
                
                await fetchMaintenance();
                resetMaintenanceForm();
            } catch (error) {
                console.error('Error saving maintenance request:', error);
                alert('Failed to save maintenance request: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const editMaintenance = (req) => {
            maintenanceForm.value = {
                id: req.id,
                room_id: req.room_id,
                issue: req.issue || '',
                priority: req.priority || 'normal',
                assigned_to: req.assigned_to_id || null,
                notes: req.notes || '',
            };
        };

        const deleteMaintenance = async (id) => {
            if (!confirm('Are you sure you want to delete this maintenance request?')) return;
            
            try {
                saving.value = true;
                await axios.delete(`/api/maintenance-requests/${id}`);
                await fetchMaintenance();
            } catch (error) {
                console.error('Error deleting maintenance request:', error);
                alert('Failed to delete maintenance request: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const startMaintenance = async (req) => {
            try {
                saving.value = true;
                await axios.put(`/api/maintenance-requests/${req.id}`, {
                    ...req,
                    status: 'in_progress',
                });
                await fetchMaintenance();
            } catch (error) {
                console.error('Error starting maintenance:', error);
                alert('Failed to start maintenance: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const resolveMaintenance = async (req) => {
            try {
                saving.value = true;
                await axios.put(`/api/maintenance-requests/${req.id}`, {
                    ...req,
                    status: 'resolved',
                });
                await fetchMaintenance();
            } catch (error) {
                console.error('Error resolving maintenance:', error);
                alert('Failed to resolve maintenance: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const resetMaintenanceForm = () => {
            maintenanceForm.value = {
                id: null,
                room_id: '',
                issue: '',
                priority: 'normal',
                assigned_to: null,
                notes: '',
            };
        };

        const getPriorityBadge = (priority) => {
            const badges = {
                low: 'badge-secondary',
                normal: 'badge-info',
                high: 'badge-warning',
                urgent: 'badge-danger',
            };
            return badges[priority] || 'badge-info';
        };

        const getMaintenanceStatusBadge = (status) => {
            const badges = {
                reported: 'badge-warning',
                in_progress: 'badge-info',
                resolved: 'badge-success',
                cancelled: 'badge-secondary',
            };
            return badges[status] || 'badge-secondary';
        };

        // Lost & Found
        const fetchLostFound = async () => {
            try {
                loadingLostFound.value = true;
                const response = await axios.get('/api/lost-and-found');
                const rawData = response.data.data || [];
                const newData = rawData.filter(i => i && i.id);
                if (newData.length > 0 || lostFoundItems.value.length === 0) {
                    lostFoundItems.value = newData;
                }
            } catch (error) {
                console.error('Error fetching lost and found items:', error);
                if (lostFoundItems.value.length === 0) {
                    lostFoundItems.value = [];
                }
            } finally {
                loadingLostFound.value = false;
            }
        };

        const saveLostFound = async () => {
            if (!lostFoundForm.value.item || !lostFoundForm.value.found_date) return;
            
            try {
                saving.value = true;
                
                if (lostFoundForm.value.id) {
                    await axios.put(`/api/lost-and-found/${lostFoundForm.value.id}`, lostFoundForm.value);
                } else {
                    await axios.post('/api/lost-and-found', lostFoundForm.value);
                }
                
                await fetchLostFound();
                resetLostFoundForm();
            } catch (error) {
                console.error('Error saving lost and found item:', error);
                alert('Failed to save item: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const editLostFound = (item) => {
            lostFoundForm.value = {
                id: item.id,
                room_id: item.room_id || null,
                item: item.item || '',
                description: item.description || '',
                found_date: item.found_date ? item.found_date.split('T')[0] : new Date().toISOString().split('T')[0],
                location: item.location || '',
                status: item.status || 'found',
            };
        };

        const deleteLostFound = async (id) => {
            if (!confirm('Are you sure you want to delete this item?')) return;
            
            try {
                saving.value = true;
                await axios.delete(`/api/lost-and-found/${id}`);
                await fetchLostFound();
            } catch (error) {
                console.error('Error deleting lost and found item:', error);
                alert('Failed to delete item: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const claimLostFound = async (item) => {
            try {
                saving.value = true;
                await axios.put(`/api/lost-and-found/${item.id}`, {
                    ...item,
                    status: 'claimed',
                    claimed_date: new Date().toISOString().split('T')[0],
                });
                await fetchLostFound();
            } catch (error) {
                console.error('Error claiming item:', error);
                alert('Failed to mark item as claimed: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const resetLostFoundForm = () => {
            lostFoundForm.value = {
                id: null,
                room_id: null,
                item: '',
                description: '',
                found_date: new Date().toISOString().split('T')[0],
                location: '',
                status: 'found',
            };
        };

        const getLostFoundStatusBadge = (status) => {
            const badges = {
                found: 'badge-info',
                claimed: 'badge-success',
                unclaimed: 'badge-warning',
                discarded: 'badge-secondary',
            };
            return badges[status] || 'badge-secondary';
        };

        const formatDate = (date) => {
            if (!date) return '';
            return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        };

        // All housekeeping functions are defined above

        onMounted(async () => {
            try {
                await Promise.allSettled([
                    fetchRooms(),
                    fetchStaff(),
                    fetchTasks(),
                    fetchRoomsByStatus(),
                    fetchMaintenance(),
                    fetchLostFound(),
                ]);
            } catch (error) {
                console.error('Error during initial data fetch:', error);
            }
        });

        return {
            tabs,
            activeTab,
            saving,
            // Cleaning Schedule
            loadingTasks,
            tasks,
            taskForm,
            activeTaskStatus,
            taskStatuses,
            filteredTasks,
            rooms,
            staffList,
            saveTask,
            editTask,
            deleteTask,
            startTask,
            completeTask,
            resetTaskForm,
            formatTaskType,
            getTaskStatusBadge,
            // Room Cleaning Status
            loadingRooms,
            roomsForCleaning,
            roomStatusFilter,
            fetchRoomsByStatus,
            markRoomCleaning,
            markRoomAvailable,
            createTaskForRoom,
            getRoomStatusBadge,
            formatRoomStatus,
            // Maintenance Issues
            loadingMaintenance,
            maintenanceRequests,
            maintenanceForm,
            activeMaintenanceStatus,
            maintenanceStatuses,
            filteredMaintenance,
            fetchMaintenance,
            saveMaintenance,
            editMaintenance,
            deleteMaintenance,
            startMaintenance,
            resolveMaintenance,
            resetMaintenanceForm,
            getPriorityBadge,
            getMaintenanceStatusBadge,
            // Lost & Found
            loadingLostFound,
            lostFoundItems,
            lostFoundForm,
            activeLostFoundStatus,
            lostFoundStatuses,
            filteredLostFound,
            fetchLostFound,
            saveLostFound,
            editLostFound,
            deleteLostFound,
            claimLostFound,
            resetLostFoundForm,
            getLostFoundStatusBadge,
            formatDate,
        };
    },
};

</script>

<style scoped>
.housekeeping-page {
    max-width: 1600px;
    margin: 0 auto;
    padding: 0 20px 40px;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 28px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

.tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid #e5e7eb;
}

.tab-button {
    padding: 12px 24px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.2s;
}

.tab-button:hover {
    color: #3b82f6;
}

.tab-button.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.card-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

.card-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.table th {
    font-weight: 600;
    color: #374151;
    background: #f9fafb;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    color: #6b7280;
    font-size: 14px;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-info {
    background: #dbeafe;
    color: #1e40af;
}

.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

.badge-secondary {
    background: #f3f4f6;
    color: #374151;
}

.badge-primary {
    background: #dbeafe;
    color: #1e40af;
}

.filter-bar {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.loading {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

.empty-state p {
    margin: 0;
    font-size: 15px;
}
</style>
            try {
                if (setLoading) {
                    loading.value = true;
                }
                const response = await axios.get('/api/rooms/amenities/list');
                // Extract data from response
                let rawData = [];
                if (response && response.data) {
                    if (response.data.data && Array.isArray(response.data.data)) {
                        rawData = response.data.data;
                    } else if (Array.isArray(response.data)) {
                        rawData = response.data;
                    }
                }
                // Filter out null/invalid amenities
                const newData = rawData.filter(a => a && a.id);
                
                // Only update state if:
                // 1. New data is non-empty, OR
                // 2. This is the first load (amenities.value is empty)
                if (newData.length > 0 || amenities.value.length === 0) {
                    amenities.value = newData;
                }
                // If newData is empty but we already have data, preserve existing data
            } catch (error) {
                console.error('Error fetching amenities:', error);
                // Only clear data on error if this is the first load
                // Preserve existing data if we already have some
                if (amenities.value.length === 0) {
                    amenities.value = [];
                }
            } finally {
                // Always set loading to false after data is set
                loadingAmenities.value = false;
                if (setLoading) {
                    loading.value = false;
                }
            }
        };

        // Save amenity
        const saveAmenity = async () => {
            if (!amenityForm.value.name) return;
            
            try {
                saving.value = true;
                
                if (amenityForm.value.id) {
                    // Update existing amenity
                    await axios.put(`/api/amenities/${amenityForm.value.id}`, {
                        name: amenityForm.value.name,
                        is_paid: amenityForm.value.is_paid,
                        price: amenityForm.value.is_paid ? amenityForm.value.price : 0.00,
                    });
                } else {
                    // Create new amenity
                    await axios.post('/api/amenities', {
                        name: amenityForm.value.name,
                        is_paid: amenityForm.value.is_paid,
                        price: amenityForm.value.is_paid ? amenityForm.value.price : 0.00,
                    });
                }
                
                await fetchAmenities(true);
                resetAmenityForm();
            } catch (error) {
                console.error('Error saving amenity:', error);
                alert('Failed to save amenity: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const editAmenity = (amenity) => {
            amenityForm.value = {
                id: amenity.id,
                name: amenity.name,
                is_paid: amenity.is_paid || false,
                price: amenity.price || 0.00,
            };
        };

        const deleteAmenity = async (id) => {
            if (!confirm('Are you sure you want to delete this amenity?')) return;
            
            try {
                saving.value = true;
                await axios.delete(`/api/amenities/${id}`);
                await fetchAmenities(true);
            } catch (error) {
                console.error('Error deleting amenity:', error);
                alert('Failed to delete amenity: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const resetAmenityForm = () => {
            amenityForm.value = {
                id: null,
                name: '',
                is_paid: false,
                price: 0.00,
            };
        };

        // Fetch services
        const fetchServices = async (setLoading = false) => {
            try {
                loadingServices.value = true;
                if (setLoading) {
                    loading.value = true;
                }
                const response = await axios.get('/api/services');
                let servicesData = [];
                if (response && response.data) {
                    if (response.data.data && Array.isArray(response.data.data)) {
                        servicesData = response.data.data;
                    } else if (Array.isArray(response.data)) {
                        servicesData = response.data;
                    }
                }
                
                // Filter out null/invalid services first
                const validServices = servicesData.filter(service => service && service.id);
                // Map services to ensure all fields exist (handle missing migration fields)
                const mappedServices = validServices.map(service => ({
                    ...service,
                    category: service.category || 'other',
                    is_free: service.is_free === true || service.is_free === 1,
                    price: Number(service.price) || 0,
                    description: service.description || '',
                    duration: service.duration || null,
                    is_active: service.is_active !== undefined ? service.is_active : true,
                }));
                
                // Only update state if:
                // 1. New data is non-empty, OR
                // 2. This is the first load (services.value is empty)
                if (mappedServices.length > 0 || services.value.length === 0) {
                    services.value = mappedServices;
                }
                // If mappedServices is empty but we already have data, preserve existing data
            } catch (error) {
                // Silently handle error - don't show alert on page load
                // Only clear data on error if this is the first load
                // Preserve existing data if we already have some
                console.error('Error fetching services:', error);
                console.error('Error details:', error.response?.data || error.message);
                if (services.value.length === 0) {
                    services.value = [];
                }
            } finally {
                // Always set loading to false after data is set
                loadingServices.value = false;
                if (setLoading) {
                    loading.value = false;
                }
            }
        };

        // Save service
        const saveService = async () => {
            if (!serviceForm.value.name || (!serviceForm.value.is_free && !serviceForm.value.price)) return;
            
            try {
                saving.value = true;
                
                const serviceData = {
                    ...serviceForm.value,
                    price: serviceForm.value.is_free ? 0.00 : (serviceForm.value.price || 0.00),
                };
                
                if (serviceForm.value.id) {
                    // Update existing service
                    await axios.put(`/api/services/${serviceForm.value.id}`, serviceData);
                } else {
                    // Create new service
                    await axios.post('/api/services', serviceData);
                }
                
                await fetchServices(true);
                resetServiceForm();
            } catch (error) {
                console.error('Error saving service:', error);
                alert('Failed to save service: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const editService = (service) => {
            serviceForm.value = {
                id: service.id,
                name: service.name || '',
                category: service.category || 'other',
                is_free: service.is_free === true || service.is_free === 1,
                price: service.price || 0.00,
                description: service.description || '',
                duration: service.duration || null,
                is_active: service.is_active !== undefined ? service.is_active : true,
            };
        };

        const deleteService = async (id) => {
            if (!confirm('Are you sure you want to delete this service?')) return;
            
            try {
                saving.value = true;
                await axios.delete(`/api/services/${id}`);
                await fetchServices(true);
            } catch (error) {
                console.error('Error deleting service:', error);
                alert('Failed to delete service: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const resetServiceForm = () => {
            serviceForm.value = {
                id: null,
                name: '',
                category: 'other',
                is_free: false,
                price: 0.00,
                description: '',
                duration: null,
                is_active: true,
            };
        };

        // Time Slots
        const timeSlots = ref([]);
        const timeSlotForm = ref({
            id: null,
            service_id: '',
            start_time: '',
            end_time: '',
            duration_minutes: null,
            is_available: true,
            max_bookings: 1,
            available_days: [],
        });
        const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        const loadTimeSlotsForService = async () => {
            if (!timeSlotForm.value.service_id) {
                timeSlots.value = [];
                return;
            }
            try {
                loading.value = true;
                const response = await axios.get('/api/service-time-slots', {
                    params: { service_id: timeSlotForm.value.service_id }
                });
                timeSlots.value = response.data.data || [];
            } catch (error) {
                console.error('Error fetching time slots:', error);
                timeSlots.value = [];
            } finally {
                loading.value = false;
            }
        };

        const saveTimeSlot = async () => {
            if (!timeSlotForm.value.service_id || !timeSlotForm.value.start_time || !timeSlotForm.value.end_time) return;
            
            try {
                saving.value = true;
                
                if (timeSlotForm.value.id) {
                    await axios.put(`/api/service-time-slots/${timeSlotForm.value.id}`, timeSlotForm.value);
                } else {
                    await axios.post('/api/service-time-slots', timeSlotForm.value);
                }
                
                await loadTimeSlotsForService();
                resetTimeSlotForm();
            } catch (error) {
                console.error('Error saving time slot:', error);
                alert('Failed to save time slot: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const editTimeSlot = (slot) => {
            timeSlotForm.value = {
                id: slot.id,
                service_id: slot.service_id,
                start_time: slot.start_time ? slot.start_time.substring(0, 5) : '',
                end_time: slot.end_time ? slot.end_time.substring(0, 5) : '',
                duration_minutes: slot.duration_minutes,
                is_available: slot.is_available !== false,
                max_bookings: slot.max_bookings || 1,
                available_days: slot.available_days || [],
            };
        };

        const deleteTimeSlot = async (id) => {
            if (!confirm('Are you sure you want to delete this time slot?')) return;
            
            try {
                saving.value = true;
                await axios.delete(`/api/service-time-slots/${id}`);
                await loadTimeSlotsForService();
            } catch (error) {
                console.error('Error deleting time slot:', error);
                alert('Failed to delete time slot: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        // OLD CODE REMOVED - All old amenities/services code has been removed

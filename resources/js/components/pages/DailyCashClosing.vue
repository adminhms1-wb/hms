<template>
    <div class="daily-cash-closing-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Daily Cash Closing</h1>
                <p class="page-subtitle">Manage daily cash closing and reconciliation</p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">New Closing</button>
        </div>

        <div class="filters-section">
            <div class="search-box">
                <input type="date" v-model="dateFilter" @change="filterClosings" class="date-filter" />
            </div>
            <select v-model="statusFilter" @change="filterClosings" class="status-filter">
                <option value="">All Status</option>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
                <option value="verified">Verified</option>
            </select>
        </div>
        
        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading...</div>
            <div v-else-if="filteredClosings.length === 0" class="empty-state">No closings found</div>
            <div v-else>
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Opening</th>
                            <th>Cash Received</th>
                            <th>Cash Paid</th>
                            <th>Expected</th>
                            <th>Actual</th>
                            <th>Difference</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="closing in paginatedClosings" :key="closing.id">
                            <td>{{ formatDate(closing.closing_date) }}</td>
                            <td>{{ closing.user?.name || '—' }}</td>
                            <td>${{ parseFloat(closing.opening_cash || 0).toFixed(2) }}</td>
                            <td>${{ parseFloat(closing.cash_received || 0).toFixed(2) }}</td>
                            <td>${{ parseFloat(closing.cash_paid || 0).toFixed(2) }}</td>
                            <td>${{ parseFloat(closing.expected_cash || 0).toFixed(2) }}</td>
                            <td>${{ parseFloat(closing.actual_cash || 0).toFixed(2) }}</td>
                            <td :class="{ 'text-danger': parseFloat(closing.difference || 0) < 0, 'text-success': parseFloat(closing.difference || 0) > 0 }">
                                ${{ parseFloat(closing.difference || 0).toFixed(2) }}
                            </td>
                            <td><span class="status-badge" :class="closing.status">{{ closing.status }}</span></td>
                            <td>
                                <button class="icon-btn" @click="openEditModal(closing)">Edit</button>
                                <button class="icon-btn icon-btn-danger" @click="confirmDelete(closing)">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="totalPages > 1" class="pagination-wrapper">
                    <div class="pagination-info"><span>Page {{ currentPage }} of {{ totalPages }}</span></div>
                    <div class="pagination-controls">
                        <button class="pagination-btn" @click="prevPage" :disabled="currentPage === 1">Previous</button>
                        <div class="pagination-numbers">
                            <button v-for="page in totalPages" :key="page" class="pagination-number" :class="{ active: page === currentPage }" @click="goToPage(page)">{{ page }}</button>
                        </div>
                        <button class="pagination-btn" @click="nextPage" :disabled="currentPage === totalPages">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content modal-large" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingClosing ? 'Edit' : 'New' }} Cash Closing</h2>
                    <button class="modal-close" @click="closeModal">×</button>
                </div>
                <form @submit.prevent="saveClosing" class="modal-body">
                    <div class="form-group">
                        <label>Closing Date *</label>
                        <input type="date" v-model="form.closing_date" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Opening Cash *</label>
                        <input type="number" step="0.01" v-model="form.opening_cash" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Cash Received</label>
                        <input type="number" step="0.01" v-model="form.cash_received" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Cash Paid</label>
                        <input type="number" step="0.01" v-model="form.cash_paid" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Card Received</label>
                        <input type="number" step="0.01" v-model="form.card_received" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Bank Transfer Received</label>
                        <input type="number" step="0.01" v-model="form.bank_transfer_received" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Online Received</label>
                        <input type="number" step="0.01" v-model="form.online_received" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Actual Cash</label>
                        <input type="number" step="0.01" v-model="form.actual_cash" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select v-model="form.status" class="form-control">
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="verified">Verified</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea v-model="form.notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>Confirm Delete</h2>
                    <button class="modal-close" @click="closeDeleteModal">×</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this closing?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="deleteClosing" :disabled="deleting">{{ deleting ? 'Deleting...' : 'Delete' }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAlert } from '@/composables/useAlert';
import { formatDate, formatDateForInput } from '@/utils/dateFormatter';

const { success: showSuccess, error: showError } = useAlert();

const closings = ref([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const dateFilter = ref('');
const statusFilter = ref('');
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingClosing = ref(null);
const closingToDelete = ref(null);
const currentPage = ref(1);
const itemsPerPage = 10;

const form = ref({
    closing_date: new Date().toISOString().split('T')[0],
    opening_cash: 0,
    cash_received: 0,
    cash_paid: 0,
    card_received: 0,
    bank_transfer_received: 0,
    online_received: 0,
    actual_cash: 0,
    status: 'open',
    notes: '',
});

const filteredClosings = computed(() => {
    let filtered = closings.value;
    if (dateFilter.value) {
        filtered = filtered.filter(c => formatDate(c.closing_date) === dateFilter.value);
    }
    if (statusFilter.value) {
        filtered = filtered.filter(c => c.status === statusFilter.value);
    }
    return filtered;
});

const totalPages = computed(() => Math.ceil(filteredClosings.value.length / itemsPerPage));
const paginatedClosings = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredClosings.value.slice(start, start + itemsPerPage);
});

const loadClosings = async () => {
    loading.value = true;
    try {
        const response = await window.axios.get('/api/daily-cash-closing');
        closings.value = response.data.closings || [];
    } catch (error) {
        showError('Failed to load closings');
    } finally {
        loading.value = false;
    }
};

const filterClosings = () => { currentPage.value = 1; };
const openCreateModal = () => {
    editingClosing.value = null;
    form.value = {
        closing_date: new Date().toISOString().split('T')[0],
        opening_cash: 0,
        cash_received: 0,
        cash_paid: 0,
        card_received: 0,
        bank_transfer_received: 0,
        online_received: 0,
        actual_cash: 0,
        status: 'open',
        notes: '',
    };
    showModal.value = true;
};
const openEditModal = (closing) => {
    editingClosing.value = closing;
    form.value = { ...closing };
    if (closing.closing_date) {
        form.value.closing_date = formatDateForInput(closing.closing_date);
    }
    showModal.value = true;
};
const saveClosing = async () => {
    saving.value = true;
    try {
        const expectedCash = parseFloat(form.value.opening_cash || 0) + 
                            parseFloat(form.value.cash_received || 0) - 
                            parseFloat(form.value.cash_paid || 0);
        const difference = parseFloat(form.value.actual_cash || 0) - expectedCash;
        
        const payload = {
            ...form.value,
            expected_cash: expectedCash,
            difference: difference,
        };
        
        if (editingClosing.value) {
            // Add _method for method spoofing
            const updatePayload = { ...payload, _method: 'PUT' };
            await window.axios.post(`/api/daily-cash-closing/${editingClosing.value.id}`, updatePayload);
            showSuccess('Cash closing updated successfully');
        } else {
            await window.axios.post('/api/daily-cash-closing', payload);
            showSuccess('Cash closing created successfully');
        }
        closeModal();
        // Delay reload to allow alert message to be visible
        setTimeout(() => {
            location.reload();
        }, 1000);
    } catch (error) {
        showError(error.response?.data?.message || 'Failed to save cash closing');
    } finally {
        saving.value = false;
    }
};
const confirmDelete = (closing) => { closingToDelete.value = closing; showDeleteModal.value = true; };
const deleteClosing = async () => {
    if (!closingToDelete.value) return;
    deleting.value = true;
    try {
        await window.axios.post(`/api/daily-cash-closing/${closingToDelete.value.id}/delete`);
        showSuccess('Cash closing deleted successfully');
        closeDeleteModal();
        // Delay reload to allow alert message to be visible
        setTimeout(() => {
            location.reload();
        }, 1000);
    } catch (error) {
        showError('Failed to delete cash closing');
    } finally {
        deleting.value = false;
    }
};
const closeModal = () => { showModal.value = false; editingClosing.value = null; };
const closeDeleteModal = () => { showDeleteModal.value = false; closingToDelete.value = null; };
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const goToPage = (page) => { currentPage.value = page; };

onMounted(() => {
    loadClosings();
});
</script>

<style scoped>
.daily-cash-closing-page { padding: 24px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.filters-section { display: flex; gap: 12px; margin-bottom: 24px; }
.search-box { flex: 1; }
.date-filter { padding: 10px 16px; border: 1px solid #e0e0e0; border-radius: 8px; }
.status-filter { padding: 10px 16px; border: 1px solid #e0e0e0; border-radius: 8px; min-width: 150px; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.loading-state, .empty-state { text-align: center; padding: 40px; color: #999; }
.inventory-table { width: 100%; border-collapse: collapse; }
.inventory-table th { text-align: left; padding: 12px; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
.inventory-table td { padding: 12px; border-bottom: 1px solid #f0f0f0; }
.text-danger { color: #d32f2f; }
.text-success { color: #388e3c; }
.status-badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; text-transform: capitalize; }
.status-badge.open { background: #e3f2fd; color: #1976d2; }
.status-badge.closed { background: #e8f5e9; color: #388e3c; }
.status-badge.verified { background: #fff3e0; color: #f57c00; }
.icon-btn { padding: 6px; border: none; background: transparent; cursor: pointer; border-radius: 4px; }
.icon-btn-danger:hover { background: #ffebee; color: #d32f2f; }
.pagination-wrapper {
    margin-top: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.pagination-info {
    font-size: 14px;
    color: #666;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pagination-btn {
    padding: 8px 16px;
    border: 1px solid #e0e0e0;
    background: #fff;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
    background: #f5f5f5;
    border-color: #999;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-numbers {
    display: flex;
    gap: 4px;
}

.pagination-number {
    min-width: 36px;
    height: 36px;
    padding: 0 12px;
    border: 1px solid #e0e0e0;
    background: #fff;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pagination-number:hover {
    background: #f5f5f5;
    border-color: #999;
}

.pagination-number.active {
    background: #1976d2;
    color: #fff;
    border-color: #1976d2;
}
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: #fff; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
.modal-large { max-width: 800px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #e0e0e0; }
.modal-header h2 { margin: 0; font-size: 20px; }
.modal-close { background: none; border: none; font-size: 24px; cursor: pointer; width: 32px; height: 32px; }
.modal-body { padding: 24px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
.form-control { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding: 20px 24px; border-top: 1px solid #e0e0e0; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
.btn-primary { background: #1976d2; color: #fff; }
.btn-secondary { background: #f5f5f5; }
.btn-danger { background: #d32f2f; color: #fff; }
</style>

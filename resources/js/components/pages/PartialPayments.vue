<template>
    <div class="partial-payments-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Partial Payments</h1>
                <p class="page-subtitle">Manage partial payments for folios</p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">Add Payment</button>
        </div>

        <div class="filters-section">
            <div class="search-box">
                <input type="text" v-model="searchQuery" placeholder="Search..." @input="filterPayments" />
            </div>
            <select v-model="statusFilter" @change="filterPayments" class="status-filter">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="failed">Failed</option>
            </select>
        </div>
        
        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading...</div>
            <div v-else-if="filteredPayments.length === 0" class="empty-state">No payments found</div>
            <div v-else>
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Folio</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Remaining</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment in paginatedPayments" :key="payment.id">
                            <td>#{{ payment.id }}</td>
                            <td>#{{ payment.folio_id || '—' }}</td>
                            <td>${{ parseFloat(payment.amount || 0).toFixed(2) }}</td>
                            <td>{{ payment.method }}</td>
                            <td><span class="status-badge" :class="payment.status">{{ payment.status }}</span></td>
                            <td>${{ parseFloat(payment.remaining_balance || 0).toFixed(2) }}</td>
                            <td>{{ formatDate(payment.created_at) }}</td>
                            <td>
                                <button class="icon-btn" @click="openEditModal(payment)">Edit</button>
                                <button class="icon-btn icon-btn-danger" @click="confirmDelete(payment)">Delete</button>
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
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingPayment ? 'Edit' : 'Add' }} Partial Payment</h2>
                    <button class="modal-close" @click="closeModal">×</button>
                </div>
                <form @submit.prevent="savePayment" class="modal-body">
                    <div class="form-group">
                        <label>Folio *</label>
                        <select v-model="form.folio_id" class="form-control" required>
                            <option value="">Select Folio</option>
                            <option v-for="folio in filteredFolios" :key="folio?.id || Math.random()" :value="folio?.id || ''">
                                {{ folio?.guest?.name || folio?.reservation?.guest_name || '—' }} (Total: ${{ parseFloat(folio?.total || 0).toFixed(2) }})
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount *</label>
                        <input type="number" step="0.01" v-model="form.amount" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Payment Method *</label>
                        <select v-model="form.method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select v-model="form.status" class="form-control">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
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
                    <p>Are you sure you want to delete this payment?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="deletePayment" :disabled="deleting">{{ deleting ? 'Deleting...' : 'Delete' }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAlert } from '@/composables/useAlert';
import { formatDate } from '@/utils/dateFormatter';

const { success: showSuccess, error: showError } = useAlert();

const payments = ref([]);
const folios = ref([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const searchQuery = ref('');
const statusFilter = ref('');
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingPayment = ref(null);
const paymentToDelete = ref(null);
const currentPage = ref(1);
const itemsPerPage = 10;

const form = ref({
    folio_id: '',
    amount: 0,
    method: 'cash',
    status: 'pending',
    is_partial: true,
    notes: '',
});

const filteredPayments = computed(() => {
    let filtered = payments.value.filter(p => p.is_partial);
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(p => p.id.toString().includes(query));
    }
    if (statusFilter.value) {
        filtered = filtered.filter(p => p.status === statusFilter.value);
    }
    return filtered;
});

const totalPages = computed(() => Math.ceil(filteredPayments.value.length / itemsPerPage));
const paginatedPayments = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredPayments.value.slice(start, start + itemsPerPage);
});

const filteredFolios = computed(() => {
    return (folios.value || []).filter(f => f != null && f.id != null);
});

const loadPayments = async () => {
    loading.value = true;
    try {
        const response = await window.axios.get('/api/partial-payments');
        payments.value = response.data.payments || [];
    } catch (error) {
        showError('Failed to load payments');
    } finally {
        loading.value = false;
    }
};

const loadFolios = async () => {
    try {
        // Try to load from bootstrap first
        if (window.__HMS_BOOTSTRAP__ && window.__HMS_BOOTSTRAP__.folios) {
            const data = window.__HMS_BOOTSTRAP__.folios || [];
            folios.value = Array.isArray(data) ? data.filter(f => f != null && f.id != null) : [];
            return;
        }
        
        // Fallback to API
        const response = await window.axios.get('/api/unified-guest-folio');
        const data = response.data.folios || [];
        // Filter out any null or invalid entries
        folios.value = Array.isArray(data) ? data.filter(f => f != null && f.id != null) : [];
    } catch (error) {
        console.error('Error loading folios:', error);
        folios.value = [];
    }
};

const filterPayments = () => { currentPage.value = 1; };
const openCreateModal = () => {
    editingPayment.value = null;
    form.value = { folio_id: '', amount: 0, method: 'cash', status: 'pending', is_partial: true, notes: '' };
    showModal.value = true;
};
const openEditModal = (payment) => {
    editingPayment.value = payment;
    form.value = { ...payment };
    showModal.value = true;
};
const savePayment = async () => {
    saving.value = true;
    try {
        if (editingPayment.value) {
            // Add _method for method spoofing
            const updatePayload = { ...form.value, _method: 'PUT' };
            await window.axios.post(`/api/partial-payments/${editingPayment.value.id}`, updatePayload);
            showSuccess('Payment updated');
        } else {
            await window.axios.post('/api/partial-payments', form.value);
            showSuccess('Payment created');
        }
        closeModal();
        // Delay reload to allow alert message to be visible
        setTimeout(() => {
            location.reload();
        }, 1000);
    } catch (error) {
        showError(error.response?.data?.message || 'Failed to save');
    } finally {
        saving.value = false;
    }
};
const confirmDelete = (payment) => { paymentToDelete.value = payment; showDeleteModal.value = true; };
const deletePayment = async () => {
    if (!paymentToDelete.value) return;
    deleting.value = true;
    try {
        await window.axios.post(`/api/partial-payments/${paymentToDelete.value.id}/delete`);
        showSuccess('Deleted successfully');
        closeDeleteModal();
        // Delay reload to allow alert message to be visible
        setTimeout(() => {
            location.reload();
        }, 1000);
    } catch (error) {
        showError('Failed to delete');
    } finally {
        deleting.value = false;
    }
};
const closeModal = () => { showModal.value = false; editingPayment.value = null; };
const closeDeleteModal = () => { showDeleteModal.value = false; paymentToDelete.value = null; };
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const goToPage = (page) => { currentPage.value = page; };

onMounted(() => {
    loadPayments();
    loadFolios();
});
</script>

<style scoped>
.partial-payments-page { padding: 24px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.filters-section { display: flex; gap: 12px; margin-bottom: 24px; }
.search-box { flex: 1; padding: 10px 16px; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; }
.search-box input { width: 100%; border: none; outline: none; }
.status-filter { padding: 10px 16px; border: 1px solid #e0e0e0; border-radius: 8px; min-width: 150px; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.loading-state, .empty-state { text-align: center; padding: 40px; color: #999; }
.inventory-table { width: 100%; border-collapse: collapse; }
.inventory-table th { text-align: left; padding: 12px; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
.inventory-table td { padding: 12px; border-bottom: 1px solid #f0f0f0; }
.status-badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; text-transform: capitalize; }
.status-badge.pending { background: #e3f2fd; color: #1976d2; }
.status-badge.paid { background: #e8f5e9; color: #388e3c; }
.status-badge.failed { background: #ffebee; color: #d32f2f; }
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

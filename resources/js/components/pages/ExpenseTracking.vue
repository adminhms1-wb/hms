<template>
    <div class="expense-tracking-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Expense Tracking</h1>
                <p class="page-subtitle">Track and manage expenses</p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">Add Expense</button>
        </div>

        <div class="filters-section">
            <div class="search-box">
                <input type="text" v-model="searchQuery" placeholder="Search..." @input="filterExpenses" />
            </div>
            <select v-model="categoryFilter" @change="filterExpenses" class="category-filter">
                <option value="">All Categories</option>
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
            <select v-model="statusFilter" @change="filterExpenses" class="status-filter">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="approved">Approved</option>
            </select>
        </div>
        
        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading...</div>
            <div v-else-if="filteredExpenses.length === 0" class="empty-state">No expenses found</div>
            <div v-else>
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="expense in paginatedExpenses" :key="expense.id">
                            <td>{{ expense.title }}</td>
                            <td>{{ expense.category || '—' }}</td>
                            <td>{{ expense.supplier?.name || '—' }}</td>
                            <td>${{ parseFloat(expense.amount || 0).toFixed(2) }}</td>
                            <td>{{ formatDate(expense.date) }}</td>
                            <td><span class="status-badge" :class="expense.status">{{ expense.status }}</span></td>
                            <td>
                                <button class="icon-btn" @click="openEditModal(expense)">Edit</button>
                                <button class="icon-btn icon-btn-danger" @click="confirmDelete(expense)">Delete</button>
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
                    <h2>{{ editingExpense ? 'Edit' : 'Add' }} Expense</h2>
                    <button class="modal-close" @click="closeModal">×</button>
                </div>
                <form @submit.prevent="saveExpense" class="modal-body">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" v-model="form.title" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" v-model="form.category" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <select v-model="form.supplier_id" class="form-control">
                            <option value="">Select Supplier</option>
                            <option v-for="supplier in filteredSuppliers" :key="supplier?.id || Math.random()" :value="supplier?.id || ''">
                                {{ supplier?.name || supplier?.company_name || '—' }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount *</label>
                        <input type="number" step="0.01" v-model="form.amount" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" v-model="form.date" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select v-model="form.payment_method" class="form-control">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select v-model="form.status" class="form-control">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="approved">Approved</option>
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
                    <p>Are you sure you want to delete this expense?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="deleteExpense" :disabled="deleting">{{ deleting ? 'Deleting...' : 'Delete' }}</button>
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

const expenses = ref([]);
const suppliers = ref([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const searchQuery = ref('');
const categoryFilter = ref('');
const statusFilter = ref('');
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingExpense = ref(null);
const expenseToDelete = ref(null);
const currentPage = ref(1);
const itemsPerPage = 10;

const form = ref({
    title: '',
    category: '',
    supplier_id: '',
    amount: 0,
    date: new Date().toISOString().split('T')[0],
    payment_method: 'cash',
    status: 'pending',
    notes: '',
});

const categories = computed(() => {
    const cats = new Set(expenses.value.map(e => e.category).filter(Boolean));
    return Array.from(cats);
});

const filteredExpenses = computed(() => {
    let filtered = expenses.value;
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(e => 
            (e.title || '').toLowerCase().includes(query) ||
            (e.category || '').toLowerCase().includes(query)
        );
    }
    if (categoryFilter.value) {
        filtered = filtered.filter(e => e.category === categoryFilter.value);
    }
    if (statusFilter.value) {
        filtered = filtered.filter(e => e.status === statusFilter.value);
    }
    return filtered;
});

const filteredSuppliers = computed(() => {
    return (suppliers.value || []).filter(s => s != null && s.id != null);
});

const totalPages = computed(() => Math.ceil(filteredExpenses.value.length / itemsPerPage));
const paginatedExpenses = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredExpenses.value.slice(start, start + itemsPerPage);
});

const loadExpenses = async () => {
    loading.value = true;
    try {
        const response = await window.axios.get('/api/expense-tracking');
        expenses.value = response.data.expenses || [];
    } catch (error) {
        showError('Failed to load expenses');
    } finally {
        loading.value = false;
    }
};

const loadSuppliers = async () => {
    try {
        // Try to load from bootstrap first
        if (window.__HMS_BOOTSTRAP__ && window.__HMS_BOOTSTRAP__.suppliers) {
            const data = window.__HMS_BOOTSTRAP__.suppliers || [];
            suppliers.value = Array.isArray(data) ? data.filter(s => s != null && s.id != null) : [];
            return;
        }
        
        // Fallback to API
        const response = await window.axios.get('/api/suppliers');
        const data = response.data.suppliers || response.data.data || [];
        // Filter out any null or invalid entries
        suppliers.value = Array.isArray(data) ? data.filter(s => s != null && s.id != null) : [];
    } catch (error) {
        console.error('Error loading suppliers:', error);
        suppliers.value = [];
    }
};

const filterExpenses = () => { currentPage.value = 1; };
const openCreateModal = () => {
    editingExpense.value = null;
    form.value = {
        title: '',
        category: '',
        supplier_id: '',
        amount: 0,
        date: new Date().toISOString().split('T')[0],
        payment_method: 'cash',
        status: 'pending',
        notes: '',
    };
    showModal.value = true;
};
const openEditModal = (expense) => {
    editingExpense.value = expense;
    form.value = { ...expense };
    if (expense.date) {
        form.value.date = formatDateForInput(expense.date);
    }
    showModal.value = true;
};
const saveExpense = async () => {
    saving.value = true;
    try {
        if (editingExpense.value) {
            // Add _method for method spoofing
            const updatePayload = { ...form.value, _method: 'PUT' };
            await window.axios.post(`/api/expense-tracking/${editingExpense.value.id}`, updatePayload);
            showSuccess('Expense updated successfully');
        } else {
            await window.axios.post('/api/expense-tracking', form.value);
            showSuccess('Expense created successfully');
        }
        closeModal();
        // Delay reload to allow alert message to be visible
        setTimeout(() => {
            location.reload();
        }, 1000);
    } catch (error) {
        showError(error.response?.data?.message || 'Failed to save expense');
    } finally {
        saving.value = false;
    }
};
const confirmDelete = (expense) => { expenseToDelete.value = expense; showDeleteModal.value = true; };
const deleteExpense = async () => {
    if (!expenseToDelete.value) return;
    deleting.value = true;
    try {
        await window.axios.post(`/api/expense-tracking/${expenseToDelete.value.id}/delete`);
        showSuccess('Expense deleted successfully');
        closeDeleteModal();
        // Delay reload to allow alert message to be visible
        setTimeout(() => {
            location.reload();
        }, 1000);
    } catch (error) {
        showError('Failed to delete expense');
    } finally {
        deleting.value = false;
    }
};
const closeModal = () => { showModal.value = false; editingExpense.value = null; };
const closeDeleteModal = () => { showDeleteModal.value = false; expenseToDelete.value = null; };
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const goToPage = (page) => { currentPage.value = page; };

onMounted(() => {
    loadExpenses();
    loadSuppliers();
});
</script>

<style scoped>
.expense-tracking-page { padding: 24px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.filters-section { display: flex; gap: 12px; margin-bottom: 24px; }
.search-box { flex: 1; padding: 10px 16px; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; }
.search-box input { width: 100%; border: none; outline: none; }
.category-filter, .status-filter { padding: 10px 16px; border: 1px solid #e0e0e0; border-radius: 8px; min-width: 150px; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.loading-state, .empty-state { text-align: center; padding: 40px; color: #999; }
.inventory-table { width: 100%; border-collapse: collapse; }
.inventory-table th { text-align: left; padding: 12px; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
.inventory-table td { padding: 12px; border-bottom: 1px solid #f0f0f0; }
.status-badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; text-transform: capitalize; }
.status-badge.pending { background: #e3f2fd; color: #1976d2; }
.status-badge.paid { background: #e8f5e9; color: #388e3c; }
.status-badge.approved { background: #fff3e0; color: #f57c00; }
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

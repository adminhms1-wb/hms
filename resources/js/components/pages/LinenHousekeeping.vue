<template>
    <div class="linen-housekeeping-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Linen & Housekeeping Items</h1>
                <p class="page-subtitle">Manage linen inventory, housekeeping supplies, and related items</p>
            </div>
            <button 
                class="btn btn-primary" 
                @click="openCreateModal"
            >
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Add Item
            </button>
        </div>

        <!-- Filters and Search -->
        <div class="filters-section">
            <div class="search-box">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                    <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Search items by name or code..."
                    @input="filterItems"
                />
            </div>
            <select v-model="selectedCategory" @change="filterItems" class="category-filter">
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                </option>
            </select>
            <select v-model="stockFilter" @change="filterItems" class="stock-filter">
                <option value="">All Stock</option>
                <option value="in_stock">In Stock</option>
                <option value="low_stock">Low Stock</option>
                <option value="out_of_stock">Out of Stock</option>
            </select>
        </div>
        
        <div class="content-card">
            <div v-if="loading" class="loading-state">
                <p>Loading items...</p>
            </div>
            
            <div v-else-if="filteredItems.length === 0" class="empty-state">
                <p>No items found</p>
            </div>
            
            <div v-else>
                <div class="table-header">
                    <div class="record-count">
                        <span class="count-label">Showing:</span>
                        <span class="count-value">{{ startIndex }}-{{ endIndex }} of {{ filteredItems.length }} (Total: {{ items.length }})</span>
                    </div>
                </div>
                <div class="inventory-table-wrapper">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in paginatedItems" :key="item.id">
                            <td>{{ item.code }}</td>
                            <td>{{ item.name }}</td>
                            <td>{{ item.category }}</td>
                            <td>{{ item.stock }}</td>
                            <td>{{ item.unit }}</td>
                            <td>
                                <span :class="['status-badge', getStockStatusClass(item.stock, item.minStock)]">
                                    {{ getStockStatus(item.stock, item.minStock) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="icon-btn" @click="openEditModal(item)" title="Edit">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M11.3333 2.00001C11.5084 1.82489 11.7163 1.68603 11.9447 1.59128C12.1731 1.49654 12.4173 1.44775 12.6667 1.44775C12.916 1.44775 13.1602 1.49654 13.3886 1.59128C13.617 1.68603 13.8249 1.82489 14 2.00001C14.1751 2.17513 14.314 2.38305 14.4087 2.61143C14.5035 2.83981 14.5523 3.08405 14.5523 3.33334C14.5523 3.58263 14.5035 3.82687 14.4087 4.05525C14.314 4.28363 14.1751 4.49155 14 4.66667L5 13.6667L1.33333 14.6667L2.33333 11L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button class="icon-btn icon-btn-danger" @click="confirmDelete(item)" title="Delete">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M2 4H14M12.6667 4V13.3333C12.6667 13.687 12.5262 14.0263 12.2761 14.2764C12.026 14.5265 11.6867 14.6667 11.3333 14.6667H4.66667C4.31305 14.6667 3.97391 14.5265 3.72381 14.2764C3.47371 14.0263 3.33333 13.687 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2.31305 5.47371 1.97391 5.72381 1.72381C5.97391 1.47371 6.31305 1.33333 6.66667 1.33333H9.33333C9.68696 1.33333 10.0261 1.47371 10.2762 1.72381C10.5263 1.97391 10.6667 2.31305 10.6667 2.66667V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
                
                <!-- Pagination Controls -->
                <div v-if="totalPages > 1" class="pagination-wrapper">
                    <div class="pagination-info">
                        <span>Page {{ currentPage }} of {{ totalPages }}</span>
                    </div>
                    <div class="pagination-controls">
                        <button 
                            class="pagination-btn" 
                            @click="prevPage" 
                            :disabled="currentPage === 1"
                            title="Previous Page"
                        >
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Previous
                        </button>
                        
                        <div class="pagination-numbers">
                            <button
                                v-for="page in totalPages"
                                :key="page"
                                class="pagination-number"
                                :class="{ active: page === currentPage }"
                                @click="goToPage(page)"
                            >
                                {{ page }}
                            </button>
                        </div>
                        
                        <button 
                            class="pagination-btn" 
                            @click="nextPage" 
                            :disabled="currentPage === totalPages"
                            title="Next Page"
                        >
                            Next
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingItem ? 'Edit Item' : 'Add Item' }}</h2>
                    <button class="modal-close" @click="closeModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="saveItem" class="modal-body">
                    <div class="form-group">
                        <label>Item Code *</label>
                        <input 
                            type="text" 
                            v-model="form.code" 
                            class="form-control" 
                            placeholder="e.g., LIN-001"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label>Item Name *</label>
                        <input 
                            type="text" 
                            v-model="form.name" 
                            class="form-control" 
                            placeholder="Enter item name"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select v-model="form.category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Current Stock *</label>
                            <input 
                                type="number" 
                                v-model="form.stock" 
                                class="form-control" 
                                placeholder="0"
                                min="0"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label>Minimum Stock *</label>
                            <input 
                                type="number" 
                                v-model="form.minStock" 
                                class="form-control" 
                                placeholder="0"
                                min="0"
                                required
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Unit *</label>
                        <input 
                            type="text" 
                            v-model="form.unit" 
                            class="form-control" 
                            placeholder="e.g., pcs, sets, kg"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea 
                            v-model="form.description" 
                            class="form-control" 
                            rows="3"
                            placeholder="Enter item description..."
                        ></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
            <div class="modal-content modal-sm" @click.stop>
                <div class="modal-header">
                    <h2>Delete Item</h2>
                    <button class="modal-close" @click="closeDeleteModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the item <strong>{{ itemToDelete?.name }}</strong>?</p>
                    <p class="text-warning">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="deleteItem" :disabled="deleting">
                        {{ deleting ? 'Deleting...' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useAlert } from '../../composables/useAlert';

export default {
    name: 'LinenHousekeeping',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const items = ref([]);
        const categories = ref([]);
        const loading = ref(false);
        const saving = ref(false);
        const deleting = ref(false);
        const showModal = ref(false);
        const showDeleteModal = ref(false);
        const editingItem = ref(null);
        const itemToDelete = ref(null);
        const searchQuery = ref('');
        const selectedCategory = ref('');
        const stockFilter = ref('');
        const currentPage = ref(1);
        const itemsPerPage = ref(10);

        const form = ref({
            code: '',
            name: '',
            category_id: '',
            stock: 0,
            minStock: 0,
            unit: '',
            description: ''
        });

        // Filter items based on search and filters
        const filteredItems = computed(() => {
            let filtered = items.value;

            if (searchQuery.value) {
                const query = searchQuery.value.toLowerCase();
                filtered = filtered.filter(item => 
                    item.name.toLowerCase().includes(query) ||
                    item.code.toLowerCase().includes(query)
                );
            }

            if (selectedCategory.value) {
                filtered = filtered.filter(item => item.category_id === selectedCategory.value);
            }

            if (stockFilter.value) {
                filtered = filtered.filter(item => {
                    const status = getStockStatus(item.stock, item.minStock).toLowerCase().replace(/\s+/g, '_');
                    return status === stockFilter.value;
                });
            }

            return filtered;
        });

        // Pagination computed properties
        const totalPages = computed(() => {
            return Math.ceil(filteredItems.value.length / itemsPerPage.value);
        });

        const paginatedItems = computed(() => {
            const start = (currentPage.value - 1) * itemsPerPage.value;
            const end = start + itemsPerPage.value;
            return filteredItems.value.slice(start, end);
        });

        const startIndex = computed(() => {
            return (currentPage.value - 1) * itemsPerPage.value + 1;
        });

        const endIndex = computed(() => {
            const end = currentPage.value * itemsPerPage.value;
            return end > filteredItems.value.length ? filteredItems.value.length : end;
        });

        // Pagination methods
        const goToPage = (page) => {
            if (page >= 1 && page <= totalPages.value) {
                currentPage.value = page;
            }
        };

        const nextPage = () => {
            if (currentPage.value < totalPages.value) {
                currentPage.value++;
            }
        };

        const prevPage = () => {
            if (currentPage.value > 1) {
                currentPage.value--;
            }
        };

        // Reset to page 1 when filters change
        const resetPagination = () => {
            currentPage.value = 1;
        };

        const getStockStatus = (stock, minStock) => {
            if (stock === 0) return 'Out of Stock';
            if (stock <= minStock) return 'Low Stock';
            return 'In Stock';
        };

        const getStockStatusClass = (stock, minStock) => {
            if (stock === 0) return 'status-out';
            if (stock <= minStock) return 'status-low';
            return 'status-in';
        };

        const fetchCategories = () => {
            categories.value = [
                { id: 1, name: 'Bed Linen' },
                { id: 2, name: 'Bath Linen' },
                { id: 3, name: 'Cleaning Supplies' },
                { id: 4, name: 'Housekeeping Tools' },
                { id: 5, name: 'Other' }
            ];
        };

        const fetchItems = () => {
            loading.value = true;
            // Get data from server-bootstrapped data (no API request)
            const bootstrapData = window.__HMS_BOOTSTRAP__?.linenHousekeepingItems || [];
            items.value = bootstrapData;
            loading.value = false;
        };

        const filterItems = () => {
            // Reactive filtering is handled by computed properties
            // Reset to page 1 when filters change
            resetPagination();
        };

        const openCreateModal = () => {
            editingItem.value = null;
            form.value = {
                code: '',
                name: '',
                category_id: '',
                stock: 0,
                minStock: 0,
                unit: '',
                description: ''
            };
            showModal.value = true;
        };

        const openEditModal = (item) => {
            editingItem.value = item;
            form.value = {
                code: item.code,
                name: item.name,
                category_id: item.category_id,
                stock: item.stock,
                minStock: item.minStock,
                unit: item.unit,
                description: item.description || ''
            };
            showModal.value = true;
        };

        const closeModal = () => {
            showModal.value = false;
            editingItem.value = null;
            form.value = {
                code: '',
                name: '',
                category_id: '',
                stock: 0,
                minStock: 0,
                unit: '',
                description: ''
            };
        };

        const saveItem = async () => {
            saving.value = true;
            try {
                const selectedCategoryObj = categories.value.find(cat => cat.id === form.value.category_id);
                const categoryName = selectedCategoryObj ? selectedCategoryObj.name : 'Other';
                
                const data = {
                    code: form.value.code,
                    name: form.value.name,
                    category_id: form.value.category_id,
                    category: categoryName,
                    stock: parseInt(form.value.stock),
                    minStock: parseInt(form.value.minStock),
                    unit: form.value.unit,
                    description: form.value.description || ''
                };

                if (editingItem.value) {
                    // Update existing item
                    await window.axios.post(`/api/linen-housekeeping-items/${editingItem.value.id}`, data);
                    showSuccess('Item updated successfully!');
                } else {
                    // Create new item
                    await window.axios.post('/api/linen-housekeeping-items', data);
                    showSuccess('Item created successfully!');
                }
                
                // Reload page to refresh bootstrap data from database
                window.location.reload();
            } catch (error) {
                console.error('Error saving item:', error);
                if (error.response?.data?.errors) {
                    showError('Validation failed: ' + Object.values(error.response.data.errors).flat().join(', '));
                } else {
                    showError('Failed to save item. Please try again.');
                }
                saving.value = false;
            }
        };

        const confirmDelete = (item) => {
            itemToDelete.value = item;
            showDeleteModal.value = true;
        };

        const closeDeleteModal = () => {
            showDeleteModal.value = false;
            itemToDelete.value = null;
        };

        const deleteItem = async () => {
            if (!itemToDelete.value) return;
            
            deleting.value = true;
            try {
                await window.axios.post(`/api/linen-housekeeping-items/${itemToDelete.value.id}/delete`);
                showSuccess('Item deleted successfully!');
                // Reload page to refresh bootstrap data from database
                window.location.reload();
            } catch (error) {
                console.error('Error deleting item:', error);
                showError('Failed to delete item. Please try again.');
                deleting.value = false;
            }
        };

        // Watch for filter changes and reset pagination
        watch([searchQuery, selectedCategory, stockFilter], () => {
            resetPagination();
        });

        onMounted(() => {
            fetchCategories();
            fetchItems();
        });

        return {
            items,
            categories,
            loading,
            saving,
            deleting,
            showModal,
            showDeleteModal,
            editingItem,
            itemToDelete,
            searchQuery,
            selectedCategory,
            stockFilter,
            form,
            filteredItems,
            paginatedItems,
            currentPage,
            itemsPerPage,
            totalPages,
            startIndex,
            endIndex,
            getStockStatus,
            getStockStatusClass,
            filterItems,
            fetchItems,
            openCreateModal,
            openEditModal,
            closeModal,
            saveItem,
            confirmDelete,
            closeDeleteModal,
            deleteItem,
            goToPage,
            nextPage,
            prevPage,
            resetPagination
        };
    }
}
</script>

<style scoped>
.linen-housekeeping-page {
    padding: 24px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
}

.page-title {
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 4px 0;
}

.page-subtitle {
    font-size: 14px;
    color: #718096;
    margin: 0;
}

.filters-section {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
}

.search-box {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.search-box svg {
    color: #a0aec0;
    flex-shrink: 0;
}

.search-box input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 14px;
    color: #2d3748;
}

.category-filter,
.stock-filter {
    min-width: 180px;
    padding: 10px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    cursor: pointer;
}

.content-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 24px;
}

.loading-state,
.empty-state {
    text-align: center;
    padding: 48px;
    color: #718096;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}

.record-count {
    display: flex;
    align-items: center;
    gap: 8px;
}

.count-label {
    font-size: 14px;
    color: #718096;
    font-weight: 500;
}

.count-value {
    font-size: 14px;
    color: #2d3748;
    font-weight: 600;
}

.inventory-table-wrapper {
    overflow-x: auto;
}

.inventory-table {
    width: 100%;
    border-collapse: collapse;
}

.inventory-table thead {
    background: #f7fafc;
}

.inventory-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #4a5568;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
}

.inventory-table td {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 14px;
    color: #2d3748;
}

.inventory-table tbody tr:hover {
    background: #f7fafc;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-in {
    background: #c6f6d5;
    color: #22543d;
}

.status-low {
    background: #feebc8;
    color: #7c2d12;
}

.status-out {
    background: #fed7d7;
    color: #742a2a;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.icon-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #718096;
    border-radius: 4px;
    transition: all 0.2s;
}

.icon-btn:hover {
    background: #edf2f7;
    color: #4a5568;
}

.icon-btn-danger:hover {
    background: #fed7d7;
    color: #c53030;
}

.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.pagination-info {
    font-size: 14px;
    color: #718096;
    font-weight: 500;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pagination-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
    background: #f7fafc;
    border-color: #cbd5e0;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-btn svg {
    width: 16px;
    height: 16px;
}

.pagination-numbers {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pagination-number {
    min-width: 36px;
    height: 36px;
    padding: 0 8px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pagination-number:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
}

.pagination-number.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

.pagination-number.active:hover {
    background: #5568d3;
    border-color: #5568d3;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-sm {
    max-width: 400px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    color: #718096;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #2c3e50;
}

.modal-body {
    padding: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #2d3748;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    margin-top: 24px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #5568d3;
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover:not(:disabled) {
    background: #cbd5e0;
}

.btn-danger {
    background: #e53e3e;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #c53030;
}

.text-warning {
    color: #d69e2e;
    font-size: 14px;
    margin-top: 8px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .filters-section {
        flex-direction: column;
    }
    
    .category-filter,
    .stock-filter {
        width: 100%;
    }
}
</style>

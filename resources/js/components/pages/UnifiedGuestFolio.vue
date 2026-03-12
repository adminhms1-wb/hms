<template>
    <div class="unified-guest-folio-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Unified Guest Folio</h1>
                <p class="page-subtitle">View and manage guest folios with room, restaurant, and service charges</p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Create Folio
            </button>
        </div>

        <div class="filters-section">
            <div class="search-box">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                    <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input type="text" v-model="searchQuery" placeholder="Search by guest name, reservation..." @input="filterFolios" />
            </div>
            <select v-model="statusFilter" @change="filterFolios" class="status-filter">
                <option value="">All Status</option>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        
        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading folios...</div>
            <div v-else-if="filteredFolios.length === 0" class="empty-state">No folios found</div>
            <div v-else>
                <div class="table-header">
                    <div class="record-count">
                        <span class="count-label">Showing:</span>
                        <span class="count-value">{{ startIndex }}-{{ endIndex }} of {{ filteredFolios.length }}</span>
                    </div>
                </div>
                <div class="inventory-table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Folio #</th>
                                <th>Guest</th>
                                <th>Reservation</th>
                                <th>Subtotal</th>
                                <th>Tax</th>
                                <th>Service</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="folio in paginatedFolios" :key="folio?.id || Math.random()">
                                <td>#{{ folio?.id || '—' }}</td>
                                <td>{{ folio?.guest?.name || folio?.reservation?.guest_name || '—' }}</td>
                                <td>{{ folio?.reservation_id ? `#${folio.reservation_id}` : '—' }}</td>
                                <td>${{ parseFloat(folio?.subtotal || 0).toFixed(2) }}</td>
                                <td>${{ parseFloat(folio?.tax_amount || 0).toFixed(2) }}</td>
                                <td>${{ parseFloat(folio?.service_charge || 0).toFixed(2) }}</td>
                                <td>${{ parseFloat(folio?.total || 0).toFixed(2) }}</td>
                                <td>${{ parseFloat(folio?.paid || 0).toFixed(2) }}</td>
                                <td>${{ parseFloat(folio?.balance || 0).toFixed(2) }}</td>
                                <td>
                                    <span class="status-badge" :class="folio?.status || 'open'">{{ folio?.status || '—' }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-btn" @click="folio && viewFolio(folio)" title="View" :disabled="!folio">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 2C4.66667 2 2 4.66667 2 8C2 11.3333 4.66667 14 8 14C11.3333 14 14 11.3333 14 8C14 4.66667 11.3333 2 8 2ZM8 10.6667C6.53333 10.6667 5.33333 9.46667 5.33333 8C5.33333 6.53333 6.53333 5.33333 8 5.33333C9.46667 5.33333 10.6667 6.53333 10.6667 8C10.6667 9.46667 9.46667 10.6667 8 10.6667Z" stroke="currentColor" stroke-width="1.5"/>
                                            </svg>
                                        </button>
                                        <button class="icon-btn" @click="folio && openEditModal(folio)" title="Edit" :disabled="!folio">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M11.3333 2.00001C11.5084 1.82489 11.7163 1.68603 11.9447 1.59128C12.1731 1.49654 12.4173 1.44775 12.6667 1.44775C12.916 1.44775 13.1602 1.49654 13.3886 1.59128C13.617 1.68603 13.8249 1.82489 14 2.00001C14.1751 2.17513 14.314 2.38305 14.4087 2.61143C14.5035 2.83981 14.5523 3.08405 14.5523 3.33334C14.5523 3.58263 14.5035 3.82687 14.4087 4.05525C14.314 4.28363 14.1751 4.49155 14 4.66667L5 13.6667L1.33333 14.6667L2.33333 11L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <button class="icon-btn icon-btn-danger" @click="folio && confirmDelete(folio)" title="Delete" :disabled="!folio">
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

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingFolio ? 'Edit Folio' : 'Create Folio' }}</h2>
                    <button class="modal-close" @click="closeModal">×</button>
                </div>
                <form @submit.prevent="saveFolio" class="modal-body">
                    <div class="form-group">
                        <label>Reservation *</label>
                        <select v-model="form.reservation_id" class="form-control" required>
                            <option value="">Select Reservation</option>
                            <option v-for="reservation in filteredReservations" :key="reservation?.id || Math.random()" :value="reservation?.id || ''">
                                {{ reservation?.guest_name || reservation?.guest?.name || '—' }}{{ reservation?.room_number ? ' (Room: ' + reservation.room_number + ')' : '' }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Guest</label>
                        <select v-model="form.guest_id" class="form-control">
                            <option value="">Select Guest</option>
                            <option v-for="guest in filteredGuests" :key="guest?.id || Math.random()" :value="guest?.id || ''">{{ guest?.name || '—' }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Folio Date</label>
                        <input type="date" v-model="form.folio_date" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Subtotal</label>
                        <input type="number" step="0.01" v-model="form.subtotal" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Tax Amount</label>
                        <input type="number" step="0.01" v-model="form.tax_amount" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Service Charge</label>
                        <input type="number" step="0.01" v-model="form.service_charge" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Discount</label>
                        <input type="number" step="0.01" v-model="form.discount" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select v-model="form.status" class="form-control">
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea v-model="form.notes" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <!-- Folio Items Management -->
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <label style="margin: 0;">Folio Items</label>
                            <button type="button" class="btn btn-secondary" @click="addFolioItem" style="padding: 6px 12px; font-size: 12px;">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="margin-right: 4px;">
                                    <path d="M7 3V11M3 7H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                Add Item
                            </button>
                        </div>
                        <div v-if="form.items && form.items.length > 0" class="folio-items-list">
                            <table class="items-table" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="padding: 8px; text-align: left; border-bottom: 1px solid #e0e0e0; font-size: 12px;">Description</th>
                                        <th style="padding: 8px; text-align: left; border-bottom: 1px solid #e0e0e0; font-size: 12px;">Module</th>
                                        <th style="padding: 8px; text-align: right; border-bottom: 1px solid #e0e0e0; font-size: 12px;">Amount</th>
                                        <th style="padding: 8px; text-align: center; border-bottom: 1px solid #e0e0e0; font-size: 12px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in form.items" :key="index">
                                        <td style="padding: 8px;">
                                            <input type="text" v-model="item.description" class="form-control" style="font-size: 13px; padding: 6px;" placeholder="Item description" />
                                        </td>
                                        <td style="padding: 8px;">
                                            <select v-model="item.module" class="form-control" style="font-size: 13px; padding: 6px;">
                                                <option value="">Select Module</option>
                                                <option value="room">Room</option>
                                                <option value="restaurant">Restaurant</option>
                                                <option value="service">Service</option>
                                                <option value="amenities">Amenities</option>
                                            </select>
                                        </td>
                                        <td style="padding: 8px;">
                                            <input type="number" step="0.01" v-model.number="item.amount" class="form-control" style="font-size: 13px; padding: 6px; text-align: right;" placeholder="0.00" @input="updateSubtotal" />
                                        </td>
                                        <td style="padding: 8px; text-align: center;">
                                            <button type="button" class="icon-btn icon-btn-danger" @click="removeFolioItem(index)" style="padding: 4px;">
                                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                    <path d="M2 3.5H12M10.5 3.5V11.5C10.5 11.7761 10.2761 12 10 12H4C3.72386 12 3.5 11.7761 3.5 11.5V3.5M5.25 3.5V2.5C5.25 2.22386 5.47386 2 5.75 2H8.25C8.52614 2 8.75 2.22386 8.75 2.5V3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="empty-items" style="padding: 12px; text-align: center; color: #999; font-size: 13px;">
                            No items added. Click "Add Item" to add folio items.
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Folio Modal -->
        <div v-if="showViewModal" class="modal-overlay" @click="closeViewModal">
            <div class="modal-content modal-large" @click.stop>
                <div class="modal-header">
                    <h2>Folio Details #{{ selectedFolio?.id }}</h2>
                    <button class="modal-close" @click="closeViewModal">×</button>
                </div>
                <div class="modal-body" v-if="selectedFolio">
                    <div class="folio-details">
                        <div class="detail-row">
                            <span class="detail-label">Guest:</span>
                            <span class="detail-value">{{ selectedFolio.guest?.name || selectedFolio.reservation?.guest_name || '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Reservation:</span>
                            <span class="detail-value">{{ selectedFolio.reservation_id ? `#${selectedFolio.reservation_id}` : '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Subtotal:</span>
                            <span class="detail-value">${{ parseFloat(selectedFolio.subtotal || 0).toFixed(2) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tax:</span>
                            <span class="detail-value">${{ parseFloat(selectedFolio.tax_amount || 0).toFixed(2) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Service Charge:</span>
                            <span class="detail-value">${{ parseFloat(selectedFolio.service_charge || 0).toFixed(2) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total:</span>
                            <span class="detail-value">${{ parseFloat(selectedFolio.total || 0).toFixed(2) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Paid:</span>
                            <span class="detail-value">${{ parseFloat(selectedFolio.paid || 0).toFixed(2) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Balance:</span>
                            <span class="detail-value">${{ parseFloat(selectedFolio.balance || 0).toFixed(2) }}</span>
                        </div>
                    </div>
                    <div v-if="selectedFolio.items && selectedFolio.items.length > 0" class="folio-items">
                        <h3>Folio Items</h3>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Module</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in selectedFolio.items" :key="item.id">
                                    <td>{{ item.description }}</td>
                                    <td>{{ item.module || '—' }}</td>
                                    <td>${{ parseFloat(item.amount || 0).toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeViewModal">Close</button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>Confirm Delete</h2>
                    <button class="modal-close" @click="closeDeleteModal">×</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this folio? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="deleteFolio" :disabled="deleting">{{ deleting ? 'Deleting...' : 'Delete' }}</button>
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

const folios = ref([]);
const reservations = ref([]);
const guests = ref([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const searchQuery = ref('');
const statusFilter = ref('');
const showModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const editingFolio = ref(null);
const selectedFolio = ref(null);
const folioToDelete = ref(null);
const currentPage = ref(1);
const itemsPerPage = 10;

const form = ref({
    reservation_id: '',
    guest_id: '',
    folio_date: '',
    subtotal: 0,
    tax_amount: 0,
    service_charge: 0,
    discount: 0,
    status: 'open',
    notes: '',
    items: [],
});

const filteredFolios = computed(() => {
    let filtered = folios.value || [];
    
    // Filter out any null or undefined entries
    filtered = filtered.filter(f => f != null);
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(f => 
            f && (
                (f.guest?.name || '').toLowerCase().includes(query) ||
                (f.reservation?.guest_name || '').toLowerCase().includes(query) ||
                (f.id ? f.id.toString().includes(query) : false)
            )
        );
    }
    
    if (statusFilter.value) {
        filtered = filtered.filter(f => f && f.status === statusFilter.value);
    }
    
    return filtered;
});

const totalPages = computed(() => Math.ceil(filteredFolios.value.length / itemsPerPage));
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage + 1);
const endIndex = computed(() => Math.min(currentPage.value * itemsPerPage, filteredFolios.value.length));
const paginatedFolios = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredFolios.value.slice(start, start + itemsPerPage);
});

const filteredReservations = computed(() => {
    return (reservations.value || []).filter(r => r != null && r.id != null);
});

const filteredGuests = computed(() => {
    return (guests.value || []).filter(g => g != null && g.id != null);
});

const loadFolios = async () => {
    loading.value = true;
    try {
        // Try to load from bootstrap first
        if (window.__HMS_BOOTSTRAP__ && window.__HMS_BOOTSTRAP__.folios) {
            const data = window.__HMS_BOOTSTRAP__.folios || [];
            folios.value = Array.isArray(data) ? data.filter(f => f != null && f.id != null) : [];
            loading.value = false;
            return;
        }
        
        // Fallback to API
        const response = await window.axios.get('/api/unified-guest-folio');
        const data = response.data.folios || [];
        // Filter out any null or invalid entries
        folios.value = Array.isArray(data) ? data.filter(f => f != null && f.id != null) : [];
    } catch (error) {
        console.error('Error loading folios:', error);
        showError('Failed to load folios');
        folios.value = [];
    } finally {
        loading.value = false;
    }
};

const loadReservations = async () => {
    try {
        // Try to load from bootstrap first
        if (window.__HMS_BOOTSTRAP__ && window.__HMS_BOOTSTRAP__.reservations) {
            const data = window.__HMS_BOOTSTRAP__.reservations || [];
            reservations.value = Array.isArray(data) ? data.filter(r => r != null && r.id != null) : [];
            return;
        }
        
        // Fallback to API - get all reservations without pagination
        const response = await window.axios.get('/api/reservations', {
            params: {
                per_page: 1000, // Get a large number of reservations
            }
        });
        
        // Handle different response structures
        let data = [];
        if (response.data.success && response.data.data) {
            // Paginated response
            if (response.data.data.data && Array.isArray(response.data.data.data)) {
                data = response.data.data.data;
            } else if (Array.isArray(response.data.data)) {
                data = response.data.data;
            }
        } else if (response.data.reservations && Array.isArray(response.data.reservations)) {
            data = response.data.reservations;
        } else if (response.data.data && Array.isArray(response.data.data)) {
            data = response.data.data;
        }
        
        // Filter out any null or invalid entries
        reservations.value = Array.isArray(data) ? data.filter(r => r != null && r.id != null) : [];
    } catch (error) {
        console.error('Error loading reservations:', error);
        reservations.value = [];
    }
};

const loadGuests = async () => {
    try {
        const response = await window.axios.get('/api/guests');
        const data = response.data.data?.data || response.data.data || response.data || [];
        // Filter out any null or invalid entries
        guests.value = Array.isArray(data) ? data.filter(g => g != null && g.id != null) : [];
    } catch (error) {
        console.error('Error loading guests:', error);
        guests.value = [];
    }
};

const filterFolios = () => {
    currentPage.value = 1;
};

const addFolioItem = () => {
    if (!form.value.items) {
        form.value.items = [];
    }
    form.value.items.push({
        description: '',
        module: '',
        amount: 0,
    });
};

const removeFolioItem = (index) => {
    if (form.value.items && form.value.items.length > index) {
        form.value.items.splice(index, 1);
        updateSubtotal();
    }
};

const updateSubtotal = () => {
    if (form.value.items && form.value.items.length > 0) {
        const total = form.value.items.reduce((sum, item) => {
            return sum + parseFloat(item.amount || 0);
        }, 0);
        form.value.subtotal = total;
    } else {
        form.value.subtotal = 0;
    }
};

const openCreateModal = () => {
    editingFolio.value = null;
    form.value = {
        reservation_id: '',
        guest_id: '',
        folio_date: new Date().toISOString().split('T')[0],
        subtotal: 0,
        tax_amount: 0,
        service_charge: 0,
        discount: 0,
        status: 'open',
        notes: '',
        items: [],
    };
    showModal.value = true;
};

const openEditModal = async (folio) => {
    editingFolio.value = folio;
    
    // Load folio items if not already loaded
    let folioItems = [];
    if (folio.items && folio.items.length > 0) {
        folioItems = folio.items.map(item => ({
            id: item.id,
            description: item.description || '',
            module: item.module || '',
            amount: parseFloat(item.amount || 0),
        }));
    } else {
        // Fetch items if not loaded
        try {
            const response = await window.axios.get(`/api/unified-guest-folio/${folio.id}`);
            if (response.data.folio && response.data.folio.items) {
                folioItems = response.data.folio.items.map(item => ({
                    id: item.id,
                    description: item.description || '',
                    module: item.module || '',
                    amount: parseFloat(item.amount || 0),
                }));
            }
        } catch (error) {
            console.error('Error loading folio items:', error);
        }
    }
    
    form.value = {
        reservation_id: folio.reservation_id || '',
        guest_id: folio.guest_id || '',
        folio_date: folio.folio_date ? formatDate(folio.folio_date) : '',
        subtotal: folio.subtotal || 0,
        tax_amount: folio.tax_amount || 0,
        service_charge: folio.service_charge || 0,
        discount: folio.discount || 0,
        status: folio.status || 'open',
        notes: folio.notes || '',
        items: folioItems,
    };
    showModal.value = true;
};

const viewFolio = async (folio) => {
    try {
        const response = await window.axios.get(`/api/unified-guest-folio/${folio.id}`);
        selectedFolio.value = response.data.folio;
        showViewModal.value = true;
    } catch (error) {
        console.error('Error loading folio details:', error);
        showError('Failed to load folio details');
    }
};

const saveFolio = async () => {
    saving.value = true;
    try {
        // Calculate subtotal from items if items exist
        if (form.value.items && form.value.items.length > 0) {
            const itemsTotal = form.value.items.reduce((sum, item) => {
                return sum + parseFloat(item.amount || 0);
            }, 0);
            form.value.subtotal = itemsTotal;
        }
        
        const total = parseFloat(form.value.subtotal || 0) + 
                     parseFloat(form.value.tax_amount || 0) + 
                     parseFloat(form.value.service_charge || 0) - 
                     parseFloat(form.value.discount || 0);
        
        const payload = {
            reservation_id: form.value.reservation_id || null,
            guest_id: form.value.guest_id || null,
            folio_date: form.value.folio_date || null,
            subtotal: form.value.subtotal || 0,
            tax_amount: form.value.tax_amount || 0,
            service_charge: form.value.service_charge || 0,
            discount: form.value.discount || 0,
            status: form.value.status || 'open',
            notes: form.value.notes || '',
            items: form.value.items || [],
        };
        
        if (editingFolio.value) {
            // Add _method for method spoofing
            const updatePayload = { ...payload, _method: 'PUT' };
            await window.axios.post(`/api/unified-guest-folio/${editingFolio.value.id}`, updatePayload);
            showSuccess('Folio updated successfully');
        } else {
            await window.axios.post('/api/unified-guest-folio', payload);
            showSuccess('Folio created successfully');
        }
        
        closeModal();
        // Delay reload to allow alert message to be visible
        setTimeout(() => {
            location.reload();
        }, 1000);
    } catch (error) {
        console.error('Error saving folio:', error);
        showError(error.response?.data?.message || 'Failed to save folio');
    } finally {
        saving.value = false;
    }
};

const confirmDelete = (folio) => {
    folioToDelete.value = folio;
    showDeleteModal.value = true;
};

const deleteFolio = async () => {
    if (!folioToDelete.value) return;
    
    deleting.value = true;
    try {
        await window.axios.post(`/api/unified-guest-folio/${folioToDelete.value.id}/delete`);
        showSuccess('Folio deleted successfully');
        closeDeleteModal();
        // Delay reload to allow alert message to be visible
        setTimeout(() => {
            location.reload();
        }, 1000);
    } catch (error) {
        console.error('Error deleting folio:', error);
        showError('Failed to delete folio');
    } finally {
        deleting.value = false;
    }
};

const closeModal = () => {
    showModal.value = false;
    editingFolio.value = null;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedFolio.value = null;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    folioToDelete.value = null;
};

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

const goToPage = (page) => {
    currentPage.value = page;
};

onMounted(() => {
    loadFolios();
    loadReservations();
    loadGuests();
});
</script>

<style scoped>
.unified-guest-folio-page {
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
    color: #1a1a1a;
    margin: 0 0 4px 0;
}

.page-subtitle {
    font-size: 14px;
    color: #666;
    margin: 0;
}

.filters-section {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
}

.search-box {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
}

.search-box svg {
    color: #999;
}

.search-box input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 14px;
}

.status-filter {
    padding: 10px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fff;
    font-size: 14px;
    min-width: 150px;
}

.content-card {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.loading-state, .empty-state {
    text-align: center;
    padding: 40px;
    color: #999;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.record-count {
    font-size: 14px;
    color: #666;
}

.count-label {
    margin-right: 8px;
}

.inventory-table-wrapper {
    overflow-x: auto;
}

.inventory-table {
    width: 100%;
    border-collapse: collapse;
}

.inventory-table th {
    text-align: left;
    padding: 12px;
    font-weight: 600;
    font-size: 13px;
    color: #666;
    border-bottom: 2px solid #e0e0e0;
}

.inventory-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.status-badge.open {
    background: #e3f2fd;
    color: #1976d2;
}

.status-badge.closed {
    background: #e8f5e9;
    color: #388e3c;
}

.status-badge.cancelled {
    background: #ffebee;
    color: #d32f2f;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.icon-btn {
    padding: 6px;
    border: none;
    background: transparent;
    color: #666;
    cursor: pointer;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-btn:hover {
    background: #f5f5f5;
    color: #333;
}

.icon-btn-danger:hover {
    background: #ffebee;
    color: #d32f2f;
}

.pagination-wrapper {
    margin-top: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pagination-controls {
    display: flex;
    gap: 8px;
    align-items: center;
}

.pagination-btn {
    padding: 8px 16px;
    border: 1px solid #e0e0e0;
    background: #fff;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
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
    padding: 8px 12px;
    border: 1px solid #e0e0e0;
    background: #fff;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.pagination-number.active {
    background: #1976d2;
    color: #fff;
    border-color: #1976d2;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: #fff;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-large {
    max-width: 900px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e0e0e0;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-body {
    padding: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    font-size: 14px;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #1976d2;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid #e0e0e0;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
}

.btn-primary {
    background: #1976d2;
    color: #fff;
}

.btn-secondary {
    background: #f5f5f5;
    color: #333;
}

.btn-danger {
    background: #d32f2f;
    color: #fff;
}

.folio-details {
    margin-bottom: 24px;
}

.detail-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.detail-label {
    font-weight: 500;
    width: 150px;
    color: #666;
}

.detail-value {
    flex: 1;
    color: #333;
}

.folio-items {
    margin-top: 24px;
}

.folio-items h3 {
    margin: 0 0 16px 0;
    font-size: 18px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th,
.items-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.items-table th {
    font-weight: 600;
    color: #666;
}
</style>

<template>
    <div class="stock-alerts-tracking-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Stock Alerts & Tracking</h1>
                <p class="page-subtitle">Monitor low stock alerts, expiry tracking, and usage consumption</p>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs-navigation">
            <button 
                class="tab-btn" 
                :class="{ active: activeTab === 'low_stock' }"
                @click="activeTab = 'low_stock'"
            >
                <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                    <path d="M10 2L3 5V9C3 13.55 6.36 17.74 10 19C13.64 17.74 17 13.55 17 9V5L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 10V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="10" cy="7" r="1" fill="currentColor"/>
                </svg>
                Low Stock Alerts
                <span v-if="lowStockAlerts.length > 0" class="badge-count">{{ lowStockAlerts.length }}</span>
            </button>
            <button 
                class="tab-btn" 
                :class="{ active: activeTab === 'expiry' }"
                @click="activeTab = 'expiry'"
            >
                <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                    <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="M10 6V10L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Expiry Tracking
                <span v-if="expiringSoonCount > 0" class="badge-count warning">{{ expiringSoonCount }}</span>
            </button>
            <button 
                class="tab-btn" 
                :class="{ active: activeTab === 'usage' }"
                @click="activeTab = 'usage'"
            >
                <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                    <path d="M3 4H17V16H3V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3 8H17M7 8V16M13 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Usage Consumption
            </button>
        </div>

        <!-- Low Stock Alerts Tab -->
        <div v-if="activeTab === 'low_stock'" class="tab-content">
            <div class="filters-section">
                <div class="search-box">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                        <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input 
                        type="text" 
                        v-model="lowStockSearch" 
                        placeholder="Search by name, code, or category..."
                        @input="filterLowStock"
                    />
                </div>
                <select v-model="lowStockStatusFilter" @change="filterLowStock" class="status-filter">
                    <option value="">All Status</option>
                    <option value="out_of_stock">Out of Stock</option>
                    <option value="low_stock">Low Stock</option>
                </select>
                <select v-model="lowStockTypeFilter" @change="filterLowStock" class="type-filter">
                    <option value="">All Types</option>
                    <option value="Inventory Store">Inventory Store</option>
                    <option value="Linen & Housekeeping">Linen & Housekeeping</option>
                    <option value="Amenities Consumables">Amenities Consumables</option>
                    <option value="Inventory Items">Inventory Items</option>
                </select>
            </div>
            
            <div class="content-card">
                <div v-if="loading" class="loading-state">
                    <p>Loading alerts...</p>
                </div>
                
                <div v-else-if="filteredLowStock.length === 0" class="empty-state">
                    <p>No low stock alerts found</p>
                </div>
                
                <div v-else>
                    <div class="table-header">
                        <div class="record-count">
                            <span class="count-label">Showing:</span>
                            <span class="count-value">{{ lowStockStartIndex }}-{{ lowStockEndIndex }} of {{ filteredLowStock.length }} (Total: {{ lowStockAlerts.length }})</span>
                        </div>
                    </div>
                    <div class="inventory-table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Current Stock</th>
                                <th>Min Stock</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="alert in paginatedLowStock" :key="`${alert.type}-${alert.id}`">
                                <td>{{ alert.code || '—' }}</td>
                                <td>{{ alert.name }}</td>
                                <td>{{ alert.category || '—' }}</td>
                                <td>{{ alert.type }}</td>
                                <td>{{ alert.current_stock }}</td>
                                <td>{{ alert.min_stock }}</td>
                                <td>{{ alert.unit }}</td>
                                <td>
                                    <span :class="['status-badge', alert.status === 'out_of_stock' ? 'status-out' : 'status-low']">
                                        {{ alert.status === 'out_of_stock' ? 'Out of Stock' : 'Low Stock' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-btn" @click="openEditStockModal(alert)" title="Edit Stock">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M11.3333 2.00001C11.5084 1.82489 11.7163 1.68603 11.9447 1.59128C12.1731 1.49654 12.4173 1.44775 12.6667 1.44775C12.916 1.44775 13.1602 1.49654 13.3886 1.59128C13.617 1.68603 13.8249 1.82489 14 2.00001C14.1751 2.17513 14.314 2.38305 14.4087 2.61143C14.5035 2.83981 14.5523 3.08405 14.5523 3.33334C14.5523 3.58263 14.5035 3.82687 14.4087 4.05525C14.314 4.28363 14.1751 4.49155 14 4.66667L5 13.6667L1.33333 14.6667L2.33333 11L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <button class="icon-btn icon-btn-danger" @click="confirmDeleteStock(alert)" title="Delete">
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
                    
                    <div v-if="lowStockTotalPages > 1" class="pagination-wrapper">
                        <div class="pagination-info">
                            <span>Page {{ lowStockCurrentPage }} of {{ lowStockTotalPages }}</span>
                        </div>
                        <div class="pagination-controls">
                            <button class="pagination-btn" @click="lowStockPrevPage" :disabled="lowStockCurrentPage === 1">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Previous
                            </button>
                            <div class="pagination-numbers">
                                <button
                                    v-for="page in lowStockTotalPages"
                                    :key="page"
                                    class="pagination-number"
                                    :class="{ active: page === lowStockCurrentPage }"
                                    @click="lowStockGoToPage(page)"
                                >
                                    {{ page }}
                                </button>
                            </div>
                            <button class="pagination-btn" @click="lowStockNextPage" :disabled="lowStockCurrentPage === lowStockTotalPages">
                                Next
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiry Tracking Tab -->
        <div v-if="activeTab === 'expiry'" class="tab-content">
            <div class="filters-section">
                <div class="search-box">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                        <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input 
                        type="text" 
                        v-model="expirySearch" 
                        placeholder="Search by name or category..."
                        @input="filterExpiry"
                    />
                </div>
                <select v-model="expiryStatusFilter" @change="filterExpiry" class="status-filter">
                    <option value="">All Items</option>
                    <option value="expiring_soon">Expiring Soon (≤30 days)</option>
                    <option value="normal">Normal (>30 days)</option>
                </select>
            </div>
            
            <div class="content-card">
                <div v-if="loading" class="loading-state">
                    <p>Loading expiry data...</p>
                </div>
                
                <div v-else-if="filteredExpiry.length === 0" class="empty-state">
                    <p>No items with expiry dates found</p>
                </div>
                
                <div v-else>
                    <div class="table-header">
                        <div class="record-count">
                            <span class="count-label">Showing:</span>
                            <span class="count-value">{{ expiryStartIndex }}-{{ expiryEndIndex }} of {{ filteredExpiry.length }} (Total: {{ expiryAlerts.length }})</span>
                        </div>
                    </div>
                    <div class="inventory-table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Expiry Date</th>
                                <th>Days Until Expiry</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in paginatedExpiry" :key="item.id">
                                <td>{{ item.name }}</td>
                                <td>{{ item.category || '—' }}</td>
                                <td>{{ item.quantity }}</td>
                                <td>{{ item.expiry_date }}</td>
                                <td>
                                    <span :class="{'text-warning': item.days_until_expiry <= 7, 'text-danger': item.days_until_expiry < 0}">
                                        {{ item.days_until_expiry < 0 ? 'Expired' : item.days_until_expiry + ' days' }}
                                    </span>
                                </td>
                                <td>
                                    <span :class="['status-badge', item.days_until_expiry <= 7 ? 'status-out' : (item.days_until_expiry <= 30 ? 'status-low' : 'status-in')]">
                                        {{ item.days_until_expiry <= 7 ? 'Expiring Soon' : (item.days_until_expiry <= 30 ? 'Expiring Soon' : 'Normal') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-btn" @click="openEditExpiryModal(item)" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M11.3333 2.00001C11.5084 1.82489 11.7163 1.68603 11.9447 1.59128C12.1731 1.49654 12.4173 1.44775 12.6667 1.44775C12.916 1.44775 13.1602 1.49654 13.3886 1.59128C13.617 1.68603 13.8249 1.82489 14 2.00001C14.1751 2.17513 14.314 2.38305 14.4087 2.61143C14.5035 2.83981 14.5523 3.08405 14.5523 3.33334C14.5523 3.58263 14.5035 3.82687 14.4087 4.05525C14.314 4.28363 14.1751 4.49155 14 4.66667L5 13.6667L1.33333 14.6667L2.33333 11L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <button class="icon-btn icon-btn-danger" @click="confirmDeleteExpiry(item)" title="Delete">
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
                    
                    <div v-if="expiryTotalPages > 1" class="pagination-wrapper">
                        <div class="pagination-info">
                            <span>Page {{ expiryCurrentPage }} of {{ expiryTotalPages }}</span>
                        </div>
                        <div class="pagination-controls">
                            <button class="pagination-btn" @click="expiryPrevPage" :disabled="expiryCurrentPage === 1">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Previous
                            </button>
                            <div class="pagination-numbers">
                                <button
                                    v-for="page in expiryTotalPages"
                                    :key="page"
                                    class="pagination-number"
                                    :class="{ active: page === expiryCurrentPage }"
                                    @click="expiryGoToPage(page)"
                                >
                                    {{ page }}
                                </button>
                            </div>
                            <button class="pagination-btn" @click="expiryNextPage" :disabled="expiryCurrentPage === expiryTotalPages">
                                Next
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Consumption Tab -->
        <div v-if="activeTab === 'usage'" class="tab-content">
            <div class="filters-section">
                <div class="search-box">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                        <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input 
                        type="text" 
                        v-model="usageSearch" 
                        placeholder="Search by item name..."
                        @input="filterUsage"
                    />
                </div>
                <select v-model="usageSourceFilter" @change="filterUsage" class="source-filter">
                    <option value="">All Sources</option>
                    <option value="Room Service">Room Service</option>
                    <option value="Restaurant">Restaurant</option>
                </select>
            </div>
            
            <div class="content-card">
                <div v-if="loading" class="loading-state">
                    <p>Loading usage data...</p>
                </div>
                
                <div v-else-if="filteredUsage.length === 0" class="empty-state">
                    <p>No usage consumption data found</p>
                </div>
                
                <div v-else>
                    <div class="table-header">
                        <div class="record-count">
                            <span class="count-label">Showing:</span>
                            <span class="count-value">{{ usageStartIndex }}-{{ usageEndIndex }} of {{ filteredUsage.length }} (Total: {{ usageConsumption.length }})</span>
                        </div>
                    </div>
                    <div class="inventory-table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Source</th>
                                <th>Quantity Consumed</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(usage, index) in paginatedUsage" :key="index">
                                <td>{{ usage.item_name }}</td>
                                <td>
                                    <span :class="['status-badge', usage.source === 'Room Service' ? 'status-in' : 'status-low']">
                                        {{ usage.source }}
                                    </span>
                                </td>
                                <td>{{ usage.quantity }}</td>
                                <td>{{ usage.unit }}</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    
                    <div v-if="usageTotalPages > 1" class="pagination-wrapper">
                        <div class="pagination-info">
                            <span>Page {{ usageCurrentPage }} of {{ usageTotalPages }}</span>
                        </div>
                        <div class="pagination-controls">
                            <button class="pagination-btn" @click="usagePrevPage" :disabled="usageCurrentPage === 1">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Previous
                            </button>
                            <div class="pagination-numbers">
                                <button
                                    v-for="page in usageTotalPages"
                                    :key="page"
                                    class="pagination-number"
                                    :class="{ active: page === usageCurrentPage }"
                                    @click="usageGoToPage(page)"
                                >
                                    {{ page }}
                                </button>
                            </div>
                            <button class="pagination-btn" @click="usageNextPage" :disabled="usageCurrentPage === usageTotalPages">
                                Next
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Stock Modal -->
        <div v-if="showStockModal" class="modal-overlay" @click="closeStockModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>Update Stock</h2>
                    <button class="modal-close" @click="closeStockModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="updateStock" class="modal-body">
                    <div class="form-group">
                        <label>Item Name</label>
                        <input 
                            type="text" 
                            :value="editingStockItem?.name" 
                            class="form-control" 
                            disabled
                        />
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <input 
                            type="text" 
                            :value="editingStockItem?.type" 
                            class="form-control" 
                            disabled
                        />
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Current Stock *</label>
                            <input 
                                type="number" 
                                v-model="stockForm.stock" 
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
                                v-model="stockForm.min_stock" 
                                class="form-control" 
                                placeholder="0"
                                min="0"
                                required
                            />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeStockModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Update Stock' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Expiry Modal -->
        <div v-if="showExpiryModal" class="modal-overlay" @click="closeExpiryModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>Update Expiry Information</h2>
                    <button class="modal-close" @click="closeExpiryModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="updateExpiry" class="modal-body">
                    <div class="form-group">
                        <label>Item Name</label>
                        <input 
                            type="text" 
                            :value="editingExpiryItem?.name" 
                            class="form-control" 
                            disabled
                        />
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Quantity *</label>
                            <input 
                                type="number" 
                                v-model="expiryForm.quantity" 
                                class="form-control" 
                                placeholder="0"
                                min="0"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input 
                                type="date" 
                                v-model="expiryForm.expiry_date" 
                                class="form-control"
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Threshold</label>
                        <input 
                            type="number" 
                            v-model="expiryForm.threshold" 
                            class="form-control" 
                            placeholder="0"
                            min="0"
                        />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeExpiryModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Update' }}
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
                    <p>Are you sure you want to delete <strong>{{ itemToDelete?.name }}</strong>?</p>
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
import { ref, computed, onMounted } from 'vue';
import { useAlert } from '../../composables/useAlert';

export default {
    name: 'StockAlertsTracking',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const loading = ref(false);
        const saving = ref(false);
        const deleting = ref(false);
        const activeTab = ref('low_stock');
        
        // Modals
        const showStockModal = ref(false);
        const showExpiryModal = ref(false);
        const showDeleteModal = ref(false);
        const editingStockItem = ref(null);
        const editingExpiryItem = ref(null);
        const itemToDelete = ref(null);
        const deleteType = ref('');
        
        // Forms
        const stockForm = ref({
            stock: 0,
            min_stock: 0
        });
        
        const expiryForm = ref({
            quantity: 0,
            expiry_date: '',
            threshold: 0
        });
        
        // Low Stock Alerts
        const lowStockAlerts = ref([]);
        const lowStockSearch = ref('');
        const lowStockStatusFilter = ref('');
        const lowStockTypeFilter = ref('');
        const lowStockCurrentPage = ref(1);
        const lowStockItemsPerPage = ref(10);
        
        // Expiry Tracking
        const expiryAlerts = ref([]);
        const expirySearch = ref('');
        const expiryStatusFilter = ref('');
        const expiryCurrentPage = ref(1);
        const expiryItemsPerPage = ref(10);
        
        // Usage Consumption
        const usageConsumption = ref([]);
        const usageSearch = ref('');
        const usageSourceFilter = ref('');
        const usageCurrentPage = ref(1);
        const usageItemsPerPage = ref(10);

        // Low Stock Filters & Pagination
        const filteredLowStock = computed(() => {
            let filtered = lowStockAlerts.value;

            if (lowStockSearch.value) {
                const query = lowStockSearch.value.toLowerCase();
                filtered = filtered.filter(item => 
                    item.name.toLowerCase().includes(query) ||
                    (item.code && item.code.toLowerCase().includes(query)) ||
                    (item.category && item.category.toLowerCase().includes(query))
                );
            }

            if (lowStockStatusFilter.value) {
                filtered = filtered.filter(item => item.status === lowStockStatusFilter.value);
            }

            if (lowStockTypeFilter.value) {
                filtered = filtered.filter(item => item.type === lowStockTypeFilter.value);
            }

            return filtered;
        });

        const lowStockTotalPages = computed(() => {
            return Math.ceil(filteredLowStock.value.length / lowStockItemsPerPage.value);
        });

        const paginatedLowStock = computed(() => {
            const start = (lowStockCurrentPage.value - 1) * lowStockItemsPerPage.value;
            const end = start + lowStockItemsPerPage.value;
            return filteredLowStock.value.slice(start, end);
        });

        const lowStockStartIndex = computed(() => {
            return (lowStockCurrentPage.value - 1) * lowStockItemsPerPage.value + 1;
        });

        const lowStockEndIndex = computed(() => {
            const end = lowStockCurrentPage.value * lowStockItemsPerPage.value;
            return end > filteredLowStock.value.length ? filteredLowStock.value.length : end;
        });

        const lowStockGoToPage = (page) => {
            if (page >= 1 && page <= lowStockTotalPages.value) {
                lowStockCurrentPage.value = page;
            }
        };

        const lowStockNextPage = () => {
            if (lowStockCurrentPage.value < lowStockTotalPages.value) {
                lowStockCurrentPage.value++;
            }
        };

        const lowStockPrevPage = () => {
            if (lowStockCurrentPage.value > 1) {
                lowStockCurrentPage.value--;
            }
        };

        const filterLowStock = () => {
            lowStockCurrentPage.value = 1;
        };

        // Expiry Filters & Pagination
        const filteredExpiry = computed(() => {
            let filtered = expiryAlerts.value;

            if (expirySearch.value) {
                const query = expirySearch.value.toLowerCase();
                filtered = filtered.filter(item => 
                    item.name.toLowerCase().includes(query) ||
                    (item.category && item.category.toLowerCase().includes(query))
                );
            }

            if (expiryStatusFilter.value) {
                filtered = filtered.filter(item => item.status === expiryStatusFilter.value);
            }

            return filtered;
        });

        const expiringSoonCount = computed(() => {
            return expiryAlerts.value.filter(item => item.days_until_expiry <= 30).length;
        });

        const expiryTotalPages = computed(() => {
            return Math.ceil(filteredExpiry.value.length / expiryItemsPerPage.value);
        });

        const paginatedExpiry = computed(() => {
            const start = (expiryCurrentPage.value - 1) * expiryItemsPerPage.value;
            const end = start + expiryItemsPerPage.value;
            return filteredExpiry.value.slice(start, end);
        });

        const expiryStartIndex = computed(() => {
            return (expiryCurrentPage.value - 1) * expiryItemsPerPage.value + 1;
        });

        const expiryEndIndex = computed(() => {
            const end = expiryCurrentPage.value * expiryItemsPerPage.value;
            return end > filteredExpiry.value.length ? filteredExpiry.value.length : end;
        });

        const expiryGoToPage = (page) => {
            if (page >= 1 && page <= expiryTotalPages.value) {
                expiryCurrentPage.value = page;
            }
        };

        const expiryNextPage = () => {
            if (expiryCurrentPage.value < expiryTotalPages.value) {
                expiryCurrentPage.value++;
            }
        };

        const expiryPrevPage = () => {
            if (expiryCurrentPage.value > 1) {
                expiryCurrentPage.value--;
            }
        };

        const filterExpiry = () => {
            expiryCurrentPage.value = 1;
        };

        // Usage Filters & Pagination
        const filteredUsage = computed(() => {
            let filtered = usageConsumption.value;

            if (usageSearch.value) {
                const query = usageSearch.value.toLowerCase();
                filtered = filtered.filter(item => 
                    item.item_name.toLowerCase().includes(query)
                );
            }

            if (usageSourceFilter.value) {
                filtered = filtered.filter(item => item.source === usageSourceFilter.value);
            }

            return filtered;
        });

        const usageTotalPages = computed(() => {
            return Math.ceil(filteredUsage.value.length / usageItemsPerPage.value);
        });

        const paginatedUsage = computed(() => {
            const start = (usageCurrentPage.value - 1) * usageItemsPerPage.value;
            const end = start + usageItemsPerPage.value;
            return filteredUsage.value.slice(start, end);
        });

        const usageStartIndex = computed(() => {
            return (usageCurrentPage.value - 1) * usageItemsPerPage.value + 1;
        });

        const usageEndIndex = computed(() => {
            const end = usageCurrentPage.value * usageItemsPerPage.value;
            return end > filteredUsage.value.length ? filteredUsage.value.length : end;
        });

        const usageGoToPage = (page) => {
            if (page >= 1 && page <= usageTotalPages.value) {
                usageCurrentPage.value = page;
            }
        };

        const usageNextPage = () => {
            if (usageCurrentPage.value < usageTotalPages.value) {
                usageCurrentPage.value++;
            }
        };

        const usagePrevPage = () => {
            if (usageCurrentPage.value > 1) {
                usageCurrentPage.value--;
            }
        };

        const filterUsage = () => {
            usageCurrentPage.value = 1;
        };

        const fetchData = async () => {
            loading.value = true;
            try {
                const response = await window.axios.get('/api/stock-alerts-tracking');
                lowStockAlerts.value = response.data.low_stock_alerts || [];
                expiryAlerts.value = response.data.expiry_alerts || [];
                usageConsumption.value = response.data.usage_consumption || [];
            } catch (error) {
                console.error('Error fetching alerts data:', error);
                showError('Failed to load alerts data. Please try again.');
            } finally {
                loading.value = false;
            }
        };

        // Stock CRUD Operations
        const getItemTypeSlug = (type) => {
            const typeMap = {
                'Inventory Store': 'inventory_store',
                'Linen & Housekeeping': 'linen_housekeeping',
                'Amenities Consumables': 'amenities_consumables',
                'Inventory Items': 'inventory_items'
            };
            return typeMap[type] || 'inventory_store';
        };

        const openEditStockModal = (item) => {
            editingStockItem.value = item;
            stockForm.value = {
                stock: item.current_stock,
                min_stock: item.min_stock
            };
            showStockModal.value = true;
        };

        const closeStockModal = () => {
            showStockModal.value = false;
            editingStockItem.value = null;
            stockForm.value = {
                stock: 0,
                min_stock: 0
            };
        };

        const updateStock = async () => {
            if (!editingStockItem.value) return;
            
            saving.value = true;
            try {
                const typeSlug = getItemTypeSlug(editingStockItem.value.type);
                await window.axios.post(`/api/stock-alerts-tracking/update-stock/${typeSlug}/${editingStockItem.value.id}`, {
                    stock: parseInt(stockForm.value.stock),
                    min_stock: parseInt(stockForm.value.min_stock)
                });
                showSuccess('Stock updated successfully!');
                closeStockModal();
                fetchData(); // Refresh data
            } catch (error) {
                console.error('Error updating stock:', error);
                if (error.response?.data?.errors) {
                    showError('Validation failed: ' + Object.values(error.response.data.errors).flat().join(', '));
                } else {
                    showError('Failed to update stock. Please try again.');
                }
            } finally {
                saving.value = false;
            }
        };

        const confirmDeleteStock = (item) => {
            itemToDelete.value = item;
            deleteType.value = getItemTypeSlug(item.type);
            showDeleteModal.value = true;
        };

        // Expiry CRUD Operations
        const openEditExpiryModal = (item) => {
            editingExpiryItem.value = item;
            expiryForm.value = {
                quantity: item.quantity,
                expiry_date: item.expiry_date ? item.expiry_date.split(' ')[0] : '',
                threshold: 0
            };
            showExpiryModal.value = true;
        };

        const closeExpiryModal = () => {
            showExpiryModal.value = false;
            editingExpiryItem.value = null;
            expiryForm.value = {
                quantity: 0,
                expiry_date: '',
                threshold: 0
            };
        };

        const updateExpiry = async () => {
            if (!editingExpiryItem.value) return;
            
            saving.value = true;
            try {
                await window.axios.post(`/api/stock-alerts-tracking/update-expiry/${editingExpiryItem.value.id}`, {
                    quantity: parseInt(expiryForm.value.quantity),
                    expiry_date: expiryForm.value.expiry_date || null,
                    threshold: parseInt(expiryForm.value.threshold) || 0
                });
                showSuccess('Item updated successfully!');
                closeExpiryModal();
                fetchData(); // Refresh data
            } catch (error) {
                console.error('Error updating expiry:', error);
                if (error.response?.data?.errors) {
                    showError('Validation failed: ' + Object.values(error.response.data.errors).flat().join(', '));
                } else {
                    showError('Failed to update item. Please try again.');
                }
            } finally {
                saving.value = false;
            }
        };

        const confirmDeleteExpiry = (item) => {
            itemToDelete.value = item;
            deleteType.value = 'inventory_items';
            showDeleteModal.value = true;
        };

        // Delete Operations
        const closeDeleteModal = () => {
            showDeleteModal.value = false;
            itemToDelete.value = null;
            deleteType.value = '';
        };

        const deleteItem = async () => {
            if (!itemToDelete.value || !deleteType.value) return;
            
            deleting.value = true;
            try {
                await window.axios.post(`/api/stock-alerts-tracking/delete-item/${deleteType.value}/${itemToDelete.value.id}`);
                showSuccess('Item deleted successfully!');
                closeDeleteModal();
                fetchData(); // Refresh data
            } catch (error) {
                console.error('Error deleting item:', error);
                showError('Failed to delete item. Please try again.');
            } finally {
                deleting.value = false;
            }
        };

        onMounted(() => {
            fetchData();
        });

        return {
            loading,
            activeTab,
            lowStockAlerts,
            lowStockSearch,
            lowStockStatusFilter,
            lowStockTypeFilter,
            lowStockCurrentPage,
            lowStockItemsPerPage,
            filteredLowStock,
            paginatedLowStock,
            lowStockTotalPages,
            lowStockStartIndex,
            lowStockEndIndex,
            lowStockGoToPage,
            lowStockNextPage,
            lowStockPrevPage,
            filterLowStock,
            expiryAlerts,
            expirySearch,
            expiryStatusFilter,
            expiryCurrentPage,
            expiryItemsPerPage,
            filteredExpiry,
            paginatedExpiry,
            expiryTotalPages,
            expiryStartIndex,
            expiryEndIndex,
            expiringSoonCount,
            expiryGoToPage,
            expiryNextPage,
            expiryPrevPage,
            filterExpiry,
            usageConsumption,
            usageSearch,
            usageSourceFilter,
            usageCurrentPage,
            usageItemsPerPage,
            filteredUsage,
            paginatedUsage,
            usageTotalPages,
            usageStartIndex,
            usageEndIndex,
            usageGoToPage,
            usageNextPage,
            usagePrevPage,
            filterUsage,
            saving,
            deleting,
            showStockModal,
            showExpiryModal,
            showDeleteModal,
            editingStockItem,
            editingExpiryItem,
            itemToDelete,
            stockForm,
            expiryForm,
            openEditStockModal,
            closeStockModal,
            updateStock,
            confirmDeleteStock,
            openEditExpiryModal,
            closeExpiryModal,
            updateExpiry,
            confirmDeleteExpiry,
            closeDeleteModal,
            deleteItem,
        };
    }
}
</script>

<style scoped>
.stock-alerts-tracking-page {
    padding: 24px;
}

.page-header {
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

.tabs-navigation {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    border-bottom: 2px solid #e2e8f0;
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    color: #718096;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: -2px;
    position: relative;
}

.tab-btn:hover {
    color: #4a5568;
    background: #f7fafc;
}

.tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
    font-weight: 600;
}

.tab-btn svg {
    width: 18px;
    height: 18px;
}

.badge-count {
    background: #667eea;
    color: white;
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
    min-width: 20px;
    text-align: center;
}

.badge-count.warning {
    background: #f59e0b;
}

.tab-content {
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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

.status-filter,
.type-filter,
.source-filter {
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

.text-warning {
    color: #d69e2e;
    font-weight: 600;
}

.text-danger {
    color: #e53e3e;
    font-weight: 600;
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

.form-control:disabled {
    background: #f7fafc;
    color: #718096;
    cursor: not-allowed;
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
    .filters-section {
        flex-direction: column;
    }
    
    .status-filter,
    .type-filter,
    .source-filter {
        width: 100%;
    }
    
    .tabs-navigation {
        overflow-x: auto;
    }
    
    .tab-btn {
        white-space: nowrap;
    }
}
</style>

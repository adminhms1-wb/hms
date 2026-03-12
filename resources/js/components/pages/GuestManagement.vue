<template>
    <div class="guest-management-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Guest Management</h1>
                <p class="page-subtitle">Manage guest profiles, history, and preferences</p>
            </div>
            <button class="btn btn-primary" @click="openGuestModal(null)">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 3V17M3 10H17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Add New Guest
            </button>
        </div>

        <!-- Filters and Search -->
        <div class="filters-bar">
            <div class="search-box">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                    <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    @input="debouncedSearch"
                    placeholder="Search by name, email, phone, or ID..."
                />
            </div>
            <div class="filter-group">
                <select v-model="filterVIP" @change="fetchGuests" class="filter-select">
                    <option value="">All Guests</option>
                    <option value="true">VIP Only</option>
                    <option value="false">Non-VIP</option>
                </select>
                <select v-model="filterBlacklist" @change="fetchGuests" class="filter-select">
                    <option value="">All Status</option>
                    <option value="true">Blacklisted</option>
                    <option value="false">Not Blacklisted</option>
                </select>
                <select v-model="filterFlagged" @change="fetchGuests" class="filter-select">
                    <option value="">All Status</option>
                    <option value="true">Flagged</option>
                    <option value="false">Not Flagged</option>
                </select>
            </div>
        </div>

        <!-- Guests Table -->
        <div class="content-card">
            <div v-if="loading" class="loading-state">
                <p>Loading guests...</p>
            </div>
            <div v-else-if="guests.length === 0" class="empty-state">
                <p>No guests found</p>
            </div>
            <div v-else class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>ID/Passport</th>
                            <th>Nationality</th>
                            <th>Status</th>
                            <th>Stays</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="guest in guests" :key="guest.id" :class="{ 'flagged-row': guest.flagged_at, 'blacklisted-row': guest.is_blacklisted }">
                            <td>
                                <div class="guest-name-cell">
                                    <strong>{{ guest.name }}</strong>
                                    <div class="guest-badges">
                                        <span v-if="guest.is_vip" class="badge badge-vip">VIP</span>
                                        <span v-if="guest.is_blacklisted" class="badge badge-danger">Blacklisted</span>
                                        <span v-if="guest.flagged_at" class="badge badge-warning">Flagged</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <div v-if="guest.email">
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                            <path d="M2 3H12V11H2V3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M2 3L7 7L12 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ guest.email }}
                                    </div>
                                    <div v-if="guest.phone">
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                            <path d="M10.5 1.5H3.5C2.67157 1.5 2 2.17157 2 3V11C2 11.8284 2.67157 12.5 3.5 12.5H10.5C11.3284 12.5 12 11.8284 12 11V3C12 2.17157 11.3284 1.5 10.5 1.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6 10.5H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        {{ guest.phone }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div v-if="guest.id_number" class="id-info">
                                    <span class="id-type">{{ formatIdType(guest.id_type) }}</span>
                                    <span class="id-number">{{ guest.id_number }}</span>
                                </div>
                                <span v-else class="text-muted">-</span>
                            </td>
                            <td>{{ guest.nationality || '-' }}</td>
                            <td>
                                <div class="status-badges">
                                    <span v-if="guest.is_vip" class="badge badge-vip">VIP</span>
                                    <span v-if="guest.is_blacklisted" class="badge badge-danger">Blacklisted</span>
                                    <span v-if="guest.flagged_at" class="badge badge-warning">Flagged</span>
                                    <span v-if="!guest.is_vip && !guest.is_blacklisted && !guest.flagged_at" class="badge badge-success">Active</span>
                                </div>
                            </td>
                            <td>{{ guest.reservations_count || 0 }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" @click="viewGuestDetails(guest)" title="View Details">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M8 3C4.68629 3 2 5.68629 2 9C2 12.3137 4.68629 15 8 15C11.3137 15 14 12.3137 14 9C14 5.68629 11.3137 3 8 3Z" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 11C9.10457 11 10 10.1046 10 9C10 7.89543 9.10457 7 8 7C6.89543 7 6 7.89543 6 9C6 10.1046 6.89543 11 8 11Z" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                    </button>
                                    <button 
                                        class="btn-icon btn-icon-info" 
                                        @click="quickViewHistory(guest)" 
                                        title="View History"
                                        v-if="guest.reservations_count > 0"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M2 4H14M2 8H14M2 12H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M6 2V6M10 10V14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                    <button class="btn-icon" @click="openGuestModal(guest)" title="Edit">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M11.3333 2.00004C11.5084 1.82493 11.7163 1.68607 11.9447 1.59128C12.1731 1.49649 12.4173 1.44775 12.6667 1.44775C12.916 1.44775 13.1602 1.49649 13.3886 1.59128C13.617 1.68607 13.8249 1.82493 14 2.00004C14.1751 2.17515 14.314 2.38309 14.4088 2.6115C14.5036 2.83991 14.5523 3.08407 14.5523 3.33337C14.5523 3.58268 14.5036 3.82684 14.4088 4.05525C14.314 4.28366 14.1751 4.4916 14 4.66671L5.00001 13.6667L1.33334 14.6667L2.33334 11L11.3333 2.00004Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button 
                                        v-if="!guest.flagged_at" 
                                        class="btn-icon btn-icon-warning" 
                                        @click="openFlagModal(guest)" 
                                        title="Flag Guest"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M3 2V14M3 2H11L12 5L11 8H3M3 2L2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button 
                                        v-else 
                                        class="btn-icon btn-icon-success" 
                                        @click="unflagGuest(guest)" 
                                        title="Unflag Guest"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M13.3333 4L6 11.3333L2.66667 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button 
                                        v-if="!guest.is_blacklisted" 
                                        class="btn-icon btn-icon-danger" 
                                        @click="openBlacklistModal(guest)" 
                                        title="Blacklist"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                    <button 
                                        v-else 
                                        class="btn-icon btn-icon-success" 
                                        @click="unblacklistGuest(guest)" 
                                        title="Remove from Blacklist"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M13.3333 4L6 11.3333L2.66667 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.total > pagination.per_page" class="pagination">
                <button 
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="!pagination.prev_page_url"
                    class="pagination-btn"
                >
                    Previous
                </button>
                <span class="pagination-info">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </span>
                <button 
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="!pagination.next_page_url"
                    class="pagination-btn"
                >
                    Next
                </button>
            </div>
        </div>

        <!-- Guest Details Modal -->
        <div v-if="showDetailsModal" class="modal-overlay" @click.self="closeDetailsModal">
            <div class="modal-content modal-large">
                <div class="modal-header">
                    <h2>Guest Details</h2>
                    <button class="modal-close" @click="closeDetailsModal">×</button>
                </div>
                <div v-if="selectedGuest" class="modal-body">
                    <!-- Guest Profile Section -->
                    <div class="details-section">
                        <h3>Profile Information</h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <label>Name</label>
                                <p>{{ selectedGuest.name }}</p>
                            </div>
                            <div class="detail-item">
                                <label>Email</label>
                                <p>{{ selectedGuest.email || '-' }}</p>
                            </div>
                            <div class="detail-item">
                                <label>Phone</label>
                                <p>{{ selectedGuest.phone || '-' }}</p>
                            </div>
                            <div class="detail-item">
                                <label>Date of Birth</label>
                                <p>{{ selectedGuest.date_of_birth ? formatDate(selectedGuest.date_of_birth) : '-' }}</p>
                            </div>
                            <div class="detail-item">
                                <label>Nationality</label>
                                <p>{{ selectedGuest.nationality || '-' }}</p>
                            </div>
                            <div class="detail-item">
                                <label>Address</label>
                                <p>{{ selectedGuest.address || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- ID/Passport Details -->
                    <div class="details-section">
                        <h3>ID/Passport Details</h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <label>ID Type</label>
                                <p>{{ formatIdType(selectedGuest.id_type) || '-' }}</p>
                            </div>
                            <div class="detail-item">
                                <label>ID/Passport Number</label>
                                <p>{{ selectedGuest.id_number || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Special Notes -->
                    <div class="details-section">
                        <h3>Special Notes</h3>
                        <div class="details-grid">
                            <div class="detail-item full-width">
                                <label>VIP Status</label>
                                <p>
                                    <span :class="['badge', selectedGuest.is_vip ? 'badge-vip' : 'badge-secondary']">
                                        {{ selectedGuest.is_vip ? 'VIP Guest' : 'Regular Guest' }}
                                    </span>
                                </p>
                            </div>
                            <div class="detail-item full-width" v-if="selectedGuest.allergies">
                                <label>Allergies</label>
                                <p>{{ selectedGuest.allergies }}</p>
                            </div>
                            <div class="detail-item full-width" v-if="selectedGuest.preferences">
                                <label>Preferences</label>
                                <div class="preferences-list">
                                    <span v-for="(value, key) in selectedGuest.preferences" :key="key" class="preference-tag">
                                        {{ key }}: {{ value }}
                                    </span>
                                </div>
                            </div>
                            <div class="detail-item full-width" v-if="selectedGuest.notes">
                                <label>Additional Notes</label>
                                <p>{{ selectedGuest.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Blacklist/Flag Status -->
                    <div class="details-section" v-if="selectedGuest.is_blacklisted || selectedGuest.flagged_at">
                        <h3>Status Information</h3>
                        <div class="details-grid">
                            <div class="detail-item full-width" v-if="selectedGuest.is_blacklisted">
                                <label>Blacklist Status</label>
                                <div class="alert alert-danger">
                                    <strong>Blacklisted</strong>
                                    <p v-if="selectedGuest.blacklist_reason">{{ selectedGuest.blacklist_reason }}</p>
                                </div>
                            </div>
                            <div class="detail-item full-width" v-if="selectedGuest.flagged_at">
                                <label>Flagged Status</label>
                                <div class="alert alert-warning">
                                    <strong>Flagged on {{ formatDateTime(selectedGuest.flagged_at) }}</strong>
                                    <p v-if="selectedGuest.flagged_reason">{{ selectedGuest.flagged_reason }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guest History -->
                    <div class="details-section">
                        <h3>Reservation History</h3>
                        <div v-if="loadingHistory" class="loading-state">
                            <p>Loading history...</p>
                        </div>
                        <div v-else-if="guestHistory.length === 0" class="empty-state">
                            <p>No previous stays</p>
                        </div>
                        <div v-else class="history-table">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Room</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="reservation in guestHistory" :key="reservation.id">
                                        <td>{{ formatDate(reservation.check_in_date) }}</td>
                                        <td>{{ formatDate(reservation.check_out_date) }}</td>
                                        <td>{{ reservation.room?.room_number }} ({{ reservation.room?.room_type?.name }})</td>
                                        <td>
                                            <span :class="['badge', getStatusBadgeClass(reservation.status)]">
                                                {{ reservation.status }}
                                            </span>
                                        </td>
                                        <td>${{ parseFloat(reservation.total_amount || 0).toFixed(2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="details-section" v-if="guestStats">
                        <h3>Statistics</h3>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value">{{ guestStats.total_stays }}</div>
                                <div class="stat-label">Total Stays</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ guestStats.total_nights }}</div>
                                <div class="stat-label">Total Nights</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">${{ parseFloat(guestStats.total_spent || 0).toFixed(2) }}</div>
                                <div class="stat-label">Total Spent</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guest Form Modal -->
        <div v-if="showGuestModal" class="modal-overlay" @click.self="closeGuestModal">
            <div class="modal-content modal-large">
                <div class="modal-header">
                    <h2>{{ editingGuest ? 'Edit Guest' : 'Add New Guest' }}</h2>
                    <button class="modal-close" @click="closeGuestModal">×</button>
                </div>
                <div class="modal-body">
                    <!-- Tabs for Edit Form -->
                    <div v-if="editingGuest" class="form-tabs">
                        <button 
                            :class="['form-tab', { active: activeFormTab === 'profile' }]"
                            @click="activeFormTab = 'profile'"
                        >
                            Profile Information
                        </button>
                        <button 
                            :class="['form-tab', { active: activeFormTab === 'history' }]"
                            @click="activeFormTab = 'history'; loadGuestHistory()"
                        >
                            Reservation History
                            <span v-if="editFormHistory.length > 0" class="tab-badge">{{ editFormHistory.length }}</span>
                        </button>
                    </div>

                    <!-- Profile Tab Content -->
                    <div v-if="!editingGuest || activeFormTab === 'profile'">
                    <form @submit.prevent="saveGuest">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Name <span class="required">*</span></label>
                                <input type="text" v-model="guestForm.name" required />
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" v-model="guestForm.email" />
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" v-model="guestForm.phone" />
                            </div>
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" v-model="guestForm.date_of_birth" />
                            </div>
                            <div class="form-group">
                                <label>Nationality</label>
                                <input type="text" v-model="guestForm.nationality" />
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea v-model="guestForm.address" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label>ID Type</label>
                                <select v-model="guestForm.id_type">
                                    <option value="">Select ID Type</option>
                                    <option value="national_id">National ID</option>
                                    <option value="passport">Passport</option>
                                    <option value="driving_license">Driving License</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>ID/Passport Number</label>
                                <input type="text" v-model="guestForm.id_number" />
                            </div>
                            <div class="form-group full-width">
                                <label>
                                    <input type="checkbox" v-model="guestForm.is_vip" />
                                    VIP Guest
                                </label>
                            </div>
                            <div class="form-group full-width">
                                <label>Allergies</label>
                                <textarea v-model="guestForm.allergies" rows="2" placeholder="List any allergies or dietary restrictions"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label>Preferences</label>
                                <div class="preferences-input">
                                    <div v-for="(value, key) in guestForm.preferences" :key="key" class="preference-input-row">
                                        <input type="text" v-model="preferenceKeys[key]" placeholder="Preference name" />
                                        <input type="text" v-model="guestForm.preferences[key]" placeholder="Value" />
                                        <button type="button" class="btn-icon btn-icon-danger" @click="removePreference(key)">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M4 4L12 12M4 12L12 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-secondary" @click="addPreference">
                                        + Add Preference
                                    </button>
                                </div>
                            </div>
                            <div class="form-group full-width">
                                <label>Additional Notes</label>
                                <textarea v-model="guestForm.notes" rows="3" placeholder="Any additional notes about the guest"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeGuestModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                {{ saving ? 'Saving...' : 'Save Guest' }}
                            </button>
                        </div>
                    </form>
                    </div>

                    <!-- History Tab Content -->
                    <div v-if="editingGuest && activeFormTab === 'history'" class="history-tab-content">
                        <div class="history-section-header">
                            <h3>Reservation History</h3>
                            <div v-if="editFormStats" class="history-stats-mini">
                                <span>Total Stays: <strong>{{ editFormStats.total_stays }}</strong></span>
                                <span>Total Nights: <strong>{{ editFormStats.total_nights }}</strong></span>
                                <span>Total Spent: <strong>${{ parseFloat(editFormStats.total_spent || 0).toFixed(2) }}</strong></span>
                            </div>
                        </div>
                        <div v-if="loadingEditFormHistory" class="loading-state">
                            <p>Loading history...</p>
                        </div>
                        <div v-else-if="editFormHistory.length === 0" class="empty-state">
                            <p>No previous stays found for this guest</p>
                        </div>
                        <div v-else class="history-table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Room</th>
                                        <th>Room Type</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="reservation in editFormHistory" :key="reservation.id">
                                        <td>{{ formatDate(reservation.check_in_date) }}</td>
                                        <td>{{ formatDate(reservation.check_out_date) }}</td>
                                        <td>{{ reservation.room?.room_number || 'N/A' }}</td>
                                        <td>{{ reservation.room?.room_type?.name || 'N/A' }}</td>
                                        <td>
                                            <span :class="['badge', getStatusBadgeClass(reservation.status)]">
                                                {{ reservation.status }}
                                            </span>
                                        </td>
                                        <td>${{ parseFloat(reservation.total_amount || 0).toFixed(2) }}</td>
                                        <td>
                                            <button 
                                                class="btn-icon" 
                                                @click="viewReservationDetails(reservation)"
                                                title="View Details"
                                            >
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8 3C4.68629 3 2 5.68629 2 9C2 12.3137 4.68629 15 8 15C11.3137 15 14 12.3137 14 9C14 5.68629 11.3137 3 8 3Z" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 11C9.10457 11 10 10.1046 10 9C10 7.89543 9.10457 7 8 7C6.89543 7 6 7.89543 6 9C6 10.1046 6.89543 11 8 11Z" stroke="currentColor" stroke-width="1.5"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blacklist Modal -->
        <div v-if="showBlacklistModal" class="modal-overlay" @click.self="closeBlacklistModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Blacklist Guest</h2>
                    <button class="modal-close" @click="closeBlacklistModal">×</button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="blacklistGuest">
                        <div class="form-group">
                            <label>Guest Name</label>
                            <input type="text" :value="guestToBlacklist?.name" disabled />
                        </div>
                        <div class="form-group">
                            <label>Reason for Blacklisting <span class="required">*</span></label>
                            <textarea v-model="blacklistReason" rows="4" required placeholder="Enter the reason for blacklisting this guest"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeBlacklistModal">Cancel</button>
                            <button type="submit" class="btn btn-danger" :disabled="saving">
                                {{ saving ? 'Blacklisting...' : 'Blacklist Guest' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Flag Guest Modal -->
        <div v-if="showFlagModal" class="modal-overlay" @click.self="closeFlagModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Flag Guest</h2>
                    <button class="modal-close" @click="closeFlagModal">×</button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="flagGuest">
                        <div class="form-group">
                            <label>Guest Name</label>
                            <input type="text" :value="guestToFlag?.name" disabled />
                        </div>
                        <div class="form-group">
                            <label>Reason for Flagging <span class="required">*</span></label>
                            <textarea v-model="flagReason" rows="4" required placeholder="Enter the reason for flagging this guest"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeFlagModal">Cancel</button>
                            <button type="submit" class="btn btn-warning" :disabled="saving">
                                {{ saving ? 'Flagging...' : 'Flag Guest' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const guests = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const filterVIP = ref('');
const filterBlacklist = ref('');
const filterFlagged = ref('');
const pagination = ref(null);
const showGuestModal = ref(false);
const showDetailsModal = ref(false);
const showBlacklistModal = ref(false);
const showFlagModal = ref(false);
const editingGuest = ref(null);
const selectedGuest = ref(null);
const guestToBlacklist = ref(null);
const guestToFlag = ref(null);
const blacklistReason = ref('');
const flagReason = ref('');
const saving = ref(false);
const guestHistory = ref([]);
const loadingHistory = ref(false);
const guestStats = ref(null);
const preferenceKeys = ref({});
const activeFormTab = ref('profile');
const editFormHistory = ref([]);
const loadingEditFormHistory = ref(false);
const editFormStats = ref(null);

const guestForm = ref({
    name: '',
    email: '',
    phone: '',
    id_number: '',
    id_type: '',
    address: '',
    date_of_birth: '',
    nationality: '',
    is_vip: false,
    allergies: '',
    preferences: {},
    notes: '',
});

// Debounced search
let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchGuests();
    }, 500);
};

const fetchGuests = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value?.current_page || 1,
        };
        
        if (searchQuery.value) params.search = searchQuery.value;
        if (filterVIP.value) params.is_vip = filterVIP.value;
        if (filterBlacklist.value) params.is_blacklisted = filterBlacklist.value;
        if (filterFlagged.value) params.is_flagged = filterFlagged.value;

        const response = await axios.get('/api/guests', { params });
        if (response.data.success) {
            guests.value = response.data.data.data || response.data.data;
            pagination.value = {
                current_page: response.data.data.current_page,
                last_page: response.data.data.last_page,
                per_page: response.data.data.per_page,
                total: response.data.data.total,
                prev_page_url: response.data.data.prev_page_url,
                next_page_url: response.data.data.next_page_url,
            };
        }
    } catch (error) {
        console.error('Error fetching guests:', error);
        showError(error.response?.data?.message || 'Failed to load guests');
    } finally {
        loading.value = false;
    }
};

const changePage = (page) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchGuests();
    }
};

const openGuestModal = (guest) => {
    editingGuest.value = guest;
    activeFormTab.value = 'profile';
    editFormHistory.value = [];
    editFormStats.value = null;
    if (guest) {
        guestForm.value = {
            name: guest.name || '',
            email: guest.email || '',
            phone: guest.phone || '',
            id_number: guest.id_number || '',
            id_type: guest.id_type || '',
            address: guest.address || '',
            date_of_birth: guest.date_of_birth || '',
            nationality: guest.nationality || '',
            is_vip: guest.is_vip || false,
            allergies: guest.allergies || '',
            preferences: guest.preferences || {},
            notes: guest.notes || '',
        };
        // Initialize preference keys
        preferenceKeys.value = {};
        if (guest.preferences) {
            Object.keys(guest.preferences).forEach(key => {
                preferenceKeys.value[key] = key;
            });
        }
    } else {
        guestForm.value = {
            name: '',
            email: '',
            phone: '',
            id_number: '',
            id_type: '',
            address: '',
            date_of_birth: '',
            nationality: '',
            is_vip: false,
            allergies: '',
            preferences: {},
            notes: '',
        };
        preferenceKeys.value = {};
    }
    showGuestModal.value = true;
};

const closeGuestModal = () => {
    showGuestModal.value = false;
    editingGuest.value = null;
    activeFormTab.value = 'profile';
    editFormHistory.value = [];
    editFormStats.value = null;
    guestForm.value = {
        name: '',
        email: '',
        phone: '',
        id_number: '',
        id_type: '',
        address: '',
        date_of_birth: '',
        nationality: '',
        is_vip: false,
        allergies: '',
        preferences: {},
        notes: '',
    };
    preferenceKeys.value = {};
};

const loadGuestHistory = async () => {
    if (!editingGuest.value || !editingGuest.value.id) return;
    
    loadingEditFormHistory.value = true;
    try {
        const [detailsResponse, historyResponse] = await Promise.all([
            axios.get(`/api/guests/${editingGuest.value.id}`),
            axios.get(`/api/guests/${editingGuest.value.id}/history`)
        ]);
        
        if (detailsResponse.data.success) {
            editFormStats.value = detailsResponse.data.stats;
        }

        if (historyResponse.data.success) {
            editFormHistory.value = historyResponse.data.data;
        }
    } catch (error) {
        console.error('Error loading guest history:', error);
        showError(error.response?.data?.message || 'Failed to load guest history');
    } finally {
        loadingEditFormHistory.value = false;
    }
};

const viewReservationDetails = (reservation) => {
    // Close guest modal and show reservation details
    closeGuestModal();
    // You can implement a reservation details modal here or navigate to reservations page
    showSuccess(`Reservation #${reservation.id} - Check-in: ${formatDate(reservation.check_in_date)}, Check-out: ${formatDate(reservation.check_out_date)}`);
};

const quickViewHistory = (guest) => {
    // Open guest modal and switch to history tab
    openGuestModal(guest);
    activeFormTab.value = 'history';
    loadGuestHistory();
};

const saveGuest = async () => {
    saving.value = true;
    try {
        // Convert preferences object properly
        const preferencesObj = {};
        Object.keys(preferenceKeys.value).forEach(key => {
            const prefKey = preferenceKeys.value[key];
            if (prefKey && guestForm.value.preferences[key]) {
                preferencesObj[prefKey] = guestForm.value.preferences[key];
            }
        });

        const data = {
            ...guestForm.value,
            preferences: Object.keys(preferencesObj).length > 0 ? preferencesObj : null,
        };

        let response;
        if (editingGuest.value) {
            response = await axios.put(`/api/guests/${editingGuest.value.id}`, data);
        } else {
            response = await axios.post('/api/guests', data);
        }

        if (response.data.success) {
            showSuccess(response.data.message || 'Guest saved successfully');
            closeGuestModal();
            fetchGuests();
        }
    } catch (error) {
        console.error('Error saving guest:', error);
        showError(error.response?.data?.message || 'Failed to save guest');
    } finally {
        saving.value = false;
    }
};

const viewGuestDetails = async (guest) => {
    selectedGuest.value = guest;
    loadingHistory.value = true;
    showDetailsModal.value = true;
    try {
        const [detailsResponse, historyResponse] = await Promise.all([
            axios.get(`/api/guests/${guest.id}`),
            axios.get(`/api/guests/${guest.id}/history`)
        ]);
        
        if (detailsResponse.data.success) {
            selectedGuest.value = detailsResponse.data.data;
            guestStats.value = detailsResponse.data.stats;
        }

        if (historyResponse.data.success) {
            guestHistory.value = historyResponse.data.data;
        }
    } catch (error) {
        console.error('Error fetching guest details:', error);
        showError(error.response?.data?.message || 'Failed to load guest details');
    } finally {
        loadingHistory.value = false;
    }
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedGuest.value = null;
    guestHistory.value = [];
    guestStats.value = null;
};

const openBlacklistModal = (guest) => {
    guestToBlacklist.value = guest;
    blacklistReason.value = '';
    showBlacklistModal.value = true;
};

const closeBlacklistModal = () => {
    showBlacklistModal.value = false;
    guestToBlacklist.value = null;
    blacklistReason.value = '';
};

const blacklistGuest = async () => {
    saving.value = true;
    try {
        const response = await axios.post(`/api/guests/${guestToBlacklist.value.id}/blacklist`, {
            blacklist_reason: blacklistReason.value
        });
        if (response.data.success) {
            showSuccess(response.data.message || 'Guest blacklisted successfully');
            closeBlacklistModal();
            fetchGuests();
        }
    } catch (error) {
        console.error('Error blacklisting guest:', error);
        showError(error.response?.data?.message || 'Failed to blacklist guest');
    } finally {
        saving.value = false;
    }
};

const unblacklistGuest = async (guest) => {
    if (!confirm('Are you sure you want to remove this guest from the blacklist?')) return;
    
    try {
        const response = await axios.post(`/api/guests/${guest.id}/unblacklist`);
        if (response.data.success) {
            showSuccess(response.data.message || 'Guest removed from blacklist');
            fetchGuests();
        }
    } catch (error) {
        console.error('Error unblacklisting guest:', error);
        showError(error.response?.data?.message || 'Failed to remove guest from blacklist');
    }
};

const openFlagModal = (guest) => {
    guestToFlag.value = guest;
    flagReason.value = '';
    showFlagModal.value = true;
};

const closeFlagModal = () => {
    showFlagModal.value = false;
    guestToFlag.value = null;
    flagReason.value = '';
};

const flagGuest = async () => {
    saving.value = true;
    try {
        const response = await axios.post(`/api/guests/${guestToFlag.value.id}/flag`, {
            flagged_reason: flagReason.value
        });
        if (response.data.success) {
            showSuccess(response.data.message || 'Guest flagged successfully');
            closeFlagModal();
            fetchGuests();
        }
    } catch (error) {
        console.error('Error flagging guest:', error);
        showError(error.response?.data?.message || 'Failed to flag guest');
    } finally {
        saving.value = false;
    }
};

const unflagGuest = async (guest) => {
    if (!confirm('Are you sure you want to unflag this guest?')) return;
    
    try {
        const response = await axios.post(`/api/guests/${guest.id}/unflag`);
        if (response.data.success) {
            showSuccess(response.data.message || 'Guest unflagged successfully');
            fetchGuests();
        }
    } catch (error) {
        console.error('Error unflagging guest:', error);
        showError(error.response?.data?.message || 'Failed to unflag guest');
    }
};

const addPreference = () => {
    const key = `pref_${Date.now()}`;
    guestForm.value.preferences[key] = '';
    preferenceKeys.value[key] = '';
};

const removePreference = (key) => {
    delete guestForm.value.preferences[key];
    delete preferenceKeys.value[key];
};

const formatIdType = (type) => {
    if (!type) return '-';
    const types = {
        'national_id': 'National ID',
        'passport': 'Passport',
        'driving_license': 'Driving License',
        'other': 'Other'
    };
    return types[type] || type;
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'badge-warning',
        'confirmed': 'badge-info',
        'checked_in': 'badge-success',
        'checked_out': 'badge-secondary',
        'cancelled': 'badge-danger',
    };
    return classes[status] || 'badge-secondary';
};

const showSuccess = (message) => {
    alert(message); // Replace with proper notification system
};

const showError = (message) => {
    alert(message); // Replace with proper notification system
};

onMounted(() => {
    fetchGuests();
});
</script>

<style scoped>
.guest-management-page {
    padding: 24px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.page-title {
    font-size: 28px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 4px 0;
}

.page-subtitle {
    font-size: 14px;
    color: #666;
    margin: 0;
}

.filters-bar {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
    align-items: center;
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
    flex-shrink: 0;
}

.search-box input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 14px;
}

.filter-group {
    display: flex;
    gap: 12px;
}

.filter-select {
    padding: 10px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    cursor: pointer;
}

.content-card {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f8f9fa;
}

.data-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    padding: 16px 12px;
    border-bottom: 1px solid #f0f0f0;
}

.data-table tbody tr:hover {
    background: #f8f9fa;
}

.data-table tbody tr.flagged-row {
    background: #fff8e1;
}

.data-table tbody tr.blacklisted-row {
    background: #ffebee;
}

.guest-name-cell {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.guest-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 13px;
}

.contact-info > div {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #666;
}

.id-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.id-type {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
}

.id-number {
    font-size: 13px;
    font-weight: 500;
}

.status-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e0e0e0;
    background: #fff;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-icon:hover {
    background: #f8f9fa;
    border-color: #d0d0d0;
}

.btn-icon-danger {
    color: #dc3545;
}

.btn-icon-danger:hover {
    background: #ffebee;
    border-color: #dc3545;
}

.btn-icon-success {
    color: #28a745;
}

.btn-icon-success:hover {
    background: #e8f5e9;
    border-color: #28a745;
}

.btn-icon-warning {
    color: #ffc107;
}

.btn-icon-warning:hover {
    background: #fff8e1;
    border-color: #ffc107;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-vip {
    background: #ffd700;
    color: #856404;
}

.badge-danger {
    background: #ffebee;
    color: #c62828;
}

.badge-warning {
    background: #fff8e1;
    color: #f57c00;
}

.badge-success {
    background: #e8f5e9;
    color: #2e7d32;
}

.badge-secondary {
    background: #f5f5f5;
    color: #666;
}

.badge-info {
    background: #e3f2fd;
    color: #1976d2;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 16px;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #f0f0f0;
}

.pagination-btn {
    padding: 8px 16px;
    border: 1px solid #e0e0e0;
    background: #fff;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.pagination-btn:hover:not(:disabled) {
    background: #f8f9fa;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-info {
    font-size: 14px;
    color: #666;
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
    padding: 20px;
}

.modal-content {
    background: #fff;
    border-radius: 12px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal-large {
    max-width: 900px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid #f0f0f0;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.modal-close {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: #f5f5f5;
    border-radius: 6px;
    cursor: pointer;
    font-size: 24px;
    color: #666;
}

.modal-close:hover {
    background: #e0e0e0;
}

.modal-body {
    padding: 24px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 24px;
    border-top: 1px solid #f0f0f0;
}

.details-section {
    margin-bottom: 32px;
}

.details-section h3 {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 16px 0;
    color: #1a1a1a;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-item label {
    font-size: 12px;
    font-weight: 600;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item p {
    margin: 0;
    font-size: 14px;
    color: #333;
}

.preferences-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.preference-tag {
    padding: 6px 12px;
    background: #f0f0f0;
    border-radius: 16px;
    font-size: 12px;
    color: #666;
}

.alert {
    padding: 12px;
    border-radius: 6px;
    margin: 0;
}

.alert-danger {
    background: #ffebee;
    color: #c62828;
}

.alert-warning {
    background: #fff8e1;
    color: #f57c00;
}

.history-table {
    margin-top: 16px;
}

.form-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    border-bottom: 2px solid #f0f0f0;
}

.form-tab {
    padding: 12px 20px;
    border: none;
    background: transparent;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-tab:hover {
    color: #4a90e2;
}

.form-tab.active {
    color: #4a90e2;
    border-bottom-color: #4a90e2;
}

.tab-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    background: #4a90e2;
    color: #fff;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

.history-tab-content {
    padding: 0;
}

.history-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f0f0;
}

.history-section-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
}

.history-stats-mini {
    display: flex;
    gap: 16px;
    font-size: 13px;
    color: #666;
}

.history-stats-mini span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.history-stats-mini strong {
    color: #1a1a1a;
    font-weight: 600;
}

.history-table-container {
    overflow-x: auto;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-top: 16px;
}

.stat-card {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-size: 14px;
    font-weight: 500;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 10px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4a90e2;
}

.required {
    color: #dc3545;
}

.preferences-input {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.preference-input-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 8px;
    align-items: center;
}

.loading-state,
.empty-state {
    text-align: center;
    padding: 40px;
    color: #999;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-primary {
    background: #4a90e2;
    color: #fff;
}

.btn-primary:hover {
    background: #357abd;
}

.btn-secondary {
    background: #f5f5f5;
    color: #666;
}

.btn-secondary:hover {
    background: #e0e0e0;
}

.btn-danger {
    background: #dc3545;
    color: #fff;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-warning {
    background: #ffc107;
    color: #000;
}

.btn-warning:hover {
    background: #e0a800;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.text-muted {
    color: #999;
}
</style>

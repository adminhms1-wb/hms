<template>
    <div class="rooms-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Room Management</h1>
                <p class="page-subtitle">Manage room types and room configurations</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tabs">
                <button 
                    @click="activeTab = 'types'" 
                    :class="['tab', { active: activeTab === 'types' }]"
                >
                    Room Types
                </button>
                <button 
                    @click="activeTab = 'rooms'" 
                    :class="['tab', { active: activeTab === 'rooms' }]"
                >
                    Rooms
                </button>
                <button 
                    @click="activeTab = 'availability'" 
                    :class="['tab', { active: activeTab === 'availability' }]"
                >
                    Room Availability
                </button>
                <button 
                    @click="activeTab = 'seasonal'" 
                    :class="['tab', { active: activeTab === 'seasonal' }]"
                >
                    Seasonal Pricing
                </button>
                <button 
                    @click="activeTab = 'pricing-rules'" 
                    :class="['tab', { active: activeTab === 'pricing-rules' }]"
                >
                    Weekend / Peak Pricing
                </button>
                <button 
                    @click="activeTab = 'checkin-checkout'" 
                    :class="['tab', { active: activeTab === 'checkin-checkout' }]"
                >
                    Check-in / Check-out Times
                </button>
            </div>
        </div>

        <!-- Room Types Tab -->
        <div v-if="activeTab === 'types'" class="tab-content">
            <div class="content-header">
                <div class="view-toggle">
                    <button 
                        :class="['btn', 'btn-sm', showTrashedTypes ? 'btn-secondary' : 'btn-outline-secondary']"
                        @click="toggleTrashedTypes"
                    >
                        {{ showTrashedTypes ? 'Show Active' : 'Show Trashed' }}
                    </button>
                </div>
                <button class="btn btn-primary" @click="openRoomTypeModal" v-if="!showTrashedTypes">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Add Room Type
                </button>
            </div>

            <div class="content-card">
                <div v-if="loadingTypes" class="loading-state">
                    <p>Loading room types...</p>
                </div>
                
                <div v-else-if="roomTypes.length === 0" class="empty-state">
                    <p>No room types found</p>
                </div>
                
                <div v-else class="room-types-grid">
                    <div v-for="type in roomTypes" :key="type.id" class="room-type-card" :class="{ 'trashed': type.deleted_at }">
                        <div class="card-header">
                            <h3>{{ type.name }} <span v-if="type.deleted_at" class="trashed-badge">(Trashed)</span></h3>
                            <div class="card-actions">
                                <template v-if="!type.deleted_at">
                                    <button class="icon-btn" @click="openEditRoomTypeModal(type)" title="Edit">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M11.3333 2.00001C11.5084 1.82491 11.7163 1.68605 11.9447 1.59128C12.1731 1.49651 12.4173 1.44775 12.6639 1.44775C12.9106 1.44775 13.1548 1.49651 13.3832 1.59128C13.6116 1.68605 13.8195 1.82491 13.9946 2.00001C14.1697 2.17511 14.3086 2.38301 14.4033 2.61141C14.4981 2.83981 14.5469 3.08401 14.5469 3.33068C14.5469 3.57734 14.4981 3.82154 14.4033 4.04994C14.3086 4.27834 14.1697 4.48624 13.9946 4.66134L5.176 13.48L1.33333 14.6667L2.52 10.824L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button class="icon-btn danger" @click="confirmDeleteRoomType(type)" title="Delete">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </template>
                                <template v-else>
                                    <button class="icon-btn success" @click="restoreRoomType(type)" title="Restore">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M2 8L6 4L10 8M6 4V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button class="icon-btn danger" @click="confirmForceDeleteRoomType(type)" title="Permanently Delete">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="description" v-if="type.description">{{ type.description }}</p>
                            <p class="description" v-else>No description</p>
                            <div class="type-details">
                                <div class="detail-item">
                                    <span class="label">Base Price:</span>
                                    <span class="value">{{ formatPriceReactive(parseFloat(type.base_price || 0)) }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Max Guests:</span>
                                    <span class="value">{{ type.max_guests || 1 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rooms Tab -->
        <div v-if="activeTab === 'rooms'" class="tab-content">
            <div class="content-header">
                <div class="filters-section">
                    <div class="search-box">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                            <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            placeholder="Search by room number or floor..."
                            @input="debouncedSearch"
                        />
                    </div>
                    <select v-model="selectedRoomType" @change="fetchRooms" class="filter-select" v-if="!showTrashedRooms">
                        <option value="">All Room Types</option>
                        <option v-for="type in roomTypes.filter(t => !t.deleted_at)" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                    <div class="view-toggle">
                        <button 
                            :class="['btn', 'btn-sm', showTrashedRooms ? 'btn-secondary' : 'btn-outline-secondary']"
                            @click="toggleTrashedRooms"
                        >
                            {{ showTrashedRooms ? 'Show Active' : 'Show Trashed' }}
                        </button>
                    </div>
                </div>
                <button class="btn btn-primary" @click="openRoomModal" v-if="!showTrashedRooms">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Add Room
                </button>
            </div>

            <div class="content-card">
                <div v-if="loadingRooms" class="loading-state">
                    <p>Loading rooms...</p>
                </div>
                
                <div v-else-if="rooms.length === 0" class="empty-state">
                    <p>No rooms found</p>
                </div>
                
                <div v-else class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Number</th>
                                <th>Room Type</th>
                                <th>Floor</th>
                                <th>Max Guests</th>
                                <th>Bed Type</th>
                                <th>Smoking</th>
                                <th>Amenities</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="room in rooms" :key="room.id" :class="{ 'trashed-row': room.deleted_at }">
                                <td><strong>{{ room.room_number }}</strong> <span v-if="room.deleted_at" class="trashed-badge">(Trashed)</span></td>
                                <td>{{ room.room_type?.name || 'N/A' }}</td>
                                <td>{{ room.floor || 'N/A' }}</td>
                                <td>{{ room.max_guests || 'N/A' }}</td>
                                <td>{{ room.bed_type || 'N/A' }}</td>
                                <td>
                                    <span :class="['badge', room.smoking ? 'badge-warning' : 'badge-success']">
                                        {{ room.smoking ? 'Smoking' : 'Non-smoking' }}
                                    </span>
                                </td>
                                <td>
                                    <div v-if="room.amenities && room.amenities.length > 0" class="amenities-list">
                                        <span 
                                            v-for="(amenity, index) in room.amenities.slice(0, 3)" 
                                            :key="amenity.id" 
                                            class="amenity-badge"
                                        >
                                            {{ amenity.name }}
                                        </span>
                                        <span v-if="room.amenities.length > 3" class="amenity-more">
                                            +{{ room.amenities.length - 3 }} more
                                        </span>
                                    </div>
                                    <span v-else class="text-muted">No amenities</span>
                                </td>
                                <td>
                                    <span :class="['badge', getStatusClass(room.status)]">
                                        {{ room.status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <template v-if="!room.deleted_at">
                                            <button class="icon-btn" @click="openEditRoomModal(room)" title="Edit">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M11.3333 2.00001C11.5084 1.82491 11.7163 1.68605 11.9447 1.59128C12.1731 1.49651 12.4173 1.44775 12.6639 1.44775C12.9106 1.44775 13.1548 1.49651 13.3832 1.59128C13.6116 1.68605 13.8195 1.82491 13.9946 2.00001C14.1697 2.17511 14.3086 2.38301 14.4033 2.61141C14.4981 2.83981 14.5469 3.08401 14.5469 3.33068C14.5469 3.57734 14.4981 3.82154 14.4033 4.04994C14.3086 4.27834 14.1697 4.48624 13.9946 4.66134L5.176 13.48L1.33333 14.6667L2.52 10.824L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <button class="icon-btn danger" @click="confirmDeleteRoom(room)" title="Delete">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                            </button>
                                        </template>
                                        <template v-else>
                                            <button class="icon-btn success" @click="restoreRoom(room)" title="Restore">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M2 8L6 4L10 8M6 4V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <button class="icon-btn danger" @click="confirmForceDeleteRoom(room)" title="Permanently Delete">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Room Availability Tab -->
        <div v-if="activeTab === 'availability'" class="tab-content">
            <div class="content-header">
                <div class="filters-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Select Date</label>
                            <input 
                                v-model="availabilityDateRange" 
                                type="date" 
                                class="form-control"
                            />
                        </div>
                        <div class="form-group">
                            <label>Room Type</label>
                            <select v-model="availabilityRoomType" @change="fetchAvailability" class="form-control">
                                <option value="">All Room Types</option>
                                <option v-for="type in roomTypes.filter(t => !t.deleted_at)" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div v-if="loadingAvailability" class="loading-state">
                    <p>Loading availability...</p>
                </div>
                <div v-else class="availability-grid">
                    <div v-for="room in availabilityRooms" :key="room.id" class="availability-card">
                        <div class="card-header">
                            <h3>{{ room.room_number }}</h3>
                            <span :class="['badge', getStatusClass(room.status)]">
                                {{ room.status }}
                            </span>
                        </div>
                        <div class="card-body">
                            <p><strong>Type:</strong> {{ room.room_type?.name || 'N/A' }}</p>
                            <p><strong>Floor:</strong> {{ room.floor || 'N/A' }}</p>
                            <p><strong>Max Guests:</strong> {{ room.max_guests || 'N/A' }}</p>
                            <p><strong>Bed Type:</strong> {{ room.bed_type || 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seasonal Pricing Tab -->
        <div v-if="activeTab === 'seasonal'" class="tab-content">
            <div class="content-header">
                <button class="btn btn-primary" @click="openSeasonalPriceModal">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Add Seasonal Price
                </button>
            </div>

            <div class="content-card">
                <div v-if="loadingSeasonalPrices" class="loading-state">
                    <p>Loading seasonal prices...</p>
                </div>
                <div v-else-if="seasonalPrices.length === 0" class="empty-state">
                    <p>No seasonal prices configured</p>
                </div>
                <div v-else class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Type</th>
                                <th>Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="price in seasonalPrices" :key="price.id">
                                <td><strong>{{ price.room_type?.name || 'N/A' }}</strong></td>
                                <td>{{ price.name || 'N/A' }}</td>
                                <td>{{ formatDate(price.start_date) }}</td>
                                <td>{{ formatDate(price.end_date) }}</td>
                                <td>{{ formatPriceReactive(parseFloat(price.price || 0)) }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-btn" @click="editSeasonalPrice(price)" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M11.3333 2.00001C11.5084 1.82491 11.7163 1.68605 11.9447 1.59128C12.1731 1.49651 12.4173 1.44775 12.6639 1.44775C12.9106 1.44775 13.1548 1.49651 13.3832 1.59128C13.6116 1.68605 13.8195 1.82491 13.9946 2.00001C14.1697 2.17511 14.3086 2.38301 14.4033 2.61141C14.4981 2.83981 14.5469 3.08401 14.5469 3.33068C14.5469 3.57734 14.4981 3.82154 14.4033 4.04994C14.3086 4.27834 14.1697 4.48624 13.9946 4.66134L5.176 13.48L1.33333 14.6667L2.52 10.824L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <button class="icon-btn danger" @click="deleteSeasonalPrice(price.id)" title="Delete">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Seasonal Price Modal -->
        <div v-if="showSeasonalPriceModal" class="modal-overlay" @click="closeSeasonalPriceModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingSeasonalPrice ? 'Edit Seasonal Price' : 'Add Seasonal Price' }}</h2>
                    <button class="modal-close" @click="closeSeasonalPriceModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveSeasonalPrice" class="form">
                        <div class="form-group">
                            <label>Room Type *</label>
                            <select v-model="seasonalPriceForm.room_type_id" class="form-control" required>
                                <option value="">Select Room Type</option>
                                <option v-for="type in roomTypes.filter(t => !t.deleted_at)" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Name</label>
                            <input 
                                v-model="seasonalPriceForm.name" 
                                type="text" 
                                class="form-control" 
                                placeholder="e.g., Summer Season, Winter Special"
                            />
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Start Date *</label>
                                <input 
                                    v-model="seasonalPriceForm.start_date" 
                                    type="date" 
                                    class="form-control" 
                                    required 
                                />
                            </div>
                            <div class="form-group">
                                <label>End Date *</label>
                                <input 
                                    v-model="seasonalPriceForm.end_date" 
                                    type="date" 
                                    class="form-control" 
                                    required 
                                />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Price ({{ getCurrencySymbol() }}) *</label>
                            <input 
                                v-model.number="seasonalPriceForm.price" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                class="form-control" 
                                required 
                            />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeSeasonalPriceModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="savingSeasonalPrice">
                                {{ savingSeasonalPrice ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Weekend / Peak Pricing Rules Tab -->
        <div v-if="activeTab === 'pricing-rules'" class="tab-content">
            <div class="content-header">
                <button class="btn btn-primary" @click="openPricingRuleModal">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Add Pricing Rule
                </button>
            </div>

            <div class="content-card">
                <div v-if="loadingPricingRules" class="loading-state">
                    <p>Loading pricing rules...</p>
                </div>
                <div v-else-if="pricingRules.length === 0" class="empty-state">
                    <p>No pricing rules configured</p>
                </div>
                <div v-else class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Type</th>
                                <th>Rule Type</th>
                                <th>Name</th>
                                <th>Day/Date</th>
                                <th>Price Adjustment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="rule in pricingRules" :key="rule.id">
                                <td><strong>{{ rule.room_type?.name || 'All Types' }}</strong></td>
                                <td>
                                    <span :class="['badge', getRuleTypeClass(rule.rule_type)]">
                                        {{ formatRuleType(rule.rule_type) }}
                                    </span>
                                </td>
                                <td>{{ rule.name || 'N/A' }}</td>
                                <td>{{ getRuleDisplayDate(rule) }}</td>
                                <td>{{ getPriceAdjustmentDisplay(rule) }}</td>
                                <td>
                                    <span :class="['badge', rule.is_active ? 'badge-success' : 'badge-secondary']">
                                        {{ rule.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-btn" @click="editPricingRule(rule)" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M11.3333 2.00001C11.5084 1.82491 11.7163 1.68605 11.9447 1.59128C12.1731 1.49651 12.4173 1.44775 12.6639 1.44775C12.9106 1.44775 13.1548 1.49651 13.3832 1.59128C13.6116 1.68605 13.8195 1.82491 13.9946 2.00001C14.1697 2.17511 14.3086 2.38301 14.4033 2.61141C14.4981 2.83981 14.5469 3.08401 14.5469 3.33068C14.5469 3.57734 14.4981 3.82154 14.4033 4.04994C14.3086 4.27834 14.1697 4.48624 13.9946 4.66134L5.176 13.48L1.33333 14.6667L2.52 10.824L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <button class="icon-btn danger" @click="deletePricingRule(rule.id)" title="Delete">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Check-in / Check-out Times Tab -->
        <div v-if="activeTab === 'checkin-checkout'" class="tab-content">
            <div class="content-header">
                <h3>Hotel Default Times</h3>
            </div>
            <div class="content-card">
                <form @submit.prevent="saveCheckInOutTimes" class="form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Check-in Time *</label>
                            <input 
                                v-model="checkInOutForm.checkin_time" 
                                type="time" 
                                class="form-control" 
                                required 
                            />
                        </div>
                        <div class="form-group">
                            <label>Check-out Time *</label>
                            <input 
                                v-model="checkInOutForm.checkout_time" 
                                type="time" 
                                class="form-control" 
                                required 
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>
                            <input 
                                type="checkbox" 
                                v-model="checkInOutForm.early_checkin_allowed" 
                            />
                            Allow Early Check-in
                        </label>
                    </div>
                    <div v-if="checkInOutForm.early_checkin_allowed" class="form-group">
                        <label>Early Check-in Time</label>
                        <input 
                            v-model="checkInOutForm.early_checkin_time" 
                            type="time" 
                            class="form-control" 
                        />
                    </div>
                    <div class="form-group">
                        <label>
                            <input 
                                type="checkbox" 
                                v-model="checkInOutForm.late_checkout_allowed" 
                            />
                            Allow Late Check-out
                        </label>
                    </div>
                    <div v-if="checkInOutForm.late_checkout_allowed" class="form-row">
                        <div class="form-group">
                            <label>Late Check-out Time</label>
                            <input 
                                v-model="checkInOutForm.late_checkout_time" 
                                type="time" 
                                class="form-control" 
                            />
                        </div>
                        <div class="form-group">
                            <label>Late Check-out Fee ({{ getCurrencySymbol() }})</label>
                            <input 
                                v-model.number="checkInOutForm.late_checkout_fee" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                class="form-control" 
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" :disabled="savingCheckInOut">
                            {{ savingCheckInOut ? 'Saving...' : 'Save Hotel Default Times' }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="content-header" style="margin-top: 32px;">
                <h3>Room Type Specific Times</h3>
                <button class="btn btn-primary" @click="openRoomTypeTimeModal">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Add Room Type Time
                </button>
            </div>
            <div class="content-card">
                <div v-if="loadingRoomTypeTimes" class="loading-state">
                    <p>Loading room type times...</p>
                </div>
                <div v-else-if="roomTypeTimes.length === 0" class="empty-state">
                    <p>No room type times configured</p>
                </div>
                <div v-else class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Type</th>
                                <th>Check-in Time</th>
                                <th>Check-out Time</th>
                                <th>Early Check-in</th>
                                <th>Late Check-out</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="time in roomTypeTimes" :key="time.id">
                                <td><strong>{{ time.room_type?.name || 'N/A' }}</strong></td>
                                <td>{{ time.checkin_time || 'N/A' }}</td>
                                <td>{{ time.checkout_time || 'N/A' }}</td>
                                <td>
                                    <span v-if="time.early_checkin_allowed" class="badge badge-success">
                                        Yes ({{ time.early_checkin_time || 'N/A' }})
                                    </span>
                                    <span v-else class="badge badge-secondary">No</span>
                                </td>
                                <td>
                                    <span v-if="time.late_checkout_allowed" class="badge badge-success">
                                        Yes ({{ time.late_checkout_time || 'N/A' }})
                                        <span v-if="time.late_checkout_fee > 0">
                                            - {{ formatPriceReactive(parseFloat(time.late_checkout_fee || 0)) }}
                                        </span>
                                    </span>
                                    <span v-else class="badge badge-secondary">No</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-btn" @click="editRoomTypeTime(time)" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M11.3333 2.00001C11.5084 1.82491 11.7163 1.68605 11.9447 1.59128C12.1731 1.49651 12.4173 1.44775 12.6639 1.44775C12.9106 1.44775 13.1548 1.49651 13.3832 1.59128C13.6116 1.68605 13.8195 1.82491 13.9946 2.00001C14.1697 2.17511 14.3086 2.38301 14.4033 2.61141C14.4981 2.83981 14.5469 3.08401 14.5469 3.33068C14.5469 3.57734 14.4981 3.82154 14.4033 4.04994C14.3086 4.27834 14.1697 4.48624 13.9946 4.66134L5.176 13.48L1.33333 14.6667L2.52 10.824L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <button class="icon-btn danger" @click="deleteRoomTypeTime(time.id)" title="Delete">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Room Type Time Modal -->
        <div v-if="showRoomTypeTimeModal" class="modal-overlay" @click="closeRoomTypeTimeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingRoomTypeTime ? 'Edit Room Type Time' : 'Add Room Type Time' }}</h2>
                    <button class="modal-close" @click="closeRoomTypeTimeModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveRoomTypeTime" class="form">
                        <div class="form-group">
                            <label>Room Type *</label>
                            <select v-model="roomTypeTimeForm.room_type_id" class="form-control" required>
                                <option value="">Select Room Type</option>
                                <option v-for="type in roomTypes.filter(t => !t.deleted_at)" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Check-in Time *</label>
                                <input 
                                    v-model="roomTypeTimeForm.checkin_time" 
                                    type="time" 
                                    class="form-control" 
                                    required 
                                />
                            </div>
                            <div class="form-group">
                                <label>Check-out Time *</label>
                                <input 
                                    v-model="roomTypeTimeForm.checkout_time" 
                                    type="time" 
                                    class="form-control" 
                                    required 
                                />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>
                                <input 
                                    type="checkbox" 
                                    v-model="roomTypeTimeForm.early_checkin_allowed" 
                                />
                                Allow Early Check-in
                            </label>
                        </div>
                        <div v-if="roomTypeTimeForm.early_checkin_allowed" class="form-group">
                            <label>Early Check-in Time</label>
                            <input 
                                v-model="roomTypeTimeForm.early_checkin_time" 
                                type="time" 
                                class="form-control" 
                            />
                        </div>
                        <div class="form-group">
                            <label>
                                <input 
                                    type="checkbox" 
                                    v-model="roomTypeTimeForm.late_checkout_allowed" 
                                />
                                Allow Late Check-out
                            </label>
                        </div>
                        <div v-if="roomTypeTimeForm.late_checkout_allowed" class="form-row">
                            <div class="form-group">
                                <label>Late Check-out Time</label>
                                <input 
                                    v-model="roomTypeTimeForm.late_checkout_time" 
                                    type="time" 
                                    class="form-control" 
                                />
                            </div>
                            <div class="form-group">
                                <label>Late Check-out Fee ({{ getCurrencySymbol() }})</label>
                                <input 
                                    v-model.number="roomTypeTimeForm.late_checkout_fee" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    class="form-control" 
                                />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeRoomTypeTimeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="savingRoomTypeTime">
                                {{ savingRoomTypeTime ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Pricing Rule Modal -->
        <div v-if="showPricingRuleModal" class="modal-overlay" @click="closePricingRuleModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingPricingRule ? 'Edit Pricing Rule' : 'Add Pricing Rule' }}</h2>
                    <button class="modal-close" @click="closePricingRuleModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="savePricingRule" class="form">
                        <div class="form-group">
                            <label>Rule Type *</label>
                            <select v-model="pricingRuleForm.rule_type" @change="onRuleTypeChange" class="form-control" required>
                                <option value="">Select Rule Type</option>
                                <option value="weekend">Weekend</option>
                                <option value="peak">Peak</option>
                                <option value="holiday">Holiday</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Room Type</label>
                            <select v-model="pricingRuleForm.room_type_id" class="form-control">
                                <option value="">All Room Types</option>
                                <option v-for="type in roomTypes.filter(t => !t.deleted_at)" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Name</label>
                            <input 
                                v-model="pricingRuleForm.name" 
                                type="text" 
                                class="form-control" 
                                placeholder="e.g., Weekend Premium, Peak Season"
                            />
                        </div>
                        <div v-if="pricingRuleForm.rule_type === 'weekend'" class="form-group">
                            <label>Day of Week *</label>
                            <select v-model="pricingRuleForm.day_of_week" class="form-control" required>
                                <option value="">Select Day</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                                <option value="sunday">Sunday</option>
                            </select>
                        </div>
                        <div v-if="pricingRuleForm.rule_type === 'peak' || pricingRuleForm.rule_type === 'holiday'" class="form-row">
                            <div class="form-group">
                                <label>Start Date *</label>
                                <input 
                                    v-model="pricingRuleForm.start_date" 
                                    type="date" 
                                    class="form-control" 
                                    required 
                                />
                            </div>
                            <div class="form-group">
                                <label>End Date *</label>
                                <input 
                                    v-model="pricingRuleForm.end_date" 
                                    type="date" 
                                    class="form-control" 
                                    required 
                                />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Price Adjustment Type *</label>
                            <select v-model="pricingRuleForm.price_adjustment_type" @change="onPriceAdjustmentChange" class="form-control" required>
                                <option value="multiplier">Percentage Multiplier</option>
                                <option value="fixed">Fixed Price</option>
                            </select>
                        </div>
                        <div v-if="pricingRuleForm.price_adjustment_type === 'multiplier'" class="form-group">
                            <label>Price Multiplier * (e.g., 1.25 for 25% increase)</label>
                            <input 
                                v-model.number="pricingRuleForm.price_multiplier" 
                                type="number" 
                                step="0.01" 
                                min="0.01" 
                                max="10" 
                                class="form-control" 
                                required 
                            />
                        </div>
                        <div v-if="pricingRuleForm.price_adjustment_type === 'fixed'" class="form-group">
                            <label>Fixed Price ({{ getCurrencySymbol() }}) *</label>
                            <input 
                                v-model.number="pricingRuleForm.fixed_price" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                class="form-control" 
                                required 
                            />
                        </div>
                        <div class="form-group">
                            <label>
                                <input 
                                    type="checkbox" 
                                    v-model="pricingRuleForm.is_active" 
                                />
                                Active
                            </label>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closePricingRuleModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="savingPricingRule">
                                {{ savingPricingRule ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Room Type Modal -->
        <div v-if="showRoomTypeModal" class="modal-overlay" @click="closeRoomTypeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingRoomType ? 'Edit Room Type' : 'Add Room Type' }}</h2>
                    <button class="modal-close" @click="closeRoomTypeModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveRoomType" class="form">
                        <div class="form-group">
                            <label>Room Type *</label>
                            <select 
                                v-model="roomTypeForm.selectedType" 
                                class="form-control" 
                                @change="handleRoomTypeSelection"
                                required
                            >
                                <option value="">Select Room Type</option>
                                <option value="Standard">Standard</option>
                                <option value="Deluxe">Deluxe</option>
                                <option value="Suite">Suite</option>
                                <option value="Family">Family</option>
                                <option value="custom">Custom types</option>
                            </select>
                        </div>
                        <div class="form-group" v-if="roomTypeForm.selectedType === 'custom'">
                            <label>Custom Room Type Name *</label>
                            <input 
                                v-model="roomTypeForm.name" 
                                type="text" 
                                class="form-control" 
                                placeholder="Enter custom room type name"
                                :required="roomTypeForm.selectedType === 'custom'"
                            />
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea 
                                v-model="roomTypeForm.description" 
                                class="form-control" 
                                rows="3"
                                placeholder="Enter room type description"
                            ></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Base Price ({{ getCurrencySymbol() }})</label>
                                <input 
                                    v-model.number="roomTypeForm.base_price" 
                                    type="number" 
                                    step="0.01"
                                    min="0"
                                    class="form-control" 
                                    placeholder="0.00"
                                />
                            </div>
                            <div class="form-group">
                                <label>Max Guests</label>
                                <input 
                                    v-model.number="roomTypeForm.max_guests" 
                                    type="number" 
                                    min="1"
                                    class="form-control" 
                                    placeholder="1"
                                />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeRoomTypeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="savingRoomType">
                                {{ savingRoomType ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Room Modal -->
        <div v-if="showRoomModal" class="modal-overlay" @click="closeRoomModal">
            <div class="modal-content modal-large" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingRoom ? 'Edit Room' : 'Add Room' }}</h2>
                    <button class="modal-close" @click="closeRoomModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveRoom" class="form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Room Type *</label>
                                <select v-model="roomForm.room_type_id" class="form-control" required>
                                    <option value="">Select Room Type</option>
                                    <option v-for="type in roomTypes" :key="type.id" :value="type.id">
                                        {{ type.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Room Number *</label>
                                <input 
                                    v-model="roomForm.room_number" 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="e.g., 101, 201"
                                    required
                                />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Floor</label>
                                <input 
                                    v-model="roomForm.floor" 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="e.g., 1, 2, Ground"
                                />
                            </div>
                            <div class="form-group">
                                <label>Max Guests</label>
                                <input 
                                    v-model.number="roomForm.max_guests" 
                                    type="number" 
                                    min="1"
                                    class="form-control" 
                                    placeholder="1"
                                />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Bed Type</label>
                                <select v-model="roomForm.bed_type" class="form-control">
                                    <option value="">Select Bed Type</option>
                                    <option value="Single">Single</option>
                                    <option value="Double">Double</option>
                                    <option value="Queen">Queen</option>
                                    <option value="King">King</option>
                                    <option value="Twin">Twin</option>
                                    <option value="Bunk">Bunk</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Smoking / Non-smoking</label>
                                <select v-model="roomForm.smoking" class="form-control">
                                    <option :value="false">Non-smoking</option>
                                    <option :value="true">Smoking</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select v-model="roomForm.status" class="form-control">
                                <option value="available">Available</option>
                                <option value="reserved">Reserved</option>
                                <option value="checked_in">Checked In</option>
                                <option value="checked_out">Checked Out</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Amenities</label>
                            <div class="amenities-grid">
                                <label v-for="amenity in allAmenities" :key="amenity.id" class="checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        :value="amenity.id"
                                        v-model="roomForm.amenities"
                                    />
                                    <span>{{ amenity.name }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeRoomModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="savingRoom">
                                {{ savingRoom ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modals -->
        <div v-if="showDeleteRoomTypeModal" class="modal-overlay" @click="closeDeleteRoomTypeModal">
            <div class="modal-content modal-small" @click.stop>
                <div class="modal-header">
                    <h2>Delete Room Type</h2>
                    <button class="modal-close" @click="closeDeleteRoomTypeModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ roomTypeToDelete?.name }}</strong>? This action can be undone by restoring it.</p>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeDeleteRoomTypeModal">Cancel</button>
                        <button type="button" class="btn btn-danger" @click="deleteRoomType" :disabled="deletingRoomType">
                            {{ deletingRoomType ? 'Deleting...' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showForceDeleteRoomTypeModal" class="modal-overlay" @click="closeForceDeleteRoomTypeModal">
            <div class="modal-content modal-small" @click.stop>
                <div class="modal-header">
                    <h2>Permanently Delete Room Type</h2>
                    <button class="modal-close" @click="closeForceDeleteRoomTypeModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to permanently delete <strong>{{ roomTypeToForceDelete?.name }}</strong>? This action cannot be undone.</p>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeForceDeleteRoomTypeModal">Cancel</button>
                        <button type="button" class="btn btn-danger" @click="forceDeleteRoomType" :disabled="forceDeletingRoomType">
                            {{ forceDeletingRoomType ? 'Deleting...' : 'Permanently Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showDeleteRoomModal" class="modal-overlay" @click="closeDeleteRoomModal">
            <div class="modal-content modal-small" @click.stop>
                <div class="modal-header">
                    <h2>Delete Room</h2>
                    <button class="modal-close" @click="closeDeleteRoomModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete room <strong>{{ roomToDelete?.room_number }}</strong>? This action can be undone by restoring it.</p>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeDeleteRoomModal">Cancel</button>
                        <button type="button" class="btn btn-danger" @click="deleteRoom" :disabled="deletingRoom">
                            {{ deletingRoom ? 'Deleting...' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showForceDeleteRoomModal" class="modal-overlay" @click="closeForceDeleteRoomModal">
            <div class="modal-content modal-small" @click.stop>
                <div class="modal-header">
                    <h2>Permanently Delete Room</h2>
                    <button class="modal-close" @click="closeForceDeleteRoomModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to permanently delete room <strong>{{ roomToForceDelete?.room_number }}</strong>? This action cannot be undone.</p>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeForceDeleteRoomModal">Cancel</button>
                        <button type="button" class="btn btn-danger" @click="forceDeleteRoom" :disabled="forceDeletingRoom">
                            {{ forceDeletingRoom ? 'Deleting...' : 'Permanently Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useAlert } from '../../composables/useAlert';
import { useHotelSettings } from '../../composables/useHotelSettings';
import { convertCurrency } from '../../utils/exchangeRates';

export default {
    name: 'Rooms',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const { formatPrice, getCurrencySymbol, loadSettings, convertPrice, hotelSettings } = useHotelSettings();
        const activeTab = ref('types');
        const roomTypes = ref([]);
        
        // Create a computed property to force reactivity when currency changes
        const currentCurrency = computed(() => hotelSettings.value.currency);
        
        // Create reactive formatPrice function that updates when currency changes
        const formatPriceReactive = computed(() => {
            // Access currency to make this computed reactive
            const currency = hotelSettings.value.currency || 'USD';
            return (amount) => {
                return formatPrice(amount);
            };
        });
        
        // Watch for currency changes to refresh prices display
        watch(currentCurrency, (newCurrency, oldCurrency) => {
            // Reload room types when currency changes to update prices
            if (newCurrency !== oldCurrency && roomTypes.value.length > 0) {
                fetchRoomTypes();
            }
        });
        const rooms = ref([]);
        const allAmenities = ref([]);
        const loadingTypes = ref(false);
        const loadingRooms = ref(false);
        const savingRoomType = ref(false);
        const savingRoom = ref(false);
        const deletingRoomType = ref(false);
        const deletingRoom = ref(false);
        const restoringRoomType = ref(false);
        const restoringRoom = ref(false);
        const forceDeletingRoomType = ref(false);
        const forceDeletingRoom = ref(false);
        const showRoomTypeModal = ref(false);
        const showRoomModal = ref(false);
        const showDeleteRoomTypeModal = ref(false);
        const showDeleteRoomModal = ref(false);
        const showForceDeleteRoomTypeModal = ref(false);
        const showForceDeleteRoomModal = ref(false);
        const editingRoomType = ref(null);
        const editingRoom = ref(null);
        const roomTypeToDelete = ref(null);
        const roomToDelete = ref(null);
        const roomTypeToForceDelete = ref(null);
        const roomToForceDelete = ref(null);
        const searchQuery = ref('');
        const selectedRoomType = ref('');
        const showTrashedTypes = ref(false);
        const showTrashedRooms = ref(false);
        let searchTimeout = null;

        // Room Availability
        const availabilityRooms = ref([]);
        const loadingAvailability = ref(false);
        const availabilityDateRange = ref('');
        const availabilityRoomType = ref('');

        // Seasonal Pricing
        const seasonalPrices = ref([]);
        const loadingSeasonalPrices = ref(false);
        const showSeasonalPriceModal = ref(false);
        const editingSeasonalPrice = ref(null);
        const savingSeasonalPrice = ref(false);
        const seasonalPriceForm = ref({
            id: null,
            room_type_id: '',
            name: '',
            start_date: '',
            end_date: '',
            price: 0
        });

        // Pricing Rules
        const pricingRules = ref([]);
        const loadingPricingRules = ref(false);
        const showPricingRuleModal = ref(false);
        const editingPricingRule = ref(null);
        const savingPricingRule = ref(false);
        const pricingRuleForm = ref({
            id: null,
            room_type_id: '',
            rule_type: '',
            name: '',
            day_of_week: '',
            start_date: '',
            end_date: '',
            price_adjustment_type: 'multiplier',
            price_multiplier: 1.0,
            fixed_price: 0,
            is_active: true
        });

        // Check-in / Check-out Times
        const checkInOutForm = ref({
            checkin_time: '',
            checkout_time: '',
            early_checkin_allowed: false,
            early_checkin_time: '',
            late_checkout_allowed: false,
            late_checkout_time: '',
            late_checkout_fee: 0
        });
        const savingCheckInOut = ref(false);
        const roomTypeTimes = ref([]);
        const loadingRoomTypeTimes = ref(false);
        const showRoomTypeTimeModal = ref(false);
        const editingRoomTypeTime = ref(null);
        const savingRoomTypeTime = ref(false);
        const roomTypeTimeForm = ref({
            id: null,
            room_type_id: '',
            checkin_time: '',
            checkout_time: '',
            early_checkin_allowed: false,
            early_checkin_time: '',
            late_checkout_allowed: false,
            late_checkout_time: '',
            late_checkout_fee: 0
        });

        const roomTypeForm = ref({
            name: '',
            description: '',
            base_price: 0,
            max_guests: 1
        });

        const roomForm = ref({
            room_type_id: '',
            room_number: '',
            floor: '',
            max_guests: '',
            bed_type: '',
            smoking: false,
            status: 'available',
            amenities: []
        });

        const fetchRoomTypes = async () => {
            loadingTypes.value = true;
            try {
                const params = {};
                if (showTrashedTypes.value) {
                    params.only_trashed = true;
                }
                const response = await axios.get('/api/room-types', { params });
                roomTypes.value = response.data.data || [];
            } catch (error) {
                console.error('Error fetching room types:', error);
                showError('Error loading room types');
            } finally {
                loadingTypes.value = false;
            }
        };

        const fetchRooms = async () => {
            loadingRooms.value = true;
            try {
                const params = {};
                if (searchQuery.value) {
                    params.search = searchQuery.value;
                }
                if (selectedRoomType.value && !showTrashedRooms.value) {
                    params.room_type_id = selectedRoomType.value;
                }
                if (showTrashedRooms.value) {
                    params.only_trashed = true;
                }
                const response = await axios.get('/api/rooms', { params });
                rooms.value = response.data.data || [];
            } catch (error) {
                console.error('Error fetching rooms:', error);
                showError('Error loading rooms');
            } finally {
                loadingRooms.value = false;
            }
        };

        const fetchAmenities = async () => {
            try {
                const response = await axios.get('/api/rooms/amenities/list');
                allAmenities.value = response.data.data || [];
            } catch (error) {
                console.error('Error fetching amenities:', error);
            }
        };

        const debouncedSearch = () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchRooms();
            }, 500);
        };

        const handleRoomTypeSelection = () => {
            if (roomTypeForm.value.selectedType && roomTypeForm.value.selectedType !== 'custom') {
                roomTypeForm.value.name = roomTypeForm.value.selectedType;
            } else if (roomTypeForm.value.selectedType !== 'custom') {
                roomTypeForm.value.name = '';
            }
        };

        const openRoomTypeModal = () => {
            editingRoomType.value = null;
            roomTypeForm.value = {
                selectedType: '',
                name: '',
                description: '',
                base_price: 0,
                max_guests: 1
            };
            showRoomTypeModal.value = true;
        };

        const openEditRoomTypeModal = (type) => {
            editingRoomType.value = type;
            const predefinedTypes = ['Standard', 'Deluxe', 'Suite', 'Family'];
            const isPredefined = predefinedTypes.includes(type.name);
            
            // Convert price from USD (base) to current currency for editing
            const basePriceInUSD = parseFloat(type.base_price || 0);
            const convertedPrice = convertPrice(basePriceInUSD);
            
            roomTypeForm.value = {
                selectedType: isPredefined ? type.name : 'custom',
                name: type.name,
                description: type.description || '',
                base_price: convertedPrice,
                max_guests: type.max_guests || 1
            };
            showRoomTypeModal.value = true;
        };

        const closeRoomTypeModal = () => {
            showRoomTypeModal.value = false;
            editingRoomType.value = null;
            roomTypeForm.value = {
                selectedType: '',
                name: '',
                description: '',
                base_price: 0,
                max_guests: 1
            };
        };

        const checkRoomTypeExists = async (name) => {
            try {
                const response = await axios.get('/api/room-types');
                const types = response.data.data || [];
                return types.some(type => 
                    type.name.toLowerCase() === name.toLowerCase() && 
                    (!editingRoomType.value || type.id !== editingRoomType.value.id)
                );
            } catch (error) {
                console.error('Error checking room type:', error);
                return false;
            }
        };

        const saveRoomType = async () => {
            // Determine the final room type name
            let finalName = '';
            if (roomTypeForm.value.selectedType === 'custom') {
                finalName = roomTypeForm.value.name.trim();
                if (!finalName) {
                    showError('Please enter a custom room type name');
                    return;
                }
            } else if (roomTypeForm.value.selectedType) {
                finalName = roomTypeForm.value.selectedType;
            } else {
                showError('Please select a room type');
                return;
            }

            // Check if room type already exists
            const exists = await checkRoomTypeExists(finalName);
            if (exists) {
                showError(`Room type "${finalName}" already exists. Please choose a different name.`);
                return;
            }

            savingRoomType.value = true;
            try {
                // Convert price from current currency to USD (base currency) before saving
                const priceInCurrentCurrency = roomTypeForm.value.base_price || 0;
                const baseCurrency = 'USD'; // Always store in USD
                const currentCurrency = hotelSettings.value.currency || 'USD';
                
                let priceInUSD = priceInCurrentCurrency;
                if (currentCurrency !== baseCurrency) {
                    // Convert from current currency to USD
                    priceInUSD = convertCurrency(priceInCurrentCurrency, currentCurrency, baseCurrency);
                }
                
                const payload = {
                    name: finalName,
                    description: roomTypeForm.value.description || '',
                    base_price: priceInUSD,
                    max_guests: roomTypeForm.value.max_guests || 1
                };

                if (editingRoomType.value) {
                    await axios.put(`/api/room-types/${editingRoomType.value.id}`, payload);
                    showSuccess('Room type updated successfully!');
                } else {
                    await axios.post('/api/room-types', payload);
                    showSuccess('Room type created successfully!');
                }
                closeRoomTypeModal();
                fetchRoomTypes();
            } catch (error) {
                console.error('Error saving room type:', error);
                showError(error.response?.data?.message || 'Error saving room type');
            } finally {
                savingRoomType.value = false;
            }
        };

        const confirmDeleteRoomType = (type) => {
            roomTypeToDelete.value = type;
            showDeleteRoomTypeModal.value = true;
        };

        const closeDeleteRoomTypeModal = () => {
            showDeleteRoomTypeModal.value = false;
            roomTypeToDelete.value = null;
        };

        const deleteRoomType = async () => {
            deletingRoomType.value = true;
            try {
                await axios.delete(`/api/room-types/${roomTypeToDelete.value.id}`);
                showSuccess('Room type deleted successfully!');
                closeDeleteRoomTypeModal();
                fetchRoomTypes();
            } catch (error) {
                console.error('Error deleting room type:', error);
                showError(error.response?.data?.message || 'Error deleting room type');
            } finally {
                deletingRoomType.value = false;
            }
        };

        const toggleTrashedTypes = () => {
            showTrashedTypes.value = !showTrashedTypes.value;
            fetchRoomTypes();
        };

        const restoreRoomType = async (type) => {
            restoringRoomType.value = true;
            try {
                await axios.post(`/api/room-types/${type.id}/restore`);
                showSuccess('Room type restored successfully!');
                fetchRoomTypes();
            } catch (error) {
                console.error('Error restoring room type:', error);
                showError(error.response?.data?.message || 'Error restoring room type');
            } finally {
                restoringRoomType.value = false;
            }
        };

        const confirmForceDeleteRoomType = (type) => {
            roomTypeToForceDelete.value = type;
            showForceDeleteRoomTypeModal.value = true;
        };

        const closeForceDeleteRoomTypeModal = () => {
            showForceDeleteRoomTypeModal.value = false;
            roomTypeToForceDelete.value = null;
        };

        const forceDeleteRoomType = async () => {
            forceDeletingRoomType.value = true;
            try {
                await axios.delete(`/api/room-types/${roomTypeToForceDelete.value.id}/force-delete`);
                showSuccess('Room type permanently deleted!');
                closeForceDeleteRoomTypeModal();
                fetchRoomTypes();
            } catch (error) {
                console.error('Error permanently deleting room type:', error);
                showError(error.response?.data?.message || 'Error permanently deleting room type');
            } finally {
                forceDeletingRoomType.value = false;
            }
        };

        const openRoomModal = () => {
            editingRoom.value = null;
            roomForm.value = {
                room_type_id: '',
                room_number: '',
                floor: '',
                max_guests: '',
                bed_type: '',
                smoking: false,
                status: 'available',
                amenities: []
            };
            showRoomModal.value = true;
        };

        const openEditRoomModal = (room) => {
            editingRoom.value = room;
            roomForm.value = {
                room_type_id: room.room_type_id,
                room_number: room.room_number,
                floor: room.floor || '',
                max_guests: room.max_guests || '',
                bed_type: room.bed_type || '',
                smoking: room.smoking || false,
                status: room.status || 'available',
                amenities: room.amenities ? room.amenities.map(a => a.id) : []
            };
            showRoomModal.value = true;
        };

        const closeRoomModal = () => {
            showRoomModal.value = false;
            editingRoom.value = null;
        };

        const saveRoom = async () => {
            savingRoom.value = true;
            try {
                const payload = { ...roomForm.value };
                if (editingRoom.value) {
                    await axios.put(`/api/rooms/${editingRoom.value.id}`, payload);
                    showSuccess('Room updated successfully!');
                } else {
                    await axios.post('/api/rooms', payload);
                    showSuccess('Room created successfully!');
                }
                closeRoomModal();
                fetchRooms();
            } catch (error) {
                console.error('Error saving room:', error);
                showError(error.response?.data?.message || 'Error saving room');
            } finally {
                savingRoom.value = false;
            }
        };

        const confirmDeleteRoom = (room) => {
            roomToDelete.value = room;
            showDeleteRoomModal.value = true;
        };

        const closeDeleteRoomModal = () => {
            showDeleteRoomModal.value = false;
            roomToDelete.value = null;
        };

        const deleteRoom = async () => {
            deletingRoom.value = true;
            try {
                await axios.delete(`/api/rooms/${roomToDelete.value.id}`);
                showSuccess('Room deleted successfully!');
                closeDeleteRoomModal();
                fetchRooms();
            } catch (error) {
                console.error('Error deleting room:', error);
                showError(error.response?.data?.message || 'Error deleting room');
            } finally {
                deletingRoom.value = false;
            }
        };

        const toggleTrashedRooms = () => {
            showTrashedRooms.value = !showTrashedRooms.value;
            fetchRooms();
        };

        const restoreRoom = async (room) => {
            restoringRoom.value = true;
            try {
                await axios.post(`/api/rooms/${room.id}/restore`);
                showSuccess('Room restored successfully!');
                fetchRooms();
            } catch (error) {
                console.error('Error restoring room:', error);
                showError(error.response?.data?.message || 'Error restoring room');
            } finally {
                restoringRoom.value = false;
            }
        };

        const confirmForceDeleteRoom = (room) => {
            roomToForceDelete.value = room;
            showForceDeleteRoomModal.value = true;
        };

        const closeForceDeleteRoomModal = () => {
            showForceDeleteRoomModal.value = false;
            roomToForceDelete.value = null;
        };

        const forceDeleteRoom = async () => {
            forceDeletingRoom.value = true;
            try {
                await axios.delete(`/api/rooms/${roomToForceDelete.value.id}/force-delete`);
                showSuccess('Room permanently deleted!');
                closeForceDeleteRoomModal();
                fetchRooms();
            } catch (error) {
                console.error('Error permanently deleting room:', error);
                showError(error.response?.data?.message || 'Error permanently deleting room');
            } finally {
                forceDeletingRoom.value = false;
            }
        };

        const getStatusClass = (status) => {
            const classes = {
                'available': 'badge-success',
                'reserved': 'badge-warning',
                'checked_in': 'badge-info',
                'checked_out': 'badge-secondary',
                'maintenance': 'badge-danger'
            };
            return classes[status] || 'badge-secondary';
        };

        // Room Availability Functions
        const fetchAvailability = async () => {
            loadingAvailability.value = true;
            try {
                const params = {};
                if (availabilityRoomType.value) {
                    params.room_type_id = availabilityRoomType.value;
                }
                const response = await axios.get('/api/rooms', { params });
                availabilityRooms.value = response.data.data || response.data || [];
            } catch (error) {
                console.error('Error fetching availability:', error);
                showError('Error loading room availability');
            } finally {
                loadingAvailability.value = false;
            }
        };

        // Seasonal Pricing Functions
        const fetchSeasonalPrices = async () => {
            loadingSeasonalPrices.value = true;
            try {
                const response = await axios.get('/api/pricing/seasonal');
                seasonalPrices.value = response.data || [];
            } catch (error) {
                console.error('Error fetching seasonal prices:', error);
                showError('Error loading seasonal prices');
            } finally {
                loadingSeasonalPrices.value = false;
            }
        };

        const openSeasonalPriceModal = () => {
            editingSeasonalPrice.value = null;
            seasonalPriceForm.value = {
                id: null,
                room_type_id: '',
                name: '',
                start_date: '',
                end_date: '',
                price: 0
            };
            showSeasonalPriceModal.value = true;
        };

        const editSeasonalPrice = (price) => {
            editingSeasonalPrice.value = price;
            // Convert price from USD to current currency for editing
            const priceInUSD = parseFloat(price.price || 0);
            const convertedPrice = convertPrice(priceInUSD);
            
            seasonalPriceForm.value = {
                id: price.id,
                room_type_id: price.room_type_id,
                name: price.name || '',
                start_date: price.start_date,
                end_date: price.end_date,
                price: convertedPrice
            };
            showSeasonalPriceModal.value = true;
        };

        const closeSeasonalPriceModal = () => {
            showSeasonalPriceModal.value = false;
            editingSeasonalPrice.value = null;
        };

        const saveSeasonalPrice = async () => {
            savingSeasonalPrice.value = true;
            try {
                // Convert price from current currency to USD before saving
                const priceInCurrentCurrency = seasonalPriceForm.value.price || 0;
                const currentCurrency = hotelSettings.value.currency || 'USD';
                const baseCurrency = 'USD';
                
                let priceInUSD = priceInCurrentCurrency;
                if (currentCurrency !== baseCurrency && priceInCurrentCurrency > 0) {
                    priceInUSD = convertCurrency(priceInCurrentCurrency, currentCurrency, baseCurrency);
                }

                const payload = {
                    id: seasonalPriceForm.value.id,
                    room_type_id: seasonalPriceForm.value.room_type_id,
                    name: seasonalPriceForm.value.name,
                    start_date: seasonalPriceForm.value.start_date,
                    end_date: seasonalPriceForm.value.end_date,
                    price: priceInUSD
                };

                const response = await axios.post('/api/pricing/seasonal', payload);
                if (response.data.success) {
                    showSuccess(response.data.message);
                    closeSeasonalPriceModal();
                    fetchSeasonalPrices();
                }
            } catch (error) {
                console.error('Error saving seasonal price:', error);
                showError(error.response?.data?.message || 'Error saving seasonal price');
            } finally {
                savingSeasonalPrice.value = false;
            }
        };

        const deleteSeasonalPrice = async (id) => {
            if (!confirm('Are you sure you want to delete this seasonal price?')) return;
            try {
                const response = await axios.delete(`/api/pricing/seasonal/${id}`);
                if (response.data.success) {
                    showSuccess(response.data.message);
                    fetchSeasonalPrices();
                }
            } catch (error) {
                console.error('Error deleting seasonal price:', error);
                showError(error.response?.data?.message || 'Error deleting seasonal price');
            }
        };

        const formatDate = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        };

        // Pricing Rules Functions
        const fetchPricingRules = async () => {
            loadingPricingRules.value = true;
            try {
                const response = await axios.get('/api/pricing/rules');
                pricingRules.value = response.data || [];
            } catch (error) {
                console.error('Error fetching pricing rules:', error);
                showError('Error loading pricing rules');
            } finally {
                loadingPricingRules.value = false;
            }
        };

        const openPricingRuleModal = () => {
            editingPricingRule.value = null;
            pricingRuleForm.value = {
                id: null,
                room_type_id: '',
                rule_type: '',
                name: '',
                day_of_week: '',
                start_date: '',
                end_date: '',
                price_adjustment_type: 'multiplier',
                price_multiplier: 1.0,
                fixed_price: 0,
                is_active: true
            };
            showPricingRuleModal.value = true;
        };

        const editPricingRule = (rule) => {
            editingPricingRule.value = rule;
            
            // Convert fixed_price from USD to current currency for editing
            let fixedPrice = 0;
            if (rule.fixed_price) {
                const priceInUSD = parseFloat(rule.fixed_price);
                fixedPrice = convertPrice(priceInUSD);
            }
            
            // Determine price adjustment type
            const priceAdjustmentType = rule.fixed_price ? 'fixed' : 'multiplier';
            
            pricingRuleForm.value = {
                id: rule.id,
                room_type_id: rule.room_type_id || '',
                rule_type: rule.rule_type,
                name: rule.name || '',
                day_of_week: rule.day_of_week || '',
                start_date: rule.start_date || '',
                end_date: rule.end_date || '',
                price_adjustment_type: priceAdjustmentType,
                price_multiplier: parseFloat(rule.price_multiplier || 1.0),
                fixed_price: fixedPrice,
                is_active: rule.is_active !== undefined ? rule.is_active : true
            };
            showPricingRuleModal.value = true;
        };

        const closePricingRuleModal = () => {
            showPricingRuleModal.value = false;
            editingPricingRule.value = null;
        };

        const onRuleTypeChange = () => {
            // Reset day_of_week and dates when rule type changes
            if (pricingRuleForm.value.rule_type !== 'weekend') {
                pricingRuleForm.value.day_of_week = '';
            }
            if (pricingRuleForm.value.rule_type === 'weekend') {
                pricingRuleForm.value.start_date = '';
                pricingRuleForm.value.end_date = '';
            }
        };

        const onPriceAdjustmentChange = () => {
            // Reset values when switching between multiplier and fixed
            if (pricingRuleForm.value.price_adjustment_type === 'multiplier') {
                pricingRuleForm.value.fixed_price = 0;
            } else {
                pricingRuleForm.value.price_multiplier = 1.0;
            }
        };

        const savePricingRule = async () => {
            savingPricingRule.value = true;
            try {
                const currentCurrency = hotelSettings.value.currency || 'USD';
                const baseCurrency = 'USD';
                
                // Convert fixed_price from current currency to USD before saving
                let fixedPriceInUSD = null;
                if (pricingRuleForm.value.price_adjustment_type === 'fixed' && pricingRuleForm.value.fixed_price) {
                    const priceInCurrentCurrency = pricingRuleForm.value.fixed_price;
                    if (currentCurrency !== baseCurrency && priceInCurrentCurrency > 0) {
                        fixedPriceInUSD = convertCurrency(priceInCurrentCurrency, currentCurrency, baseCurrency);
                    } else {
                        fixedPriceInUSD = priceInCurrentCurrency;
                    }
                }

                const payload = {
                    id: pricingRuleForm.value.id,
                    room_type_id: pricingRuleForm.value.room_type_id || null,
                    rule_type: pricingRuleForm.value.rule_type,
                    name: pricingRuleForm.value.name,
                    day_of_week: pricingRuleForm.value.rule_type === 'weekend' ? pricingRuleForm.value.day_of_week : null,
                    start_date: (pricingRuleForm.value.rule_type === 'peak' || pricingRuleForm.value.rule_type === 'holiday') ? pricingRuleForm.value.start_date : null,
                    end_date: (pricingRuleForm.value.rule_type === 'peak' || pricingRuleForm.value.rule_type === 'holiday') ? pricingRuleForm.value.end_date : null,
                    price_multiplier: pricingRuleForm.value.price_adjustment_type === 'multiplier' ? pricingRuleForm.value.price_multiplier : null,
                    fixed_price: fixedPriceInUSD,
                    is_active: pricingRuleForm.value.is_active
                };

                const response = await axios.post('/api/pricing/rules', payload);
                if (response.data.success) {
                    showSuccess(response.data.message);
                    closePricingRuleModal();
                    fetchPricingRules();
                }
            } catch (error) {
                console.error('Error saving pricing rule:', error);
                showError(error.response?.data?.message || 'Error saving pricing rule');
            } finally {
                savingPricingRule.value = false;
            }
        };

        const deletePricingRule = async (id) => {
            if (!confirm('Are you sure you want to delete this pricing rule?')) return;
            try {
                const response = await axios.delete(`/api/pricing/rules/${id}`);
                if (response.data.success) {
                    showSuccess(response.data.message);
                    fetchPricingRules();
                }
            } catch (error) {
                console.error('Error deleting pricing rule:', error);
                showError(error.response?.data?.message || 'Error deleting pricing rule');
            }
        };

        const formatRuleType = (ruleType) => {
            const types = {
                'weekend': 'Weekend',
                'peak': 'Peak',
                'holiday': 'Holiday'
            };
            return types[ruleType] || ruleType;
        };

        const getRuleTypeClass = (ruleType) => {
            const classes = {
                'weekend': 'badge-info',
                'peak': 'badge-warning',
                'holiday': 'badge-danger'
            };
            return classes[ruleType] || 'badge-secondary';
        };

        const getRuleDisplayDate = (rule) => {
            if (rule.rule_type === 'weekend' && rule.day_of_week) {
                return rule.day_of_week.charAt(0).toUpperCase() + rule.day_of_week.slice(1);
            } else if (rule.start_date && rule.end_date) {
                return `${formatDate(rule.start_date)} - ${formatDate(rule.end_date)}`;
            }
            return 'N/A';
        };

        const getPriceAdjustmentDisplay = (rule) => {
            if (rule.fixed_price) {
                return formatPriceReactive.value(parseFloat(rule.fixed_price));
            } else if (rule.price_multiplier) {
                const percentage = ((parseFloat(rule.price_multiplier) - 1) * 100).toFixed(0);
                return `${percentage > 0 ? '+' : ''}${percentage}%`;
            }
            return 'N/A';
        };

        // Check-in / Check-out Times Functions
        const loadCheckInOutTimes = async () => {
            try {
                const response = await axios.get('/api/hotel/settings');
                if (response.data) {
                    checkInOutForm.value = {
                        checkin_time: response.data.checkin_time || '14:00',
                        checkout_time: response.data.checkout_time || '11:00',
                        early_checkin_allowed: response.data.early_checkin_allowed || false,
                        early_checkin_time: response.data.early_checkin_time || '',
                        late_checkout_allowed: response.data.late_checkout_allowed || false,
                        late_checkout_time: response.data.late_checkout_time || '',
                        late_checkout_fee: response.data.late_checkout_fee || 0
                    };
                }
            } catch (error) {
                console.error('Error loading check-in/check-out times:', error);
            }
        };

        const saveCheckInOutTimes = async () => {
            savingCheckInOut.value = true;
            try {
                const currentCurrency = hotelSettings.value.currency || 'USD';
                const baseCurrency = 'USD';
                
                // Convert late_checkout_fee from current currency to USD before saving
                let feeInUSD = 0;
                if (checkInOutForm.value.late_checkout_fee) {
                    const feeInCurrentCurrency = checkInOutForm.value.late_checkout_fee;
                    if (currentCurrency !== baseCurrency && feeInCurrentCurrency > 0) {
                        feeInUSD = convertCurrency(feeInCurrentCurrency, currentCurrency, baseCurrency);
                    } else {
                        feeInUSD = feeInCurrentCurrency;
                    }
                }

                const payload = {
                    checkin_time: checkInOutForm.value.checkin_time,
                    checkout_time: checkInOutForm.value.checkout_time,
                    early_checkin_allowed: checkInOutForm.value.early_checkin_allowed,
                    early_checkin_time: checkInOutForm.value.early_checkin_allowed ? checkInOutForm.value.early_checkin_time : null,
                    late_checkout_allowed: checkInOutForm.value.late_checkout_allowed,
                    late_checkout_time: checkInOutForm.value.late_checkout_allowed ? checkInOutForm.value.late_checkout_time : null,
                    late_checkout_fee: feeInUSD
                };

                const response = await axios.post('/api/hotel/settings', payload);
                if (response.data.success) {
                    showSuccess('Check-in / Check-out times saved successfully!');
                }
            } catch (error) {
                console.error('Error saving check-in/check-out times:', error);
                showError(error.response?.data?.message || 'Error saving check-in/check-out times');
            } finally {
                savingCheckInOut.value = false;
            }
        };

        const fetchRoomTypeTimes = async () => {
            loadingRoomTypeTimes.value = true;
            try {
                const response = await axios.get('/api/room-type-times');
                roomTypeTimes.value = response.data || [];
            } catch (error) {
                console.error('Error fetching room type times:', error);
                showError('Error loading room type times');
            } finally {
                loadingRoomTypeTimes.value = false;
            }
        };

        const openRoomTypeTimeModal = () => {
            editingRoomTypeTime.value = null;
            roomTypeTimeForm.value = {
                id: null,
                room_type_id: '',
                checkin_time: '',
                checkout_time: '',
                early_checkin_allowed: false,
                early_checkin_time: '',
                late_checkout_allowed: false,
                late_checkout_time: '',
                late_checkout_fee: 0
            };
            showRoomTypeTimeModal.value = true;
        };

        const editRoomTypeTime = (time) => {
            editingRoomTypeTime.value = time;
            
            // Convert late_checkout_fee from USD to current currency for editing
            let fee = 0;
            if (time.late_checkout_fee) {
                const feeInUSD = parseFloat(time.late_checkout_fee);
                fee = convertPrice(feeInUSD);
            }
            
            // Helper function to format time for HTML time input (HH:mm format)
            const formatTimeForInput = (timeValue) => {
                if (!timeValue) return '';
                // If already in HH:mm format, return as is
                if (typeof timeValue === 'string' && /^\d{2}:\d{2}$/.test(timeValue)) {
                    return timeValue;
                }
                // If it's a longer string, extract HH:mm part
                if (typeof timeValue === 'string') {
                    const match = timeValue.match(/(\d{2}:\d{2})/);
                    if (match) {
                        return match[1];
                    }
                }
                return '';
            };
            
            roomTypeTimeForm.value = {
                id: time.id,
                room_type_id: time.room_type_id,
                checkin_time: formatTimeForInput(time.checkin_time),
                checkout_time: formatTimeForInput(time.checkout_time),
                early_checkin_allowed: time.early_checkin_allowed || false,
                early_checkin_time: formatTimeForInput(time.early_checkin_time),
                late_checkout_allowed: time.late_checkout_allowed || false,
                late_checkout_time: formatTimeForInput(time.late_checkout_time),
                late_checkout_fee: fee
            };
            showRoomTypeTimeModal.value = true;
        };

        const closeRoomTypeTimeModal = () => {
            showRoomTypeTimeModal.value = false;
            editingRoomTypeTime.value = null;
        };

        const saveRoomTypeTime = async () => {
            savingRoomTypeTime.value = true;
            try {
                const currentCurrency = hotelSettings.value.currency || 'USD';
                const baseCurrency = 'USD';
                
                // Convert late_checkout_fee from current currency to USD before saving
                let feeInUSD = 0;
                if (roomTypeTimeForm.value.late_checkout_fee) {
                    const feeInCurrentCurrency = roomTypeTimeForm.value.late_checkout_fee;
                    if (currentCurrency !== baseCurrency && feeInCurrentCurrency > 0) {
                        feeInUSD = convertCurrency(feeInCurrentCurrency, currentCurrency, baseCurrency);
                    } else {
                        feeInUSD = feeInCurrentCurrency;
                    }
                }

                const payload = {
                    id: roomTypeTimeForm.value.id,
                    room_type_id: roomTypeTimeForm.value.room_type_id,
                    checkin_time: roomTypeTimeForm.value.checkin_time,
                    checkout_time: roomTypeTimeForm.value.checkout_time,
                    early_checkin_allowed: roomTypeTimeForm.value.early_checkin_allowed,
                    early_checkin_time: roomTypeTimeForm.value.early_checkin_allowed ? roomTypeTimeForm.value.early_checkin_time : null,
                    late_checkout_allowed: roomTypeTimeForm.value.late_checkout_allowed,
                    late_checkout_time: roomTypeTimeForm.value.late_checkout_allowed ? roomTypeTimeForm.value.late_checkout_time : null,
                    late_checkout_fee: feeInUSD
                };

                const response = await axios.post('/api/room-type-times', payload);
                if (response.data.success) {
                    showSuccess(response.data.message);
                    closeRoomTypeTimeModal();
                    fetchRoomTypeTimes();
                }
            } catch (error) {
                console.error('Error saving room type time:', error);
                showError(error.response?.data?.message || 'Error saving room type time');
            } finally {
                savingRoomTypeTime.value = false;
            }
        };

        const deleteRoomTypeTime = async (id) => {
            if (!confirm('Are you sure you want to delete this room type time?')) return;
            try {
                const response = await axios.delete(`/api/room-type-times/${id}`);
                if (response.data.success) {
                    showSuccess(response.data.message);
                    fetchRoomTypeTimes();
                }
            } catch (error) {
                console.error('Error deleting room type time:', error);
                showError(error.response?.data?.message || 'Error deleting room type time');
            }
        };

        onMounted(async () => {
            // Load settings first to ensure currency is available for price conversion
            await loadSettings();
            fetchRoomTypes();
            fetchRooms();
            fetchAmenities();
            fetchAvailability();
            fetchSeasonalPrices();
            fetchPricingRules();
            loadCheckInOutTimes();
            fetchRoomTypeTimes();
        });

        return {
            activeTab,
            roomTypes,
            rooms,
            allAmenities,
            loadingTypes,
            loadingRooms,
            savingRoomType,
            savingRoom,
            deletingRoomType,
            deletingRoom,
            restoringRoomType,
            restoringRoom,
            forceDeletingRoomType,
            forceDeletingRoom,
            showRoomTypeModal,
            showRoomModal,
            showDeleteRoomTypeModal,
            showDeleteRoomModal,
            showForceDeleteRoomTypeModal,
            showForceDeleteRoomModal,
            editingRoomType,
            editingRoom,
            roomTypeToDelete,
            roomToDelete,
            roomTypeToForceDelete,
            roomToForceDelete,
            searchQuery,
            selectedRoomType,
            showTrashedTypes,
            showTrashedRooms,
            roomTypeForm,
            roomForm,
            fetchRoomTypes,
            fetchRooms,
            debouncedSearch,
            handleRoomTypeSelection,
            openRoomTypeModal,
            openEditRoomTypeModal,
            closeRoomTypeModal,
            checkRoomTypeExists,
            saveRoomType,
            confirmDeleteRoomType,
            closeDeleteRoomTypeModal,
            deleteRoomType,
            toggleTrashedTypes,
            restoreRoomType,
            confirmForceDeleteRoomType,
            closeForceDeleteRoomTypeModal,
            forceDeleteRoomType,
            openRoomModal,
            openEditRoomModal,
            closeRoomModal,
            saveRoom,
            confirmDeleteRoom,
            closeDeleteRoomModal,
            deleteRoom,
            toggleTrashedRooms,
            restoreRoom,
            confirmForceDeleteRoom,
            closeForceDeleteRoomModal,
            forceDeleteRoom,
            getStatusClass,
            formatPrice,
            formatPriceReactive,
            getCurrencySymbol,
            convertPrice,
            hotelSettings,
            currentCurrency,
            // Availability
            availabilityRooms,
            loadingAvailability,
            availabilityDateRange,
            availabilityRoomType,
            fetchAvailability,
            // Seasonal Pricing
            seasonalPrices,
            loadingSeasonalPrices,
            showSeasonalPriceModal,
            editingSeasonalPrice,
            savingSeasonalPrice,
            seasonalPriceForm,
            openSeasonalPriceModal,
            editSeasonalPrice,
            closeSeasonalPriceModal,
            saveSeasonalPrice,
            deleteSeasonalPrice,
            fetchSeasonalPrices,
            formatDate,
            // Pricing Rules
            pricingRules,
            loadingPricingRules,
            showPricingRuleModal,
            editingPricingRule,
            savingPricingRule,
            pricingRuleForm,
            openPricingRuleModal,
            editPricingRule,
            closePricingRuleModal,
            onRuleTypeChange,
            onPriceAdjustmentChange,
            savePricingRule,
            deletePricingRule,
            fetchPricingRules,
            formatRuleType,
            getRuleTypeClass,
            getRuleDisplayDate,
            getPriceAdjustmentDisplay,
            // Check-in / Check-out Times
            checkInOutForm,
            savingCheckInOut,
            roomTypeTimes,
            loadingRoomTypeTimes,
            showRoomTypeTimeModal,
            editingRoomTypeTime,
            savingRoomTypeTime,
            roomTypeTimeForm,
            loadCheckInOutTimes,
            saveCheckInOutTimes,
            fetchRoomTypeTimes,
            openRoomTypeTimeModal,
            editRoomTypeTime,
            closeRoomTypeTimeModal,
            saveRoomTypeTime,
            deleteRoomTypeTime
        };
    }
}
</script>

<style scoped>
.rooms-page {
    padding: 24px;
}

.page-header {
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
}

.page-subtitle {
    font-size: 14px;
    color: #718096;
}

/* Tabs */
.tabs-container {
    margin-bottom: 24px;
}

.tabs {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e2e8f0;
}

.tab {
    padding: 12px 24px;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    color: #718096;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: -2px;
}

.tab:hover {
    color: #667eea;
}

.tab.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.tab-content {
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 16px;
}

.view-toggle {
    display: flex;
    gap: 8px;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 14px;
}

.btn-outline-secondary {
    background: transparent;
    border: 1px solid #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

.trashed-badge {
    font-size: 12px;
    color: #dc3545;
    font-weight: normal;
    margin-left: 8px;
}

.room-type-card.trashed {
    opacity: 0.7;
    border-color: #dc3545;
}

.trashed-row {
    opacity: 0.7;
    background-color: #fff5f5;
}

.icon-btn.success {
    color: #28a745;
}

.icon-btn.success:hover {
    background-color: #d4edda;
}

.filters-section {
    display: flex;
    gap: 12px;
    flex: 1;
}

.search-box {
    position: relative;
    flex: 1;
    max-width: 300px;
}

.search-box svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
}

.search-box input {
    width: 100%;
    padding: 10px 12px 10px 40px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
}

.filter-select {
    padding: 10px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    min-width: 180px;
}

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 24px;
}

.loading-state, .empty-state {
    text-align: center;
    padding: 40px;
    color: #718096;
}

/* Room Types Grid */
.room-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.room-type-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
}

.room-type-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.card-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1a202c;
    margin: 0;
}

.card-actions {
    display: flex;
    gap: 8px;
}

.icon-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: #718096;
    cursor: pointer;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.icon-btn:hover {
    background: #e2e8f0;
    color: #667eea;
}

.icon-btn.danger:hover {
    background: #fee;
    color: #dc3545;
}

.description {
    color: #718096;
    font-size: 14px;
    margin-bottom: 16px;
    line-height: 1.5;
}

.type-details {
    display: flex;
    gap: 24px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-item .label {
    font-size: 12px;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item .value {
    font-size: 16px;
    font-weight: 600;
    color: #1a202c;
}

/* Table */
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
    font-size: 12px;
    font-weight: 600;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
    color: #2d3748;
}

.data-table tbody tr:hover {
    background: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.badge-success {
    background: #d1e7dd;
    color: #0f5132;
}

.badge-warning {
    background: #fff3cd;
    color: #664d03;
}

.badge-info {
    background: #cff4fc;
    color: #055160;
}

.badge-secondary {
    background: #e2e8f0;
    color: #495057;
}

.badge-danger {
    background: #f8d7da;
    color: #842029;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

/* Modal */
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
    padding: 20px;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-large {
    max-width: 700px;
}

.modal-small {
    max-width: 400px;
}

.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: #1a202c;
    margin: 0;
}

.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: #718096;
    cursor: pointer;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #e2e8f0;
    color: #1a202c;
}

.modal-body {
    padding: 24px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
}

.form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    font-size: 14px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    transition: all 0.2s;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 6px;
    max-height: 200px;
    overflow-y: auto;
}

.amenities-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}

.amenity-badge {
    display: inline-block;
    padding: 4px 8px;
    background: #e3f2fd;
    color: #1976d2;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
}

.amenity-more {
    display: inline-block;
    padding: 4px 8px;
    background: #f5f5f5;
    color: #666;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    font-style: italic;
}

.text-muted {
    color: #999;
    font-size: 13px;
    font-style: italic;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: background 0.2s;
}

.checkbox-label:hover {
    background: #e2e8f0;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #5568d3;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    background: #e2e8f0;
    color: #495057;
}

.btn-secondary:hover {
    background: #cbd5e0;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #c82333;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .room-types-grid {
        grid-template-columns: 1fr;
    }
    
    .content-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filters-section {
        flex-direction: column;
    }
    
    .search-box {
        max-width: 100%;
    }
}

/* Availability Styles */
.availability-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.availability-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 16px;
    transition: all 0.2s;
}

.availability-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.availability-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e9ecef;
}

.availability-card .card-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
}

.availability-card .card-body p {
    margin: 8px 0;
    font-size: 14px;
    color: #6c757d;
}

.availability-card .card-body strong {
    color: #2c3e50;
}

</style>

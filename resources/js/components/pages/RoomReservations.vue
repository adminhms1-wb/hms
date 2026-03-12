<template>
    <div class="room-reservations-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Room & Reservation Management</h1>
                <p class="page-subtitle">Manage room reservations and bookings</p>
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

        <!-- Guest Assignment Tab -->
        <div v-if="activeTab === 'guest-assignment'" class="tab-content">
            <div class="content-header">
                <div class="filters-section">
                    <select v-model="assignmentRoomFilter" @change="fetchRoomsForAssignment" class="filter-select">
                        <option value="">All Rooms</option>
                        <option v-for="room in allRooms" :key="room.id" :value="room.id">
                            {{ room.room_number }} - {{ room.room_type?.name }}
                        </option>
                    </select>
                    <select v-model="assignmentStatusFilter" @change="fetchRoomsForAssignment" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="available">Available</option>
                        <option value="reserved">Reserved</option>
                        <option value="checked_in">Checked In</option>
                    </select>
                </div>
                <button class="btn btn-primary" @click="openGuestAssignmentModal(null)">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 3V17M3 10H17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Assign Guest to Room
                </button>
            </div>

            <div class="content-card">
                <div v-if="loadingAssignment" class="loading-state">
                    <p>Loading rooms...</p>
                </div>
                <div v-else-if="roomsForAssignment.length === 0" class="empty-state">
                    <p>No rooms found</p>
                </div>
                <div v-else class="assignment-grid">
                    <div 
                        v-for="room in roomsForAssignment" 
                        :key="room.id" 
                        class="room-card"
                        :class="getRoomCardClass(room)"
                    >
                        <div class="room-card-header">
                            <div>
                                <h3>{{ room.room_number }}</h3>
                                <p>{{ room.room_type?.name }}</p>
                            </div>
                            <span :class="['badge', getRoomStatusClass(room.status)]">
                                {{ formatRoomStatus(room.status) }}
                            </span>
                        </div>
                        <div class="room-card-body">
                            <div v-if="getCurrentReservation(room)" class="current-reservation">
                                <div class="reservation-info">
                                    <strong>Current Guest:</strong>
                                    <p>{{ getCurrentReservation(room).guest_name }}</p>
                                    <small>{{ getCurrentReservation(room).guest_email }}</small>
                                </div>
                                <div class="reservation-dates">
                                    <span>Check-in: {{ formatDate(getCurrentReservation(room).check_in_date) }}</span>
                                    <span>Check-out: {{ formatDate(getCurrentReservation(room).check_out_date) }}</span>
                                </div>
                                <div class="reservation-actions">
                                    <button class="btn btn-sm btn-info" @click="viewReservationDetails(getCurrentReservation(room))">
                                        View Details
                                    </button>
                                    <button 
                                        v-if="getCurrentReservation(room).status === 'confirmed'"
                                        class="btn btn-sm btn-success" 
                                        @click="checkIn(getCurrentReservation(room))"
                                    >
                                        Check In
                                    </button>
                                </div>
                            </div>
                            <div v-else class="no-reservation">
                                <p>No current reservation</p>
                                <button class="btn btn-sm btn-primary" @click="openGuestAssignmentModal(room)">
                                    Assign Guest
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Group Bookings Tab -->
        <div v-if="activeTab === 'group-bookings'" class="tab-content">
            <div class="content-header">
                <div class="filters-section">
                    <div class="search-box">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                            <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <input 
                            type="text" 
                            v-model="groupSearchQuery" 
                            placeholder="Search by group ID..."
                            @input="debouncedGroupSearch"
                        />
                    </div>
                </div>
                <button class="btn btn-primary" @click="openGroupBookingModal">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 3V17M3 10H17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Create Group Booking
                </button>
            </div>

            <div class="content-card">
                <div v-if="loadingGroups" class="loading-state">
                    <p>Loading group bookings...</p>
                </div>
                <div v-else-if="groupBookings.length === 0" class="empty-state">
                    <p>No group bookings found</p>
                </div>
                <div v-else class="group-bookings-list">
                    <div 
                        v-for="group in groupBookings" 
                        :key="group.group_id" 
                        class="group-booking-card"
                    >
                        <div class="group-header">
                            <div>
                                <h3>Group: {{ group.group_id }}</h3>
                                <p>{{ group.reservations.length }} room(s) booked</p>
                            </div>
                            <div class="group-stats">
                                <span>Total: ${{ group.total_amount.toFixed(2) }}</span>
                                <span>Guests: {{ group.total_guests }}</span>
                            </div>
                        </div>
                        <div class="group-reservations">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Room</th>
                                        <th>Guest</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="reservation in group.reservations" :key="reservation.id">
                                        <td>{{ reservation.room?.room_number }}</td>
                                        <td>{{ reservation.guest_name }}</td>
                                        <td>{{ formatDate(reservation.check_in_date) }}</td>
                                        <td>{{ formatDate(reservation.check_out_date) }}</td>
                                        <td>
                                            <span :class="['badge', getStatusClass(reservation.status)]">
                                                {{ formatStatus(reservation.status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-icon" @click="openReservationModal(reservation)" title="View">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8 3C5.5 3 3.5 5 3.5 7.5C3.5 10 5.5 12 8 12C10.5 12 12.5 10 12.5 7.5C12.5 5 10.5 3 8 3Z" fill="currentColor"/>
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

        <!-- Reservations Tab -->
        <div v-if="activeTab === 'reservations'" class="tab-content">
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
                            placeholder="Search by guest name, room number..."
                            @input="debouncedSearch"
                        />
                    </div>
                    <select v-model="filterStatus" @change="fetchReservations" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="checked_in">Checked In</option>
                        <option value="checked_out">Checked Out</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select v-model="filterBookingType" @change="fetchReservations" class="filter-select">
                        <option value="">All Types</option>
                        <option value="walk_in">Walk-in</option>
                        <option value="advance">Advance</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <button class="btn btn-primary" @click="openReservationModal(null)">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 3V17M3 10H17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    New Reservation
                </button>
            </div>

            <div class="content-card">
                <div v-if="loading" class="loading-state">
                    <p>Loading reservations...</p>
                </div>
                <div v-else-if="reservations.length === 0" class="empty-state">
                    <p>No reservations found</p>
                </div>
                <div v-else class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="reservation in reservations" :key="reservation.id">
                                <td>#{{ reservation.id }}</td>
                                <td>
                                    <div class="guest-info">
                                        <strong>{{ reservation.guest_name }}</strong>
                                        <small v-if="reservation.guest_email">{{ reservation.guest_email }}</small>
                                        <div v-if="reservation.guests && reservation.guests.length > 0" class="additional-guests-badge">
                                            <span class="badge badge-info">+{{ reservation.guests.length }} more</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ reservation.room?.room_number }}</strong>
                                    <small>{{ reservation.room?.room_type?.name }}</small>
                                </td>
                                <td>{{ formatDate(reservation.check_in_date) }}</td>
                                <td>{{ formatDate(reservation.check_out_date) }}</td>
                                <td>{{ reservation.number_of_guests }}</td>
                                <td>${{ reservation.total_amount }}</td>
                                <td>
                                    <span :class="['badge', getBookingTypeClass(reservation.booking_type)]">
                                        {{ formatBookingType(reservation.booking_type) }}
                                    </span>
                                </td>
                                <td>
                                    <span :class="['badge', getStatusClass(reservation.status)]">
                                        {{ formatStatus(reservation.status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button 
                                            v-if="reservation.status === 'confirmed'" 
                                            class="btn-icon btn-success" 
                                            @click="checkIn(reservation)"
                                            title="Check In"
                                        >
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M13 4L6 11L3 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <button 
                                            v-if="reservation.status === 'checked_in'" 
                                            class="btn-icon btn-info" 
                                            @click="checkOut(reservation)"
                                            title="Check Out"
                                        >
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M3 8H13M13 8L10 5M13 8L10 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                <button 
                                    v-if="['confirmed', 'checked_in'].includes(reservation.status)" 
                                    class="btn-icon btn-warning" 
                                    @click="openExtendModal(reservation)"
                                    title="Extend Stay"
                                >
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <span class="btn-label">Extend</span>
                                </button>
                                <button 
                                    v-if="['confirmed', 'checked_in'].includes(reservation.status)" 
                                    class="btn-icon btn-secondary" 
                                    @click="openEarlyCheckoutModal(reservation)"
                                    title="Early Checkout"
                                >
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M3 8H13M3 8L6 5M3 8L6 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="btn-label">Early Out</span>
                                </button>
                                        <button 
                                            v-if="['pending', 'confirmed'].includes(reservation.status)" 
                                            class="btn-icon btn-danger" 
                                            @click="openCancelModal(reservation)"
                                            title="Cancel"
                                        >
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                        <button 
                                            v-if="reservation.status !== 'cancelled' && reservation.status !== 'checked_out'"
                                            class="btn-icon btn-info" 
                                            @click="manageReservationGuests(reservation)" 
                                            title="Manage Guests"
                                        >
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 8C9.65685 8 11 6.65685 11 5C11 3.34315 9.65685 2 8 2C6.34315 2 5 3.34315 5 5C5 6.65685 6.34315 8 8 8Z" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M2 13.5C2 11.0147 4.68629 9 8 9C11.3137 9 14 11.0147 14 13.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                        <button class="btn-icon" @click="openReservationModal(reservation)" title="View Details">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 3C5.5 3 3.5 5 3.5 7.5C3.5 10 5.5 12 8 12C10.5 12 12.5 10 12.5 7.5C12.5 5 10.5 3 8 3ZM8 10C6.5 10 5.5 9 5.5 7.5C5.5 6 6.5 5 8 5C9.5 5 10.5 6 10.5 7.5C10.5 9 9.5 10 8 10Z" fill="currentColor"/>
                                                <path d="M8 1C4 1 1 4 1 8C1 12 4 15 8 15C12 15 15 12 15 8C15 4 12 1 8 1ZM8 13C5.5 13 3.5 11 3.5 8.5C3.5 6 5.5 4 8 4C10.5 4 12.5 6 12.5 8.5C12.5 11 10.5 13 8 13Z" fill="currentColor"/>
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

        <!-- Room Status Tab -->
        <div v-if="activeTab === 'room-status'" class="tab-content">
            <div class="content-header">
                <div class="filters-section">
                    <select v-model="roomStatusFilter" @change="fetchRooms" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="available">Available</option>
                        <option value="reserved">Reserved</option>
                        <option value="checked_in">Checked In</option>
                        <option value="checked_out">Checked Out</option>
                        <option value="under_cleaning">Under Cleaning</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
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
                                <th>Current Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="room in rooms" :key="room.id">
                                <td><strong>{{ room.room_number }}</strong></td>
                                <td>{{ room.room_type?.name || 'N/A' }}</td>
                                <td>{{ room.floor || 'N/A' }}</td>
                                <td>
                                    <span :class="['badge', getRoomStatusClass(room.status)]">
                                        {{ formatRoomStatus(room.status) }}
                                    </span>
                                </td>
                                <td>
                                    <select 
                                        :value="room.status" 
                                        @change="updateRoomStatus(room, $event.target.value)"
                                        class="status-select"
                                    >
                                        <option value="available">Available</option>
                                        <option value="reserved">Reserved</option>
                                        <option value="checked_in">Checked In</option>
                                        <option value="checked_out">Checked Out</option>
                                        <option value="under_cleaning">Under Cleaning</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Reservation Modal -->
        <div v-if="showReservationModal" class="modal-overlay" @click.self="closeReservationModal">
            <div class="modal-content large-modal">
                <div class="modal-header">
                    <h3>{{ isCreatingGroupBooking ? 'Create Group Booking' : (editingReservation ? 'Edit Reservation' : 'New Reservation') }}</h3>
                    <button class="modal-close" @click="closeReservationModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="saveReservation" class="modal-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Booking Type *</label>
                            <select v-model="reservationForm.booking_type" class="form-control" required :disabled="isCreatingGroupBooking">
                                <option value="walk_in">Walk-in</option>
                                <option value="advance">Advance Booking</option>
                                <option value="online">Online Booking</option>
                            </select>
                            <small class="form-hint" v-if="isCreatingGroupBooking">Group bookings are automatically set as Advance Booking</small>
                        </div>
                        <div class="form-group">
                            <label>Room *</label>
                            <select v-model="reservationForm.room_id" class="form-control" required @change="calculateAmount">
                                <option value="">Select Room</option>
                                <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                                    {{ room.room_number }} - {{ room.room_type?.name }} (${{ room.room_type?.base_price }}/night)
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Check-in Date *</label>
                            <input 
                                type="date" 
                                v-model="reservationForm.check_in_date" 
                                class="form-control" 
                                required
                                :min="getMinCheckInDate"
                                @change="calculateAmount(); validateBookingDates()"
                            />
                            <small class="form-hint" v-if="reservationForm.booking_type === 'advance'">
                                Advance bookings must be at least 1 day in the future
                            </small>
                            <small class="form-hint" v-if="reservationForm.booking_type === 'online'">
                                Online bookings must be at least 1 day in the future
                            </small>
                        </div>
                        <div class="form-group">
                            <label>Check-out Date *</label>
                            <input 
                                type="date" 
                                v-model="reservationForm.check_out_date" 
                                class="form-control" 
                                required
                                :min="getMinCheckOutDate"
                                @change="calculateAmount"
                            />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Guest Name *</label>
                            <input 
                                type="text" 
                                v-model="reservationForm.guest_name" 
                                class="form-control" 
                                placeholder="Enter guest name"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label>Number of Guests *</label>
                            <input 
                                type="number" 
                                v-model.number="reservationForm.number_of_guests" 
                                class="form-control" 
                                min="1"
                                required
                            />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Guest Email</label>
                            <input 
                                type="email" 
                                v-model="reservationForm.guest_email" 
                                class="form-control" 
                                placeholder="guest@example.com"
                            />
                        </div>
                        <div class="form-group">
                            <label>Guest Phone</label>
                            <input 
                                type="tel" 
                                v-model="reservationForm.guest_phone" 
                                class="form-control" 
                                placeholder="+1234567890"
                            />
                        </div>
                    </div>
                    <div class="form-group" v-if="reservationForm.booking_type === 'advance' || isCreatingGroupBooking">
                        <label>
                            <input type="checkbox" v-model="isGroupBooking" :checked="isCreatingGroupBooking" /> Group Booking
                        </label>
                        <input 
                            v-if="isGroupBooking || isCreatingGroupBooking"
                            type="text" 
                            v-model="reservationForm.group_id" 
                            class="form-control" 
                            placeholder="Group ID (auto-generated if empty)"
                        />
                        <small class="form-hint" v-if="isCreatingGroupBooking">Leave empty to auto-generate a group ID</small>
                    </div>
                    <div v-if="reservationForm.booking_type === 'online'" class="online-booking-fields">
                        <div class="form-row">
                            <div class="form-group">
                                <label>External Booking ID</label>
                                <input 
                                    type="text" 
                                    v-model="reservationForm.external_booking_id" 
                                    class="form-control" 
                                    placeholder="External system booking reference"
                                />
                            </div>
                            <div class="form-group">
                                <label>Payment Status</label>
                                <select v-model="reservationForm.payment_status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="failed">Failed</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <input 
                                    type="text" 
                                    v-model="reservationForm.payment_method" 
                                    class="form-control" 
                                    placeholder="Credit Card, PayPal, etc."
                                />
                            </div>
                            <div class="form-group">
                                <label>Webhook URL</label>
                                <input 
                                    type="url" 
                                    v-model="reservationForm.webhook_url" 
                                    class="form-control" 
                                    placeholder="https://example.com/webhook"
                                />
                                <small class="form-hint">URL to receive booking status updates</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Special Requests</label>
                        <textarea 
                            v-model="reservationForm.special_requests" 
                            class="form-control" 
                            rows="3"
                            placeholder="Any special requests or notes..."
                        ></textarea>
                    </div>
                    <div class="form-group">
                        <label>Total Amount</label>
                        <input 
                            type="text" 
                            :value="'$' + calculatedAmount.toFixed(2)" 
                            class="form-control" 
                            disabled
                        />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeReservationModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Save Reservation' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Extend Stay Modal -->
        <div v-if="showExtendModal" class="modal-overlay" @click.self="closeExtendModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Extend Stay</h3>
                    <button class="modal-close" @click="closeExtendModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="extendStay" class="modal-form">
                    <div class="form-group">
                        <label>Current Check-out: {{ formatDate(extendingReservation?.check_out_date) }}</label>
                        <label>New Check-out Date *</label>
                        <input 
                            type="date" 
                            v-model="extendForm.new_check_out_date" 
                            class="form-control" 
                            required
                            :min="extendingReservation?.check_out_date"
                            @change="calculateExtendAmount"
                        />
                    </div>
                    <div class="alert alert-info" v-if="extendAdditionalAmount > 0">
                        <strong>Additional Amount:</strong> ${{ extendAdditionalAmount.toFixed(2) }}<br>
                        <small>New Total: ${{ (extendingReservation?.total_amount + extendAdditionalAmount).toFixed(2) }}</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeExtendModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Processing...' : 'Extend Stay' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Early Checkout Modal -->
        <div v-if="showEarlyCheckoutModal" class="modal-overlay" @click.self="closeEarlyCheckoutModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Early Checkout</h3>
                    <button class="modal-close" @click="closeEarlyCheckoutModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="earlyCheckout" class="modal-form">
                    <div class="form-group">
                        <label>Current Check-out: {{ formatDate(earlyCheckoutReservation?.check_out_date) }}</label>
                        <label>New Check-out Date *</label>
                        <input 
                            type="date" 
                            v-model="earlyCheckoutForm.new_check_out_date" 
                            class="form-control" 
                            required
                            :min="earlyCheckoutReservation?.check_in_date"
                            :max="earlyCheckoutReservation?.check_out_date"
                            @change="calculateEstimatedRefund"
                        />
                    </div>
                    <div class="alert alert-info" v-if="estimatedRefund > 0">
                        Estimated Refund: ${{ estimatedRefund.toFixed(2) }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeEarlyCheckoutModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Processing...' : 'Process Early Checkout' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Manage Multiple Guests Modal -->
        <div v-if="showManageGuestsModal" class="modal-overlay" @click.self="closeManageGuestsModal">
            <div class="modal-content large-modal">
                <div class="modal-header">
                    <h3>Manage Guests - Reservation #{{ managingReservation?.id }}</h3>
                    <button class="modal-close" @click="closeManageGuestsModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div v-if="loadingGuests" class="loading-state">
                        <p>Loading guests...</p>
                    </div>
                    <div v-else>
                        <!-- Primary Guest -->
                        <div class="guests-section">
                            <h4>Primary Guest</h4>
                            <div class="guest-card primary-guest">
                                <div class="guest-info">
                                    <strong>{{ managingReservation?.guest_name }}</strong>
                                    <small v-if="managingReservation?.guest_email">{{ managingReservation.guest_email }}</small>
                                    <small v-if="managingReservation?.guest_phone">{{ managingReservation.guest_phone }}</small>
                                </div>
                                <span class="badge badge-primary">Primary</span>
                            </div>
                        </div>

                        <!-- Additional Guests -->
                        <div class="guests-section">
                            <div class="section-header">
                                <h4>Additional Guests ({{ reservationGuests.additional_guests?.length || 0 }})</h4>
                                <button class="btn btn-sm btn-primary" @click="openAddGuestsModal">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    Add Guests
                                </button>
                            </div>
                            <div v-if="reservationGuests.additional_guests && reservationGuests.additional_guests.length > 0" class="guests-list">
                                <div v-for="guest in reservationGuests.additional_guests" :key="guest.id" class="guest-card">
                                    <div class="guest-info">
                                        <strong>{{ guest.name }}</strong>
                                        <small v-if="guest.email">{{ guest.email }}</small>
                                        <small v-if="guest.phone">{{ guest.phone }}</small>
                                    </div>
                                    <button 
                                        class="btn-icon btn-icon-danger" 
                                        @click="removeGuestFromReservation(guest.id)"
                                        title="Remove Guest"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M4 4L12 12M4 12L12 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div v-else class="empty-state">
                                <p>No additional guests added yet</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeManageGuestsModal">Close</button>
                </div>
            </div>
        </div>

        <!-- Add Guests to Reservation Modal -->
        <div v-if="showAddGuestsModal" class="modal-overlay" @click.self="closeAddGuestsModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add Guests to Reservation</h3>
                    <button class="modal-close" @click="closeAddGuestsModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Search and Select Guests</label>
                        <input 
                            type="text" 
                            v-model="guestSearchQuery" 
                            @input="searchGuests"
                            class="form-control"
                            placeholder="Search by name, email, or phone..."
                        />
                    </div>
                    <div v-if="loadingGuestSearch" class="loading-state">
                        <p>Searching...</p>
                    </div>
                    <div v-else-if="availableGuests.length === 0" class="empty-state">
                        <p>No guests found</p>
                    </div>
                    <div v-else class="guests-select-list">
                        <div 
                            v-for="guest in availableGuests" 
                            :key="guest.id"
                            class="guest-select-item"
                            :class="{ selected: selectedGuestIds.includes(guest.id) }"
                            @click="toggleGuestSelection(guest.id)"
                        >
                            <input 
                                type="checkbox" 
                                :checked="selectedGuestIds.includes(guest.id)"
                                @change="toggleGuestSelection(guest.id)"
                            />
                            <div class="guest-select-info">
                                <strong>{{ guest.name }}</strong>
                                <small v-if="guest.email">{{ guest.email }}</small>
                                <small v-if="guest.phone">{{ guest.phone }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeAddGuestsModal">Cancel</button>
                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        @click="addGuestsToReservation"
                        :disabled="selectedGuestIds.length === 0 || saving"
                    >
                        {{ saving ? 'Adding...' : `Add ${selectedGuestIds.length} Guest(s)` }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Guest Assignment Modal -->
        <div v-if="showGuestAssignmentModal" class="modal-overlay" @click.self="closeGuestAssignmentModal">
            <div class="modal-content large-modal">
                <div class="modal-header">
                    <h3>Assign Guest to Room</h3>
                    <button class="modal-close" @click="closeGuestAssignmentModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="saveGuestAssignment" class="modal-form">
                    <div class="form-group">
                        <label>Room *</label>
                        <select v-model="assignmentForm.room_id" class="form-control" required>
                            <option value="">Select Room</option>
                            <option v-for="room in allRooms" :key="room.id" :value="room.id">
                                {{ room.room_number }} - {{ room.room_type?.name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Check-in Date *</label>
                            <input 
                                type="date" 
                                v-model="assignmentForm.check_in_date" 
                                class="form-control" 
                                required
                                :min="minDate"
                            />
                        </div>
                        <div class="form-group">
                            <label>Check-out Date *</label>
                            <input 
                                type="date" 
                                v-model="assignmentForm.check_out_date" 
                                class="form-control" 
                                required
                                :min="assignmentForm.check_in_date || minDate"
                            />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Guest Name *</label>
                            <input 
                                type="text" 
                                v-model="assignmentForm.guest_name" 
                                class="form-control" 
                                placeholder="Enter guest name"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label>Number of Guests *</label>
                            <input 
                                type="number" 
                                v-model.number="assignmentForm.number_of_guests" 
                                class="form-control" 
                                min="1"
                                required
                            />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Guest Email</label>
                            <input 
                                type="email" 
                                v-model="assignmentForm.guest_email" 
                                class="form-control" 
                                placeholder="guest@example.com"
                            />
                        </div>
                        <div class="form-group">
                            <label>Guest Phone</label>
                            <input 
                                type="tel" 
                                v-model="assignmentForm.guest_phone" 
                                class="form-control" 
                                placeholder="+1234567890"
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Special Requests</label>
                        <textarea 
                            v-model="assignmentForm.special_requests" 
                            class="form-control" 
                            rows="3"
                            placeholder="Any special requests or notes..."
                        ></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeGuestAssignmentModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Assigning...' : 'Assign Guest' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div v-if="showCancelModal" class="modal-overlay" @click.self="closeCancelModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Cancel Reservation</h3>
                    <button class="modal-close" @click="closeCancelModal">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="cancelReservation" class="modal-form">
                    <div class="form-group">
                        <label>Reservation #{{ cancellingReservation?.id }}</label>
                        <p>Guest: {{ cancellingReservation?.guest_name }}</p>
                        <p>Amount: ${{ cancellingReservation?.total_amount }}</p>
                    </div>
                    <div class="form-group">
                        <label>Cancellation Reason</label>
                        <textarea 
                            v-model="cancelForm.cancellation_reason" 
                            class="form-control" 
                            rows="3"
                            placeholder="Enter cancellation reason (optional)"
                        ></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <strong>Cancellation Policy:</strong><br>
                        • More than 7 days: 100% refund<br>
                        • 3-7 days: 50% refund<br>
                        • Less than 3 days: No refund
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeCancelModal">Cancel</button>
                        <button type="submit" class="btn btn-danger" :disabled="saving">
                            {{ saving ? 'Processing...' : 'Cancel Reservation' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useAlert } from '../../composables/useAlert.js';

export default {
    name: 'RoomReservations',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        
        const activeTab = ref('reservations');
        const tabs = [
            { id: 'reservations', label: 'Reservations' },
            { id: 'guest-assignment', label: 'Guest Assignment' },
            { id: 'group-bookings', label: 'Group Bookings' },
            { id: 'room-status', label: 'Room Status' }
        ];

        // Reservations
        const reservations = ref([]);
        const loading = ref(false);
        const searchQuery = ref('');
        const filterStatus = ref('');
        const filterBookingType = ref('');

        // Rooms
        const rooms = ref([]);
        const loadingRooms = ref(false);
        const roomStatusFilter = ref('');
        const availableRooms = ref([]);
        const allRooms = ref([]);

        // Guest Assignment
        const roomsForAssignment = ref([]);
        const loadingAssignment = ref(false);
        const assignmentRoomFilter = ref('');
        const assignmentStatusFilter = ref('');
        const showGuestAssignmentModal = ref(false);
        const showManageGuestsModal = ref(false);
        const showAddGuestsModal = ref(false);
        const managingReservation = ref(null);
        const reservationGuests = ref({
            primary_guest: null,
            additional_guests: [],
            all_guests: []
        });
        const loadingGuests = ref(false);
        const availableGuests = ref([]);
        const guestSearchQuery = ref('');
        const loadingGuestSearch = ref(false);
        const selectedGuestIds = ref([]);
        const assignmentRoom = ref(null);
        const assignmentForm = ref({
            room_id: '',
            guest_name: '',
            guest_email: '',
            guest_phone: '',
            check_in_date: '',
            check_out_date: '',
            number_of_guests: 1,
            special_requests: ''
        });

        // Group Bookings
        const groupBookings = ref([]);
        const loadingGroups = ref(false);
        const groupSearchQuery = ref('');
        const showGroupBookingModal = ref(false);
        const isCreatingGroupBooking = ref(false);

        // Modals
        const showReservationModal = ref(false);
        const showExtendModal = ref(false);
        const showEarlyCheckoutModal = ref(false);
        const showCancelModal = ref(false);
        const editingReservation = ref(null);
        const extendingReservation = ref(null);
        const earlyCheckoutReservation = ref(null);
        const cancellingReservation = ref(null);
        const saving = ref(false);
        const isGroupBooking = ref(false);

        // Forms
        const reservationForm = ref({
            room_id: '',
            guest_name: '',
            guest_email: '',
            guest_phone: '',
            check_in_date: '',
            check_out_date: '',
            number_of_guests: 1,
            booking_type: 'advance',
            group_id: '',
            special_requests: '',
            external_booking_id: '',
            webhook_url: '',
            payment_status: 'pending',
            payment_method: ''
        });

        const extendForm = ref({
            new_check_out_date: ''
        });
        const extendAdditionalAmount = ref(0);

        const earlyCheckoutForm = ref({
            new_check_out_date: ''
        });

        const cancelForm = ref({
            cancellation_reason: ''
        });

        const calculatedAmount = ref(0);
        const estimatedRefund = ref(0);

        const minDate = computed(() => {
            return new Date().toISOString().split('T')[0];
        });

        const getMinCheckInDate = computed(() => {
            const today = new Date();
            if (reservationForm.value.booking_type === 'advance' || reservationForm.value.booking_type === 'online') {
                // Advance and online bookings must be at least 1 day in the future
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);
                return tomorrow.toISOString().split('T')[0];
            }
            // Walk-in can be today
            return today.toISOString().split('T')[0];
        });

        const getMinCheckOutDate = computed(() => {
            if (reservationForm.value.check_in_date) {
                const checkIn = new Date(reservationForm.value.check_in_date);
                checkIn.setDate(checkIn.getDate() + 1);
                return checkIn.toISOString().split('T')[0];
            }
            return getMinCheckInDate.value;
        });

        const validateBookingDates = () => {
            if (!reservationForm.value.check_in_date) return;
            
            const checkIn = new Date(reservationForm.value.check_in_date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            checkIn.setHours(0, 0, 0, 0);

            if ((reservationForm.value.booking_type === 'advance' || reservationForm.value.booking_type === 'online') && checkIn <= today) {
                showError('Advance and online bookings must be made for future dates (at least 1 day in advance)');
                reservationForm.value.check_in_date = '';
            }
        };

        let searchTimeout = null;
        const debouncedSearch = () => {
            if (searchTimeout) clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchReservations();
            }, 300);
        };

        let groupSearchTimeout = null;
        const debouncedGroupSearch = () => {
            if (groupSearchTimeout) clearTimeout(groupSearchTimeout);
            groupSearchTimeout = setTimeout(() => {
                fetchGroupBookings();
            }, 300);
        };

        const fetchReservations = async () => {
            loading.value = true;
            try {
                const params = {};
                if (filterStatus.value) params.status = filterStatus.value;
                if (filterBookingType.value) params.booking_type = filterBookingType.value;
                if (searchQuery.value) params.search = searchQuery.value;

                const response = await axios.get('/api/reservations', { params });
                if (response.data.success) {
                    reservations.value = response.data.data.data || response.data.data || [];
                }
            } catch (error) {
                console.error('Error fetching reservations:', error);
                showError('Failed to load reservations');
            } finally {
                loading.value = false;
            }
        };

        const fetchRooms = async () => {
            loadingRooms.value = true;
            try {
                const params = { all: 1 };
                if (roomStatusFilter.value) params.status = roomStatusFilter.value;

                const response = await axios.get('/api/rooms', { params });
                if (response.data.success) {
                    rooms.value = response.data.data || [];
                } else {
                    rooms.value = response.data.data?.data || response.data.data || [];
                }
            } catch (error) {
                console.error('Error fetching rooms:', error);
                showError('Failed to load rooms');
            } finally {
                loadingRooms.value = false;
            }
        };

        const fetchAvailableRooms = async () => {
            try {
                const response = await axios.get('/api/rooms?all=1');
                if (response.data.success) {
                    availableRooms.value = (response.data.data || []).filter(r => 
                        ['available', 'checked_out'].includes(r.status)
                    );
                }
            } catch (error) {
                console.error('Error fetching available rooms:', error);
            }
        };

        const calculateAmount = () => {
            if (!reservationForm.value.room_id || !reservationForm.value.check_in_date || !reservationForm.value.check_out_date) {
                calculatedAmount.value = 0;
                return;
            }

            const room = availableRooms.value.find(r => r.id === reservationForm.value.room_id);
            if (!room || !room.room_type) {
                calculatedAmount.value = 0;
                return;
            }

            const checkIn = new Date(reservationForm.value.check_in_date);
            const checkOut = new Date(reservationForm.value.check_out_date);
            const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            calculatedAmount.value = (room.room_type.base_price || 100) * nights;
        };

        const openReservationModal = (reservation) => {
            isCreatingGroupBooking.value = false;
            editingReservation.value = reservation;
            if (reservation) {
                reservationForm.value = {
                    room_id: reservation.room_id,
                    guest_name: reservation.guest_name,
                    guest_email: reservation.guest_email || '',
                    guest_phone: reservation.guest_phone || '',
                    check_in_date: reservation.check_in_date,
                    check_out_date: reservation.check_out_date,
                    number_of_guests: reservation.number_of_guests,
                    booking_type: reservation.booking_type || 'advance',
                    group_id: reservation.group_id || '',
                    special_requests: reservation.special_requests || '',
                    external_booking_id: reservation.external_booking_id || '',
                    webhook_url: reservation.webhook_url || '',
                    payment_status: reservation.payment_status || 'pending',
                    payment_method: reservation.payment_method || ''
                };
                isGroupBooking.value = !!reservation.group_id;
            } else {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                reservationForm.value = {
                    room_id: '',
                    guest_name: '',
                    guest_email: '',
                    guest_phone: '',
                    check_in_date: tomorrow.toISOString().split('T')[0],
                    check_out_date: '',
                    number_of_guests: 1,
                    booking_type: 'advance',
                    group_id: '',
                    special_requests: '',
                    external_booking_id: '',
                    webhook_url: '',
                    payment_status: 'pending',
                    payment_method: ''
                };
                isGroupBooking.value = false;
            }
            fetchAvailableRooms();
            calculateAmount();
            showReservationModal.value = true;
        };

        const closeReservationModal = () => {
            showReservationModal.value = false;
            editingReservation.value = null;
            isCreatingGroupBooking.value = false;
            isGroupBooking.value = false;
        };

        const saveReservation = async () => {
            saving.value = true;
            try {
                const data = { ...reservationForm.value };
                if (isGroupBooking.value && !data.group_id) {
                    data.is_group_booking = true;
                }

                let response;
                if (editingReservation.value) {
                    response = await axios.put(`/api/reservations/${editingReservation.value.id}`, data);
                } else {
                    response = await axios.post('/api/reservations', data);
                }

                if (response.data.success) {
                    showSuccess(response.data.message || 'Reservation saved successfully');
                    closeReservationModal();
                    fetchReservations();
                } else {
                    showError(response.data.message || 'Failed to save reservation');
                }
            } catch (error) {
                console.error('Error saving reservation:', error);
                showError(error.response?.data?.message || 'Failed to save reservation');
            } finally {
                saving.value = false;
            }
        };

        const checkIn = async (reservation) => {
            if (!confirm('Check in this guest?')) return;
            try {
                const response = await axios.post(`/api/reservations/${reservation.id}/check-in`);
                if (response.data.success) {
                    showSuccess('Guest checked in successfully');
                    fetchReservations();
                    fetchRooms();
                }
            } catch (error) {
                showError(error.response?.data?.message || 'Failed to check in guest');
            }
        };

        const checkOut = async (reservation) => {
            if (!confirm('Check out this guest?')) return;
            try {
                const response = await axios.post(`/api/reservations/${reservation.id}/check-out`);
                if (response.data.success) {
                    showSuccess('Guest checked out successfully');
                    fetchReservations();
                    fetchRooms();
                }
            } catch (error) {
                showError(error.response?.data?.message || 'Failed to check out guest');
            }
        };

        const openExtendModal = (reservation) => {
            extendingReservation.value = reservation;
            extendForm.value = {
                new_check_out_date: ''
            };
            extendAdditionalAmount.value = 0;
            showExtendModal.value = true;
        };

        const calculateExtendAmount = () => {
            if (!extendingReservation.value || !extendForm.value.new_check_out_date) {
                extendAdditionalAmount.value = 0;
                return;
            }

            const oldCheckOut = new Date(extendingReservation.value.check_out_date);
            const newCheckOut = new Date(extendForm.value.new_check_out_date);
            const nights = Math.ceil((newCheckOut - oldCheckOut) / (1000 * 60 * 60 * 24));
            
            if (nights <= 0) {
                extendAdditionalAmount.value = 0;
                return;
            }

            const room = extendingReservation.value.room;
            const basePrice = room?.room_type?.base_price || 100;
            extendAdditionalAmount.value = basePrice * nights;
        };

        const closeExtendModal = () => {
            showExtendModal.value = false;
            extendingReservation.value = null;
        };

        const extendStay = async () => {
            saving.value = true;
            try {
                const response = await axios.post(
                    `/api/reservations/${extendingReservation.value.id}/extend`,
                    extendForm.value
                );
                if (response.data.success) {
                    showSuccess(`Stay extended. Additional amount: $${response.data.additional_amount}`);
                    closeExtendModal();
                    fetchReservations();
                } else {
                    showError(response.data.message || 'Failed to extend stay');
                }
            } catch (error) {
                showError(error.response?.data?.message || 'Failed to extend stay');
            } finally {
                saving.value = false;
            }
        };

        const openEarlyCheckoutModal = (reservation) => {
            earlyCheckoutReservation.value = reservation;
            earlyCheckoutForm.value = {
                new_check_out_date: reservation.check_out_date
            };
            estimatedRefund.value = 0;
            calculateEstimatedRefund();
            showEarlyCheckoutModal.value = true;
        };

        const closeEarlyCheckoutModal = () => {
            showEarlyCheckoutModal.value = false;
            earlyCheckoutReservation.value = null;
        };

        const calculateEstimatedRefund = () => {
            if (!earlyCheckoutReservation.value || !earlyCheckoutForm.value.new_check_out_date) {
                estimatedRefund.value = 0;
                return;
            }

            const oldCheckOut = new Date(earlyCheckoutReservation.value.check_out_date);
            const newCheckOut = new Date(earlyCheckoutForm.value.new_check_out_date);
            const nights = Math.ceil((oldCheckOut - newCheckOut) / (1000 * 60 * 60 * 24));
            
            if (nights <= 0) {
                estimatedRefund.value = 0;
                return;
            }

            const room = earlyCheckoutReservation.value.room;
            const basePrice = room?.room_type?.base_price || 100;
            estimatedRefund.value = basePrice * nights;
        };

        const earlyCheckout = async () => {
            saving.value = true;
            try {
                const response = await axios.post(
                    `/api/reservations/${earlyCheckoutReservation.value.id}/early-checkout`,
                    earlyCheckoutForm.value
                );
                if (response.data.success) {
                    showSuccess(`Early checkout processed. Refund: $${response.data.refund_amount}`);
                    closeEarlyCheckoutModal();
                    fetchReservations();
                    fetchRooms();
                } else {
                    showError(response.data.message || 'Failed to process early checkout');
                }
            } catch (error) {
                showError(error.response?.data?.message || 'Failed to process early checkout');
            } finally {
                saving.value = false;
            }
        };

        const openCancelModal = (reservation) => {
            cancellingReservation.value = reservation;
            cancelForm.value = {
                cancellation_reason: ''
            };
            showCancelModal.value = true;
        };

        const closeCancelModal = () => {
            showCancelModal.value = false;
            cancellingReservation.value = null;
        };

        const cancelReservation = async () => {
            saving.value = true;
            try {
                const response = await axios.post(
                    `/api/reservations/${cancellingReservation.value.id}/cancel`,
                    cancelForm.value
                );
                if (response.data.success) {
                    showSuccess(`Reservation cancelled. Refund: $${response.data.refund_amount}`);
                    closeCancelModal();
                    fetchReservations();
                    fetchRooms();
                } else {
                    showError(response.data.message || 'Failed to cancel reservation');
                }
            } catch (error) {
                showError(error.response?.data?.message || 'Failed to cancel reservation');
            } finally {
                saving.value = false;
            }
        };

        const updateRoomStatus = async (room, newStatus) => {
            try {
                const response = await axios.post(`/api/rooms/${room.id}/status`, { status: newStatus });
                if (response.data.success) {
                    showSuccess('Room status updated successfully');
                    fetchRooms();
                }
            } catch (error) {
                showError(error.response?.data?.message || 'Failed to update room status');
            }
        };

        const formatDate = (date) => {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        };

        const formatStatus = (status) => {
            const statusMap = {
                'pending': 'Pending',
                'confirmed': 'Confirmed',
                'checked_in': 'Checked In',
                'checked_out': 'Checked Out',
                'cancelled': 'Cancelled'
            };
            return statusMap[status] || status;
        };

        const formatBookingType = (type) => {
            const typeMap = {
                'walk_in': 'Walk-in',
                'advance': 'Advance',
                'online': 'Online'
            };
            return typeMap[type] || type;
        };

        const formatRoomStatus = (status) => {
            const statusMap = {
                'available': 'Available',
                'reserved': 'Reserved',
                'checked_in': 'Checked In',
                'checked_out': 'Checked Out',
                'under_cleaning': 'Under Cleaning',
                'maintenance': 'Maintenance'
            };
            return statusMap[status] || status;
        };

        const getStatusClass = (status) => {
            const classMap = {
                'pending': 'badge-warning',
                'confirmed': 'badge-info',
                'checked_in': 'badge-success',
                'checked_out': 'badge-secondary',
                'cancelled': 'badge-danger'
            };
            return classMap[status] || 'badge-secondary';
        };

        const getBookingTypeClass = (type) => {
            const classMap = {
                'walk_in': 'badge-primary',
                'advance': 'badge-info',
                'online': 'badge-success'
            };
            return classMap[type] || 'badge-secondary';
        };

        const getRoomStatusClass = (status) => {
            const classMap = {
                'available': 'badge-success',
                'reserved': 'badge-warning',
                'checked_in': 'badge-info',
                'checked_out': 'badge-secondary',
                'under_cleaning': 'badge-warning',
                'maintenance': 'badge-danger'
            };
            return classMap[status] || 'badge-secondary';
        };

        const fetchRoomsForAssignment = async () => {
            loadingAssignment.value = true;
            try {
                const params = {};
                if (assignmentRoomFilter.value) params.room_id = assignmentRoomFilter.value;
                if (assignmentStatusFilter.value) params.status = assignmentStatusFilter.value;

                const response = await axios.get('/api/rooms/for-assignment', { params });
                if (response.data.success) {
                    roomsForAssignment.value = response.data.data || [];
                } else {
                    showError(response.data.message || 'Failed to load rooms');
                    roomsForAssignment.value = [];
                }
            } catch (error) {
                console.error('Error fetching rooms for assignment:', error);
                const errorMessage = error.response?.data?.message || error.message || 'Failed to load rooms';
                showError(errorMessage);
                roomsForAssignment.value = [];
            } finally {
                loadingAssignment.value = false;
            }
        };

        const fetchGroupBookings = async () => {
            loadingGroups.value = true;
            try {
                const params = {};
                if (groupSearchQuery.value) params.group_id = groupSearchQuery.value;

                const response = await axios.get('/api/reservations/groups/all', { params });
                if (response.data.success) {
                    groupBookings.value = response.data.data || [];
                }
            } catch (error) {
                console.error('Error fetching group bookings:', error);
                showError('Failed to load group bookings');
            } finally {
                loadingGroups.value = false;
            }
        };

        const fetchAllRooms = async () => {
            try {
                const response = await axios.get('/api/rooms?all=1');
                if (response.data.success) {
                    allRooms.value = response.data.data || [];
                } else {
                    allRooms.value = response.data.data?.data || response.data.data || [];
                }
            } catch (error) {
                console.error('Error fetching all rooms:', error);
            }
        };

        const openGuestAssignmentModal = (room) => {
            assignmentRoom.value = room;
            if (room) {
                assignmentForm.value = {
                    room_id: room.id,
                    guest_name: '',
                    guest_email: '',
                    guest_phone: '',
                    check_in_date: new Date().toISOString().split('T')[0],
                    check_out_date: '',
                    number_of_guests: 1,
                    special_requests: ''
                };
            } else {
                assignmentForm.value = {
                    room_id: '',
                    guest_name: '',
                    guest_email: '',
                    guest_phone: '',
                    check_in_date: new Date().toISOString().split('T')[0],
                    check_out_date: '',
                    number_of_guests: 1,
                    special_requests: ''
                };
            }
            showGuestAssignmentModal.value = true;
        };

        const closeGuestAssignmentModal = () => {
            showGuestAssignmentModal.value = false;
            assignmentRoom.value = null;
        };

        const manageReservationGuests = async (reservation) => {
            managingReservation.value = reservation;
            loadingGuests.value = true;
            showManageGuestsModal.value = true;
            try {
                const response = await axios.get(`/api/reservations/${reservation.id}/guests`);
                if (response.data.success) {
                    reservationGuests.value = response.data.data;
                }
            } catch (error) {
                console.error('Error loading reservation guests:', error);
                showError(error.response?.data?.message || 'Failed to load guests');
            } finally {
                loadingGuests.value = false;
            }
        };

        const closeManageGuestsModal = () => {
            showManageGuestsModal.value = false;
            managingReservation.value = null;
            reservationGuests.value = {
                primary_guest: null,
                additional_guests: [],
                all_guests: []
            };
        };

        const openAddGuestsModal = () => {
            guestSearchQuery.value = '';
            availableGuests.value = [];
            selectedGuestIds.value = [];
            showAddGuestsModal.value = true;
            searchGuests();
        };

        const closeAddGuestsModal = () => {
            showAddGuestsModal.value = false;
            guestSearchQuery.value = '';
            availableGuests.value = [];
            selectedGuestIds.value = [];
        };

        let guestSearchTimeout = null;
        const searchGuests = () => {
            clearTimeout(guestSearchTimeout);
            guestSearchTimeout = setTimeout(async () => {
                if (!guestSearchQuery.value || guestSearchQuery.value.length < 2) {
                    availableGuests.value = [];
                    return;
                }
                loadingGuestSearch.value = true;
                try {
                    const response = await axios.get('/api/guests', {
                        params: { search: guestSearchQuery.value, per_page: 50 }
                    });
                    if (response.data.success) {
                        let guests = [];
                        // Handle paginated response
                        if (response.data.data) {
                            if (Array.isArray(response.data.data)) {
                                guests = response.data.data;
                            } else if (response.data.data.data && Array.isArray(response.data.data.data)) {
                                // Paginated response
                                guests = response.data.data.data;
                            }
                        }
                        // Filter out guests already in the reservation
                        const existingGuestIds = [
                            managingReservation.value?.guest_id,
                            ...(reservationGuests.value.additional_guests || []).map(g => g.id)
                        ].filter(Boolean);
                        availableGuests.value = Array.isArray(guests) ? guests.filter(g => g && !existingGuestIds.includes(g.id)) : [];
                    }
                } catch (error) {
                    console.error('Error searching guests:', error);
                    showError('Failed to search guests');
                } finally {
                    loadingGuestSearch.value = false;
                }
            }, 300);
        };

        const toggleGuestSelection = (guestId) => {
            const index = selectedGuestIds.value.indexOf(guestId);
            if (index > -1) {
                selectedGuestIds.value.splice(index, 1);
            } else {
                selectedGuestIds.value.push(guestId);
            }
        };

        const addGuestsToReservation = async () => {
            if (selectedGuestIds.value.length === 0) return;
            saving.value = true;
            try {
                const response = await axios.post(`/api/reservations/${managingReservation.value.id}/guests/add`, {
                    guest_ids: selectedGuestIds.value
                });
                if (response.data.success) {
                    showSuccess(response.data.message);
                    // Reload guests
                    const guestsResponse = await axios.get(`/api/reservations/${managingReservation.value.id}/guests`);
                    if (guestsResponse.data.success) {
                        reservationGuests.value = guestsResponse.data.data;
                    }
                    // Reload reservations list
                    fetchReservations();
                    closeAddGuestsModal();
                }
            } catch (error) {
                console.error('Error adding guests:', error);
                showError(error.response?.data?.message || 'Failed to add guests');
            } finally {
                saving.value = false;
            }
        };

        const removeGuestFromReservation = async (guestId) => {
            if (!confirm('Are you sure you want to remove this guest from the reservation?')) {
                return;
            }
            saving.value = true;
            try {
                const response = await axios.post(`/api/reservations/${managingReservation.value.id}/guests/remove`, {
                    guest_ids: [guestId]
                });
                if (response.data.success) {
                    showSuccess(response.data.message);
                    // Reload guests
                    const guestsResponse = await axios.get(`/api/reservations/${managingReservation.value.id}/guests`);
                    if (guestsResponse.data.success) {
                        reservationGuests.value = guestsResponse.data.data;
                    }
                    // Reload reservations list
                    fetchReservations();
                }
            } catch (error) {
                console.error('Error removing guest:', error);
                showError(error.response?.data?.message || 'Failed to remove guest');
            } finally {
                saving.value = false;
            }
        };

        const saveGuestAssignment = async () => {
            saving.value = true;
            try {
                const response = await axios.post('/api/reservations/assign-guest', assignmentForm.value);
                if (response.data.success) {
                    showSuccess('Guest assigned to room successfully');
                    closeGuestAssignmentModal();
                    fetchRoomsForAssignment();
                    fetchReservations();
                } else {
                    showError(response.data.message || 'Failed to assign guest');
                }
            } catch (error) {
                console.error('Error assigning guest:', error);
                showError(error.response?.data?.message || 'Failed to assign guest');
            } finally {
                saving.value = false;
            }
        };

        const openGroupBookingModal = () => {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            isCreatingGroupBooking.value = true;
            reservationForm.value = {
                room_id: '',
                guest_name: '',
                guest_email: '',
                guest_phone: '',
                check_in_date: tomorrow.toISOString().split('T')[0],
                check_out_date: '',
                number_of_guests: 1,
                booking_type: 'advance',
                group_id: '',
                special_requests: '',
                external_booking_id: '',
                webhook_url: '',
                payment_status: 'pending',
                payment_method: ''
            };
            isGroupBooking.value = true;
            editingReservation.value = null;
            fetchAvailableRooms();
            calculateAmount();
            showReservationModal.value = true;
        };

        const getCurrentReservation = (room) => {
            if (!room.reservations || room.reservations.length === 0) return null;
            return room.reservations.find(r => 
                ['pending', 'confirmed', 'checked_in'].includes(r.status)
            ) || null;
        };

        const getRoomCardClass = (room) => {
            const reservation = getCurrentReservation(room);
            if (reservation) {
                if (reservation.status === 'checked_in') return 'room-occupied';
                if (reservation.status === 'confirmed') return 'room-reserved';
            }
            return 'room-available';
        };

        const viewReservationDetails = (reservation) => {
            openReservationModal(reservation);
        };

        // Watch for tab changes to load appropriate data
        watch(activeTab, (newTab) => {
            if (newTab === 'guest-assignment') {
                fetchRoomsForAssignment();
                if (allRooms.value.length === 0) {
                    fetchAllRooms();
                }
            } else if (newTab === 'group-bookings') {
                fetchGroupBookings();
            } else if (newTab === 'reservations') {
                fetchReservations();
            } else if (newTab === 'room-status') {
                fetchRooms();
            }
        });

        onMounted(() => {
            fetchReservations();
            fetchRooms();
            fetchAllRooms();
        });

        return {
            activeTab,
            tabs,
            reservations,
            loading,
            searchQuery,
            filterStatus,
            filterBookingType,
            rooms,
            loadingRooms,
            roomStatusFilter,
            showReservationModal,
            showExtendModal,
            showEarlyCheckoutModal,
            showCancelModal,
            editingReservation,
            extendingReservation,
            earlyCheckoutReservation,
            cancellingReservation,
            saving,
            isGroupBooking,
            reservationForm,
            extendForm,
            earlyCheckoutForm,
            cancelForm,
            calculatedAmount,
            estimatedRefund,
            availableRooms,
            minDate,
            debouncedSearch,
            fetchReservations,
            fetchRooms,
            openReservationModal,
            closeReservationModal,
            saveReservation,
            checkIn,
            checkOut,
            openExtendModal,
            closeExtendModal,
            extendStay,
            openEarlyCheckoutModal,
            closeEarlyCheckoutModal,
            earlyCheckout,
            openCancelModal,
            closeCancelModal,
            cancelReservation,
            updateRoomStatus,
            formatDate,
            formatStatus,
            formatBookingType,
            formatRoomStatus,
            getStatusClass,
            getBookingTypeClass,
            getRoomStatusClass,
            calculateAmount,
            getMinCheckInDate,
            getMinCheckOutDate,
            validateBookingDates,
            fetchRoomsForAssignment,
            fetchGroupBookings,
            fetchAllRooms,
            openGuestAssignmentModal,
            closeGuestAssignmentModal,
            saveGuestAssignment,
            openGroupBookingModal,
            getCurrentReservation,
            getRoomCardClass,
            viewReservationDetails,
            debouncedGroupSearch,
            roomsForAssignment,
            loadingAssignment,
            assignmentRoomFilter,
            assignmentStatusFilter,
            showGuestAssignmentModal,
            assignmentRoom,
            assignmentForm,
            groupBookings,
            loadingGroups,
            groupSearchQuery,
            showGroupBookingModal,
            allRooms,
            extendAdditionalAmount,
            calculateExtendAmount,
            isCreatingGroupBooking,
            showManageGuestsModal,
            showAddGuestsModal,
            managingReservation,
            reservationGuests,
            loadingGuests,
            availableGuests,
            guestSearchQuery,
            loadingGuestSearch,
            selectedGuestIds,
            manageReservationGuests,
            closeManageGuestsModal,
            openAddGuestsModal,
            closeAddGuestsModal,
            searchGuests,
            toggleGuestSelection,
            addGuestsToReservation,
            removeGuestFromReservation
        };
    }
}
</script>

<style scoped>
.room-reservations-page {
    max-width: 1600px;
    margin: 0 auto;
    padding: 0 20px;
}

.page-header {
    margin-bottom: 32px;
}

.page-title {
    font-size: 32px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
}

.page-subtitle {
    font-size: 16px;
    color: #718096;
}

.tabs-container {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    border-bottom: 2px solid #e2e8f0;
}

.tab-btn {
    padding: 12px 24px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    color: #718096;
    transition: all 0.2s;
    margin-bottom: -2px;
}

.tab-btn:hover {
    color: #667eea;
}

.tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.tab-content {
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    gap: 16px;
    flex-wrap: wrap;
}

.filters-section {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    flex: 1;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 250px;
}

.search-box svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #718096;
}

.search-box input {
    width: 100%;
    padding: 10px 12px 10px 40px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.filter-select {
    padding: 10px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    min-width: 150px;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
}

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.loading-state, .empty-state {
    padding: 60px 20px;
    text-align: center;
    color: #718096;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f7fafc;
}

.data-table th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #4a5568;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    padding: 16px;
    border-top: 1px solid #e2e8f0;
    font-size: 14px;
    color: #2d3748;
}

.guest-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.guest-info small {
    color: #718096;
    font-size: 12px;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-success { background: #c6f6d5; color: #22543d; }
.badge-warning { background: #feebc8; color: #7c2d12; }
.badge-danger { background: #fed7d7; color: #742a2a; }
.badge-info { background: #bee3f8; color: #2c5282; }
.badge-secondary { background: #e2e8f0; color: #4a5568; }
.badge-primary { background: #c3dafe; color: #2c5282; }

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-icon {
    padding: 6px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    background: #f7fafc;
    color: #4a5568;
}

.btn-icon:hover {
    background: #edf2f7;
    transform: translateY(-1px);
}

.btn-icon.btn-success { background: #c6f6d5; color: #22543d; }
.btn-icon.btn-info { background: #bee3f8; color: #2c5282; }
.btn-icon.btn-warning { background: #feebc8; color: #7c2d12; }
.btn-icon.btn-danger { background: #fed7d7; color: #742a2a; }
.btn-icon.btn-secondary { background: #e2e8f0; color: #4a5568; }

.status-select {
    padding: 6px 10px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
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
    padding: 20px;
    backdrop-filter: blur(4px);
}

.modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
}

.large-modal {
    max-width: 800px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 2px solid #e2e8f0;
}

.modal-header h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: #1a202c;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    color: #718096;
    border-radius: 6px;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f7fafc;
    color: #2d3748;
}

.modal-form {
    padding: 24px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #2d3748;
}

.form-control {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #2d3748;
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
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 20px;
    border-top: 2px solid #e2e8f0;
    margin-top: 24px;
}

.btn-secondary {
    background: #f7fafc;
    color: #4a5568;
    border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
    background: #edf2f7;
}

.btn-danger {
    background: #f56565;
    color: white;
}

.btn-danger:hover {
    background: #e53e3e;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
}

.alert-info {
    background: #bee3f8;
    color: #2c5282;
}

.alert-warning {
    background: #feebc8;
    color: #7c2d12;
}

.online-booking-fields {
    background: #f7fafc;
    padding: 16px;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    margin-top: 16px;
}

.online-booking-fields .form-group {
    margin-bottom: 16px;
}

/* Guest Assignment Styles */
.assignment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    padding: 20px;
}

.room-card {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s;
}

.room-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.room-card.room-available {
    border-color: #48bb78;
}

.room-card.room-reserved {
    border-color: #9f7aea;
}

.room-card.room-occupied {
    border-color: #4299e1;
}

.room-card-header {
    padding: 16px;
    background: #f7fafc;
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.room-card-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #2d3748;
}

.room-card-header p {
    margin: 4px 0 0 0;
    font-size: 14px;
    color: #718096;
}

.room-card-body {
    padding: 16px;
}

.current-reservation {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.reservation-info strong {
    display: block;
    margin-bottom: 4px;
    color: #2d3748;
    font-size: 14px;
}

.reservation-info p {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1a202c;
}

.reservation-info small {
    display: block;
    color: #718096;
    font-size: 13px;
    margin-top: 4px;
}

.reservation-dates {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 13px;
    color: #4a5568;
    padding: 8px;
    background: #f7fafc;
    border-radius: 6px;
}

.reservation-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.no-reservation {
    text-align: center;
    padding: 20px;
    color: #718096;
}

.no-reservation p {
    margin-bottom: 12px;
}

/* Group Bookings Styles */
.group-bookings-list {
    padding: 20px;
}

.group-booking-card {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
}

.group-header {
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.group-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
}

.group-header p {
    margin: 4px 0 0 0;
    opacity: 0.9;
    font-size: 14px;
}

.group-stats {
    display: flex;
    flex-direction: column;
    gap: 8px;
    text-align: right;
    font-weight: 600;
}

.group-reservations {
    padding: 20px;
}

.btn-label {
    margin-left: 4px;
    font-size: 11px;
}

.additional-guests-badge {
    margin-top: 4px;
}

.guests-section {
    margin-bottom: 24px;
}

.guests-section h4 {
    font-size: 16px;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 12px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.guest-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 8px;
}

.guest-card.primary-guest {
    background: #e6f3ff;
    border-color: #4a90e2;
}

.guest-info {
    flex: 1;
}

.guest-info strong {
    display: block;
    color: #1a202c;
    margin-bottom: 4px;
}

.guest-info small {
    display: block;
    color: #718096;
    font-size: 12px;
}

.guests-list {
    max-height: 300px;
    overflow-y: auto;
}

.guests-select-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-top: 12px;
}

.guest-select-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
    cursor: pointer;
    transition: background 0.2s;
}

.guest-select-item:last-child {
    border-bottom: none;
}

.guest-select-item:hover {
    background: #f7fafc;
}

.guest-select-item.selected {
    background: #e6f3ff;
    border-color: #4a90e2;
}

.guest-select-item input[type="checkbox"] {
    margin-right: 12px;
    cursor: pointer;
}

.guest-select-info {
    flex: 1;
}

.guest-select-info strong {
    display: block;
    color: #1a202c;
    margin-bottom: 4px;
}

.guest-select-info small {
    display: block;
    color: #718096;
    font-size: 12px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .content-header {
        flex-direction: column;
    }
    
    .filters-section {
        width: 100%;
    }
}
</style>

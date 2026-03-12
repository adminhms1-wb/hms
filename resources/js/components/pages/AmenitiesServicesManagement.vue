<template>
    <div class="amenities-services-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Amenities & Services Management</h1>
                <p class="page-subtitle">
                    Manage hotel amenities and services, including pricing and availability.
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

        <!-- Amenities Tab -->
        <div v-if="activeTab === 'amenities'" class="tab-content">
            <div class="content-grid">
                <section class="content-card">
                    <div class="card-header">
                        <h2>Room Amenities</h2>
                        <p>Manage amenities that can be assigned to rooms (WiFi, TV, AC, etc.)</p>
                    </div>
                    <div class="card-body" v-show="true">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Amenity Name *</label>
                                <input v-model="amenityForm.name" type="text" placeholder="e.g. WiFi, TV, Air Conditioning" />
                            </div>
                            <div class="form-group">
                                <label>Is Paid Service?</label>
                                <select v-model="amenityForm.is_paid">
                                    <option :value="false">Free</option>
                                    <option :value="true">Paid</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Price (if paid)</label>
                                <input 
                                    v-model.number="amenityForm.price" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    :disabled="!amenityForm.is_paid"
                                />
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-secondary" @click="resetAmenityForm">Clear</button>
                            <button class="btn btn-primary" @click="saveAmenity" :disabled="!amenityForm.name || saving">
                                {{ saving ? 'Saving...' : (amenityForm.id ? 'Update Amenity' : 'Add Amenity') }}
                            </button>
                        </div>

                        <div v-if="loadingAmenities" class="loading-state">
                            <p>Loading amenities...</p>
                        </div>
                        <div v-else-if="amenities.length === 0" class="empty-state">
                            <p>No amenities found. Add your first amenity above.</p>
                        </div>
                        <div v-else class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="amenity in amenities" :key="amenity.id">
                                        <td><strong>{{ amenity.name }}</strong></td>
                                        <td>
                                            <span :class="['badge', amenity.is_paid ? 'badge-warning' : 'badge-success']">
                                                {{ amenity.is_paid ? 'Paid' : 'Free' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span v-if="amenity.is_paid">${{ (Number(amenity.price) || 0).toFixed(2) }}</span>
                                            <span v-else class="text-muted">Free</span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline" @click="editAmenity(amenity)">Edit</button>
                                                <button class="btn btn-sm btn-danger" @click="deleteAmenity(amenity.id)" :disabled="saving">Remove</button>
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

        <!-- Services Tab -->
        <div v-if="activeTab === 'services'" class="tab-content">
            <div class="content-grid">
                <section class="content-card">
                    <div class="card-header">
                        <h2>Hotel Services</h2>
                        <p>Manage additional services offered by the hotel (Spa, Gym, Laundry, etc.)</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Service Name *</label>
                                <input v-model="serviceForm.name" type="text" placeholder="e.g. Spa Treatment, Laundry Service" />
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select v-model="serviceForm.category">
                                    <option value="wellness">Wellness & Spa</option>
                                    <option value="fitness">Fitness & Gym</option>
                                    <option value="dining">Dining</option>
                                    <option value="transport">Transportation</option>
                                    <option value="business">Business Services</option>
                                    <option value="entertainment">Entertainment</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Is Free Service?</label>
                                <select v-model="serviceForm.is_free">
                                    <option :value="false">Paid</option>
                                    <option :value="true">Free</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Price (if paid) *</label>
                                <input 
                                    v-model.number="serviceForm.price" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    :disabled="serviceForm.is_free"
                                />
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea v-model="serviceForm.description" rows="2" placeholder="Service description"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Duration (minutes)</label>
                                <input v-model.number="serviceForm.duration" type="number" min="0" placeholder="Optional" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Status</label>
                                <select v-model="serviceForm.is_active">
                                    <option :value="true">Active</option>
                                    <option :value="false">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-secondary" @click="resetServiceForm">Clear</button>
                            <button class="btn btn-primary" @click="saveService" :disabled="!serviceForm.name || (!serviceForm.is_free && !serviceForm.price) || saving">
                                {{ saving ? 'Saving...' : (serviceForm.id ? 'Update Service' : 'Add Service') }}
                            </button>
                        </div>

                        <div class="filter-chips">
                            <button
                                v-for="category in serviceCategories"
                                :key="category.value"
                                :class="['chip', { active: activeServiceCategory === category.value }]"
                                @click="activeServiceCategory = category.value"
                            >
                                {{ category.label }}
                            </button>
                        </div>

                        <div v-if="loadingServices" class="loading-state">
                            <p>Loading services...</p>
                        </div>
                        <div v-else-if="filteredServices.length === 0" class="empty-state">
                            <p>No services found. Add your first service above.</p>
                        </div>
                        <div v-else class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Category</th>
                                        <th>Type</th>
                                        <th>Price</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="service in filteredServices" :key="service.id">
                                        <td>
                                            <strong>{{ service.name }}</strong>
                                            <small v-if="service.description">{{ service.description }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ getCategoryLabel(service.category || 'other') }}</span>
                                        </td>
                                        <td>
                                            <span :class="['badge', (service.is_free === true || service.is_free === 1) ? 'badge-success' : 'badge-warning']">
                                                {{ (service.is_free === true || service.is_free === 1) ? 'Free' : 'Paid' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span v-if="!(service.is_free === true || service.is_free === 1)">${{ (Number(service.price) || 0).toFixed(2) }}</span>
                                            <span v-else class="text-muted">Free</span>
                                        </td>
                                        <td>{{ service.duration ? service.duration + ' min' : '-' }}</td>
                                        <td>
                                            <span :class="['badge', (service.is_active !== false && service.is_active !== null) ? 'badge-success' : 'badge-secondary']">
                                                {{ (service.is_active !== false && service.is_active !== null) ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline" @click="editService(service)">Edit</button>
                                                <button class="btn btn-sm btn-danger" @click="deleteService(service.id)" :disabled="saving">Remove</button>
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

        <!-- Time Slots Tab -->
        <div v-if="activeTab === 'time-slots'" class="tab-content">
            <div class="content-grid">
                <section class="content-card">
                    <div class="card-header">
                        <h2>Service Time Slots</h2>
                        <p>Manage booking time slots for services</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Select Service *</label>
                                <select v-model="timeSlotForm.service_id" @change="loadTimeSlotsForService">
                                    <option value="">Select a service</option>
                                    <option v-for="service in services" :key="service?.id || service" :value="service?.id">
                                        {{ service?.name || 'Unknown' }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Start Time *</label>
                                <input v-model="timeSlotForm.start_time" type="time" />
                            </div>
                            <div class="form-group">
                                <label>End Time *</label>
                                <input v-model="timeSlotForm.end_time" type="time" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Max Bookings</label>
                                <input v-model.number="timeSlotForm.max_bookings" type="number" min="1" />
                            </div>
                            <div class="form-group">
                                <label>Available Days</label>
                                <div class="checkbox-group">
                                    <label v-for="(day, index) in daysOfWeek" :key="index" class="checkbox-label">
                                        <input type="checkbox" :value="index" v-model="timeSlotForm.available_days" />
                                        {{ day }}
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select v-model="timeSlotForm.is_available">
                                    <option :value="true">Available</option>
                                    <option :value="false">Unavailable</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-secondary" @click="resetTimeSlotForm">Clear</button>
                            <button class="btn btn-primary" @click="saveTimeSlot" :disabled="!timeSlotForm.service_id || !timeSlotForm.start_time || !timeSlotForm.end_time || saving">
                                {{ saving ? 'Saving...' : (timeSlotForm.id ? 'Update Time Slot' : 'Add Time Slot') }}
                            </button>
                        </div>

                        <div v-if="loading" class="loading-state">
                            <p>Loading time slots...</p>
                        </div>
                        <div v-else-if="timeSlots.length === 0" class="empty-state">
                            <p>No time slots found. Select a service and add time slots above.</p>
                        </div>
                        <div v-else class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Time</th>
                                        <th>Duration</th>
                                        <th>Max Bookings</th>
                                        <th>Available Days</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="slot in timeSlots" :key="slot.id">
                                        <td><strong>{{ getServiceName(slot.service_id) }}</strong></td>
                                        <td>{{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}</td>
                                        <td>{{ slot.duration_minutes }} min</td>
                                        <td>{{ slot.max_bookings }}</td>
                                        <td>{{ formatAvailableDays(slot.available_days) }}</td>
                                        <td>
                                            <span :class="['badge', slot.is_available ? 'badge-success' : 'badge-secondary']">
                                                {{ slot.is_available ? 'Available' : 'Unavailable' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline" @click="editTimeSlot(slot)">Edit</button>
                                                <button class="btn btn-sm btn-danger" @click="deleteTimeSlot(slot.id)" :disabled="saving">Remove</button>
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

        <!-- Service Bookings Tab -->
        <div v-if="activeTab === 'bookings'" class="tab-content">
            <div class="content-grid">
                <section class="content-card">
                    <div class="card-header">
                        <h2>Service Bookings</h2>
                        <p>Manage service bookings with staff assignment and charge posting</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Service *</label>
                                <select v-model="bookingForm.service_id" @change="loadTimeSlotsForBooking">
                                    <option value="">Select a service</option>
                                    <option v-for="service in services" :key="service?.id || service" :value="service?.id">
                                        {{ service?.name || 'Unknown' }} - ${{ (Number(service?.price) || 0).toFixed(2) }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Guest *</label>
                                <select v-model="bookingForm.guest_id" @change="loadGuestReservations">
                                    <option value="">Select a guest</option>
                                    <option v-for="guest in guests" :key="guest?.id || guest" :value="guest?.id">
                                        {{ guest?.name || 'Unknown' }} ({{ guest?.email || '' }})
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date *</label>
                                <input v-model="bookingForm.date" type="date" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Time Slot</label>
                                <select v-model="bookingForm.time_slot_id">
                                    <option value="">Select a time slot</option>
                                    <option v-for="slot in availableTimeSlots" :key="slot?.id || slot" :value="slot?.id">
                                        {{ slot ? (formatTime(slot.start_time) + ' - ' + formatTime(slot.end_time)) : '' }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Start Time</label>
                                <input v-model="bookingForm.start_time" type="time" />
                            </div>
                            <div class="form-group">
                                <label>End Time</label>
                                <input v-model="bookingForm.end_time" type="time" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Charge Type *</label>
                                <select v-model="bookingForm.charge_type" @change="onChargeTypeChange">
                                    <option value="direct">Direct Payment</option>
                                    <option value="room">Charge to Room</option>
                                </select>
                            </div>
                            <div class="form-group" v-if="bookingForm.charge_type === 'room'">
                                <label>Reservation *</label>
                                <select v-model="bookingForm.reservation_id">
                                    <option value="">Select a reservation</option>
                                    <option v-for="res in guestReservations" :key="res?.id || res" :value="res?.id">
                                        Room {{ res?.room?.room_number || 'N/A' }} - {{ res?.check_in_date || '' }} to {{ res?.check_out_date || '' }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Assigned Staff</label>
                                <select v-model="bookingForm.assigned_staff_id">
                                    <option value="">Select staff</option>
                                    <option v-for="staff in staffList" :key="staff?.id || staff" :value="staff?.id">
                                        {{ staff?.name || 'Unknown' }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Payment Status</label>
                                <select v-model="bookingForm.payment_status">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Payment Method</label>
                                <input v-model="bookingForm.payment_method" type="text" placeholder="Cash, Card, etc." />
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea v-model="bookingForm.notes" rows="2" placeholder="Additional notes"></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-secondary" @click="resetBookingForm">Clear</button>
                            <button class="btn btn-primary" @click="saveBooking" :disabled="!bookingForm.service_id || !bookingForm.guest_id || !bookingForm.date || saving">
                                {{ saving ? 'Saving...' : (bookingForm.id ? 'Update Booking' : 'Create Booking') }}
                            </button>
                        </div>

                        <div class="filter-chips" style="margin-top: 20px;">
                            <button
                                v-for="status in bookingStatuses"
                                :key="status.value"
                                :class="['chip', { active: activeBookingStatus === status.value }]"
                                @click="activeBookingStatus = status.value"
                            >
                                {{ status.label }}
                            </button>
                        </div>

                        <div v-if="loading" class="loading-state">
                            <p>Loading bookings...</p>
                        </div>
                        <div v-else-if="filteredBookings.length === 0" class="empty-state">
                            <p>No bookings found.</p>
                        </div>
                        <div v-else class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Guest</th>
                                        <th>Date & Time</th>
                                        <th>Charge Type</th>
                                        <th>Amount</th>
                                        <th>Staff</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="booking in filteredBookings" :key="booking?.id || booking">
                                        <td><strong>{{ booking.service?.name }}</strong></td>
                                        <td>{{ booking.guest?.name }}</td>
                                        <td>
                                            {{ formatDate(booking.date) }}
                                            <small v-if="booking.start_time">{{ formatTime(booking.start_time) }}</small>
                                        </td>
                                        <td>
                                            <span :class="['badge', booking.charge_type === 'room' ? 'badge-info' : 'badge-warning']">
                                                {{ booking.charge_type === 'room' ? 'Room' : 'Direct' }}
                                            </span>
                                        </td>
                                        <td>${{ (Number(booking.amount) || 0).toFixed(2) }}</td>
                                        <td>{{ booking.assigned_staff?.name || '-' }}</td>
                                        <td>
                                            <span :class="['badge', getBookingStatusBadge(booking.status)]">
                                                {{ booking.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline" @click="editBooking(booking)">Edit</button>
                                                <button v-if="booking.charge_type === 'room' && booking.payment_status !== 'paid'" class="btn btn-sm btn-primary" @click="postChargesToRoom(booking)" :disabled="saving">
                                                    Post Charges
                                                </button>
                                                <button class="btn btn-sm btn-danger" @click="deleteBooking(booking.id)" :disabled="saving">Remove</button>
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

        <!-- Usage Logs Tab -->
        <div v-if="activeTab === 'usage-logs'" class="tab-content">
            <div class="content-grid">
                <section class="content-card">
                    <div class="card-header">
                        <h2>Service Usage Logs</h2>
                        <p>View all service usage history and transactions</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Service</label>
                                <select v-model="usageLogFilters.service_id" @change="fetchUsageLogs(true)">
                                    <option value="">All Services</option>
                                    <option v-for="service in services" :key="service.id" :value="service.id">
                                        {{ service.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Start Date</label>
                                <input v-model="usageLogFilters.start_date" type="date" @change="fetchUsageLogs(true)" />
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input v-model="usageLogFilters.end_date" type="date" @change="fetchUsageLogs(true)" />
                            </div>
                        </div>

                        <div v-if="loading" class="loading-state">
                            <p>Loading usage logs...</p>
                        </div>
                        <div v-else-if="usageLogs.length === 0" class="empty-state">
                            <p>No usage logs found.</p>
                        </div>
                        <div v-else class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Service</th>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Amount</th>
                                        <th>Charge Type</th>
                                        <th>Payment Status</th>
                                        <th>Processed By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="log in usageLogs" :key="log.id">
                                        <td>
                                            {{ formatDate(log.usage_date) }}
                                            <small v-if="log.usage_time">{{ formatTime(log.usage_time) }}</small>
                                        </td>
                                        <td><strong>{{ log.service?.name }}</strong></td>
                                        <td>{{ log.guest?.name || '-' }}</td>
                                        <td>{{ log.room?.room_number || '-' }}</td>
                                        <td>${{ (Number(log.amount) || 0).toFixed(2) }}</td>
                                        <td>
                                            <span :class="['badge', log.charge_type === 'room' ? 'badge-info' : 'badge-warning']">
                                                {{ log.charge_type === 'room' ? 'Room' : 'Direct' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span :class="['badge', getPaymentStatusBadge(log.payment_status)]">
                                                {{ log.payment_status }}
                                            </span>
                                        </td>
                                        <td>{{ log.processed_by?.name || '-' }}</td>
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
    name: 'AmenitiesServicesManagement',
    setup() {
        const tabs = [
            { id: 'amenities', label: 'Amenities' },
            { id: 'services', label: 'Services' },
            { id: 'time-slots', label: 'Time Slots' },
            { id: 'bookings', label: 'Service Bookings' },
            { id: 'usage-logs', label: 'Usage Logs' },
        ];
        const activeTab = ref('amenities');
        const saving = ref(false);
        const loading = ref(false);
        const loadingAmenities = ref(true);
        const loadingServices = ref(true);

        // Amenities
        const amenities = ref([]);
        const amenityForm = ref({
            id: null,
            name: '',
            is_paid: false,
            price: 0.00,
        });

        // Services
        const services = ref([]);
        const serviceForm = ref({
            id: null,
            name: '',
            category: 'other',
            is_free: false,
            price: 0.00,
            description: '',
            duration: null,
            is_active: true,
        });
        const activeServiceCategory = ref('all');
        const serviceCategories = [
            { value: 'all', label: 'All' },
            { value: 'wellness', label: 'Wellness & Spa' },
            { value: 'fitness', label: 'Fitness & Gym' },
            { value: 'dining', label: 'Dining' },
            { value: 'transport', label: 'Transportation' },
            { value: 'business', label: 'Business' },
            { value: 'entertainment', label: 'Entertainment' },
            { value: 'other', label: 'Other' },
        ];

        const filteredServices = computed(() => {
            if (activeServiceCategory.value === 'all') {
                return services.value;
            }
            return services.value.filter(s => s.category === activeServiceCategory.value);
        });

        const getCategoryLabel = (category) => {
            const cat = serviceCategories.find(c => c.value === category);
            return cat ? cat.label : category;
        };

        // Fetch amenities
        const fetchAmenities = async (setLoading = false) => {
            loadingAmenities.value = true;
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

        const resetTimeSlotForm = () => {
            timeSlotForm.value = {
                id: null,
                service_id: '',
                start_time: '',
                end_time: '',
                duration_minutes: null,
                is_available: true,
                max_bookings: 1,
                available_days: [],
            };
        };

        const getServiceName = (serviceId) => {
            const service = services.value.find(s => s.id === serviceId);
            return service ? service.name : 'Unknown';
        };

        const formatTime = (time) => {
            if (!time) return '';
            if (typeof time === 'string' && time.includes('T')) {
                return new Date(time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            }
            if (typeof time === 'string' && time.length === 5) {
                return time;
            }
            return time;
        };

        const formatAvailableDays = (days) => {
            if (!days || !Array.isArray(days) || days.length === 0) return 'All days';
            return days.map(d => daysOfWeek[d]).join(', ');
        };

        // Service Bookings
        const bookings = ref([]);
        const guests = ref([]);
        const staffList = ref([]);
        const guestReservations = ref([]);
        const availableTimeSlots = ref([]);
        const activeBookingStatus = ref('all');
        const bookingStatuses = [
            { value: 'all', label: 'All' },
            { value: 'pending', label: 'Pending' },
            { value: 'confirmed', label: 'Confirmed' },
            { value: 'completed', label: 'Completed' },
            { value: 'cancelled', label: 'Cancelled' },
        ];
        const bookingForm = ref({
            id: null,
            service_id: '',
            guest_id: '',
            room_id: null,
            reservation_id: null,
            time_slot_id: null,
            assigned_staff_id: null,
            date: '',
            start_time: '',
            end_time: '',
            charge_type: 'direct',
            payment_status: 'pending',
            payment_method: '',
            notes: '',
        });

        const filteredBookings = computed(() => {
            const validBookings = bookings.value.filter(b => b && b.id);
            if (activeBookingStatus.value === 'all') {
                return validBookings;
            }
            return validBookings.filter(b => b.status === activeBookingStatus.value);
        });

        const fetchBookings = async (setLoading = false) => {
            try {
                if (setLoading) loading.value = true;
                const response = await axios.get('/api/service-bookings');
                const newData = (response.data.data || []).filter(b => b && b.id);
                // Only update if new data is non-empty or this is first load
                if (newData.length > 0 || bookings.value.length === 0) {
                    bookings.value = newData;
                }
            } catch (error) {
                console.error('Error fetching bookings:', error);
                // Only clear on error if first load
                if (bookings.value.length === 0) {
                    bookings.value = [];
                }
            } finally {
                if (setLoading) loading.value = false;
            }
        };

        const fetchGuests = async () => {
            try {
                const response = await axios.get('/api/guests');
                let rawData = [];
                
                // Handle paginated response
                if (response.data.success && response.data.data) {
                    if (Array.isArray(response.data.data)) {
                        rawData = response.data.data;
                    } else if (response.data.data.data && Array.isArray(response.data.data.data)) {
                        // Paginated response
                        rawData = response.data.data.data;
                    } else if (Array.isArray(response.data)) {
                        rawData = response.data;
                    }
                } else if (Array.isArray(response.data)) {
                    rawData = response.data;
                } else if (Array.isArray(response.data.data)) {
                    rawData = response.data.data;
                }
                
                const newData = Array.isArray(rawData) ? rawData.filter(g => g && g.id) : [];
                // Only update if new data is non-empty or this is first load
                if (newData.length > 0 || guests.value.length === 0) {
                    guests.value = newData;
                }
            } catch (error) {
                console.error('Error fetching guests:', error);
                // Only clear on error if first load
                if (guests.value.length === 0) {
                    guests.value = [];
                }
            }
        };

        const fetchStaff = async () => {
            try {
                const response = await axios.get('/api/users');
                const allUsers = response.data.data || response.data || [];
                const newData = allUsers.filter(u => u && u.id && u.status === 'active');
                // Only update if new data is non-empty or this is first load
                if (newData.length > 0 || staffList.value.length === 0) {
                    staffList.value = newData;
                }
            } catch (error) {
                console.error('Error fetching staff:', error);
                // Only clear on error if first load
                if (staffList.value.length === 0) {
                    staffList.value = [];
                }
            }
        };

        const loadTimeSlotsForBooking = async () => {
            if (!bookingForm.value.service_id) {
                availableTimeSlots.value = [];
                return;
            }
            try {
                const response = await axios.get(`/api/services/${bookingForm.value.service_id}/time-slots`, {
                    params: { date: bookingForm.value.date || new Date().toISOString().split('T')[0] }
                });
                const rawData = response.data.data || [];
                availableTimeSlots.value = rawData.filter(slot => slot && slot.id);
            } catch (error) {
                console.error('Error fetching time slots:', error);
                availableTimeSlots.value = [];
            }
        };

        const loadGuestReservations = async () => {
            if (!bookingForm.value.guest_id) {
                guestReservations.value = [];
                return;
            }
            try {
                const response = await axios.get(`/api/guests/${bookingForm.value.guest_id}/history`);
                const rawData = response.data.data || [];
                guestReservations.value = rawData.filter(r => 
                    r && r.id && ['confirmed', 'checked_in'].includes(r.status)
                );
            } catch (error) {
                console.error('Error fetching guest reservations:', error);
                guestReservations.value = [];
            }
        };

        const onChargeTypeChange = () => {
            if (bookingForm.value.charge_type === 'direct') {
                bookingForm.value.reservation_id = null;
            }
        };

        const saveBooking = async () => {
            if (!bookingForm.value.service_id || !bookingForm.value.guest_id || !bookingForm.value.date) return;
            
            try {
                saving.value = true;
                
                if (bookingForm.value.id) {
                    await axios.put(`/api/service-bookings/${bookingForm.value.id}`, bookingForm.value);
                } else {
                    await axios.post('/api/service-bookings', bookingForm.value);
                }
                
                await fetchBookings(true);
                resetBookingForm();
            } catch (error) {
                console.error('Error saving booking:', error);
                alert('Failed to save booking: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const editBooking = (booking) => {
            bookingForm.value = {
                id: booking.id,
                service_id: booking.service_id,
                guest_id: booking.guest_id,
                room_id: booking.room_id,
                reservation_id: booking.reservation_id,
                time_slot_id: booking.time_slot_id,
                assigned_staff_id: booking.assigned_staff_id,
                date: booking.date ? booking.date.split('T')[0] : '',
                start_time: booking.start_time ? booking.start_time.substring(0, 5) : '',
                end_time: booking.end_time ? booking.end_time.substring(0, 5) : '',
                charge_type: booking.charge_type || 'direct',
                payment_status: booking.payment_status || 'pending',
                payment_method: booking.payment_method || '',
                notes: booking.notes || '',
            };
            if (bookingForm.value.guest_id) {
                loadGuestReservations();
            }
            if (bookingForm.value.service_id) {
                loadTimeSlotsForBooking();
            }
        };

        const deleteBooking = async (id) => {
            if (!confirm('Are you sure you want to delete this booking?')) return;
            
            try {
                saving.value = true;
                await axios.delete(`/api/service-bookings/${id}`);
                await fetchBookings(true);
            } catch (error) {
                console.error('Error deleting booking:', error);
                alert('Failed to delete booking: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const postChargesToRoom = async (booking) => {
            if (!confirm('Post charges to room bill?')) return;
            
            try {
                saving.value = true;
                await axios.post(`/api/service-bookings/${booking.id}/post-charges`);
                await fetchBookings(true);
                alert('Charges posted to room bill successfully');
            } catch (error) {
                console.error('Error posting charges:', error);
                alert('Failed to post charges: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const resetBookingForm = () => {
            bookingForm.value = {
                id: null,
                service_id: '',
                guest_id: '',
                room_id: null,
                reservation_id: null,
                time_slot_id: null,
                assigned_staff_id: null,
                date: '',
                start_time: '',
                end_time: '',
                charge_type: 'direct',
                payment_status: 'pending',
                payment_method: '',
                notes: '',
            };
            guestReservations.value = [];
            availableTimeSlots.value = [];
        };

        const getBookingStatusBadge = (status) => {
            const badges = {
                pending: 'badge-warning',
                confirmed: 'badge-info',
                completed: 'badge-success',
                cancelled: 'badge-secondary',
            };
            return badges[status] || 'badge-secondary';
        };

        // Usage Logs
        const usageLogs = ref([]);
        const usageLogFilters = ref({
            service_id: '',
            start_date: '',
            end_date: '',
        });

        const fetchUsageLogs = async (setLoading = false) => {
            try {
                if (setLoading) loading.value = true;
                const params = {};
                if (usageLogFilters.value.service_id) {
                    params.service_id = usageLogFilters.value.service_id;
                }
                if (usageLogFilters.value.start_date) {
                    params.start_date = usageLogFilters.value.start_date;
                }
                if (usageLogFilters.value.end_date) {
                    params.end_date = usageLogFilters.value.end_date;
                }
                const response = await axios.get('/api/service-bookings/usage-logs', { params });
                const newData = response.data.data || [];
                // For usage logs, always update when filters are applied (user action)
                // But preserve data if empty response and no filters (likely error)
                if (newData.length > 0 || usageLogs.value.length === 0 || Object.keys(params).length > 0) {
                    usageLogs.value = newData;
                }
            } catch (error) {
                console.error('Error fetching usage logs:', error);
                // Only clear on error if first load
                if (usageLogs.value.length === 0) {
                    usageLogs.value = [];
                }
            } finally {
                if (setLoading) loading.value = false;
            }
        };

        const getPaymentStatusBadge = (status) => {
            const badges = {
                pending: 'badge-warning',
                paid: 'badge-success',
                refunded: 'badge-secondary',
            };
            return badges[status] || 'badge-secondary';
        };

        const formatDate = (date) => {
            if (!date) return '';
            return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        };

        onMounted(async () => {
            // Fetch data silently on mount - errors are handled internally without alerts
            // Use Promise.allSettled to ensure both requests complete even if one fails
            // Loading states are managed inside fetch functions
            try {
                await Promise.allSettled([
                    fetchAmenities(false),
                    fetchServices(false),
                    fetchGuests(),
                    fetchStaff(),
                    fetchBookings(false),
                    fetchUsageLogs(false),
                ]);
            } catch (error) {
                console.error('Error during initial data fetch:', error);
                loadingAmenities.value = false;
                loadingServices.value = false;
            }
        });

        return {
            tabs,
            activeTab,
            saving,
            loading,
            loadingAmenities,
            loadingServices,
            // Amenities
            amenities,
            amenityForm,
            saveAmenity,
            editAmenity,
            deleteAmenity,
            resetAmenityForm,
            // Services
            services,
            serviceForm,
            activeServiceCategory,
            serviceCategories,
            filteredServices,
            getCategoryLabel,
            saveService,
            editService,
            deleteService,
            resetServiceForm,
            // Time Slots
            timeSlots,
            timeSlotForm,
            daysOfWeek,
            loadTimeSlotsForService,
            saveTimeSlot,
            editTimeSlot,
            deleteTimeSlot,
            resetTimeSlotForm,
            getServiceName,
            formatTime,
            formatAvailableDays,
            // Service Bookings
            bookings,
            guests,
            staffList,
            guestReservations,
            availableTimeSlots,
            activeBookingStatus,
            bookingStatuses,
            bookingForm,
            filteredBookings,
            fetchBookings,
            loadTimeSlotsForBooking,
            loadGuestReservations,
            onChargeTypeChange,
            saveBooking,
            editBooking,
            deleteBooking,
            postChargesToRoom,
            resetBookingForm,
            getBookingStatusBadge,
            // Usage Logs
            usageLogs,
            usageLogFilters,
            fetchUsageLogs,
            getPaymentStatusBadge,
            formatDate,
        };
    },
};
</script>

<style scoped>
.amenities-services-page {
    max-width: 1600px;
    margin: 0 auto;
    padding: 0 20px 40px;
}

.page-header {
    margin-bottom: 24px;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 4px;
}

.page-subtitle {
    font-size: 14px;
    color: #718096;
}

.tabs-container {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e2e8f0;
}

.tab-btn {
    padding: 10px 18px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: #718096;
    margin-bottom: -2px;
    transition: all 0.2s;
}

.tab-btn:hover {
    color: #4c6fff;
}

.tab-btn.active {
    color: #4c6fff;
    border-bottom-color: #4c6fff;
}

.tab-content {
    animation: fadeIn 0.2s;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 18px;
}

.content-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    padding: 16px 18px 18px;
}

.card-header h2 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 4px;
    color: #1a202c;
}

.card-header p {
    margin: 0;
    font-size: 13px;
    color: #718096;
}

.card-body {
    margin-top: 10px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 10px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.form-group label {
    font-size: 12px;
    font-weight: 500;
    color: #4a5568;
}

input,
select,
textarea {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 7px 10px;
    font-size: 13px;
    outline: none;
    transition: all 0.15s ease;
}

input:focus,
select:focus,
textarea:focus {
    border-color: #4c6fff;
    box-shadow: 0 0 0 1px rgba(76, 111, 255, 0.25);
}

input:disabled {
    background: #f7fafc;
    color: #a0aec0;
    cursor: not-allowed;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    border: none;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #ffffff;
}

.btn-primary:hover:not(:disabled) {
    opacity: 0.95;
}

.btn-secondary {
    background: #edf2f7;
    color: #4a5568;
}

.btn-secondary:hover:not(:disabled) {
    background: #e2e8f0;
}

.btn-danger {
    background: #f56565;
    color: #ffffff;
}

.btn-danger:hover:not(:disabled) {
    background: #e53e3e;
}

.btn-outline {
    background: transparent;
    border: 1px solid #cbd5e0;
    color: #4a5568;
}

.btn-outline:hover:not(:disabled) {
    background: #f7fafc;
}

.btn-sm {
    padding: 4px 10px;
    font-size: 11px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 8px;
}

.filter-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
}

.chip {
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    padding: 5px 12px;
    font-size: 11px;
    cursor: pointer;
    background: #f7fafc;
    color: #4a5568;
}

.chip.active {
    background: #4c6fff;
    border-color: #4c6fff;
    color: #ffffff;
}

.table-container {
    width: 100%;
    overflow-x: auto;
    margin-top: 10px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.data-table th,
.data-table td {
    padding: 7px 8px;
    border-bottom: 1px solid #edf2f7;
    text-align: left;
}

.data-table th {
    font-weight: 600;
    color: #4a5568;
    background: #f7fafc;
}

.data-table td small {
    display: block;
    color: #718096;
    font-size: 11px;
    margin-top: 2px;
}

.action-buttons {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

.badge-secondary {
    background: #e5e7eb;
    color: #374151;
}

.badge-warning {
    background: #fef9c3;
    color: #854d0e;
}

.badge-info {
    background: #e0f2fe;
    color: #0369a1;
}

.text-muted {
    color: #718096;
}

.loading-state {
    text-align: center;
    padding: 40px 20px;
    color: #718096;
    font-size: 14px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #718096;
    font-size: 13px;
    background: #f7fafc;
    border-radius: 8px;
    border: 1px dashed #e2e8f0;
    margin-top: 10px;
}

@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: minmax(0, 1fr);
    }
}

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
    cursor: pointer;
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: minmax(0, 1fr);
    }
}
</style>

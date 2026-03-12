<template>
    <div class="room-service-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Room Service Module</h1>
                <p class="page-subtitle">
                    Manage room service orders, staff assignments, and delivery tracking.
                </p>
            </div>
        </div>

        <div class="content-grid">
            <!-- Room Service Order Form -->
            <section class="content-card">
                <div class="card-header">
                    <h2>Room Service Order</h2>
                    <p>Guest places order from room - Auto-posts to room bill.</p>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Room Number *</label>
                            <input 
                                v-model="roomServiceForm.room_number" 
                                type="text" 
                                placeholder="e.g. 305"
                                @input="loadRoomDetails"
                            />
                        </div>
                        <div class="form-group">
                            <label>Guest Name</label>
                            <input 
                                v-model="roomServiceForm.guest_name" 
                                type="text" 
                                placeholder="Auto-filled from reservation"
                                readonly
                            />
                        </div>
                        <div class="form-group">
                            <label>Reservation ID</label>
                            <input 
                                v-model="roomServiceForm.reservation_id" 
                                type="text" 
                                placeholder="Auto-linked"
                                readonly
                            />
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Room Service Menu</label>
                        <div v-if="activeMenuItems.length === 0" class="empty-menu-message">
                            <p>No menu items available. Please add items to the restaurant menu first.</p>
                        </div>
                        <div v-else class="menu-items-grid">
                            <button
                                v-for="item in activeMenuItems"
                                :key="item.id"
                                class="menu-pill"
                                @click="addItemToRoomServiceOrder(item)"
                                :title="item.description || item.name"
                            >
                                <span class="menu-pill-name">{{ item.name }}</span>
                                <span class="menu-pill-price">${{ item.price.toFixed(2) }}</span>
                            </button>
                        </div>
                        <div v-if="activeMenuItems.length > 0" class="menu-info">
                            <small>Click on menu items to add them to the order</small>
                        </div>
                    </div>

                    <div class="order-items" v-if="roomServiceForm.items.length">
                        <table class="data-table compact">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(oi, idx) in roomServiceForm.items" :key="oi.id + '-' + idx">
                                    <td>{{ oi.name }}</td>
                                    <td>
                                        <input
                                            type="number"
                                            min="1"
                                            v-model.number="oi.qty"
                                            @change="recalculateRoomServiceTotals"
                                            class="qty-input"
                                        />
                                    </td>
                                    <td>${{ oi.price.toFixed(2) }}</td>
                                    <td>${{ (oi.price * oi.qty).toFixed(2) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" @click="removeRoomServiceItem(idx)">×</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="order-summary" v-if="roomServiceForm.items.length">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>${{ roomServiceTotals.subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax:</span>
                            <span>${{ roomServiceTotals.tax.toFixed(2) }}</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>${{ roomServiceTotals.total.toFixed(2) }}</span>
                        </div>
                        <div class="summary-row info">
                            <small>💡 Charges will be auto-posted to room bill</small>
                        </div>
                    </div>

                    <div class="form-actions" v-if="roomServiceForm.items.length">
                        <button class="btn btn-secondary" @click="clearOrderForm" :disabled="saving">
                            Clear Order
                        </button>
                        <button class="btn btn-primary" @click="placeRoomServiceOrder" :disabled="!roomServiceForm.room_number || roomServiceForm.items.length === 0 || saving">
                            {{ saving ? 'Placing Order...' : 'Place Room Service Order' }}
                        </button>
                    </div>
                    <div v-else class="order-help-text">
                        <p><strong>Instructions:</strong></p>
                        <ol>
                            <li>Enter the room number</li>
                            <li>Click on menu items below to add them to your order</li>
                            <li>Adjust quantities as needed</li>
                            <li>Click "Place Room Service Order" when ready</li>
                        </ol>
                    </div>
                </div>
            </section>

            <!-- Active Room Service Orders -->
            <section class="content-card">
                <div class="card-header">
                    <h2>Active Room Service Orders</h2>
                    <p>Track orders from placement to delivery with time tracking.</p>
                </div>
                <div class="card-body">
                        <div class="filter-chips">
                            <button
                                v-for="status in roomServiceStatuses"
                                :key="status.value"
                                :class="['chip', { active: roomServiceStatusFilter === status.value }]"
                                @click="roomServiceStatusFilter = status.value; fetchRoomServiceOrders()"
                            >
                                {{ status.label }}
                            </button>
                        </div>

                    <div v-if="filteredRoomServiceOrders.length === 0" class="empty-state">
                        <p>No room service orders</p>
                    </div>
                    <div v-else class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Room</th>
                                    <th>Guest</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Staff</th>
                                    <th>Time Tracking</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in filteredRoomServiceOrders" :key="order.id">
                                    <td>#{{ order.id }}</td>
                                    <td>
                                        <strong>{{ order.room_number }}</strong>
                                    </td>
                                    <td>{{ order.guest_name || '-' }}</td>
                                    <td>
                                        <small>{{ order.items.length }} item(s)</small>
                                    </td>
                                    <td>
                                        <span :class="['badge', getRoomServiceStatusClass(order.status)]">
                                            {{ order.status }}
                                        </span>
                                    </td>
                                    <td>
                                        <select 
                                            v-model="order.assigned_staff_id"
                                            @change="assignStaffToRoomServiceOrder(order)"
                                            class="staff-select"
                                        >
                                            <option :value="null">Assign Staff</option>
                                            <option v-for="staff in serviceStaff" :key="staff.id" :value="staff.id">
                                                {{ staff.name }}
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="time-tracking">
                                            <small v-if="order.order_time">
                                                Ordered: {{ formatTime(order.order_time) }}
                                            </small>
                                            <small v-if="order.kitchen_time">
                                                Kitchen: {{ formatTime(order.kitchen_time) }}
                                            </small>
                                            <small v-if="order.delivery_time">
                                                Out: {{ formatTime(order.delivery_time) }}
                                            </small>
                                            <small v-if="order.delivered_time" class="delivered">
                                                Delivered: {{ formatTime(order.delivered_time) }}
                                            </small>
                                            <small v-if="order.delivered_time" class="duration">
                                                Total: {{ calculateDuration(order.order_time, order.delivered_time) }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>${{ (order.total_amount || order.total || 0).toFixed(2) }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button 
                                                v-if="order.status === 'NEW'"
                                                class="btn btn-sm btn-warning"
                                                @click="routeToKitchen(order)"
                                            >
                                                Route to Kitchen
                                            </button>
                                            <button 
                                                v-if="order.status === 'KOT'"
                                                class="btn btn-sm btn-info"
                                                @click="updateRoomServiceStatus(order, 'PREPARING')"
                                            >
                                                Preparing
                                            </button>
                                            <button 
                                                v-if="order.status === 'PREPARING'"
                                                class="btn btn-sm btn-primary"
                                                @click="updateRoomServiceStatus(order, 'OUT_FOR_DELIVERY')"
                                            >
                                                Out for Delivery
                                            </button>
                                            <button 
                                                v-if="order.status === 'OUT_FOR_DELIVERY'"
                                                class="btn btn-sm btn-success"
                                                @click="markDelivered(order)"
                                            >
                                                Mark Delivered
                                            </button>
                                                <button 
                                                    v-if="order.status === 'DELIVERED' && !order.charges_posted"
                                                    class="btn btn-sm btn-success"
                                                    @click="postChargesToRoom(order)"
                                                >
                                                    Post to Room Bill
                                                </button>
                                                <span v-if="order.charges_posted" class="badge badge-success">
                                                    Posted
                                                </span>
                                                <button 
                                                    v-if="order.status === 'CANCELLED'"
                                                    class="btn btn-sm btn-danger"
                                                    disabled
                                                >
                                                    Cancelled
                                                </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Service Staff Management -->
            <section class="content-card">
                <div class="card-header">
                    <h2>Service Staff Assignment</h2>
                    <p>Manage service staff for room service delivery.</p>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Staff Name</label>
                            <input v-model="staffForm.name" type="text" placeholder="e.g. John Doe" />
                        </div>
                        <div class="form-group">
                            <label>Employee ID</label>
                            <input v-model="staffForm.employee_id" type="text" placeholder="e.g. EMP001" />
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select v-model="staffForm.status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary btn-block" @click="saveStaff">
                                {{ staffForm.id ? 'Update Staff' : 'Add Staff' }}
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Employee ID</th>
                                    <th>Active Orders</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="staff in serviceStaff" :key="staff.id">
                                    <td>{{ staff.name }}</td>
                                    <td>{{ staff.employee_id }}</td>
                                    <td>
                                        {{ getStaffActiveOrdersCount(staff.id) }}
                                    </td>
                                    <td>
                                        <span :class="['badge', staff.status === 'active' ? 'badge-success' : 'badge-secondary']">
                                            {{ staff.status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline" @click="editStaff(staff)">
                                                Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger" @click="removeStaff(staff.id)">
                                                Remove
                                            </button>
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
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'RoomServiceModule',
    setup() {
        const loading = ref(false);
        const saving = ref(false);
        // Menu items (shared with restaurant menu)
        const menuItems = ref([
            { id: 1, name: 'Tomato Soup', description: 'Fresh tomatoes, herbs', category_id: 1, price: 4.5, tax_rate: 5, is_active: true },
            { id: 2, name: 'Grilled Chicken', description: 'Served with veggies', category_id: 2, price: 12.0, tax_rate: 5, is_active: true },
            { id: 3, name: 'Chocolate Cake', description: 'Rich dark chocolate', category_id: 3, price: 6.0, tax_rate: 5, is_active: true },
            { id: 4, name: 'Caesar Salad', description: 'Fresh lettuce, parmesan, croutons', category_id: 1, price: 8.5, tax_rate: 5, is_active: true },
            { id: 5, name: 'Beef Steak', description: 'Premium cut, grilled to perfection', category_id: 2, price: 24.0, tax_rate: 5, is_active: true },
            { id: 6, name: 'Fish & Chips', description: 'Battered fish with fries', category_id: 2, price: 14.0, tax_rate: 5, is_active: true },
            { id: 7, name: 'Pasta Carbonara', description: 'Creamy pasta with bacon', category_id: 2, price: 13.5, tax_rate: 5, is_active: true },
            { id: 8, name: 'Ice Cream Sundae', description: 'Vanilla ice cream with toppings', category_id: 3, price: 7.0, tax_rate: 5, is_active: true },
            { id: 9, name: 'Fresh Orange Juice', description: 'Freshly squeezed', category_id: 1, price: 3.5, tax_rate: 5, is_active: true },
            { id: 10, name: 'Coffee', description: 'Hot brewed coffee', category_id: 1, price: 2.5, tax_rate: 5, is_active: true },
            { id: 11, name: 'Tea', description: 'Hot tea selection', category_id: 1, price: 2.0, tax_rate: 5, is_active: true },
            { id: 12, name: 'Sandwich Club', description: 'Triple decker sandwich', category_id: 2, price: 9.5, tax_rate: 5, is_active: true },
        ]);

        const activeMenuItems = computed(() =>
            menuItems.value.filter(i => i.is_active)
        );

        // Room Service Module
        const roomServiceOrders = ref([]);
        const lastRoomServiceOrderId = ref(2000);
        const roomServiceForm = ref({
            room_number: '',
            guest_name: '',
            reservation_id: '',
            items: [],
            total: 0,
        });
        const roomServiceTotals = ref({
            subtotal: 0,
            tax: 0,
            total: 0,
        });
        const roomServiceStatusFilter = ref('all');
        const roomServiceStatuses = [
            { value: 'all', label: 'All' },
            { value: 'NEW', label: 'New' },
            { value: 'KOT', label: 'In Kitchen' },
            { value: 'PREPARING', label: 'Preparing' },
            { value: 'OUT_FOR_DELIVERY', label: 'Out for Delivery' },
            { value: 'DELIVERED', label: 'Delivered' },
        ];

        // Service Staff
        const serviceStaff = ref([
            { id: 1, name: 'John Doe', employee_id: 'EMP001', status: 'active' },
            { id: 2, name: 'Jane Smith', employee_id: 'EMP002', status: 'active' },
        ]);
        const staffForm = ref({
            id: null,
            name: '',
            employee_id: '',
            status: 'active',
        });

        const loadRoomDetails = async () => {
            if (!roomServiceForm.value.room_number) return;
            
            try {
                loading.value = true;
                const response = await axios.get('/api/room-service-orders/room/details', {
                    params: { room_number: roomServiceForm.value.room_number }
                });
                
                if (response.data.success) {
                    roomServiceForm.value.guest_name = response.data.guest_name || '';
                    roomServiceForm.value.reservation_id = response.data.reservation_id || '';
                }
            } catch (error) {
                // If room not found, clear guest details
                roomServiceForm.value.guest_name = '';
                roomServiceForm.value.reservation_id = '';
            } finally {
                loading.value = false;
            }
        };

        const addItemToRoomServiceOrder = (item) => {
            const existing = roomServiceForm.value.items.find(it => it.id === item.id);
            if (existing) {
                existing.qty += 1;
            } else {
                roomServiceForm.value.items.push({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    qty: 1,
                });
            }
            recalculateRoomServiceTotals();
        };

        const removeRoomServiceItem = (idx) => {
            roomServiceForm.value.items.splice(idx, 1);
            recalculateRoomServiceTotals();
        };

        const recalculateRoomServiceTotals = () => {
            const subtotal = roomServiceForm.value.items.reduce(
                (sum, item) => sum + item.price * item.qty,
                0
            );
            const tax = subtotal * 0.05; // 5% tax
            const total = subtotal + tax;
            roomServiceTotals.value = { subtotal, tax, total };
            roomServiceForm.value.total = total;
        };

        const placeRoomServiceOrder = async () => {
            if (!roomServiceForm.value.room_number || !roomServiceForm.value.items.length) {
                alert('Please enter a room number and add at least one item to the order.');
                return;
            }
            
            try {
                saving.value = true;
                
                // Prepare order data for API
                const orderData = {
                    room_number: roomServiceForm.value.room_number,
                    guest_name: roomServiceForm.value.guest_name || null,
                    reservation_id: roomServiceForm.value.reservation_id || null,
                    items: roomServiceForm.value.items.map(item => ({
                        menu_item_id: item.id,
                        qty: item.qty,
                        price: item.price,
                    })),
                    subtotal: roomServiceTotals.value.subtotal,
                    tax: roomServiceTotals.value.tax,
                    total_amount: roomServiceTotals.value.total,
                };
                
                const response = await axios.post('/api/room-service-orders', orderData);
                
                if (response.data.success) {
                    // Add the new order to the list
                    roomServiceOrders.value.unshift(response.data.order);
                    
                    // Reset form but keep room details
                    const savedRoomNumber = roomServiceForm.value.room_number;
                    const savedGuestName = roomServiceForm.value.guest_name;
                    const savedReservationId = roomServiceForm.value.reservation_id;
                    
                    roomServiceForm.value.items = [];
                    roomServiceForm.value.total = 0;
                    roomServiceTotals.value = { subtotal: 0, tax: 0, total: 0 };
                    
                    // Keep room details for next order
                    roomServiceForm.value.room_number = savedRoomNumber;
                    roomServiceForm.value.guest_name = savedGuestName;
                    roomServiceForm.value.reservation_id = savedReservationId;
                    
                    alert(`Room Service Order #${response.data.order.id} placed successfully!\nTotal: $${response.data.order.total_amount}\nRoom: ${response.data.order.room_number}`);
                }
            } catch (error) {
                console.error('Error placing order:', error);
                alert('Failed to place order: ' + (error.response?.data?.message || error.message));
            } finally {
                saving.value = false;
            }
        };

        const clearOrderForm = () => {
            if (confirm('Clear the current order? Room details will be kept.')) {
                roomServiceForm.value.items = [];
                roomServiceForm.value.total = 0;
                roomServiceTotals.value = { subtotal: 0, tax: 0, total: 0 };
            }
        };

        const routeToKitchen = async (order) => {
            await updateRoomServiceStatus(order, 'KOT');
        };

        const updateRoomServiceStatus = async (order, newStatus) => {
            try {
                const response = await axios.put(`/api/room-service-orders/${order.id}`, {
                    status: newStatus
                });
                
                if (response.data.success) {
                    // Update local order with server response
                    const index = roomServiceOrders.value.findIndex(o => o.id === order.id);
                    if (index !== -1) {
                        roomServiceOrders.value[index] = response.data.order;
                    }
                }
            } catch (error) {
                console.error('Error updating order status:', error);
                alert('Failed to update order status: ' + (error.response?.data?.message || error.message));
            }
        };

        const markDelivered = async (order) => {
            await updateRoomServiceStatus(order, 'DELIVERED');
        };

        const postChargesToRoom = async (order) => {
            if (!confirm(`Post charges of $${order.total_amount || order.total} to room ${order.room_number} bill?`)) {
                return;
            }
            
            try {
                const response = await axios.post(`/api/room-service-orders/${order.id}/post-charges`);
                
                if (response.data.success) {
                    // Update local order
                    const index = roomServiceOrders.value.findIndex(o => o.id === order.id);
                    if (index !== -1) {
                        roomServiceOrders.value[index] = response.data.order;
                    }
                    alert(`Charges posted to room ${order.room_number} bill successfully!`);
                }
            } catch (error) {
                console.error('Error posting charges:', error);
                alert('Failed to post charges: ' + (error.response?.data?.message || error.message));
            }
        };

        const assignStaffToRoomServiceOrder = async (order) => {
            try {
                const response = await axios.put(`/api/room-service-orders/${order.id}`, {
                    assigned_staff_id: order.assigned_staff_id
                });
                
                if (response.data.success) {
                    // Update local order
                    const index = roomServiceOrders.value.findIndex(o => o.id === order.id);
                    if (index !== -1) {
                        roomServiceOrders.value[index] = response.data.order;
                    }
                }
            } catch (error) {
                console.error('Error assigning staff:', error);
                alert('Failed to assign staff: ' + (error.response?.data?.message || error.message));
            }
        };
        
        const fetchRoomServiceOrders = async () => {
            try {
                loading.value = true;
                const params = {};
                if (roomServiceStatusFilter.value !== 'all') {
                    params.status = roomServiceStatusFilter.value;
                }
                
                const response = await axios.get('/api/room-service-orders', { params });
                roomServiceOrders.value = response.data.map(order => ({
                    ...order,
                    items: order.items || [],
                    order_time: order.order_time || order.ordered_at ? new Date(order.order_time || order.ordered_at) : null,
                    kitchen_time: order.kitchen_time ? new Date(order.kitchen_time) : null,
                    delivery_time: order.delivery_time ? new Date(order.delivery_time) : null,
                    delivered_time: order.delivered_time ? new Date(order.delivered_time) : null,
                }));
            } catch (error) {
                console.error('Error fetching orders:', error);
                // Don't show alert on page load - errors are logged to console
                // Only show alert for user-initiated actions
            } finally {
                loading.value = false;
            }
        };

        const filteredRoomServiceOrders = computed(() => {
            // Filtering is now done server-side, but we can also filter client-side if needed
            return roomServiceOrders.value;
        });
        
        // Watch status filter and refetch orders
        const watchStatusFilter = () => {
            fetchRoomServiceOrders();
        };

        const getRoomServiceStatusClass = (status) => {
            const classes = {
                'NEW': 'badge-warning',
                'KOT': 'badge-info',
                'PREPARING': 'badge-primary',
                'OUT_FOR_DELIVERY': 'badge-primary',
                'DELIVERED': 'badge-success',
            };
            return classes[status] || 'badge-secondary';
        };

        const formatTime = (date) => {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        };

        const calculateDuration = (start, end) => {
            if (!start || !end) return '-';
            const startTime = new Date(start);
            const endTime = new Date(end);
            const diffMs = endTime - startTime;
            const diffMins = Math.floor(diffMs / 60000);
            if (diffMins < 60) {
                return `${diffMins} min`;
            }
            const hours = Math.floor(diffMins / 60);
            const mins = diffMins % 60;
            return `${hours}h ${mins}m`;
        };

        const saveStaff = () => {
            if (!staffForm.value.name || !staffForm.value.employee_id) return;
            if (staffForm.value.id) {
                const idx = serviceStaff.value.findIndex(s => s.id === staffForm.value.id);
                if (idx !== -1) serviceStaff.value[idx] = { ...staffForm.value };
            } else {
                const newId = Math.max(...serviceStaff.value.map(s => s.id), 0) + 1;
                serviceStaff.value.push({
                    ...staffForm.value,
                    id: newId,
                });
            }
            staffForm.value = { id: null, name: '', employee_id: '', status: 'active' };
        };

        const editStaff = (staff) => {
            staffForm.value = { ...staff };
        };

        const removeStaff = (id) => {
            serviceStaff.value = serviceStaff.value.filter(s => s.id !== id);
        };

        const getStaffActiveOrdersCount = (staffId) => {
            return roomServiceOrders.value.filter(
                o => o.assigned_staff_id === staffId && 
                     ['NEW', 'KOT', 'PREPARING', 'OUT_FOR_DELIVERY'].includes(o.status)
            ).length;
        };

        onMounted(() => {
            fetchRoomServiceOrders();
        });

        return {
            activeMenuItems,
            roomServiceOrders,
            roomServiceForm,
            roomServiceTotals,
            roomServiceStatusFilter,
            roomServiceStatuses,
            filteredRoomServiceOrders,
            serviceStaff,
            staffForm,
            loadRoomDetails,
            addItemToRoomServiceOrder,
            removeRoomServiceItem,
            recalculateRoomServiceTotals,
            placeRoomServiceOrder,
            clearOrderForm,
            routeToKitchen,
            updateRoomServiceStatus,
            markDelivered,
            postChargesToRoom,
            assignStaffToRoomServiceOrder,
            getRoomServiceStatusClass,
            formatTime,
            calculateDuration,
            saveStaff,
            editStaff,
            removeStaff,
            getStaffActiveOrdersCount,
        };
    },
};
</script>

<style scoped>
.room-service-page {
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

.content-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
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

.btn-block {
    width: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #ffffff;
}

.btn-primary:hover {
    opacity: 0.95;
}

.btn-secondary {
    background: #edf2f7;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #e2e8f0;
}

.btn-danger {
    background: #f56565;
    color: #ffffff;
}

.btn-danger:hover {
    background: #e53e3e;
}

.btn-outline {
    background: transparent;
    border: 1px solid #cbd5e0;
    color: #4a5568;
}

.btn-outline:hover {
    background: #f7fafc;
}

.btn-sm {
    padding: 4px 10px;
    font-size: 11px;
}

.btn-warning {
    background: #fbbf24;
    color: #78350f;
}

.btn-warning:hover {
    background: #f59e0b;
}

.btn-info {
    background: #60a5fa;
    color: #1e3a8a;
}

.btn-info:hover {
    background: #3b82f6;
}

.btn-success {
    background: #10b981;
    color: #ffffff;
}

.btn-success:hover {
    background: #059669;
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

.data-table.compact th,
.data-table.compact td {
    padding: 5px 6px;
}

.action-buttons {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.menu-items-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.menu-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 10px;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    cursor: pointer;
    font-size: 11px;
}

.menu-pill:hover {
    background: #edf2ff;
    border-color: #4c6fff;
}

.menu-pill-name {
    font-weight: 500;
}

.menu-pill-price {
    color: #4a5568;
}

.qty-input {
    width: 56px;
}

.order-summary {
    margin-top: 10px;
    border-radius: 10px;
    border: 1px dashed #e2e8f0;
    padding: 8px 10px;
    background: #faf5ff;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    margin-bottom: 2px;
}

.summary-row.total {
    font-weight: 600;
    margin-top: 4px;
    padding-top: 4px;
    border-top: 1px solid #e2e8f0;
}

.summary-row.info {
    margin-top: 6px;
    padding-top: 6px;
    border-top: 1px dashed #e2e8f0;
    color: #4c6fff;
    font-style: italic;
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

.badge-primary {
    background: #dbeafe;
    color: #1e40af;
}

.staff-select {
    min-width: 140px;
    font-size: 11px;
    padding: 4px 8px;
}

.time-tracking {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 10px;
}

.time-tracking small {
    color: #718096;
}

.time-tracking .delivered {
    color: #166534;
    font-weight: 600;
}

.time-tracking .duration {
    color: #4c6fff;
    font-weight: 600;
}

.empty-state {
    text-align: center;
    padding: 20px;
    color: #718096;
    font-size: 13px;
}

.empty-menu-message {
    padding: 15px;
    background: #fef3c7;
    border: 1px solid #fbbf24;
    border-radius: 8px;
    color: #92400e;
    font-size: 13px;
    margin-bottom: 10px;
}

.menu-info {
    margin-top: 8px;
    color: #718096;
    font-size: 11px;
    font-style: italic;
}

.order-help-text {
    margin-top: 15px;
    padding: 12px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    font-size: 12px;
    color: #0c4a6e;
}

.order-help-text p {
    margin: 0 0 8px 0;
    font-weight: 600;
}

.order-help-text ol {
    margin: 0;
    padding-left: 20px;
}

.order-help-text li {
    margin-bottom: 4px;
}

@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: minmax(0, 1fr);
    }
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: minmax(0, 1fr);
    }
}
</style>

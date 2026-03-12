<template>
    <div class="restaurant-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Restaurant Management (POS)</h1>
                <p class="page-subtitle">
                    Manage restaurant menu, tables, orders, KOT, discounts, and payments from one place.
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

        <!-- Menu Management -->
        <div v-if="activeTab === 'menu'" class="tab-content">
            <div class="content-grid">
                <!-- Categories & Pricing -->
                <section class="content-card">
                    <div class="card-header">
                        <h2>Restaurant Menu Management</h2>
                        <p>Manage categories and menu items with pricing.</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Category Name</label>
                                <input v-model="categoryForm.name" type="text" placeholder="e.g. Starters" />
                            </div>
                            <div class="form-group">
                                <label>Display Order</label>
                                <input v-model.number="categoryForm.sort_order" type="number" min="1" />
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary btn-block" @click="saveCategory">
                                    {{ categoryForm.id ? 'Update Category' : 'Add Category' }}
                                </button>
                            </div>
                        </div>

                        <div class="chips-row">
                            <button
                                v-for="cat in categories"
                                :key="cat.id"
                                :class="['chip', { active: selectedCategoryId === cat.id }]"
                                @click="selectCategory(cat)"
                            >
                                {{ cat.name }}
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Menu Items -->
                <section class="content-card">
                    <div class="card-header">
                        <h2>Menu Items</h2>
                        <p>Add and manage menu items with categories & pricing.</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Item Name</label>
                                <input v-model="itemForm.name" type="text" placeholder="e.g. Caesar Salad" />
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select v-model="itemForm.category_id">
                                    <option :value="null">Select category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Price</label>
                                <input v-model.number="itemForm.price" type="number" step="0.01" min="0" />
                            </div>
                            <div class="form-group">
                                <label>Tax %</label>
                                <input v-model.number="itemForm.tax_rate" type="number" step="0.1" min="0" />
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select v-model="itemForm.is_active">
                                    <option :value="true">Active</option>
                                    <option :value="false">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea
                                v-model="itemForm.description"
                                rows="2"
                                placeholder="Short description, e.g. Lettuce, parmesan, house dressing"
                            ></textarea>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-secondary" @click="resetItemForm">Clear</button>
                            <button class="btn btn-primary" @click="saveItem">
                                {{ itemForm.id ? 'Update Item' : 'Add Item' }}
                            </button>
                        </div>

                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in filteredMenuItems" :key="item.id">
                                        <td>
                                            <strong>{{ item.name }}</strong>
                                            <small v-if="item.description">{{ item.description }}</small>
                                        </td>
                                        <td>{{ getCategoryName(item.category_id) }}</td>
                                        <td>${{ item.price.toFixed(2) }}</td>
                                        <td>
                                            <span :class="['badge', item.is_active ? 'badge-success' : 'badge-secondary']">
                                                {{ item.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline" @click="editItem(item)">Edit</button>
                                                <button class="btn btn-sm btn-danger" @click="removeItem(item.id)">Remove</button>
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

        <!-- Orders (Dine-in, Takeaway, Room-charge) -->
        <div v-if="activeTab === 'orders'" class="tab-content">
            <div class="content-grid">
                <section class="content-card">
                    <div class="card-header">
                        <h2>New Order</h2>
                        <p>Create dine-in, takeaway, or room-charge orders.</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Order Type</label>
                                <select v-model="orderForm.type">
                                    <option value="dine_in">Dine-in</option>
                                    <option value="takeaway">Takeaway</option>
                                    <option value="room_charge">Room-charge</option>
                                </select>
                            </div>
                            <div class="form-group" v-if="orderForm.type === 'dine_in'">
                                <label>Table</label>
                                <select v-model="orderForm.table_id">
                                    <option :value="null">Select table</option>
                                    <option v-for="table in tables" :key="table.id" :value="table.id">
                                        {{ table.name }} ({{ table.status }})
                                    </option>
                                </select>
                            </div>
                            <div class="form-group" v-if="orderForm.type === 'room_charge'">
                                <label>Room Number</label>
                                <input v-model="orderForm.room_number" type="text" placeholder="e.g. 305" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Guest Name</label>
                                <input v-model="orderForm.guest_name" type="text" placeholder="Optional" />
                            </div>
                            <div class="form-group">
                                <label>Discount %</label>
                                <input v-model.number="orderForm.discount_percent" type="number" min="0" max="100" />
                            </div>
                            <div class="form-group">
                                <label>Promotion</label>
                                <select v-model="orderForm.promotion_id">
                                    <option :value="null">None</option>
                                    <option v-for="promo in promotions" :key="promo.id" :value="promo.id">
                                        {{ promo.name }} ({{ promo.label }})
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Add Items</label>
                            <div class="menu-items-grid">
                                <button
                                    v-for="item in activeMenuItems"
                                    :key="item.id"
                                    class="menu-pill"
                                    @click="addItemToOrder(item)"
                                >
                                    <span class="menu-pill-name">{{ item.name }}</span>
                                    <span class="menu-pill-price">${{ item.price.toFixed(2) }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="order-items" v-if="orderForm.items.length">
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
                                    <tr v-for="(oi, idx) in orderForm.items" :key="oi.id + '-' + idx">
                                        <td>{{ oi.name }}</td>
                                        <td>
                                            <input
                                                type="number"
                                                min="1"
                                                v-model.number="oi.qty"
                                                @change="recalculateOrderTotals"
                                                class="qty-input"
                                            />
                                        </td>
                                        <td>${{ oi.price.toFixed(2) }}</td>
                                        <td>${{ (oi.price * oi.qty).toFixed(2) }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" @click="removeOrderItem(idx)">×</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="order-summary" v-if="orderForm.items.length">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>${{ orderTotals.subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="summary-row" v-if="orderTotals.discount > 0">
                                <span>Discount</span>
                                <span>- ${{ orderTotals.discount.toFixed(2) }}</span>
                            </div>
                            <div class="summary-row" v-if="orderTotals.promotion_discount > 0">
                                <span>Promotion</span>
                                <span>- ${{ orderTotals.promotion_discount.toFixed(2) }}</span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Total</span>
                                <span>${{ orderTotals.total.toFixed(2) }}</span>
                            </div>
                        </div>

                        <div class="form-row" v-if="orderForm.items.length">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select v-model="orderForm.payment_method">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="wallet">Wallet</option>
                                    <option value="mixed">Mixed</option>
                                </select>
                            </div>
                            <div class="form-group" v-if="orderForm.payment_method === 'mixed'">
                                <label>Mixed Payment Notes</label>
                                <input
                                    v-model="orderForm.payment_notes"
                                    type="text"
                                    placeholder="e.g. 50% cash, 50% card"
                                />
                            </div>
                        </div>

                        <div class="form-actions" v-if="orderForm.items.length">
                            <button class="btn btn-secondary" @click="resetOrderForm">Clear Order</button>
                            <button class="btn btn-primary" @click="placeOrder">Place Order</button>
                        </div>
                    </div>
                </section>

                <!-- Order Status Tracking -->
                <section class="content-card">
                    <div class="card-header">
                        <h2>Order Status Tracking</h2>
                        <p>Monitor dine-in, takeaway, and room-charge orders.</p>
                    </div>
                    <div class="card-body">
                        <div class="chips-row">
                            <button
                                v-for="filter in orderStatusFilters"
                                :key="filter.id"
                                :class="['chip', { active: activeOrderFilter === filter.id }]"
                                @click="activeOrderFilter = filter.id"
                            >
                                {{ filter.label }}
                            </button>
                        </div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Table / Room</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="order in filteredOrders" :key="order.id">
                                        <td>#{{ order.id }}</td>
                                        <td>{{ formatOrderType(order.type) }}</td>
                                        <td>
                                            <span v-if="order.type === 'dine_in'">
                                                {{ getTableName(order.table_id) }}
                                            </span>
                                            <span v-else-if="order.type === 'room_charge'">
                                                Room {{ order.room_number }}
                                            </span>
                                            <span v-else>Takeaway</span>
                                        </td>
                                        <td>{{ order.items.length }}</td>
                                        <td>
                                            <span :class="['badge', getOrderStatusClass(order.status)]">
                                                {{ order.status }}
                                            </span>
                                        </td>
                                        <td>${{ order.total.toFixed(2) }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <button
                                                    class="btn btn-sm btn-outline"
                                                    @click="advanceOrderStatus(order)"
                                                >
                                                    Next Status
                                                </button>
                                                <button
                                                    class="btn btn-sm btn-danger"
                                                    @click="cancelOrder(order)"
                                                >
                                                    Cancel
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

        <!-- Table Management & KOT -->
        <div v-if="activeTab === 'tables_kot'" class="tab-content">
            <div class="content-grid">
                <!-- Table Management -->
                <section class="content-card">
                    <div class="card-header">
                        <h2>Table Management</h2>
                        <p>Manage table layout and occupancy.</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Table Name / No.</label>
                                <input v-model="tableForm.name" type="text" placeholder="e.g. T01" />
                            </div>
                            <div class="form-group">
                                <label>Seats</label>
                                <input v-model.number="tableForm.seats" type="number" min="1" />
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary btn-block" @click="saveTable">
                                    {{ tableForm.id ? 'Update Table' : 'Add Table' }}
                                </button>
                            </div>
                        </div>

                        <div class="tables-grid">
                            <div
                                v-for="table in tables"
                                :key="table.id"
                                :class="['table-card', 'status-' + table.status]"
                                @click="editTable(table)"
                            >
                                <div class="table-name">{{ table.name }}</div>
                                <div class="table-seats">{{ table.seats }} seats</div>
                                <div class="table-status">{{ table.status }}</div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Kitchen Order Tickets (KOT) -->
                <section class="content-card">
                    <div class="card-header">
                        <h2>Kitchen Order Tickets (KOT)</h2>
                        <p>View and manage orders pending in kitchen.</p>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Table / Room</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="order in kotOrders" :key="order.id">
                                        <td>#{{ order.id }}</td>
                                        <td>{{ formatOrderType(order.type) }}</td>
                                        <td>
                                            <span v-if="order.type === 'dine_in'">{{ getTableName(order.table_id) }}</span>
                                            <span v-else-if="order.type === 'room_charge'">
                                                Room {{ order.room_number }}
                                            </span>
                                            <span v-else>Takeaway</span>
                                        </td>
                                        <td>
                                            <ul class="kot-items">
                                                <li v-for="it in order.items" :key="it.id">
                                                    {{ it.qty }} × {{ it.name }}
                                                </li>
                                            </ul>
                                        </td>
                                        <td>
                                            <span :class="['badge', getOrderStatusClass(order.status)]">
                                                {{ order.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" @click="markKotDone(order)">
                                                Mark as Done
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Discounts, Promotions & Payments Overview -->
        <div v-if="activeTab === 'promotions'" class="tab-content">
            <div class="content-grid">
                <section class="content-card">
                    <div class="card-header">
                        <h2>Discounts & Promotions</h2>
                        <p>Configure offers to be applied on restaurant bills.</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Promotion Name</label>
                                <input v-model="promoForm.name" type="text" placeholder="e.g. Happy Hour" />
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select v-model="promoForm.type">
                                    <option value="percent">Percent</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Value</label>
                                <input v-model.number="promoForm.value" type="number" min="0" step="0.01" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Code (optional)</label>
                                <input v-model="promoForm.code" type="text" placeholder="e.g. HAPPY50" />
                            </div>
                            <div class="form-group">
                                <label>Active</label>
                                <select v-model="promoForm.is_active">
                                    <option :value="true">Yes</option>
                                    <option :value="false">No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary btn-block" @click="savePromotion">
                                    {{ promoForm.id ? 'Update Promotion' : 'Add Promotion' }}
                                </button>
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Label</th>
                                        <th>Code</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="promo in promotions" :key="promo.id">
                                        <td>{{ promo.name }}</td>
                                        <td>{{ promo.label }}</td>
                                        <td>{{ promo.code || '-' }}</td>
                                        <td>
                                            <span :class="['badge', promo.is_active ? 'badge-success' : 'badge-secondary']">
                                                {{ promo.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline" @click="editPromotion(promo)">
                                                    Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger" @click="removePromotion(promo.id)">
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

                <section class="content-card">
                    <div class="card-header">
                        <h2>Payment Methods (Overview)</h2>
                        <p>Summary of recent orders by payment method.</p>
                    </div>
                    <div class="card-body">
                        <div class="summary-row" v-for="pm in paymentSummary" :key="pm.method">
                            <span>{{ pm.label }}</span>
                            <span>${{ pm.total.toFixed(2) }} ({{ pm.count }} orders)</span>
                        </div>
                        <p v-if="!paymentSummary.length" class="empty-state">
                            No orders yet. Place some orders to see payment summaries.
                        </p>
                    </div>
                </section>
            </div>
        </div>

        <!-- Room Service Module -->
        <div v-if="activeTab === 'room_service'" class="tab-content">
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
                            <div class="menu-items-grid">
                                <button
                                    v-for="item in activeMenuItems"
                                    :key="item.id"
                                    class="menu-pill"
                                    @click="addItemToRoomServiceOrder(item)"
                                >
                                    <span class="menu-pill-name">{{ item.name }}</span>
                                    <span class="menu-pill-price">${{ item.price.toFixed(2) }}</span>
                                </button>
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
                            <button class="btn btn-primary btn-block" @click="placeRoomServiceOrder" :disabled="!roomServiceForm.room_number">
                                Place Room Service Order
                            </button>
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
                                @click="roomServiceStatusFilter = status.value"
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
                                        <td>${{ order.total.toFixed(2) }}</td>
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
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';

export default {
    name: 'RestaurantOperations',
    setup() {
        const tabs = [
            { id: 'menu', label: 'Menu Management' },
            { id: 'orders', label: 'Orders' },
            { id: 'tables_kot', label: 'Tables & KOT' },
            { id: 'promotions', label: 'Discounts & Payments' },
            { id: 'room_service', label: 'Room Service Module' },
        ];
        const activeTab = ref('menu');

        // Categories
        const categories = ref([
            { id: 1, name: 'Starters', sort_order: 1 },
            { id: 2, name: 'Main Course', sort_order: 2 },
            { id: 3, name: 'Desserts', sort_order: 3 },
        ]);
        const categoryForm = ref({
            id: null,
            name: '',
            sort_order: 1,
        });
        const selectedCategoryId = ref(null);

        const selectCategory = (cat) => {
            selectedCategoryId.value = cat.id;
            categoryForm.value = { ...cat };
        };

        const resetCategoryForm = () => {
            categoryForm.value = {
                id: null,
                name: '',
                sort_order: categories.value.length + 1,
            };
        };

        const saveCategory = () => {
            if (!categoryForm.value.name) return;
            if (categoryForm.value.id) {
                const idx = categories.value.findIndex(c => c.id === categoryForm.value.id);
                if (idx !== -1) categories.value[idx] = { ...categoryForm.value };
            } else {
                const newId = Math.max(...categories.value.map(c => c.id), 0) + 1;
                categories.value.push({
                    id: newId,
                    name: categoryForm.value.name,
                    sort_order: categoryForm.value.sort_order || categories.value.length + 1,
                });
            }
            resetCategoryForm();
        };

        // Menu items
        const menuItems = ref([
            { id: 1, name: 'Tomato Soup', description: 'Fresh tomatoes, herbs', category_id: 1, price: 4.5, tax_rate: 5, is_active: true },
            { id: 2, name: 'Grilled Chicken', description: 'Served with veggies', category_id: 2, price: 12.0, tax_rate: 5, is_active: true },
            { id: 3, name: 'Chocolate Cake', description: 'Rich dark chocolate', category_id: 3, price: 6.0, tax_rate: 5, is_active: true },
        ]);
        const itemForm = ref({
            id: null,
            name: '',
            description: '',
            category_id: null,
            price: 0,
            tax_rate: 0,
            is_active: true,
        });

        const getCategoryName = (id) => {
            return categories.value.find(c => c.id === id)?.name || '-';
        };

        const filteredMenuItems = computed(() => {
            if (!selectedCategoryId.value) return menuItems.value;
            return menuItems.value.filter(i => i.category_id === selectedCategoryId.value);
        });

        const resetItemForm = () => {
            itemForm.value = {
                id: null,
                name: '',
                description: '',
                category_id: selectedCategoryId.value || null,
                price: 0,
                tax_rate: 0,
                is_active: true,
            };
        };

        const saveItem = () => {
            if (!itemForm.value.name || !itemForm.value.category_id) return;
            if (itemForm.value.id) {
                const idx = menuItems.value.findIndex(i => i.id === itemForm.value.id);
                if (idx !== -1) menuItems.value[idx] = { ...itemForm.value };
            } else {
                const newId = Math.max(...menuItems.value.map(i => i.id), 0) + 1;
                menuItems.value.push({
                    ...itemForm.value,
                    id: newId,
                });
            }
            resetItemForm();
        };

        const editItem = (item) => {
            itemForm.value = { ...item };
            selectedCategoryId.value = item.category_id;
        };

        const removeItem = (id) => {
            menuItems.value = menuItems.value.filter(i => i.id !== id);
        };

        const activeMenuItems = computed(() =>
            menuItems.value.filter(i => i.is_active)
        );

        // Tables
        const tables = ref([
            { id: 1, name: 'T01', seats: 4, status: 'available' },
            { id: 2, name: 'T02', seats: 2, status: 'occupied' },
            { id: 3, name: 'T03', seats: 6, status: 'reserved' },
        ]);
        const tableForm = ref({
            id: null,
            name: '',
            seats: 4,
            status: 'available',
        });

        const saveTable = () => {
            if (!tableForm.value.name) return;
            if (tableForm.value.id) {
                const idx = tables.value.findIndex(t => t.id === tableForm.value.id);
                if (idx !== -1) tables.value[idx] = { ...tableForm.value };
            } else {
                const newId = Math.max(...tables.value.map(t => t.id), 0) + 1;
                tables.value.push({
                    ...tableForm.value,
                    id: newId,
                });
            }
            tableForm.value = { id: null, name: '', seats: 4, status: 'available' };
        };

        const editTable = (table) => {
            tableForm.value = { ...table };
        };

        const getTableName = (id) => {
            return tables.value.find(t => t.id === id)?.name || '-';
        };

        // Promotions / discounts
        const promotions = ref([
            { id: 1, name: 'Happy Hour', type: 'percent', value: 10, code: 'HAPPY10', is_active: true, label: '10% OFF' },
        ]);
        const promoForm = ref({
            id: null,
            name: '',
            type: 'percent',
            value: 0,
            code: '',
            is_active: true,
        });

        const savePromotion = () => {
            if (!promoForm.value.name || !promoForm.value.value) return;
            const label = promoForm.value.type === 'percent'
                ? `${promoForm.value.value}% OFF`
                : `$${promoForm.value.value.toFixed(2)} OFF`;
            if (promoForm.value.id) {
                const idx = promotions.value.findIndex(p => p.id === promoForm.value.id);
                if (idx !== -1) promotions.value[idx] = { ...promoForm.value, label };
            } else {
                const newId = Math.max(...promotions.value.map(p => p.id), 0) + 1;
                promotions.value.push({ ...promoForm.value, id: newId, label });
            }
            promoForm.value = { id: null, name: '', type: 'percent', value: 0, code: '', is_active: true };
        };

        const editPromotion = (promo) => {
            promoForm.value = { ...promo };
        };

        const removePromotion = (id) => {
            promotions.value = promotions.value.filter(p => p.id !== id);
        };

        // Orders
        const orders = ref([]);
        const lastOrderId = ref(1000);
        const orderForm = ref({
            id: null,
            type: 'dine_in',
            table_id: null,
            room_number: '',
            guest_name: '',
            items: [],
            discount_percent: 0,
            promotion_id: null,
            payment_method: 'cash',
            payment_notes: '',
            status: 'NEW',
            total: 0,
        });

        const orderTotals = ref({
            subtotal: 0,
            discount: 0,
            promotion_discount: 0,
            total: 0,
        });

        const recalculateOrderTotals = () => {
            const subtotal = orderForm.value.items.reduce(
                (sum, it) => sum + it.price * it.qty,
                0
            );
            let discount = 0;
            if (orderForm.value.discount_percent > 0) {
                discount = (subtotal * orderForm.value.discount_percent) / 100;
            }
            let promoDiscount = 0;
            const promo = promotions.value.find(
                p => p.id === orderForm.value.promotion_id && p.is_active
            );
            if (promo) {
                if (promo.type === 'percent') {
                    promoDiscount = (subtotal * promo.value) / 100;
                } else {
                    promoDiscount = promo.value;
                }
            }
            const total = Math.max(subtotal - discount - promoDiscount, 0);
            orderTotals.value = {
                subtotal,
                discount,
                promotion_discount: promoDiscount,
                total,
            };
            orderForm.value.total = total;
        };

        const addItemToOrder = (item) => {
            const existing = orderForm.value.items.find(it => it.id === item.id);
            if (existing) {
                existing.qty += 1;
            } else {
                orderForm.value.items.push({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    qty: 1,
                });
            }
            recalculateOrderTotals();
        };

        const removeOrderItem = (idx) => {
            orderForm.value.items.splice(idx, 1);
            recalculateOrderTotals();
        };

        const resetOrderForm = () => {
            orderForm.value = {
                id: null,
                type: 'dine_in',
                table_id: null,
                room_number: '',
                guest_name: '',
                items: [],
                discount_percent: 0,
                promotion_id: null,
                payment_method: 'cash',
                payment_notes: '',
                status: 'NEW',
                total: 0,
            };
            orderTotals.value = {
                subtotal: 0,
                discount: 0,
                promotion_discount: 0,
                total: 0,
            };
        };

        const placeOrder = () => {
            if (!orderForm.value.items.length) return;
            const newId = ++lastOrderId.value;
            const newOrder = {
                ...orderForm.value,
                id: newId,
                created_at: new Date().toISOString(),
            };
            orders.value.unshift(newOrder);
            resetOrderForm();
        };

        const orderStatusSequence = ['NEW', 'KOT', 'SERVED', 'PAID', 'CANCELLED'];

        const advanceOrderStatus = (order) => {
            const idx = orderStatusSequence.indexOf(order.status);
            if (idx === -1 || order.status === 'PAID' || order.status === 'CANCELLED') return;
            order.status = orderStatusSequence[idx + 1] || order.status;
        };

        const cancelOrder = (order) => {
            order.status = 'CANCELLED';
        };

        const orderStatusFilters = [
            { id: 'all', label: 'All' },
            { id: 'NEW', label: 'New' },
            { id: 'KOT', label: 'In Kitchen' },
            { id: 'SERVED', label: 'Served' },
            { id: 'PAID', label: 'Paid' },
        ];
        const activeOrderFilter = ref('all');

        const filteredOrders = computed(() => {
            if (activeOrderFilter.value === 'all') return orders.value;
            return orders.value.filter(o => o.status === activeOrderFilter.value);
        });

        const kotOrders = computed(() =>
            orders.value.filter(o => o.status === 'NEW' || o.status === 'KOT')
        );

        const markKotDone = (order) => {
            order.status = 'SERVED';
        };

        const formatOrderType = (type) => {
            if (type === 'dine_in') return 'Dine-in';
            if (type === 'takeaway') return 'Takeaway';
            if (type === 'room_charge') return 'Room-charge';
            return type;
        };

        const getOrderStatusClass = (status) => {
            const map = {
                NEW: 'badge-warning',
                KOT: 'badge-info',
                SERVED: 'badge-success',
                PAID: 'badge-secondary',
                CANCELLED: 'badge-danger',
            };
            return map[status] || 'badge-secondary';
        };

        // Payment summary (from orders)
        const paymentSummary = computed(() => {
            const methods = {
                cash: { label: 'Cash', total: 0, count: 0 },
                card: { label: 'Card', total: 0, count: 0 },
                wallet: { label: 'Wallet', total: 0, count: 0 },
                mixed: { label: 'Mixed', total: 0, count: 0 },
            };
            orders.value
                .filter(o => o.status === 'PAID')
                .forEach(o => {
                    const key = o.payment_method || 'cash';
                    if (!methods[key]) return;
                    methods[key].total += o.total || 0;
                    methods[key].count += 1;
                });
            return Object.values(methods).filter(m => m.count > 0);
        });

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

        const loadRoomDetails = () => {
            // Simulate loading room details from reservation
            // In real implementation, this would call an API
            if (roomServiceForm.value.room_number) {
                // Mock data - in real app, fetch from /api/reservations?room_number=...
                const mockReservation = {
                    guest_name: 'Guest ' + roomServiceForm.value.room_number,
                    reservation_id: 'RES' + roomServiceForm.value.room_number,
                };
                roomServiceForm.value.guest_name = mockReservation.guest_name;
                roomServiceForm.value.reservation_id = mockReservation.reservation_id;
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

        const placeRoomServiceOrder = () => {
            if (!roomServiceForm.value.room_number || !roomServiceForm.value.items.length) return;
            
            const newOrder = {
                id: lastRoomServiceOrderId.value++,
                room_number: roomServiceForm.value.room_number,
                guest_name: roomServiceForm.value.guest_name,
                reservation_id: roomServiceForm.value.reservation_id,
                items: [...roomServiceForm.value.items],
                total: roomServiceTotals.value.total,
                status: 'NEW',
                assigned_staff_id: null,
                order_time: new Date(),
                kitchen_time: null,
                delivery_time: null,
                delivered_time: null,
                charges_posted: false,
            };
            
            roomServiceOrders.value.push(newOrder);
            
            // Reset form but keep room details
            roomServiceForm.value.items = [];
            roomServiceForm.value.total = 0;
            roomServiceTotals.value = { subtotal: 0, tax: 0, total: 0 };
            
            alert(`Room Service Order #${newOrder.id} placed successfully!`);
        };

        const routeToKitchen = (order) => {
            order.status = 'KOT';
            order.kitchen_time = new Date();
        };

        const updateRoomServiceStatus = (order, newStatus) => {
            order.status = newStatus;
            if (newStatus === 'PREPARING' && !order.kitchen_time) {
                order.kitchen_time = new Date();
            }
            if (newStatus === 'OUT_FOR_DELIVERY') {
                order.delivery_time = new Date();
            }
        };

        const markDelivered = (order) => {
            order.status = 'DELIVERED';
            order.delivered_time = new Date();
        };

        const postChargesToRoom = (order) => {
            if (confirm(`Post charges of $${order.total.toFixed(2)} to room ${order.room_number} bill?`)) {
                order.charges_posted = true;
                // In real implementation, this would call an API to post charges to reservation
                alert(`Charges posted to room ${order.room_number} bill successfully!`);
            }
        };

        const assignStaffToRoomServiceOrder = (order) => {
            // Staff assignment is handled via v-model on the select
            if (order.assigned_staff_id) {
                const staff = serviceStaff.value.find(s => s.id === order.assigned_staff_id);
                if (staff) {
                    console.log(`Order #${order.id} assigned to ${staff.name}`);
                }
            }
        };

        const filteredRoomServiceOrders = computed(() => {
            if (roomServiceStatusFilter.value === 'all') {
                return roomServiceOrders.value;
            }
            return roomServiceOrders.value.filter(o => o.status === roomServiceStatusFilter.value);
        });

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
            // Could load initial data from API in future; currently using in-memory demo data.
        });

        return {
            tabs,
            activeTab,
            // Menu
            categories,
            categoryForm,
            selectedCategoryId,
            selectCategory,
            saveCategory,
            menuItems,
            itemForm,
            filteredMenuItems,
            getCategoryName,
            resetItemForm,
            saveItem,
            editItem,
            removeItem,
            activeMenuItems,
            // Tables & KOT
            tables,
            tableForm,
            saveTable,
            editTable,
            getTableName,
            kotOrders,
            markKotDone,
            // Promotions
            promotions,
            promoForm,
            savePromotion,
            editPromotion,
            removePromotion,
            // Orders
            orders,
            orderForm,
            orderTotals,
            addItemToOrder,
            removeOrderItem,
            resetOrderForm,
            placeOrder,
            orderStatusFilters,
            activeOrderFilter,
            filteredOrders,
            advanceOrderStatus,
            cancelOrder,
            formatOrderType,
            getOrderStatusClass,
            // Payment summary
            paymentSummary,
            // Room Service Module
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
.restaurant-page {
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

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 8px;
}

.chips-row {
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

.summary-total span:last-child {
    font-weight: 700;
}

.tables-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.table-card {
    width: 120px;
    border-radius: 10px;
    padding: 8px 10px;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    background: #f9fafb;
    font-size: 12px;
}

.table-card .table-name {
    font-weight: 600;
    margin-bottom: 2px;
}

.table-card .table-seats {
    color: #718096;
    margin-bottom: 2px;
}

.table-card .table-status {
    font-size: 11px;
    text-transform: capitalize;
}

.table-card.status-available {
    border-color: #34d399;
}

.table-card.status-occupied {
    border-color: #fb923c;
}

.table-card.status-reserved {
    border-color: #60a5fa;
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

.badge-danger {
    background: #fee2e2;
    color: #b91c1c;
}

.kot-items {
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 11px;
}

.kot-items li {
    margin-bottom: 2px;
}

.summary-row.empty-state {
    color: #718096;
}

.filter-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
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

.badge-primary {
    background: #dbeafe;
    color: #1e40af;
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

.empty-state {
    text-align: center;
    padding: 20px;
    color: #718096;
    font-size: 13px;
}

.summary-row.info {
    margin-top: 6px;
    padding-top: 6px;
    border-top: 1px dashed #e2e8f0;
    color: #4c6fff;
    font-style: italic;
}

.summary-row.total {
    font-weight: 600;
    margin-top: 4px;
    padding-top: 4px;
    border-top: 1px solid #e2e8f0;
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


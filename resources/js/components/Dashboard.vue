<template>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="app-sidebar" :class="{ 'sidebar-mini': sidebarMini }">
            <div class="sidebar-header">
                <div class="logo-wrapper">
                    <img
                        v-if="hotelLogo"
                        :src="hotelLogo"
                        alt="Hotel Logo"
                        class="logo-image"
                        @error="handleImageError"
                    />
                    <div v-if="!sidebarMini" class="logo-text-wrapper">
                        <span class="logo-text" v-if="hotelName">{{ hotelName }}</span>
                    </div>
                </div>
            </div>

            <nav class="sidebar-menu">
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="menu-item"
                    :class="{ active: isDashboardActive }"
                    exact-active-class="active"
                >
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M3 10L9 4L15 10M5 10V16H15V10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span v-if="!sidebarMini">Dashboard</span>
                </router-link>

                <!-- Users & Permissions Dropdown -->
                <div
                    v-if="(hasPermission('view_users') || hasPermission('view_roles') || hasPermission('manage_system') || isSuperAdmin)"
                    class="menu-dropdown"
                    :class="{ 'dropdown-open': isUsersPermissionsDropdownOpen, 'active': isUsersPermissionsActive }"
                >
                    <button
                        class="menu-item menu-dropdown-toggle"
                        @click="toggleUsersPermissionsDropdown"
                    >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 10C12.7614 10 15 7.76142 15 5C15 2.23858 12.7614 0 10 0C7.23858 0 5 2.23858 5 5C5 7.76142 7.23858 10 10 10Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M10 12C5.58172 12 2 13.7909 2 16V20H18V16C18 13.7909 14.4183 12 10 12Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <span v-if="!sidebarMini">Users & Permissions</span>
                        <svg
                            v-if="!sidebarMini"
                            width="16"
                            height="16"
                            viewBox="0 0 16 16"
                            fill="none"
                            class="dropdown-arrow"
                            :class="{ 'rotated': isUsersPermissionsDropdownOpen }"
                        >
                            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div v-if="isUsersPermissionsDropdownOpen && !sidebarMini" class="menu-dropdown-content">
                        <router-link
                            v-if="hasPermission('view_users') || isSuperAdmin"
                            :to="{ name: 'users' }"
                            class="menu-dropdown-item"
                            :class="{ active: isUsersActive }"
                            @click="closeUsersPermissionsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 10C12.7614 10 15 7.76142 15 5C15 2.23858 12.7614 0 10 0C7.23858 0 5 2.23858 5 5C5 7.76142 7.23858 10 10 10Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 12C5.58172 12 2 13.7909 2 16V20H18V16C18 13.7909 14.4183 12 10 12Z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <span>Users</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_roles') || isSuperAdmin"
                            :to="{ name: 'roles' }"
                            class="menu-dropdown-item"
                            :class="{ active: isRolesActive }"
                            @click="closeUsersPermissionsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 5V9C3 13.55 6.36 17.74 10 19C13.64 17.74 17 13.55 17 9V5L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 10C11.1046 10 12 9.10457 12 8C12 6.89543 11.1046 6 10 6C8.89543 6 8 6.89543 8 8C8 9.10457 8.89543 10 10 10Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 10V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Roles & Permissions</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_system') || isSuperAdmin"
                            :to="{ name: 'permissions' }"
                            class="menu-dropdown-item"
                            :class="{ active: isPermissionsActive }"
                            @click="closeUsersPermissionsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M9 2L2 5V9C2 13.55 5.36 17.74 9 19C12.64 17.74 16 13.55 16 9V5L9 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 6V10M9 10L12 7M9 10L6 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Permissions</span>
                        </router-link>
                    </div>
                </div>

                <!-- Hotel Operations Dropdown -->
                <div
                    v-if="hasPermission('manage_restaurant') || hasPermission('view_rooms') || hasPermission('view_bookings') || hasPermission('manage_amenities') || hasPermission('manage_housekeeping') || isSuperAdmin"
                    class="menu-dropdown"
                    :class="{ 'dropdown-open': isHotelOperationsDropdownOpen, 'active': isHotelOperationsActive }"
                >
                    <button
                        class="menu-item menu-dropdown-toggle"
                        @click="toggleHotelOperationsDropdown"
                    >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M3 4H17V16H3V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3 8H17M10 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7 12H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span v-if="!sidebarMini">Hotel Operations</span>
                        <svg
                            v-if="!sidebarMini"
                            width="16"
                            height="16"
                            viewBox="0 0 16 16"
                            fill="none"
                            class="dropdown-arrow"
                            :class="{ 'rotated': isHotelOperationsDropdownOpen }"
                        >
                            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div v-if="isHotelOperationsDropdownOpen && !sidebarMini" class="menu-dropdown-content">
                        <router-link
                            v-if="hasPermission('manage_restaurant') || isSuperAdmin"
                            :to="{ name: 'restaurantOperations' }"
                            class="menu-dropdown-item"
                            :class="{ active: isRestaurantOperationsActive }"
                            @click="closeHotelOperationsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <rect x="2" y="5" width="16" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M5 5V3C5 2.44772 5.44772 2 6 2H8V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M12 5V3C12 2.44772 12.4477 2 13 2H15V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M4.5 9H15.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Restaurant Management (POS)</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_rooms') || hasPermission('manage_rooms') || isSuperAdmin"
                            :to="{ name: 'rooms' }"
                            class="menu-dropdown-item"
                            :class="{ active: isRoomsActive }"
                            @click="closeHotelOperationsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 8H18M6 8V16M14 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 4V8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Room Management</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_bookings') || hasPermission('create_bookings') || hasPermission('edit_bookings') || isSuperAdmin"
                            :to="{ name: 'roomReservations' }"
                            class="menu-dropdown-item"
                            :class="{ active: isRoomReservationsActive }"
                            @click="closeHotelOperationsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M3 4H17V16H3V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3 8H17M7 8V16M13 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 4V8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M5 12H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Room & Reservation Management</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_bookings') || hasPermission('checkin_guests') || hasPermission('checkout_guests') || isSuperAdmin"
                            :to="{ name: 'frontDeskOperations' }"
                            class="menu-dropdown-item"
                            :class="{ active: isFrontDeskOperationsActive }"
                            @click="closeHotelOperationsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <rect x="2" y="4" width="16" height="12" rx="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M2 9H18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M7 13H9.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M12 13H14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Front Desk Operations</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_amenities') || isSuperAdmin"
                            :to="{ name: 'amenitiesServices' }"
                            class="menu-dropdown-item"
                            :class="{ active: isAmenitiesServicesActive }"
                            @click="closeHotelOperationsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 5V9C3 13.55 6.36 17.74 10 19C13.64 17.74 17 13.55 17 9V5L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 10C11.1046 10 12 9.10457 12 8C12 6.89543 11.1046 6 10 6C8.89543 6 8 6.89543 8 8C8 9.10457 8.89543 10 10 10Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 10V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="15" cy="5" r="1.5" fill="currentColor"/>
                            </svg>
                            <span>Amenities & Services Management</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_housekeeping') || isSuperAdmin"
                            :to="{ name: 'housekeeping' }"
                            class="menu-dropdown-item"
                            :class="{ active: isHousekeepingActive }"
                            @click="closeHotelOperationsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 8H18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M6 4V2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M14 4V2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="6" cy="12" r="1" fill="currentColor"/>
                                <circle cx="10" cy="12" r="1" fill="currentColor"/>
                                <circle cx="14" cy="12" r="1" fill="currentColor"/>
                            </svg>
                            <span>Housekeeping Module</span>
                        </router-link>
                    </div>
                </div>

                <!-- Stock and Inventory Dropdown -->
                <div
                    v-if="hasPermission('view_inventory') || hasPermission('manage_inventory') || hasPermission('manage_suppliers') || isSuperAdmin"
                    class="menu-dropdown"
                    :class="{ 'dropdown-open': isStockInventoryDropdownOpen, 'active': isStockInventoryActive }"
                >
                    <button
                        class="menu-item menu-dropdown-toggle"
                        @click="toggleStockInventoryDropdown"
                    >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M3 4H17V16H3V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3 8H17M7 8V16M13 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 4V8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M5 12H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span v-if="!sidebarMini">Stock and Inventory</span>
                        <svg
                            v-if="!sidebarMini"
                            width="16"
                            height="16"
                            viewBox="0 0 16 16"
                            fill="none"
                            class="dropdown-arrow"
                            :class="{ 'rotated': isStockInventoryDropdownOpen }"
                        >
                            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div v-if="isStockInventoryDropdownOpen && !sidebarMini" class="menu-dropdown-content">
                        <router-link
                            v-if="hasPermission('view_inventory') || hasPermission('manage_inventory') || isSuperAdmin"
                            :to="{ name: 'inventoryStore' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isInventoryStoreActive }"
                            @click="closeStockInventoryDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M3 4H17V16H3V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3 8H17M7 8V16M13 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 4V8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M5 12H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Inventory & Store Management</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_inventory') || hasPermission('manage_housekeeping') || isSuperAdmin"
                            :to="{ name: 'linenHousekeeping' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isLinenHousekeepingActive }"
                            @click="closeStockInventoryDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 8H18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M6 4V2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M14 4V2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="6" cy="12" r="1" fill="currentColor"/>
                                <circle cx="10" cy="12" r="1" fill="currentColor"/>
                                <circle cx="14" cy="12" r="1" fill="currentColor"/>
                            </svg>
                            <span>Linen & Housekeeping Items</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_inventory') || hasPermission('manage_amenities') || isSuperAdmin"
                            :to="{ name: 'amenitiesConsumables' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isAmenitiesConsumablesActive }"
                            @click="closeStockInventoryDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 5V9C3 13.55 6.36 17.74 10 19C13.64 17.74 17 13.55 17 9V5L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 10C11.1046 10 12 9.10457 12 8C12 6.89543 11.1046 6 10 6C8.89543 6 8 6.89543 8 8C8 9.10457 8.89543 10 10 10Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 10V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="15" cy="5" r="1.5" fill="currentColor"/>
                            </svg>
                            <span>Amenities Consumables</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_inventory') || isSuperAdmin"
                            :to="{ name: 'stockInOut' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isStockInOutActive }"
                            @click="closeStockInventoryDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M4 4H16V16H4V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 8L10 6L12 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 12L10 14L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 6V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Stock In / Out</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_suppliers') || isSuperAdmin"
                            :to="{ name: 'supplierManagement' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isSupplierManagementActive }"
                            @click="closeStockInventoryDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 7C2 5.34315 3.34315 4 5 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M2 11C2 12.6569 3.34315 14 5 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M2 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Supplier Management</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_inventory') || hasPermission('manage_inventory') || isSuperAdmin"
                            :to="{ name: 'stockAlertsTracking' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isStockAlertsTrackingActive }"
                            @click="closeStockInventoryDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 5V9C3 13.55 6.36 17.74 10 19C13.64 17.74 17 13.55 17 9V5L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 10V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="10" cy="7" r="1" fill="currentColor"/>
                                <circle cx="15" cy="5" r="1.5" fill="currentColor"/>
                            </svg>
                            <span>Stock Alerts & Tracking</span>
                        </router-link>
                    </div>
                </div>

                <!-- Staff & Shift Management Dropdown -->
                <div
                    v-if="hasPermission('manage_staff') || hasPermission('manage_shifts') || hasPermission('view_attendance') || isSuperAdmin"
                    class="menu-dropdown"
                    :class="{ 'dropdown-open': isStaffShiftDropdownOpen, 'active': isStaffShiftActive }"
                >
                    <button
                        class="menu-item menu-dropdown-toggle"
                        @click="toggleStaffShiftDropdown"
                    >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 2C8.89543 2 8 2.89543 8 4C8 5.10457 8.89543 6 10 6C11.1046 6 12 5.10457 12 4C12 2.89543 11.1046 2 10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 8C7.79086 8 6 9.79086 6 12V14H14V12C14 9.79086 12.2091 8 10 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3 18H17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M2 10H4M16 10H18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="3" cy="10" r="1" fill="currentColor"/>
                            <circle cx="17" cy="10" r="1" fill="currentColor"/>
                        </svg>
                        <span v-if="!sidebarMini">Staff & Shift Management</span>
                        <svg
                            v-if="!sidebarMini"
                            width="16"
                            height="16"
                            viewBox="0 0 16 16"
                            fill="none"
                            class="dropdown-arrow"
                            :class="{ 'rotated': isStaffShiftDropdownOpen }"
                        >
                            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div v-if="isStaffShiftDropdownOpen && !sidebarMini" class="menu-dropdown-content">
                        <router-link
                            v-if="hasPermission('manage_staff') || isSuperAdmin"
                            :to="{ name: 'staffProfiles' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isStaffProfilesActive }"
                            @click="closeStaffShiftDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2C8.89543 2 8 2.89543 8 4C8 5.10457 8.89543 6 10 6C11.1046 6 12 5.10457 12 4C12 2.89543 11.1046 2 10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 8C7.79086 8 6 9.79086 6 12V14H14V12C14 9.79086 12.2091 8 10 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3 18H17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Staff Profiles</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_staff') || hasPermission('view_roles') || isSuperAdmin"
                            :to="{ name: 'roleAssignment' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isRoleAssignmentActive }"
                            @click="closeStaffShiftDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 5V9C3 13.55 6.36 17.74 10 19C13.64 17.74 17 13.55 17 9V5L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 10C11.1046 10 12 9.10457 12 8C12 6.89543 11.1046 6 10 6C8.89543 6 8 6.89543 8 8C8 9.10457 8.89543 10 10 10Z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <span>Role Assignment</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_shifts') || isSuperAdmin"
                            :to="{ name: 'shiftScheduling' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isShiftSchedulingActive }"
                            @click="closeStaffShiftDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 6V10L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Shift Scheduling</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_attendance') || hasPermission('manage_staff') || isSuperAdmin"
                            :to="{ name: 'attendance' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isAttendanceActive }"
                            @click="closeStaffShiftDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 5V9C3 13.55 6.36 17.74 10 19C13.64 17.74 17 13.55 17 9V5L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 10V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="10" cy="7" r="1" fill="currentColor"/>
                            </svg>
                            <span>Attendance</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_housekeeping') || hasPermission('manage_staff') || isSuperAdmin"
                            :to="{ name: 'taskAssignment' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isTaskAssignmentActive }"
                            @click="closeStaffShiftDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M3 4H17V16H3V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3 8H17M7 8V16M13 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 4V8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Task Assignment</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_staff') || isSuperAdmin"
                            :to="{ name: 'performanceLogs' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isPerformanceLogsActive }"
                            @click="closeStaffShiftDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M3 4H17V16H3V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3 8H17M7 8V16M13 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M5 12H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Performance Logs</span>
                        </router-link>
                    </div>
                </div>

                <!-- Billing & Finance Dropdown -->
                <div
                    v-if="hasPermission('view_billing') || hasPermission('manage_billing') || hasPermission('manage_accounts') || isSuperAdmin"
                    class="menu-dropdown"
                    :class="{ 'dropdown-open': isBillingFinanceDropdownOpen, 'active': isBillingFinanceActive }"
                >
                    <button
                        class="menu-item menu-dropdown-toggle"
                        @click="toggleBillingFinanceDropdown"
                    >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 8H18M6 8V16M14 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 4V8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="5" cy="12" r="1" fill="currentColor"/>
                            <circle cx="15" cy="12" r="1" fill="currentColor"/>
                        </svg>
                        <span v-if="!sidebarMini">Billing & Finance</span>
                        <svg
                            v-if="!sidebarMini"
                            width="16"
                            height="16"
                            viewBox="0 0 16 16"
                            fill="none"
                            class="dropdown-arrow"
                            :class="{ 'rotated': isBillingFinanceDropdownOpen }"
                        >
                            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div v-if="isBillingFinanceDropdownOpen && !sidebarMini" class="menu-dropdown-content">
                        <router-link
                            v-if="hasPermission('view_billing') || hasPermission('manage_billing') || isSuperAdmin"
                            :to="{ name: 'unifiedGuestFolio' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isUnifiedGuestFolioActive }"
                            @click="closeBillingFinanceDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 8H18M6 8V16M14 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Unified Guest Folio</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_billing') || isSuperAdmin"
                            :to="{ name: 'splitBilling' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isSplitBillingActive }"
                            @click="closeBillingFinanceDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2V18M2 10H18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="5" cy="5" r="1.5" fill="currentColor"/>
                                <circle cx="15" cy="5" r="1.5" fill="currentColor"/>
                                <circle cx="5" cy="15" r="1.5" fill="currentColor"/>
                                <circle cx="15" cy="15" r="1.5" fill="currentColor"/>
                            </svg>
                            <span>Split Billing</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_payments') || hasPermission('manage_billing') || isSuperAdmin"
                            :to="{ name: 'partialPayments' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isPartialPaymentsActive }"
                            @click="closeBillingFinanceDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Partial Payments</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_payments') || hasPermission('manage_billing') || isSuperAdmin"
                            :to="{ name: 'advancePayments' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isAdvancePaymentsActive }"
                            @click="closeBillingFinanceDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 5V9C3 13.55 6.36 17.74 10 19C13.64 17.74 17 13.55 17 9V5L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 10V14M10 6V10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Advance Payments</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_payments') || hasPermission('manage_billing') || isSuperAdmin"
                            :to="{ name: 'refundHandling' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isRefundHandlingActive }"
                            @click="closeBillingFinanceDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M18 10L2 10M10 2L2 10L10 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Refund Handling</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_billing') || hasPermission('manage_accounts') || isSuperAdmin"
                            :to="{ name: 'taxServiceCharge' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isTaxServiceChargeActive }"
                            @click="closeBillingFinanceDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2V18M2 10H18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <span>Tax & Service Charge</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_accounts') || hasPermission('view_financial_reports') || isSuperAdmin"
                            :to="{ name: 'expenseTracking' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isExpenseTrackingActive }"
                            @click="closeBillingFinanceDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Expense Tracking</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_payments') || hasPermission('manage_accounts') || isSuperAdmin"
                            :to="{ name: 'supplierPayments' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isSupplierPaymentsActive }"
                            @click="closeBillingFinanceDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="15" cy="12" r="1.5" fill="currentColor"/>
                            </svg>
                            <span>Supplier Payments</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_accounts') || hasPermission('view_financial_reports') || isSuperAdmin"
                            :to="{ name: 'dailyCashClosing' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isDailyCashClosingActive }"
                            @click="closeBillingFinanceDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 6V10L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Daily Cash Closing</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_financial_reports') || hasPermission('view_reports') || isSuperAdmin"
                            :to="{ name: 'profitLossReport' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isProfitLossReportActive }"
                            @click="closeBillingFinanceDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M4 2V6M16 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Profit & Loss Report</span>
                        </router-link>
                    </div>
                </div>

                <!-- Reports & Analytics Dropdown -->
                <div
                    v-if="hasPermission('view_reports') || isSuperAdmin"
                    class="menu-dropdown"
                    :class="{ 'dropdown-open': isReportsAnalyticsDropdownOpen, 'active': isReportsAnalyticsActive }"
                >
                    <button
                        class="menu-item menu-dropdown-toggle"
                        @click="toggleReportsAnalyticsDropdown"
                    >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M4 2V6M16 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="15" cy="12" r="1.5" fill="currentColor"/>
                        </svg>
                        <span v-if="!sidebarMini">Reports & Analytics</span>
                        <svg
                            v-if="!sidebarMini"
                            width="16"
                            height="16"
                            viewBox="0 0 16 16"
                            fill="none"
                            class="dropdown-arrow"
                            :class="{ 'rotated': isReportsAnalyticsDropdownOpen }"
                        >
                            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div v-if="isReportsAnalyticsDropdownOpen && !sidebarMini" class="menu-dropdown-content">
                        <router-link
                            v-if="hasPermission('view_reports') || isSuperAdmin"
                            :to="{ name: 'occupancyRateReport' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isOccupancyRateReportActive }"
                            @click="closeReportsAnalyticsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Occupancy Rate</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_reports') || hasPermission('view_financial_reports') || isSuperAdmin"
                            :to="{ name: 'dailyMonthlyRevenueReport' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isDailyMonthlyRevenueReportActive }"
                            @click="closeReportsAnalyticsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Daily/Monthly Revenue</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_reports') || isSuperAdmin"
                            :to="{ name: 'roomTypePerformanceReport' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isRoomTypePerformanceReportActive }"
                            @click="closeReportsAnalyticsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Room Type Performance</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_reports') || hasPermission('view_restaurant_orders') || isSuperAdmin"
                            :to="{ name: 'restaurantSalesReport' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isRestaurantSalesReportActive }"
                            @click="closeReportsAnalyticsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Restaurant Sales</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_reports') || hasPermission('view_room_service') || isSuperAdmin"
                            :to="{ name: 'roomServicePerformanceReport' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isRoomServicePerformanceReportActive }"
                            @click="closeReportsAnalyticsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Room Service Performance</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_reports') || hasPermission('manage_amenities') || isSuperAdmin"
                            :to="{ name: 'amenitiesUsageReport' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isAmenitiesUsageReportActive }"
                            @click="closeReportsAnalyticsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Amenities Usage</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_reports') || hasPermission('view_housekeeping') || hasPermission('manage_housekeeping') || isSuperAdmin"
                            :to="{ name: 'housekeepingEfficiencyReport' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isHousekeepingEfficiencyReportActive }"
                            @click="closeReportsAnalyticsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Housekeeping Efficiency</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_reports') || hasPermission('view_inventory') || isSuperAdmin"
                            :to="{ name: 'inventoryConsumptionReport' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isInventoryConsumptionReportActive }"
                            @click="closeReportsAnalyticsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Inventory Consumption</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_reports') || hasPermission('view_financial_reports') || isSuperAdmin"
                            :to="{ name: 'expenseVsIncomeReport' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isExpenseVsIncomeReportActive }"
                            @click="closeReportsAnalyticsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Expense vs Income</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_reports') || hasPermission('manage_staff') || isSuperAdmin"
                            :to="{ name: 'staffProductivityReport' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isStaffProductivityReportActive }"
                            @click="closeReportsAnalyticsDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Staff Productivity</span>
                        </router-link>
                    </div>
                </div>

                <!-- Settings & White-Labeling Dropdown -->
                <div
                    v-if="hasPermission('system_settings') || hasPermission('manage_system') || isSuperAdmin"
                    class="menu-dropdown"
                    :class="{ 'dropdown-open': isSettingsWhiteLabelingDropdownOpen, 'active': isSettingsWhiteLabelingActive }"
                >
                    <button
                        class="menu-item menu-dropdown-toggle"
                        @click="toggleSettingsWhiteLabelingDropdown"
                    >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 12C11.1046 12 12 11.1046 12 10C12 8.89543 11.1046 8 10 8C8.89543 8 8 8.89543 8 10C8 11.1046 8.89543 12 10 12Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M10 2V4M10 16V18M18 10H16M4 10H2M15.6569 4.34315L14.2426 5.75736M5.75736 14.2426L4.34315 15.6569M15.6569 15.6569L14.2426 14.2426M5.75736 5.75736L4.34315 4.34315" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span v-if="!sidebarMini">Settings & White-Labeling</span>
                        <svg
                            v-if="!sidebarMini"
                            width="16"
                            height="16"
                            viewBox="0 0 16 16"
                            fill="none"
                            class="dropdown-arrow"
                            :class="{ 'rotated': isSettingsWhiteLabelingDropdownOpen }"
                        >
                            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div v-if="isSettingsWhiteLabelingDropdownOpen && !sidebarMini" class="menu-dropdown-content">
                        <router-link
                            v-if="hasPermission('system_settings') || isSuperAdmin"
                            :to="{ name: 'logoColorTheme' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isLogoColorThemeActive }"
                            @click="closeSettingsWhiteLabelingDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Logo, Color Theme, Hotel Name</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('system_settings') || isSuperAdmin"
                            :to="{ name: 'invoiceReceiptTemplates' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isInvoiceReceiptTemplatesActive }"
                            @click="closeSettingsWhiteLabelingDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Invoice & Receipt Templates</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('system_settings') || isSuperAdmin"
                            :to="{ name: 'customFooterBranding' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isCustomFooterBrandingActive }"
                            @click="closeSettingsWhiteLabelingDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Custom Footer Branding</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_system') || isSuperAdmin"
                            :to="{ name: 'moduleEnableDisable' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isModuleEnableDisableActive }"
                            @click="closeSettingsWhiteLabelingDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Module Enable/Disable</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('system_settings') || isSuperAdmin"
                            :to="{ name: 'taxCurrencySetup' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isTaxCurrencySetupActive }"
                            @click="closeSettingsWhiteLabelingDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Tax & Currency Setup</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('system_settings') || isSuperAdmin"
                            :to="{ name: 'languageSettings' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isLanguageSettingsActive }"
                            @click="closeSettingsWhiteLabelingDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Language Settings</span>
                        </router-link>
                    </div>
                </div>

                <!-- Backup, Logs & Security Dropdown -->
                <div
                    v-if="hasPermission('manage_system') || hasPermission('system_settings') || isSuperAdmin"
                    class="menu-dropdown"
                    :class="{ 'dropdown-open': isBackupLogsSecurityDropdownOpen, 'active': isBackupLogsSecurityActive }"
                    ref="backupLogsSecurityDropdown"
                >
                    <button
                        class="menu-item menu-dropdown-toggle"
                        @click="toggleBackupLogsSecurityDropdown"
                    >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 2L3 5V9C3 13.4183 6.58172 17 11 17C15.4183 17 19 13.4183 19 9V5L12 2M10 2L17 5M10 2V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span v-if="!sidebarMini">Backup, Logs & Security</span>
                        <svg
                            v-if="!sidebarMini"
                            width="16"
                            height="16"
                            viewBox="0 0 16 16"
                            fill="none"
                            class="dropdown-arrow"
                            :class="{ 'rotated': isBackupLogsSecurityDropdownOpen }"
                        >
                            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div v-if="isBackupLogsSecurityDropdownOpen && !sidebarMini" class="menu-dropdown-content">
                        <router-link
                            v-if="hasPermission('manage_system') || isSuperAdmin"
                            :to="{ name: 'dailyAutoBackup' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isDailyAutoBackupActive }"
                            @click="closeBackupLogsSecurityDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 5V9C3 13.4183 6.58172 17 11 17C15.4183 17 19 13.4183 19 9V5L12 2M10 2L17 5M10 2V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Daily Auto-Backup</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_system') || isSuperAdmin"
                            :to="{ name: 'manualBackup' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isManualBackupActive }"
                            @click="closeBackupLogsSecurityDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 5V9C3 13.4183 6.58172 17 11 17C15.4183 17 19 13.4183 19 9V5L12 2M10 2L17 5M10 2V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Manual Backup</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_system') || isSuperAdmin"
                            :to="{ name: 'activityLogs' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isActivityLogsActive }"
                            @click="closeBackupLogsSecurityDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18V16H2V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 8H14M6 12H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Activity Logs</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('view_roles') || hasPermission('manage_role_permissions') || isSuperAdmin"
                            :to="{ name: 'roleBasedPermissions' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isRoleBasedPermissionsActive }"
                            @click="closeBackupLogsSecurityDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 10C12.7614 10 15 7.76142 15 5C15 2.23858 12.7614 0 10 0C7.23858 0 5 2.23858 5 5C5 7.76142 7.23858 10 10 10Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 12C5.58172 12 2 13.7909 2 16V20H18V16C18 13.7909 14.4183 12 10 12Z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <span>Role-Based Permissions</span>
                        </router-link>
                        <router-link
                            v-if="hasPermission('manage_system') || isSuperAdmin"
                            :to="{ name: 'secureAuthentication' }"
                            class="menu-dropdown-item"
                            active-class="active"
                            :class="{ active: isSecureAuthenticationActive }"
                            @click="closeBackupLogsSecurityDropdown"
                        >
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L3 5V9C3 13.4183 6.58172 17 11 17C15.4183 17 19 13.4183 19 9V5L12 2M10 2L17 5M10 2V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Secure Authentication</span>
                        </router-link>
                    </div>
                </div>
            </nav>

            <div class="sidebar-footer">
                <button class="sidebar-toggle-btn" @click="toggleSidebar">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M11 3L6 9L11 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button class="sidebar-logout-btn" @click="handleLogout" title="Logout">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M7 2H3C2.44772 2 2 2.44772 2 3V15C2 15.5523 2.44772 16 3 16H7M12 13L16 9M16 9L12 5M16 9H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span v-if="!sidebarMini">Logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="app-main">
            <!-- Top Header -->
            <header class="app-header">
                <div class="header-left">
                    <button class="mobile-menu-btn" @click="toggleMobileMenu">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M3 5H17M3 10H17M3 15H17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <h1 class="page-title">{{ currentPageTitle }}</h1>
                </div>

                <div class="header-right">
                    <div class="header-search">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                            <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <input type="text" placeholder="Search..." />
                    </div>

                    <button class="header-icon-btn">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 2C10 1.44772 9.55228 1 9 1C8.44772 1 8 1.44772 8 2V3H3C2.44772 3 2 3.44772 2 4V5H18V4C18 3.44772 17.5523 3 17 3H12V2Z" fill="currentColor"/>
                            <path d="M2 7V16C2 17.1046 2.89543 18 4 18H16C17.1046 18 18 17.1046 18 16V7H2Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <span class="badge">3</span>
                    </button>

                    <!-- User Dropdown -->
                    <div class="user-dropdown-wrapper">
                        <button
                            class="user-dropdown-toggle"
                            @click="toggleUserDropdown"
                            :class="{ 'active': isUserDropdownOpen }"
                        >
                            <div class="user-avatar">
                                <span>{{ userInitial }}</span>
                            </div>
                            <div class="user-info">
                                <span class="user-name">{{ userName }}</span>
                                <span class="user-role">{{ userRoleName }}</span>
                            </div>
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 16 16"
                                fill="none"
                                class="dropdown-arrow"
                                :class="{ 'rotated': isUserDropdownOpen }"
                            >
                                <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div v-if="isUserDropdownOpen" class="user-dropdown-menu">
                            <div class="user-dropdown-header">
                                <div class="user-avatar-large">
                                    <span>{{ userInitial }}</span>
                                </div>
                                <div class="user-details">
                                    <div class="user-name-large">{{ userName }}</div>
                                    <div class="user-email">{{ userEmail }}</div>
                                    <div class="user-role-badge">{{ userRoleName }}</div>
                                </div>
                            </div>
                            <div class="user-dropdown-divider"></div>
                            <button class="user-dropdown-item" @click="handleLogout">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M7 2H3C2.44772 2 2 2.44772 2 3V15C2 15.5523 2.44772 16 3 16H7M12 13L16 9M16 9L12 5M16 9H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="app-content">
                <router-view />
            </main>

            <!-- Footer -->
            <footer class="app-footer">
                <div class="footer-content">
                    <div class="footer-left">
                        <p>&copy; {{ currentYear }} Hotel Management System. All rights reserved.</p>
                    </div>
                    <div class="footer-right">
                        <a href="#" class="footer-link">Privacy Policy</a>
                        <span class="footer-separator">|</span>
                        <a href="#" class="footer-link">Terms of Service</a>
                        <span class="footer-separator">|</span>
                        <a href="#" class="footer-link">Support</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { usePermissions } from '../composables/usePermissions';

export default {
    name: 'Dashboard',
    setup() {
        const router = useRouter();
        const route = useRoute();
        const { hasPermissionSync, isSuperAdmin, getUserData, getUserDataSync } = usePermissions();

        // Create reactive user data ref to trigger updates
        const userData = ref(null);
        const userDataLoaded = ref(false);

        // Watch for user data changes and update reactive ref
        watch(() => getUserDataSync(), (newUserData) => {
            userData.value = newUserData;
            userDataLoaded.value = !!newUserData;
        }, { immediate: true });

        // Create reactive permission check function that updates when user data changes
        // NOTE: For sidebar navigation, we now always allow access so all menu items are visible
        // for any authenticated user, regardless of stored permissions or role.
        const hasPermission = (permission) => {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:hasPermission',message:'Permission check bypassed for menu access',data:{permission,hasUserData:!!userData.value,userRole:userData.value?.role,userDataLoaded:userDataLoaded.value},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'F'})}).catch(()=>{});
            // #endregion
            // We intentionally return true here to remove navigation access restrictions
            // from the left-side menu without changing the underlying permission system.
            const currentUser = userData.value || getUserDataSync(); // keep reactive dependency
            return true;
        };
        const sidebarMini = ref(false);
        const userEmail = ref('user@example.com');
        const userInitial = ref('U');
        const userName = ref('User');
        const userRoleName = ref('');
        const isUserDropdownOpen = ref(false);
        const hotelName = ref('');
        const hotelLogo = ref(null);
        const hotelFavicon = ref(null);
        const hotelInfoLoaded = ref(false);

        const currentPageTitle = computed(() => {
            const titles = {
                'dashboard': 'Dashboard',
                'users': 'Users',
                'roles': 'Roles & Permissions',
                'permissions': 'Permissions',
                'hotelSetup': 'Hotel Setup & Configuration',
                'restaurantOperations': 'Restaurant Management (POS)',
                'rooms': 'Room Management',
                'roomServiceModule': 'Room Service Module',
                'roomReservations': 'Room & Reservation Management',
                'guestManagement': 'Guest Management',
                'frontDeskOperations': 'Front Desk Operations',
                'amenitiesServices': 'Amenities & Services Management',
                'inventoryStore': 'Inventory & Store Management',
                'linenHousekeeping': 'Linen & Housekeeping Items',
                'amenitiesConsumables': 'Amenities Consumables',
                'stockInOut': 'Stock In / Out',
                'supplierManagement': 'Supplier Management',
                'stockAlertsTracking': 'Stock Alerts & Tracking',
                'staffProfiles': 'Staff Profiles',
                'roleAssignment': 'Role Assignment',
                'shiftScheduling': 'Shift Scheduling',
                'attendance': 'Attendance',
                'taskAssignment': 'Task Assignment',
                'performanceLogs': 'Performance Logs',
                'settings': 'Settings'
            };
            return titles[route.name] || 'Dashboard';
        });

        const currentYear = new Date().getFullYear();

        // Computed properties for active menu items
        const isDashboardActive = computed(() => {
            return route.name === 'dashboard' || route.path === '/' || route.path === '/dashboard';
        });

        const isUsersActive = computed(() => {
            return route.name === 'users' || route.path === '/users' || route.path.startsWith('/users');
        });

        const isRolesActive = computed(() => {
            return route.name === 'roles' || route.path === '/roles' || route.path.startsWith('/roles');
        });

        const isPermissionsActive = computed(() => {
            return route.name === 'permissions' || route.path === '/permissions' || route.path.startsWith('/permissions');
        });

        const isUsersPermissionsActive = computed(() => {
            return isUsersActive.value || isRolesActive.value || isPermissionsActive.value;
        });

        const isUsersPermissionsDropdownOpen = ref(false);

        // Auto-open dropdown if any child route is active
        watch(() => route.path, (newPath) => {
            if (newPath.startsWith('/users') || newPath.startsWith('/roles') || newPath.startsWith('/permissions')) {
                isUsersPermissionsDropdownOpen.value = true;
            }
        }, { immediate: true });

        const toggleUsersPermissionsDropdown = () => {
            isUsersPermissionsDropdownOpen.value = !isUsersPermissionsDropdownOpen.value;
        };

        const closeUsersPermissionsDropdown = () => {
            // Don't close on mobile, let it stay open for better UX
            if (window.innerWidth > 768) {
                isUsersPermissionsDropdownOpen.value = false;
            }
        };

        const isHotelSetupActive = computed(() => {
            return route.name === 'hotelSetup' || route.path === '/hotel-setup' || route.path.startsWith('/hotel-setup');
        });

        const isRestaurantOperationsActive = computed(() => {
            return route.name === 'restaurantOperations' || route.path === '/restaurant-operations' || route.path.startsWith('/restaurant-operations');
        });

        const isRoomsActive = computed(() => {
            return route.name === 'rooms' || route.path === '/rooms' || route.path.startsWith('/rooms');
        });

        const isRoomServiceModuleActive = computed(() => {
            return route.name === 'roomServiceModule' || route.path === '/room-service-module' || route.path.startsWith('/room-service-module');
        });

        const isRoomReservationsActive = computed(() => {
            return route.name === 'roomReservations' || route.path === '/room-reservations' || route.path.startsWith('/room-reservations');
        });

        const isGuestManagementActive = computed(() => {
            return route.name === 'guestManagement' || route.path === '/guest-management' || route.path.startsWith('/guest-management');
        });

        const isFrontDeskOperationsActive = computed(() => {
            return route.name === 'frontDeskOperations' || route.path === '/front-desk-operations' || route.path.startsWith('/front-desk-operations');
        });

        const isAmenitiesServicesActive = computed(() => {
            return route.name === 'amenitiesServices' || route.path === '/amenities-services' || route.path.startsWith('/amenities-services');
        });

        const isHousekeepingActive = computed(() => {
            return (route.name === 'housekeeping' || route.path === '/housekeeping' || route.path.startsWith('/housekeeping/'))
                && !route.path.startsWith('/housekeeping-efficiency-report');
        });

        const isSettingsActive = computed(() => {
            return route.name === 'settings' || route.path === '/settings' || route.path.startsWith('/settings');
        });

        const isHotelOperationsActive = computed(() => {
            return isRestaurantOperationsActive.value ||
                   isRoomsActive.value ||
                   isRoomReservationsActive.value ||
                   isFrontDeskOperationsActive.value ||
                   isAmenitiesServicesActive.value ||
                   isHousekeepingActive.value;
        });

        const isHotelOperationsDropdownOpen = ref(false);

        // Auto-open dropdown if any child route is active
        watch(() => route.path, (newPath) => {
            if (newPath.startsWith('/restaurant-operations') ||
                newPath.startsWith('/rooms') ||
                newPath.startsWith('/room-reservations') ||
                newPath.startsWith('/front-desk-operations') ||
                newPath.startsWith('/amenities-services') ||
                (newPath.startsWith('/housekeeping') && !newPath.startsWith('/housekeeping-efficiency-report'))) {
                isHotelOperationsDropdownOpen.value = true;
            }
        }, { immediate: true });

        const toggleHotelOperationsDropdown = () => {
            isHotelOperationsDropdownOpen.value = !isHotelOperationsDropdownOpen.value;
        };

        const closeHotelOperationsDropdown = () => {
            // Don't close on mobile, let it stay open for better UX
            if (window.innerWidth > 768) {
                isHotelOperationsDropdownOpen.value = false;
            }
        };

        const isInventoryStoreActive = computed(() => {
            return route.name === 'inventoryStore' || route.path === '/inventory-store' || route.path.startsWith('/inventory-store');
        });

        const isLinenHousekeepingActive = computed(() => {
            return route.name === 'linenHousekeeping' || route.path === '/linen-housekeeping' || route.path.startsWith('/linen-housekeeping');
        });

        const isAmenitiesConsumablesActive = computed(() => {
            return route.name === 'amenitiesConsumables' || route.path === '/amenities-consumables' || route.path.startsWith('/amenities-consumables');
        });

        const isStockInOutActive = computed(() => {
            return route.name === 'stockInOut' || route.path === '/stock-in-out' || route.path.startsWith('/stock-in-out');
        });

        const isSupplierManagementActive = computed(() => {
            return route.name === 'supplierManagement' || route.path === '/supplier-management' || route.path.startsWith('/supplier-management');
        });

        const isStockAlertsTrackingActive = computed(() => {
            return route.name === 'stockAlertsTracking' || route.path === '/stock-alerts-tracking' || route.path.startsWith('/stock-alerts-tracking');
        });

        const isStockInventoryActive = computed(() => {
            return isInventoryStoreActive.value || isLinenHousekeepingActive.value || isAmenitiesConsumablesActive.value || isStockInOutActive.value || isSupplierManagementActive.value || isStockAlertsTrackingActive.value;
        });

        const isStockInventoryDropdownOpen = ref(false);

        // Auto-open dropdown if any child route is active
        watch(() => route.path, (newPath) => {
            if (newPath.startsWith('/inventory-store') || newPath.startsWith('/linen-housekeeping') || newPath.startsWith('/amenities-consumables') || newPath.startsWith('/stock-in-out') || newPath.startsWith('/supplier-management') || newPath.startsWith('/stock-alerts-tracking')) {
                isStockInventoryDropdownOpen.value = true;
            }
        }, { immediate: true });

        const toggleStockInventoryDropdown = () => {
            isStockInventoryDropdownOpen.value = !isStockInventoryDropdownOpen.value;
        };

        const closeStockInventoryDropdown = () => {
            // Don't close on mobile, let it stay open for better UX
            if (window.innerWidth > 768) {
                isStockInventoryDropdownOpen.value = false;
            }
        };

        // Staff & Shift Management
        const isStaffProfilesActive = computed(() => {
            return route.name === 'staffProfiles' || route.path === '/staff-profiles' || route.path.startsWith('/staff-profiles');
        });

        const isRoleAssignmentActive = computed(() => {
            return route.name === 'roleAssignment' || route.path === '/role-assignment' || route.path.startsWith('/role-assignment');
        });

        const isShiftSchedulingActive = computed(() => {
            return route.name === 'shiftScheduling' || route.path === '/shift-scheduling' || route.path.startsWith('/shift-scheduling');
        });

        const isAttendanceActive = computed(() => {
            return route.name === 'attendance' || route.path === '/attendance' || route.path.startsWith('/attendance');
        });

        const isTaskAssignmentActive = computed(() => {
            return route.name === 'taskAssignment' || route.path === '/task-assignment' || route.path.startsWith('/task-assignment');
        });

        const isPerformanceLogsActive = computed(() => {
            return route.name === 'performanceLogs' || route.path === '/performance-logs' || route.path.startsWith('/performance-logs');
        });

        const isStaffShiftActive = computed(() => {
            return isStaffProfilesActive.value || isRoleAssignmentActive.value || isShiftSchedulingActive.value || isAttendanceActive.value || isTaskAssignmentActive.value || isPerformanceLogsActive.value;
        });

        const isStaffShiftDropdownOpen = ref(false);

        // Auto-open dropdown if any child route is active
        watch(() => route.path, (newPath) => {
            if (newPath.startsWith('/staff-profiles') || newPath.startsWith('/role-assignment') || newPath.startsWith('/shift-scheduling') || newPath.startsWith('/attendance') || newPath.startsWith('/task-assignment') || newPath.startsWith('/performance-logs')) {
                isStaffShiftDropdownOpen.value = true;
            }
        }, { immediate: true });

        const toggleStaffShiftDropdown = () => {
            isStaffShiftDropdownOpen.value = !isStaffShiftDropdownOpen.value;
        };

        const closeStaffShiftDropdown = () => {
            // Don't close on mobile, let it stay open for better UX
            if (window.innerWidth > 768) {
                isStaffShiftDropdownOpen.value = false;
            }
        };

        // Billing & Finance
        const isUnifiedGuestFolioActive = computed(() => {
            return route.name === 'unifiedGuestFolio' || route.path === '/unified-guest-folio' || route.path.startsWith('/unified-guest-folio');
        });

        const isSplitBillingActive = computed(() => {
            return route.name === 'splitBilling' || route.path === '/split-billing' || route.path.startsWith('/split-billing');
        });

        const isPartialPaymentsActive = computed(() => {
            return route.name === 'partialPayments' || route.path === '/partial-payments' || route.path.startsWith('/partial-payments');
        });

        const isAdvancePaymentsActive = computed(() => {
            return route.name === 'advancePayments' || route.path === '/advance-payments' || route.path.startsWith('/advance-payments');
        });

        const isRefundHandlingActive = computed(() => {
            return route.name === 'refundHandling' || route.path === '/refund-handling' || route.path.startsWith('/refund-handling');
        });

        const isTaxServiceChargeActive = computed(() => {
            return route.name === 'taxServiceCharge' || route.path === '/tax-service-charge' || route.path.startsWith('/tax-service-charge');
        });

        const isExpenseTrackingActive = computed(() => {
            return route.name === 'expenseTracking' || route.path === '/expense-tracking' || route.path.startsWith('/expense-tracking');
        });

        const isSupplierPaymentsActive = computed(() => {
            return route.name === 'supplierPayments' || route.path === '/supplier-payments' || route.path.startsWith('/supplier-payments');
        });

        const isDailyCashClosingActive = computed(() => {
            return route.name === 'dailyCashClosing' || route.path === '/daily-cash-closing' || route.path.startsWith('/daily-cash-closing');
        });

        const isProfitLossReportActive = computed(() => {
            return route.name === 'profitLossReport' || route.path === '/profit-loss-report' || route.path.startsWith('/profit-loss-report');
        });

        const isBillingFinanceActive = computed(() => {
            return isUnifiedGuestFolioActive.value || isSplitBillingActive.value || isPartialPaymentsActive.value ||
                   isAdvancePaymentsActive.value || isRefundHandlingActive.value || isTaxServiceChargeActive.value ||
                   isExpenseTrackingActive.value || isSupplierPaymentsActive.value || isDailyCashClosingActive.value ||
                   isProfitLossReportActive.value;
        });

        const isBillingFinanceDropdownOpen = ref(false);

        // Auto-open dropdown if any child route is active
        watch(() => route.path, (newPath) => {
            if (newPath.startsWith('/unified-guest-folio') || newPath.startsWith('/split-billing') ||
                newPath.startsWith('/partial-payments') || newPath.startsWith('/advance-payments') ||
                newPath.startsWith('/refund-handling') || newPath.startsWith('/tax-service-charge') ||
                newPath.startsWith('/expense-tracking') || newPath.startsWith('/supplier-payments') ||
                newPath.startsWith('/daily-cash-closing') || newPath.startsWith('/profit-loss-report')) {
                isBillingFinanceDropdownOpen.value = true;
            }
        }, { immediate: true });

        const toggleBillingFinanceDropdown = () => {
            isBillingFinanceDropdownOpen.value = !isBillingFinanceDropdownOpen.value;
        };

        const closeBillingFinanceDropdown = () => {
            // Don't close on mobile, let it stay open for better UX
            if (window.innerWidth > 768) {
                isBillingFinanceDropdownOpen.value = false;
            }
        };

        // Reports & Analytics
        const isOccupancyRateReportActive = computed(() => {
            return route.name === 'occupancyRateReport' || route.path === '/occupancy-rate-report' || route.path.startsWith('/occupancy-rate-report');
        });

        const isDailyMonthlyRevenueReportActive = computed(() => {
            return route.name === 'dailyMonthlyRevenueReport' || route.path === '/daily-monthly-revenue-report' || route.path.startsWith('/daily-monthly-revenue-report');
        });

        const isRoomTypePerformanceReportActive = computed(() => {
            return route.name === 'roomTypePerformanceReport' || route.path === '/room-type-performance-report' || route.path.startsWith('/room-type-performance-report');
        });

        const isRestaurantSalesReportActive = computed(() => {
            return route.name === 'restaurantSalesReport' || route.path === '/restaurant-sales-report' || route.path.startsWith('/restaurant-sales-report');
        });

        const isRoomServicePerformanceReportActive = computed(() => {
            return route.name === 'roomServicePerformanceReport' || route.path === '/room-service-performance-report' || route.path.startsWith('/room-service-performance-report');
        });

        const isAmenitiesUsageReportActive = computed(() => {
            return route.name === 'amenitiesUsageReport' || route.path === '/amenities-usage-report' || route.path.startsWith('/amenities-usage-report');
        });

        const isHousekeepingEfficiencyReportActive = computed(() => {
            return route.name === 'housekeepingEfficiencyReport' || route.path === '/housekeeping-efficiency-report' || route.path.startsWith('/housekeeping-efficiency-report');
        });

        const isInventoryConsumptionReportActive = computed(() => {
            return route.name === 'inventoryConsumptionReport' || route.path === '/inventory-consumption-report' || route.path.startsWith('/inventory-consumption-report');
        });

        const isExpenseVsIncomeReportActive = computed(() => {
            return route.name === 'expenseVsIncomeReport' || route.path === '/expense-vs-income-report' || route.path.startsWith('/expense-vs-income-report');
        });

        const isStaffProductivityReportActive = computed(() => {
            return route.name === 'staffProductivityReport' || route.path === '/staff-productivity-report' || route.path.startsWith('/staff-productivity-report');
        });

        const isReportsAnalyticsActive = computed(() => {
            return isOccupancyRateReportActive.value || isDailyMonthlyRevenueReportActive.value ||
                   isRoomTypePerformanceReportActive.value || isRestaurantSalesReportActive.value ||
                   isRoomServicePerformanceReportActive.value || isAmenitiesUsageReportActive.value ||
                   isHousekeepingEfficiencyReportActive.value || isInventoryConsumptionReportActive.value ||
                   isExpenseVsIncomeReportActive.value || isStaffProductivityReportActive.value;
        });

        const isReportsAnalyticsDropdownOpen = ref(false);

        // Auto-open dropdown if any child route is active
        watch(() => route.path, (newPath) => {
            if (newPath.startsWith('/occupancy-rate-report') || newPath.startsWith('/daily-monthly-revenue-report') ||
                newPath.startsWith('/room-type-performance-report') || newPath.startsWith('/restaurant-sales-report') ||
                newPath.startsWith('/room-service-performance-report') || newPath.startsWith('/amenities-usage-report') ||
                newPath.startsWith('/housekeeping-efficiency-report') || newPath.startsWith('/inventory-consumption-report') ||
                newPath.startsWith('/expense-vs-income-report') || newPath.startsWith('/staff-productivity-report')) {
                isReportsAnalyticsDropdownOpen.value = true;
            }
        }, { immediate: true });

        const toggleReportsAnalyticsDropdown = () => {
            isReportsAnalyticsDropdownOpen.value = !isReportsAnalyticsDropdownOpen.value;
        };

        const closeReportsAnalyticsDropdown = () => {
            // Don't close on mobile, let it stay open for better UX
            if (window.innerWidth > 768) {
                isReportsAnalyticsDropdownOpen.value = false;
            }
        };

        // Settings & White-Labeling
        const isLogoColorThemeActive = computed(() => {
            return route.name === 'logoColorTheme' || route.path === '/logo-color-theme' || route.path.startsWith('/logo-color-theme');
        });

        const isInvoiceReceiptTemplatesActive = computed(() => {
            return route.name === 'invoiceReceiptTemplates' || route.path === '/invoice-receipt-templates' || route.path.startsWith('/invoice-receipt-templates');
        });

        const isCustomFooterBrandingActive = computed(() => {
            return route.name === 'customFooterBranding' || route.path === '/custom-footer-branding' || route.path.startsWith('/custom-footer-branding');
        });

        const isModuleEnableDisableActive = computed(() => {
            return route.name === 'moduleEnableDisable' || route.path === '/module-enable-disable' || route.path.startsWith('/module-enable-disable');
        });

        const isTaxCurrencySetupActive = computed(() => {
            return route.name === 'taxCurrencySetup' || route.path === '/tax-currency-setup' || route.path.startsWith('/tax-currency-setup');
        });

        const isLanguageSettingsActive = computed(() => {
            return route.name === 'languageSettings' || route.path === '/language-settings' || route.path.startsWith('/language-settings');
        });

        const isSettingsWhiteLabelingActive = computed(() => {
            return isLogoColorThemeActive.value || isInvoiceReceiptTemplatesActive.value || isCustomFooterBrandingActive.value ||
                   isModuleEnableDisableActive.value || isTaxCurrencySetupActive.value || isLanguageSettingsActive.value;
        });

        const isSettingsWhiteLabelingDropdownOpen = ref(false);

        // Auto-open dropdown if any child route is active
        watch(() => route.path, (newPath) => {
            if (newPath.startsWith('/logo-color-theme') || newPath.startsWith('/invoice-receipt-templates') ||
                newPath.startsWith('/custom-footer-branding') || newPath.startsWith('/module-enable-disable') ||
                newPath.startsWith('/tax-currency-setup') || newPath.startsWith('/language-settings')) {
                isSettingsWhiteLabelingDropdownOpen.value = true;
            }
        }, { immediate: true });

        const toggleSettingsWhiteLabelingDropdown = () => {
            isSettingsWhiteLabelingDropdownOpen.value = !isSettingsWhiteLabelingDropdownOpen.value;
        };

        const closeSettingsWhiteLabelingDropdown = () => {
            // Don't close on mobile, let it stay open for better UX
            if (window.innerWidth > 768) {
                isSettingsWhiteLabelingDropdownOpen.value = false;
            }
        };

        // Backup, Logs & Security
        const isDailyAutoBackupActive = computed(() => {
            return route.name === 'dailyAutoBackup' || route.path === '/daily-auto-backup' || route.path.startsWith('/daily-auto-backup');
        });

        const isManualBackupActive = computed(() => {
            return route.name === 'manualBackup' || route.path === '/manual-backup' || route.path.startsWith('/manual-backup');
        });

        const isActivityLogsActive = computed(() => {
            return route.name === 'activityLogs' || route.path === '/activity-logs' || route.path.startsWith('/activity-logs');
        });

        const isRoleBasedPermissionsActive = computed(() => {
            return route.name === 'roleBasedPermissions' || route.path === '/role-based-permissions' || route.path.startsWith('/role-based-permissions');
        });

        const isSecureAuthenticationActive = computed(() => {
            return route.name === 'secureAuthentication' || route.path === '/secure-authentication' || route.path.startsWith('/secure-authentication');
        });

        const isBackupLogsSecurityActive = computed(() => {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:1558',message:'isBackupLogsSecurityActive computed',data:{isDailyAutoBackupActive:isDailyAutoBackupActive.value,isManualBackupActive:isManualBackupActive.value,isActivityLogsActive:isActivityLogsActive.value,isRoleBasedPermissionsActive:isRoleBasedPermissionsActive.value,isSecureAuthenticationActive:isSecureAuthenticationActive.value},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
            // #endregion
            return isDailyAutoBackupActive.value || isManualBackupActive.value || isActivityLogsActive.value ||
                   isRoleBasedPermissionsActive.value || isSecureAuthenticationActive.value;
        });

        const isBackupLogsSecurityDropdownOpen = ref(false);
        const backupLogsSecurityDropdown = ref(null);
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:1563',message:'isBackupLogsSecurityDropdownOpen initialized',data:{value:isBackupLogsSecurityDropdownOpen.value},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
        // #endregion

        // Auto-open dropdown if any child route is active
        watch(() => route.path, (newPath) => {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:1566',message:'route.path watch triggered',data:{newPath,currentDropdownOpen:isBackupLogsSecurityDropdownOpen.value},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
            if (newPath.startsWith('/daily-auto-backup') || newPath.startsWith('/manual-backup') ||
                newPath.startsWith('/activity-logs') || newPath.startsWith('/role-based-permissions') ||
                newPath.startsWith('/secure-authentication')) {
                isBackupLogsSecurityDropdownOpen.value = true;
                // #region agent log
                fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:1570',message:'Setting dropdown open to true',data:{newPath},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
                // #endregion
            }
        }, { immediate: true });

        const toggleBackupLogsSecurityDropdown = () => {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:1574',message:'toggleBackupLogsSecurityDropdown called',data:{before:isBackupLogsSecurityDropdownOpen.value},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
            // #endregion
            isBackupLogsSecurityDropdownOpen.value = !isBackupLogsSecurityDropdownOpen.value;
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:1575',message:'toggleBackupLogsSecurityDropdown after toggle',data:{after:isBackupLogsSecurityDropdownOpen.value},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
            // #endregion
        };

        const closeBackupLogsSecurityDropdown = () => {
            // Don't close on mobile, let it stay open for better UX
            if (window.innerWidth > 768) {
                isBackupLogsSecurityDropdownOpen.value = false;
            }
        };

        const toggleUserDropdown = () => {
            isUserDropdownOpen.value = !isUserDropdownOpen.value;
        };

        // Close user dropdown when clicking outside
        const handleClickOutside = (event) => {
            if (isUserDropdownOpen.value && !event.target.closest('.user-dropdown-wrapper')) {
                isUserDropdownOpen.value = false;
            }
        };

        const toggleSidebar = () => {
            sidebarMini.value = !sidebarMini.value;
        };

        const toggleMobileMenu = () => {
            // Mobile menu toggle logic
        };

        const handleImageError = (event) => {
            // Hide broken image
            event.target.style.display = 'none';
        };

        const handleLogout = async () => {
            try {
                await axios.post('/api/logout', {}, {
                    withCredentials: true
                });
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                // Clear any remaining localStorage items (for backward compatibility)
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                localStorage.removeItem('user_role');
                // Redirect to login
                router.push('/login');
            }
        };

        const loadHotelInfo = async () => {
            try {
                // #region agent log
                fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:loadHotelInfo','message':'Function entry','data':{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                // #endregion

                const response = await axios.get('/api/hotel/info');
                const data = response.data;

                // #region agent log
                fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:loadHotelInfo','message':'Response received','data':{'logo':data.logo,'favicon':data.favicon,'origin':window.location.origin,'pathname':window.location.pathname},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                // #endregion

                hotelName.value = data.name || '';

                // Format logo URL properly
                if (data.logo) {
                    let logoUrl = data.logo;

                    // #region agent log
                    fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:loadHotelInfo','message':'Logo URL before processing','data':{'logoUrl':logoUrl,'startsWithHttp':logoUrl.startsWith('http')},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                    // #endregion

                    // If it's a full URL, use it directly
                    if (!logoUrl.startsWith('http')) {
                        // If it's a relative path, construct full URL
                        if (logoUrl.startsWith('/')) {
                            logoUrl = window.location.origin + logoUrl;
                        } else {
                            // Construct URL with /storage/ path (removed app/public)
                            logoUrl = window.location.origin + '/storage/' + logoUrl;
                        }
                    }

                    // Keep app/public in path (do not remove)

                    // #region agent log
                    fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:loadHotelInfo','message':'Logo URL after processing','data':{'finalLogoUrl':logoUrl},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                    // #endregion

                    hotelLogo.value = logoUrl;
                } else {
                    hotelLogo.value = null;
                }

                // Format favicon URL properly
                if (data.favicon) {
                    let faviconUrl = data.favicon;

                    // If it's a full URL, use it directly
                    if (!faviconUrl.startsWith('http')) {
                        // If it's a relative path, construct full URL
                        if (faviconUrl.startsWith('/')) {
                            faviconUrl = window.location.origin + faviconUrl;
                        } else {
                            // Construct URL with /storage/ path (removed app/public)
                            faviconUrl = window.location.origin + '/storage/' + faviconUrl;
                        }
                    }

                    // Keep app/public in path (do not remove)

                    hotelFavicon.value = faviconUrl;

                    // Update favicon dynamically
                    const faviconLink = document.getElementById('favicon-link');
                    if (faviconLink) {
                        faviconLink.href = faviconUrl;
                    } else {
                        const link = document.createElement('link');
                        link.id = 'favicon-link';
                        link.rel = 'icon';
                        link.type = 'image/x-icon';
                        link.href = faviconUrl;
                        document.head.appendChild(link);
                    }
                } else {
                    hotelFavicon.value = null;
                }

                hotelInfoLoaded.value = true;

                // Update page title
                if (data.name) {
                    document.title = data.name + ' - Hotel Management System';
                }
            } catch (error) {
                console.error('Error loading hotel information:', error);
                hotelInfoLoaded.value = true;
            }
        };

        // Load user data immediately in setup (before template renders)
        // This ensures permissions are available when template checks them
        const initializeUserData = async () => {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:initializeUserData',message:'Initializing user data',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            // Initialize permissions cache first
            const loadedUserData = await getUserData();
            if (loadedUserData) {
                userData.value = loadedUserData;
                userDataLoaded.value = true;
                // #region agent log
                fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:initializeUserData',message:'User data initialized',data:{role:loadedUserData.role,permissionsCount:loadedUserData.permissions?.length||0},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                // #endregion
            }
        };

        // Call immediately (don't wait for onMounted)
        initializeUserData();

        onMounted(async () => {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:1727',message:'onMounted hook started',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            // Get user from session via API and initialize permissions cache
            try {
                const response = await axios.get('/api/auth/check');
                if (response.data.success && response.data.authenticated && response.data.user) {
                    const userData = response.data.user;
                    userEmail.value = userData.email || 'user@example.com';
                    userName.value = userData.name || userData.email || 'User';
                    userInitial.value = userData.name ? userData.name.charAt(0).toUpperCase() : (userData.email ? userData.email.charAt(0).toUpperCase() : 'U');
                    userRoleName.value = userData.roleName || userData.role || '';

                    // Ensure permissions cache is up to date
                    await getUserData();
                    // #region agent log
                    fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:onMounted',message:'User data loaded',data:{userName:userName.value,userRole:userRoleName.value,userEmail:userEmail.value},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                    // #endregion
                }
            } catch (error) {
                console.error('Error checking authentication:', error);
                // #region agent log
                fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:1741',message:'Auth check error',data:{error:error.message},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
                // #endregion
            }

            // Load hotel information
            loadHotelInfo();

            // Check if Backup Logs Security dropdown element exists in DOM
            setTimeout(() => {
                // #region agent log
                const dropdownExists = backupLogsSecurityDropdown.value !== null;
                const dropdownElement = backupLogsSecurityDropdown.value;
                const dropdownInDOM = dropdownElement ? document.contains(dropdownElement) : false;
                const dropdownVisible = dropdownElement ? (dropdownElement.offsetParent !== null) : false;
                const dropdownClasses = dropdownElement ? dropdownElement.className : '';
                fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'Dashboard.vue:1750',message:'Checking dropdown element in DOM',data:{dropdownExists,dropdownInDOM,dropdownVisible,dropdownClasses,isBackupLogsSecurityDropdownOpen:isBackupLogsSecurityDropdownOpen.value,isBackupLogsSecurityActive:isBackupLogsSecurityActive.value,sidebarMini:sidebarMini.value},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                // #endregion
            }, 100);

            // Add click outside listener for user dropdown
            document.addEventListener('click', handleClickOutside);

            // Listen for hotel info updates from Hotel Setup page
            window.addEventListener('hotel-info-updated', (event) => {
                const hotelData = event.detail;
                hotelName.value = hotelData.name || '';

                // Format logo URL properly
                if (hotelData.logo) {
                    let logoUrl = hotelData.logo;

                    // If it's a full URL, use it directly
                    if (!logoUrl.startsWith('http')) {
                        // If it's a relative path, construct full URL
                        if (logoUrl.startsWith('/')) {
                            logoUrl = window.location.origin + logoUrl;
                        } else {
                            logoUrl = window.location.origin + '/storage/' + logoUrl;
                        }
                    }

                    // Remove 'app/public' from URL if it exists
                    logoUrl = logoUrl.replace('/storage/app/public/', '/storage/');
                    logoUrl = logoUrl.replace('storage/app/public/', 'storage/');

                    hotelLogo.value = logoUrl;
                } else {
                    hotelLogo.value = null;
                }

                // Format favicon URL properly
                if (hotelData.favicon) {
                    let faviconUrl = hotelData.favicon;

                    // If it's a full URL, use it directly
                    if (!faviconUrl.startsWith('http')) {
                        // If it's a relative path, construct full URL
                        if (faviconUrl.startsWith('/')) {
                            faviconUrl = window.location.origin + faviconUrl;
                        } else {
                            // Construct URL with /storage/ path (removed app/public)
                            faviconUrl = window.location.origin + '/storage/' + faviconUrl;
                        }
                    }

                    // Keep app/public in path (do not remove)

                    hotelFavicon.value = faviconUrl;

                    // Update favicon
                    const faviconLink = document.getElementById('favicon-link');
                    if (faviconLink) {
                        faviconLink.href = faviconUrl;
                    } else {
                        const link = document.createElement('link');
                        link.id = 'favicon-link';
                        link.rel = 'icon';
                        link.type = 'image/x-icon';
                        link.href = faviconUrl;
                        document.head.appendChild(link);
                    }
                } else {
                    hotelFavicon.value = null;
                }

                // Update page title
                if (hotelData.name) {
                    document.title = hotelData.name + ' - Hotel Management System';
                }
            });
        });

        return {
            sidebarMini,
            userEmail,
            userName,
            userInitial,
            userRoleName,
            isUserDropdownOpen,
            toggleUserDropdown,
            hotelName,
            hotelLogo,
            hotelFavicon,
            handleImageError,
            hotelInfoLoaded,
            currentPageTitle,
            currentYear,
            route,
            isDashboardActive,
            isUsersActive,
            isRolesActive,
            isPermissionsActive,
            isUsersPermissionsActive,
            isUsersPermissionsDropdownOpen,
            isHotelSetupActive,
            isRestaurantOperationsActive,
            isRoomsActive,
            isRoomServiceModuleActive,
            isRoomReservationsActive,
            isGuestManagementActive,
            isFrontDeskOperationsActive,
            isAmenitiesServicesActive,
            isHousekeepingActive,
            isSettingsActive,
            isHotelOperationsActive,
            isHotelOperationsDropdownOpen,
            isStockInventoryActive,
            isStockInventoryDropdownOpen,
            isInventoryStoreActive,
            isLinenHousekeepingActive,
            isAmenitiesConsumablesActive,
            isStockInOutActive,
            isSupplierManagementActive,
            isStockAlertsTrackingActive,
            isStaffShiftActive,
            isStaffShiftDropdownOpen,
            isStaffProfilesActive,
            isRoleAssignmentActive,
            isShiftSchedulingActive,
            isAttendanceActive,
            isTaskAssignmentActive,
            isPerformanceLogsActive,
            isBillingFinanceActive,
            isBillingFinanceDropdownOpen,
            isUnifiedGuestFolioActive,
            isSplitBillingActive,
            isPartialPaymentsActive,
            isAdvancePaymentsActive,
            isRefundHandlingActive,
            isTaxServiceChargeActive,
            isExpenseTrackingActive,
            isSupplierPaymentsActive,
            isDailyCashClosingActive,
            isProfitLossReportActive,
            isReportsAnalyticsActive,
            isReportsAnalyticsDropdownOpen,
            isOccupancyRateReportActive,
            isDailyMonthlyRevenueReportActive,
            isRoomTypePerformanceReportActive,
            isRestaurantSalesReportActive,
            isRoomServicePerformanceReportActive,
            isAmenitiesUsageReportActive,
            isHousekeepingEfficiencyReportActive,
            isInventoryConsumptionReportActive,
            isExpenseVsIncomeReportActive,
            isStaffProductivityReportActive,
            isSettingsWhiteLabelingActive,
            isSettingsWhiteLabelingDropdownOpen,
            isLogoColorThemeActive,
            isInvoiceReceiptTemplatesActive,
            isCustomFooterBrandingActive,
            isModuleEnableDisableActive,
            isTaxCurrencySetupActive,
            isLanguageSettingsActive,
            toggleSidebar,
            toggleMobileMenu,
            handleLogout,
            toggleUsersPermissionsDropdown,
            closeUsersPermissionsDropdown,
            toggleHotelOperationsDropdown,
            closeHotelOperationsDropdown,
            toggleStockInventoryDropdown,
            closeStockInventoryDropdown,
            toggleStaffShiftDropdown,
            closeStaffShiftDropdown,
            toggleBillingFinanceDropdown,
            closeBillingFinanceDropdown,
            toggleReportsAnalyticsDropdown,
            closeReportsAnalyticsDropdown,
            toggleSettingsWhiteLabelingDropdown,
            closeSettingsWhiteLabelingDropdown,
            isBackupLogsSecurityActive,
            isBackupLogsSecurityDropdownOpen,
            toggleBackupLogsSecurityDropdown,
            closeBackupLogsSecurityDropdown,
            backupLogsSecurityDropdown,
            isDailyAutoBackupActive,
            isManualBackupActive,
            isActivityLogsActive,
            isRoleBasedPermissionsActive,
            isSecureAuthenticationActive,
            hasPermission,
            isSuperAdmin
        };
    }
}
</script>

<style scoped>
.app-container {
    display: flex;
    min-height: 100vh;
    background: #f7f8fc;
}

/* Sidebar Styles - Hotel Management System Theme */
.app-sidebar {
    width: 260px;
    background: #2c3e50;
    color: #ecf0f1;
    display: flex;
    flex-direction: column;
    position: fixed;
    height: 100vh;
    left: 0;
    top: 0;
    z-index: 1000;
    transition: width 0.3s ease;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar-mini {
    width: 70px;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    min-height: 70px;
    display: flex;
    align-items: center;
}

.logo-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    width: 100%;
    text-align: center;
}


.logo-image {
    max-width: 100%;
    height: auto;
    max-height: 80px;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

.logo-image[src=""],
.logo-image:not([src]) {
    display: none;
}

.logo-text-wrapper {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    line-height: 1.3;
}

.logo-text {
    font-size: 15px;
    font-weight: 700;
    color: white;
    word-wrap: break-word;
    word-break: break-word;
    line-height: 1.4;
    text-align: center;
    max-width: 100%;
    display: block;
}

.sidebar-menu {
    flex: 1;
    padding: 20px 0;
    overflow-y: visible;
    overflow-x: hidden;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #bdc3c7;
    text-decoration: none;
    transition: all 0.2s;
    gap: 12px;
    margin: 4px 12px;
    border-radius: 6px;
}

.menu-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.menu-item.active {
    background: #667eea;
    color: white;
}

.menu-item svg {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
}

.menu-item span {
    white-space: nowrap;
    overflow: hidden;
    font-size: 14px;
    font-weight: 500;
}

/* Dropdown Menu Styles */
.menu-dropdown {
    position: relative;
}

.menu-dropdown-toggle {
    width: 100%;
    justify-content: space-between;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
}

.menu-dropdown-toggle .dropdown-arrow {
    transition: transform 0.3s ease;
    flex-shrink: 0;
}

.menu-dropdown-toggle .dropdown-arrow.rotated {
    transform: rotate(180deg);
}

.menu-dropdown.active .menu-dropdown-toggle {
    background: #667eea;
    color: white;
}

.menu-dropdown-content {
    margin-left: 20px;
    margin-top: 4px;
    padding-left: 12px;
    border-left: 2px solid rgba(255, 255, 255, 0.2);
    display: flex;
    flex-direction: column;
    gap: 4px;
    animation: slideDown 0.2s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.menu-dropdown-item {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    color: #bdc3c7;
    text-decoration: none;
    transition: all 0.2s;
    gap: 12px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
}

.menu-dropdown-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.menu-dropdown-item.active {
    background: rgba(102, 126, 234, 0.3);
    color: white;
}

.menu-dropdown-item svg {
    flex-shrink: 0;
    width: 18px;
    height: 18px;
}

.sidebar-mini .menu-dropdown-content {
    display: none;
}

.sidebar-footer {
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sidebar-toggle-btn {
    width: 100%;
    padding: 10px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 6px;
    color: #ecf0f1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.sidebar-toggle-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.sidebar-mini .sidebar-toggle-btn svg {
    transform: rotate(180deg);
}

.sidebar-logout-btn {
    width: 100%;
    padding: 10px;
    background: rgba(231, 76, 60, 0.2);
    border: none;
    border-radius: 6px;
    color: #ecf0f1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
    font-size: 14px;
    font-weight: 500;
}

.sidebar-logout-btn:hover {
    background: rgba(231, 76, 60, 0.3);
    color: white;
}

.sidebar-logout-btn svg {
    flex-shrink: 0;
    width: 18px;
    height: 18px;
}

.sidebar-mini .sidebar-logout-btn span {
    display: none;
}

.sidebar-mini .sidebar-logout-btn {
    justify-content: center;
}

/* Main Content */
.app-main {
    flex: 1;
    margin-left: 260px;
    transition: margin-left 0.3s ease;
    display: flex;
    flex-direction: column;
}

.sidebar-mini ~ .app-main {
    margin-left: 70px;
}

.app-header {
    background: white;
    padding: 0 30px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    cursor: pointer;
    color: #2c3e50;
    padding: 8px;
}

.page-title {
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.header-search {
    position: relative;
    display: flex;
    align-items: center;
}

.header-search svg {
    position: absolute;
    left: 12px;
    color: #95a5a6;
}

.header-search input {
    padding: 8px 12px 8px 36px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    width: 250px;
    transition: all 0.2s;
}

.header-search input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.header-icon-btn {
    position: relative;
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    color: #2c3e50;
    transition: all 0.2s;
}

.header-icon-btn:hover {
    color: #667eea;
}

.badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #e74c3c;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
}

.header-logout-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #e74c3c;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    color: white;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
}

.header-logout-btn:hover {
    background: #c0392b;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
}

.header-logout-btn svg {
    flex-shrink: 0;
    width: 18px;
    height: 18px;
}

.header-logout-btn span {
    white-space: nowrap;
}

.user-dropdown-wrapper {
    position: relative;
}

.user-dropdown-toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    background: #f7f8fc;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.user-dropdown-toggle:hover {
    background: #edf0f5;
    border-color: #667eea;
}

.user-dropdown-toggle.active {
    background: #edf0f5;
    border-color: #667eea;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

.user-role {
    font-size: 12px;
    color: #7f8c8d;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

.user-dropdown-toggle .dropdown-arrow {
    transition: transform 0.2s;
    flex-shrink: 0;
    color: #7f8c8d;
}

.user-dropdown-toggle .dropdown-arrow.rotated {
    transform: rotate(180deg);
}

.user-dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 280px;
    z-index: 1000;
    overflow: hidden;
}

.user-dropdown-header {
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    gap: 16px;
}

.user-avatar-large {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 20px;
    flex-shrink: 0;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.user-details {
    flex: 1;
    min-width: 0;
}

.user-name-large {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-email {
    font-size: 13px;
    opacity: 0.9;
    margin-bottom: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-role-badge {
    display: inline-block;
    padding: 4px 10px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.user-dropdown-divider {
    height: 1px;
    background: #e0e0e0;
    margin: 8px 0;
}

.user-dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 12px 20px;
    background: none;
    border: none;
    text-align: left;
    cursor: pointer;
    color: #2c3e50;
    font-size: 14px;
    transition: background 0.2s;
}

.user-dropdown-item:hover {
    background: #f7f8fc;
}

.user-dropdown-item svg {
    flex-shrink: 0;
    color: #7f8c8d;
}

.user-dropdown-item span {
    flex: 1;
}

.logout-btn {
    background: none;
    border: none;
    padding: 6px;
    cursor: pointer;
    color: #7f8c8d;
    transition: all 0.2s;
    border-radius: 4px;
}

.logout-btn:hover {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.app-content {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
}

/* Footer Styles */
.app-footer {
    background: white;
    border-top: 1px solid #e0e0e0;
    padding: 20px 30px;
    margin-top: auto;
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1400px;
    margin: 0 auto;
}

.footer-left p {
    font-size: 14px;
    color: #7f8c8d;
    margin: 0;
}

.footer-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.footer-link {
    font-size: 14px;
    color: #667eea;
    text-decoration: none;
    transition: color 0.2s;
}

.footer-link:hover {
    color: #5568d3;
    text-decoration: underline;
}

.footer-separator {
    color: #bdc3c7;
    font-size: 14px;
}

@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }

    .footer-right {
        flex-wrap: wrap;
        justify-content: center;
    }

    .app-sidebar {
        transform: translateX(-100%);
    }

    .mobile-menu-btn {
        display: block;
    }

    .app-main {
        margin-left: 0;
    }

    .header-search {
        display: none;
    }

    .header-logout-btn span {
        display: none;
    }

    .header-logout-btn {
        padding: 8px;
        min-width: 36px;
        justify-content: center;
    }
}
</style>

import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';
import App from './App.vue';
import Login from './components/Login.vue';
import SuperAdminLogin from './components/SuperAdminLogin.vue';
import Dashboard from './components/Dashboard.vue';
import DashboardHome from './components/pages/DashboardHome.vue';
import Users from './components/pages/Users.vue';
import Roles from './components/pages/Roles.vue';
import Permissions from './components/pages/Permissions.vue';
import Settings from './components/pages/Settings.vue';
import HotelSetup from './components/pages/HotelSetup.vue';
import RestaurantOperations from './components/pages/RestaurantOperations.vue';
import Rooms from './components/pages/Rooms.vue';
import RoomReservations from './components/pages/RoomReservations.vue';
import GuestManagement from './components/pages/GuestManagement.vue';
import FrontDeskOperations from './components/pages/FrontDeskOperations.vue';
import RoomServiceModule from './components/pages/RoomServiceModule.vue';
import AmenitiesServicesManagement from './components/pages/AmenitiesServicesManagement.vue';
import HousekeepingModule from './components/pages/HousekeepingModule.vue';
import InventoryStore from './components/pages/InventoryStore.vue';
import LinenHousekeeping from './components/pages/LinenHousekeeping.vue';
import AmenitiesConsumables from './components/pages/AmenitiesConsumables.vue';
import StockInOut from './components/pages/StockInOut.vue';
import SupplierManagement from './components/pages/SupplierManagement.vue';
import StockAlertsTracking from './components/pages/StockAlertsTracking.vue';
import StaffProfiles from './components/pages/StaffProfiles.vue';
import RoleAssignment from './components/pages/RoleAssignment.vue';
import ShiftScheduling from './components/pages/ShiftScheduling.vue';
import Attendance from './components/pages/Attendance.vue';
import TaskAssignment from './components/pages/TaskAssignment.vue';
import PerformanceLogs from './components/pages/PerformanceLogs.vue';
import UnifiedGuestFolio from './components/pages/UnifiedGuestFolio.vue';
import SplitBilling from './components/pages/SplitBilling.vue';
import PartialPayments from './components/pages/PartialPayments.vue';
import AdvancePayments from './components/pages/AdvancePayments.vue';
import RefundHandling from './components/pages/RefundHandling.vue';
import TaxServiceCharge from './components/pages/TaxServiceCharge.vue';
import ExpenseTracking from './components/pages/ExpenseTracking.vue';
import SupplierPayments from './components/pages/SupplierPayments.vue';
import DailyCashClosing from './components/pages/DailyCashClosing.vue';
import ProfitLossReport from './components/pages/ProfitLossReport.vue';
import OccupancyRateReport from './components/pages/OccupancyRateReport.vue';
import DailyMonthlyRevenueReport from './components/pages/DailyMonthlyRevenueReport.vue';
import RoomTypePerformanceReport from './components/pages/RoomTypePerformanceReport.vue';
import RestaurantSalesReport from './components/pages/RestaurantSalesReport.vue';
import RoomServicePerformanceReport from './components/pages/RoomServicePerformanceReport.vue';
import AmenitiesUsageReport from './components/pages/AmenitiesUsageReport.vue';
import HousekeepingEfficiencyReport from './components/pages/HousekeepingEfficiencyReport.vue';
import InventoryConsumptionReport from './components/pages/InventoryConsumptionReport.vue';
import ExpenseVsIncomeReport from './components/pages/ExpenseVsIncomeReport.vue';
import StaffProductivityReport from './components/pages/StaffProductivityReport.vue';
import LogoColorTheme from './components/pages/LogoColorTheme.vue';
import InvoiceReceiptTemplates from './components/pages/InvoiceReceiptTemplates.vue';
import CustomFooterBranding from './components/pages/CustomFooterBranding.vue';
import ModuleEnableDisable from './components/pages/ModuleEnableDisable.vue';
import TaxCurrencySetup from './components/pages/TaxCurrencySetup.vue';
import LanguageSettings from './components/pages/LanguageSettings.vue';
import DailyAutoBackup from './components/pages/DailyAutoBackup.vue';
import ManualBackup from './components/pages/ManualBackup.vue';
import ActivityLogs from './components/pages/ActivityLogs.vue';
import RoleBasedPermissions from './components/pages/RoleBasedPermissions.vue';
import SecureAuthentication from './components/pages/SecureAuthentication.vue';

const routes = [
    {
        path: '/install',
        name: 'install',
        component: () => import('./components/Install.vue')
    },
    {
        path: '/login',
        name: 'login',
        component: Login
    },
    {
        path: '/super-admin/login',
        name: 'superAdminLogin',
        component: SuperAdminLogin
    },
    {
        path: '/',
        component: Dashboard,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: DashboardHome,
                meta: { requiresAuth: true }
            },
            {
                path: 'users',
                name: 'users',
                component: Users,
                meta: { requiresAuth: true }
            },
            {
                path: 'roles',
                name: 'roles',
                component: Roles,
                meta: { requiresAuth: true }
            },
            {
                path: 'permissions',
                name: 'permissions',
                component: Permissions,
                meta: { requiresAuth: true }
            },
            {
                path: 'settings',
                name: 'settings',
                component: Settings,
                meta: { requiresAuth: true }
            },
            {
                path: 'hotel-setup',
                name: 'hotelSetup',
                component: HotelSetup,
                meta: { requiresAuth: true }
            },
            {
                path: 'restaurant-operations',
                name: 'restaurantOperations',
                component: RestaurantOperations,
                meta: { requiresAuth: true }
            },
            {
                path: 'rooms',
                name: 'rooms',
                component: Rooms,
                meta: { requiresAuth: true }
            },
            {
                path: 'room-service-module',
                name: 'roomServiceModule',
                component: RoomServiceModule,
                meta: { requiresAuth: true }
            },
            {
                path: 'room-reservations',
                name: 'roomReservations',
                component: RoomReservations,
                meta: { requiresAuth: true }
            },
            {
                path: 'guest-management',
                name: 'guestManagement',
                component: GuestManagement,
                meta: { requiresAuth: true }
            },
            {
                path: 'front-desk-operations',
                name: 'frontDeskOperations',
                component: FrontDeskOperations,
                meta: { requiresAuth: true }
            },
            {
                path: 'amenities-services',
                name: 'amenitiesServices',
                component: AmenitiesServicesManagement,
                meta: { requiresAuth: true }
            },
            {
                path: 'housekeeping',
                name: 'housekeeping',
                component: HousekeepingModule,
                meta: { requiresAuth: true }
            },
            {
                path: 'inventory-store',
                name: 'inventoryStore',
                component: InventoryStore,
                meta: { requiresAuth: true }
            },
            {
                path: 'linen-housekeeping',
                name: 'linenHousekeeping',
                component: LinenHousekeeping,
                meta: { requiresAuth: true }
            },
            {
                path: 'amenities-consumables',
                name: 'amenitiesConsumables',
                component: AmenitiesConsumables,
                meta: { requiresAuth: true }
            },
            {
                path: 'stock-in-out',
                name: 'stockInOut',
                component: StockInOut,
                meta: { requiresAuth: true }
            },
            {
                path: 'supplier-management',
                name: 'supplierManagement',
                component: SupplierManagement,
                meta: { requiresAuth: true }
            },
            {
                path: 'stock-alerts-tracking',
                name: 'stockAlertsTracking',
                component: StockAlertsTracking,
                meta: { requiresAuth: true }
            },
            {
                path: 'staff-profiles',
                name: 'staffProfiles',
                component: StaffProfiles,
                meta: { requiresAuth: true }
            },
            {
                path: 'role-assignment',
                name: 'roleAssignment',
                component: RoleAssignment,
                meta: { requiresAuth: true }
            },
            {
                path: 'shift-scheduling',
                name: 'shiftScheduling',
                component: ShiftScheduling,
                meta: { requiresAuth: true }
            },
            {
                path: 'attendance',
                name: 'attendance',
                component: Attendance,
                meta: { requiresAuth: true }
            },
            {
                path: 'task-assignment',
                name: 'taskAssignment',
                component: TaskAssignment,
                meta: { requiresAuth: true }
            },
            {
                path: 'performance-logs',
                name: 'performanceLogs',
                component: PerformanceLogs,
                meta: { requiresAuth: true }
            },
            {
                path: 'unified-guest-folio',
                name: 'unifiedGuestFolio',
                component: UnifiedGuestFolio,
                meta: { requiresAuth: true }
            },
            {
                path: 'split-billing',
                name: 'splitBilling',
                component: SplitBilling,
                meta: { requiresAuth: true }
            },
            {
                path: 'partial-payments',
                name: 'partialPayments',
                component: PartialPayments,
                meta: { requiresAuth: true }
            },
            {
                path: 'advance-payments',
                name: 'advancePayments',
                component: AdvancePayments,
                meta: { requiresAuth: true }
            },
            {
                path: 'refund-handling',
                name: 'refundHandling',
                component: RefundHandling,
                meta: { requiresAuth: true }
            },
            {
                path: 'tax-service-charge',
                name: 'taxServiceCharge',
                component: TaxServiceCharge,
                meta: { requiresAuth: true }
            },
            {
                path: 'expense-tracking',
                name: 'expenseTracking',
                component: ExpenseTracking,
                meta: { requiresAuth: true }
            },
            {
                path: 'supplier-payments',
                name: 'supplierPayments',
                component: SupplierPayments,
                meta: { requiresAuth: true }
            },
            {
                path: 'daily-cash-closing',
                name: 'dailyCashClosing',
                component: DailyCashClosing,
                meta: { requiresAuth: true }
            },
            {
                path: 'profit-loss-report',
                name: 'profitLossReport',
                component: ProfitLossReport,
                meta: { requiresAuth: true }
            },
            {
                path: 'occupancy-rate-report',
                name: 'occupancyRateReport',
                component: OccupancyRateReport,
                meta: { requiresAuth: true }
            },
            {
                path: 'daily-monthly-revenue-report',
                name: 'dailyMonthlyRevenueReport',
                component: DailyMonthlyRevenueReport,
                meta: { requiresAuth: true }
            },
            {
                path: 'room-type-performance-report',
                name: 'roomTypePerformanceReport',
                component: RoomTypePerformanceReport,
                meta: { requiresAuth: true }
            },
            {
                path: 'restaurant-sales-report',
                name: 'restaurantSalesReport',
                component: RestaurantSalesReport,
                meta: { requiresAuth: true }
            },
            {
                path: 'room-service-performance-report',
                name: 'roomServicePerformanceReport',
                component: RoomServicePerformanceReport,
                meta: { requiresAuth: true }
            },
            {
                path: 'amenities-usage-report',
                name: 'amenitiesUsageReport',
                component: AmenitiesUsageReport,
                meta: { requiresAuth: true }
            },
            {
                path: 'housekeeping-efficiency-report',
                name: 'housekeepingEfficiencyReport',
                component: HousekeepingEfficiencyReport,
                meta: { requiresAuth: true }
            },
            {
                path: 'inventory-consumption-report',
                name: 'inventoryConsumptionReport',
                component: InventoryConsumptionReport,
                meta: { requiresAuth: true }
            },
            {
                path: 'expense-vs-income-report',
                name: 'expenseVsIncomeReport',
                component: ExpenseVsIncomeReport,
                meta: { requiresAuth: true }
            },
            {
                path: 'staff-productivity-report',
                name: 'staffProductivityReport',
                component: StaffProductivityReport,
                meta: { requiresAuth: true }
            },
            {
                path: 'logo-color-theme',
                name: 'logoColorTheme',
                component: LogoColorTheme,
                meta: { requiresAuth: true }
            },
            {
                path: 'invoice-receipt-templates',
                name: 'invoiceReceiptTemplates',
                component: InvoiceReceiptTemplates,
                meta: { requiresAuth: true }
            },
            {
                path: 'custom-footer-branding',
                name: 'customFooterBranding',
                component: CustomFooterBranding,
                meta: { requiresAuth: true }
            },
            {
                path: 'module-enable-disable',
                name: 'moduleEnableDisable',
                component: ModuleEnableDisable,
                meta: { requiresAuth: true }
            },
            {
                path: 'tax-currency-setup',
                name: 'taxCurrencySetup',
                component: TaxCurrencySetup,
                meta: { requiresAuth: true }
            },
            {
                path: 'language-settings',
                name: 'languageSettings',
                component: LanguageSettings,
                meta: { requiresAuth: true }
            },
            {
                path: 'daily-auto-backup',
                name: 'dailyAutoBackup',
                component: DailyAutoBackup,
                meta: { requiresAuth: true }
            },
            {
                path: 'manual-backup',
                name: 'manualBackup',
                component: ManualBackup,
                meta: { requiresAuth: true }
            },
            {
                path: 'activity-logs',
                name: 'activityLogs',
                component: ActivityLogs,
                meta: { requiresAuth: true }
            },
            {
                path: 'role-based-permissions',
                name: 'roleBasedPermissions',
                component: RoleBasedPermissions,
                meta: { requiresAuth: true }
            },
            {
                path: 'secure-authentication',
                name: 'secureAuthentication',
                component: SecureAuthentication,
                meta: { requiresAuth: true }
            }
        ]
    },
    {
        path: '/dashboard',
        redirect: '/'
    }
];

// Auto-detect base path for subdirectory setups
const getBasePath = () => {
    const path = window.location.pathname;
    // If accessing from subdirectory, extract base path
    if (path.includes('/public/')) {
        const publicIndex = path.indexOf('/public/');
        return path.substring(0, publicIndex + 7) + '/';
    }
    return '/';
};

const router = createRouter({
    history: createWebHistory(getBasePath()),
    routes
});

// Navigation guard for authentication
router.beforeEach(async (to, from, next) => {
    // Allow access to installation page without auth check
    if (to.name === 'install' || to.path.startsWith('/install')) {
        next();
        return;
    }
    
    // Check if route requires authentication
    if (to.meta.requiresAuth) {
        try {
            // Check authentication via API
            const response = await axios.get('/api/auth/check');
            if (response.data.success && response.data.authenticated) {
                // User is authenticated, allow access
                next();
            } else {
                // Not authenticated, redirect to login using named route
                next({ name: 'login' });
            }
        } catch (error) {
            // Check if error is an authentication error (401) vs network/server error
            if (error.response && error.response.status === 401) {
                // Explicit authentication failure, redirect to login
                next({ name: 'login' });
            } else if (error.response && error.response.status >= 500) {
                // Server error - allow navigation if coming from authenticated route
                // This prevents false redirects on temporary server issues
                if (from.meta && from.meta.requiresAuth) {
                    // Coming from authenticated route, likely temporary server issue
                    next();
                } else {
                    // Not coming from authenticated route, require fresh auth check
                    next({ name: 'login' });
                }
            } else {
                // Network error or other issue - be conservative and require auth
                // But only if we're not already navigating between authenticated routes
                if (from.meta && from.meta.requiresAuth) {
                    // Allow navigation between authenticated routes on network errors
                    next();
                } else {
                    next({ name: 'login' });
                }
            }
        }
    } else if (to.name === 'login' || to.name === 'superAdminLogin') {
        // If accessing login pages, check if already authenticated
        try {
            const response = await axios.get('/api/auth/check');
            if (response.data.success && response.data.authenticated) {
                // Already authenticated, redirect to dashboard using named route
                next({ name: 'dashboard' });
            } else {
                // Not authenticated, allow access to login page
                next();
            }
        } catch (error) {
            // On error, allow access to login page (user can try to login)
            next();
        }
    } else {
        // Route doesn't require auth, allow access
        next();
    }
});

const app = createApp(App);
app.use(router);
app.mount('#app');

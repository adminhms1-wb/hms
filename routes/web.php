<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\RoomTypeTimeController;
use App\Http\Controllers\InventoryStoreItemController;
use App\Http\Controllers\InventoryTransactionController;

// API Routes for CRUD operations
Route::prefix('api')->group(function () {
    // Authentication routes
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/super-admin/login', [\App\Http\Controllers\AuthController::class, 'superAdminLogin']);
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/auth/check', [\App\Http\Controllers\AuthController::class, 'authCheck']);

    // Roles CRUD
    Route::apiResource('roles', RoleController::class);

    // Users CRUD
    Route::apiResource('users', UserController::class);

    // Permissions CRUD
    Route::apiResource('permissions', \App\Http\Controllers\PermissionController::class);

    // Hotel routes
    Route::get('/hotel/info', [HotelController::class, 'getInfo']);
    Route::post('/hotel/info', [HotelController::class, 'saveInfo']);
    Route::get('/hotel/settings', [HotelController::class, 'getSettings']);
    Route::post('/hotel/settings', [HotelController::class, 'saveSettings']);

    // Tax & Service Charge routes
    Route::get('/tax-service-charge/settings', [\App\Http\Controllers\TaxServiceChargeController::class, 'getSettings']);
    Route::post('/tax-service-charge/settings', [\App\Http\Controllers\TaxServiceChargeController::class, 'saveSettings']);

    // Room Management routes
    Route::apiResource('room-types', \App\Http\Controllers\RoomTypeController::class);
    Route::post('/room-types/{id}/restore', [\App\Http\Controllers\RoomTypeController::class, 'restore']);
    Route::delete('/room-types/{id}/force-delete', [\App\Http\Controllers\RoomTypeController::class, 'forceDelete']);
    // Specific room routes must come before apiResource to avoid route conflicts
    Route::get('/rooms/amenities/list', [\App\Http\Controllers\RoomController::class, 'getAmenities']);
    Route::get('/rooms/availability', [\App\Http\Controllers\RoomController::class, 'getAvailability']);
    Route::get('/rooms/for-assignment', [\App\Http\Controllers\ReservationController::class, 'getRoomsForAssignment']);
    Route::apiResource('rooms', \App\Http\Controllers\RoomController::class);
    Route::post('/rooms/{id}/restore', [\App\Http\Controllers\RoomController::class, 'restore']);
    Route::delete('/rooms/{id}/force-delete', [\App\Http\Controllers\RoomController::class, 'forceDelete']);

    // Reservation routes
    Route::get('/reservations', [\App\Http\Controllers\ReservationController::class, 'index']);
    Route::post('/reservations', [\App\Http\Controllers\ReservationController::class, 'store']);
    Route::get('/reservations/{reservation}', [\App\Http\Controllers\ReservationController::class, 'show']);
    Route::put('/reservations/{reservation}', [\App\Http\Controllers\ReservationController::class, 'update']);
    Route::post('/reservations/{reservation}/check-in', [\App\Http\Controllers\ReservationController::class, 'checkIn']);
    Route::post('/reservations/{reservation}/check-out', [\App\Http\Controllers\ReservationController::class, 'checkOut']);
    Route::post('/reservations/{reservation}/extend', [\App\Http\Controllers\ReservationController::class, 'extendStay']);
    Route::post('/reservations/{reservation}/early-checkout', [\App\Http\Controllers\ReservationController::class, 'earlyCheckout']);
    Route::post('/reservations/{reservation}/cancel', [\App\Http\Controllers\ReservationController::class, 'cancel']);
    Route::get('/reservations/group/{groupId}', [\App\Http\Controllers\ReservationController::class, 'getGroupBookings']);
    Route::get('/reservations/groups/all', [\App\Http\Controllers\ReservationController::class, 'getAllGroupBookings']);
    Route::post('/reservations/assign-guest', [\App\Http\Controllers\ReservationController::class, 'assignGuestToRoom']);
    Route::post('/rooms/{room}/status', [\App\Http\Controllers\ReservationController::class, 'updateRoomStatus']);
    Route::get('/reservations/{reservation}/guests', [\App\Http\Controllers\ReservationController::class, 'getReservationGuests']);
    Route::post('/reservations/{reservation}/guests/add', [\App\Http\Controllers\ReservationController::class, 'addGuests']);
    Route::post('/reservations/{reservation}/guests/remove', [\App\Http\Controllers\ReservationController::class, 'removeGuests']);
    Route::post('/reservations/{reservation}/deposit', [\App\Http\Controllers\ReservationController::class, 'updateDeposit']);
    Route::get('/reservations/{reservation}/bill-preview', [\App\Http\Controllers\ReservationController::class, 'billPreview']);

    // Online Booking API (Public endpoints for external integrations)
    Route::prefix('online-booking')->group(function () {
        Route::get('/rooms/available', [\App\Http\Controllers\OnlineBookingController::class, 'getAvailableRooms']);
        Route::post('/bookings', [\App\Http\Controllers\OnlineBookingController::class, 'createBooking']);
        Route::post('/bookings/{reservationId}/status', [\App\Http\Controllers\OnlineBookingController::class, 'updateBookingStatus']);
        Route::post('/bookings/{reservationId}/cancel', [\App\Http\Controllers\OnlineBookingController::class, 'cancelBooking']);
    });

    // Pricing routes
    Route::get('/pricing/seasonal', [PricingController::class, 'getSeasonalPrices']);
    Route::post('/pricing/seasonal', [PricingController::class, 'saveSeasonalPrice']);
    Route::delete('/pricing/seasonal/{id}', [PricingController::class, 'deleteSeasonalPrice']);
    Route::get('/pricing/rules', [PricingController::class, 'getPricingRules']);
    Route::post('/pricing/rules', [PricingController::class, 'savePricingRule']);
    Route::delete('/pricing/rules/{id}', [PricingController::class, 'deletePricingRule']);

    // Room Type Times routes
    Route::get('/room-type-times', [RoomTypeTimeController::class, 'index']);
    Route::post('/room-type-times', [RoomTypeTimeController::class, 'store']);
    Route::delete('/room-type-times/{id}', [RoomTypeTimeController::class, 'destroy']);

    // Guest Management routes
    Route::apiResource('guests', \App\Http\Controllers\GuestController::class);
    Route::get('/guests/{id}/history', [\App\Http\Controllers\GuestController::class, 'getHistory']);
    Route::post('/guests/{id}/flag', [\App\Http\Controllers\GuestController::class, 'flagGuest']);
    Route::post('/guests/{id}/unflag', [\App\Http\Controllers\GuestController::class, 'unflagGuest']);
    Route::post('/guests/{id}/blacklist', [\App\Http\Controllers\GuestController::class, 'blacklistGuest']);
    Route::post('/guests/{id}/unblacklist', [\App\Http\Controllers\GuestController::class, 'unblacklistGuest']);
    Route::get('/reservations/{reservationId}/guests', [\App\Http\Controllers\GuestController::class, 'getReservationGuests']);
    Route::post('/reservations/{reservationId}/guests', [\App\Http\Controllers\GuestController::class, 'addGuestToReservation']);
    Route::delete('/reservations/{reservationId}/guests/{guestId}', [\App\Http\Controllers\GuestController::class, 'removeGuestFromReservation']);

    // Room Service routes
    Route::get('/room-service-orders', [\App\Http\Controllers\RoomServiceController::class, 'index']);
    Route::post('/room-service-orders', [\App\Http\Controllers\RoomServiceController::class, 'store']);
    Route::get('/room-service-orders/{roomServiceOrder}', [\App\Http\Controllers\RoomServiceController::class, 'show']);
    Route::put('/room-service-orders/{roomServiceOrder}', [\App\Http\Controllers\RoomServiceController::class, 'update']);
    Route::post('/room-service-orders/{roomServiceOrder}/post-charges', [\App\Http\Controllers\RoomServiceController::class, 'postCharges']);
    Route::get('/room-service-orders/room/details', [\App\Http\Controllers\RoomServiceController::class, 'getRoomDetails']);

    // Amenities & Services routes
    Route::apiResource('amenities', \App\Http\Controllers\AmenityController::class);
    Route::apiResource('services', \App\Http\Controllers\ServiceController::class);

    // Service Bookings routes
    // Specific routes must come before parameterized routes to avoid route conflicts
    Route::get('/service-bookings/usage-logs', [\App\Http\Controllers\ServiceBookingController::class, 'getUsageLogs']);
    Route::get('/service-bookings', [\App\Http\Controllers\ServiceBookingController::class, 'index']);
    Route::post('/service-bookings', [\App\Http\Controllers\ServiceBookingController::class, 'store']);
    Route::get('/service-bookings/{serviceBooking}', [\App\Http\Controllers\ServiceBookingController::class, 'show']);
    Route::put('/service-bookings/{serviceBooking}', [\App\Http\Controllers\ServiceBookingController::class, 'update']);
    Route::delete('/service-bookings/{serviceBooking}', [\App\Http\Controllers\ServiceBookingController::class, 'destroy']);
    Route::get('/services/{service}/time-slots', [\App\Http\Controllers\ServiceBookingController::class, 'getTimeSlots']);
    Route::post('/service-bookings/{serviceBooking}/post-charges', [\App\Http\Controllers\ServiceBookingController::class, 'postChargesToRoom']);

    // Service Time Slots routes
    Route::apiResource('service-time-slots', \App\Http\Controllers\ServiceTimeSlotController::class);

    // Housekeeping routes
    Route::apiResource('housekeeping-tasks', \App\Http\Controllers\HousekeepingController::class);
    Route::get('/housekeeping-tasks/rooms/needing-cleaning', [\App\Http\Controllers\HousekeepingController::class, 'getRoomsNeedingCleaning']);

    // Maintenance requests routes
    Route::apiResource('maintenance-requests', \App\Http\Controllers\MaintenanceRequestController::class);

    // Lost and found routes
    Route::apiResource('lost-and-found', \App\Http\Controllers\LostAndFoundController::class);

    // Inventory Store Management routes
    Route::get('/inventory-store-items', [InventoryStoreItemController::class, 'index']);
    Route::post('/inventory-store-items', [InventoryStoreItemController::class, 'store']);
    Route::match(['put', 'post'], '/inventory-store-items/{id}', [InventoryStoreItemController::class, 'update']);
    Route::match(['delete', 'post'], '/inventory-store-items/{id}/delete', [InventoryStoreItemController::class, 'destroy']);

    // Linen Housekeeping Items routes
    Route::get('/linen-housekeeping-items', [\App\Http\Controllers\LinenHousekeepingItemController::class, 'index']);
    Route::post('/linen-housekeeping-items', [\App\Http\Controllers\LinenHousekeepingItemController::class, 'store']);
    Route::match(['put', 'post'], '/linen-housekeeping-items/{id}', [\App\Http\Controllers\LinenHousekeepingItemController::class, 'update']);
    Route::match(['delete', 'post'], '/linen-housekeeping-items/{id}/delete', [\App\Http\Controllers\LinenHousekeepingItemController::class, 'destroy']);

    // Amenities Consumables Items routes
    Route::get('/amenities-consumables-items', [\App\Http\Controllers\AmenitiesConsumableItemController::class, 'index']);
    Route::post('/amenities-consumables-items', [\App\Http\Controllers\AmenitiesConsumableItemController::class, 'store']);
    Route::match(['put', 'post'], '/amenities-consumables-items/{id}', [\App\Http\Controllers\AmenitiesConsumableItemController::class, 'update']);
    Route::match(['delete', 'post'], '/amenities-consumables-items/{id}/delete', [\App\Http\Controllers\AmenitiesConsumableItemController::class, 'destroy']);

    // Suppliers routes
    Route::get('/suppliers', [\App\Http\Controllers\SupplierController::class, 'index']);
    Route::post('/suppliers', [\App\Http\Controllers\SupplierController::class, 'store']);
    Route::match(['put', 'post'], '/suppliers/{id}', [\App\Http\Controllers\SupplierController::class, 'update']);
    Route::match(['delete', 'post'], '/suppliers/{id}/delete', [\App\Http\Controllers\SupplierController::class, 'destroy']);

    // Stock Alerts & Tracking routes
    Route::get('/stock-alerts-tracking', [\App\Http\Controllers\StockAlertsTrackingController::class, 'index']);
    Route::post('/stock-alerts-tracking/update-stock/{type}/{id}', [\App\Http\Controllers\StockAlertsTrackingController::class, 'updateStock']);
    Route::post('/stock-alerts-tracking/update-expiry/{id}', [\App\Http\Controllers\StockAlertsTrackingController::class, 'updateExpiry']);
    Route::post('/stock-alerts-tracking/delete-item/{type}/{id}', [\App\Http\Controllers\StockAlertsTrackingController::class, 'deleteItem']);

    // Staff & Shift Management routes
    Route::get('/staff-profiles', [\App\Http\Controllers\StaffProfileController::class, 'index']);
    Route::post('/staff-profiles', [\App\Http\Controllers\StaffProfileController::class, 'store']);
    Route::match(['put', 'post'], '/staff-profiles/{id}', [\App\Http\Controllers\StaffProfileController::class, 'update']);
    Route::match(['delete', 'post'], '/staff-profiles/{id}/delete', [\App\Http\Controllers\StaffProfileController::class, 'destroy']);

    Route::get('/role-assignment', [\App\Http\Controllers\RoleAssignmentController::class, 'index']);
    Route::post('/role-assignment/{userId}', [\App\Http\Controllers\RoleAssignmentController::class, 'assignRole']);

    Route::get('/shift-scheduling', [\App\Http\Controllers\ShiftSchedulingController::class, 'index']);
    Route::post('/shift-scheduling', [\App\Http\Controllers\ShiftSchedulingController::class, 'store']);
    Route::match(['put', 'post'], '/shift-scheduling/{id}', [\App\Http\Controllers\ShiftSchedulingController::class, 'update']);
    Route::match(['delete', 'post'], '/shift-scheduling/{id}/delete', [\App\Http\Controllers\ShiftSchedulingController::class, 'destroy']);

    Route::get('/attendance', [\App\Http\Controllers\AttendanceController::class, 'index']);
    Route::post('/attendance', [\App\Http\Controllers\AttendanceController::class, 'store']);
    Route::match(['put', 'post'], '/attendance/{id}', [\App\Http\Controllers\AttendanceController::class, 'update']);
    Route::match(['delete', 'post'], '/attendance/{id}/delete', [\App\Http\Controllers\AttendanceController::class, 'destroy']);

    Route::get('/task-assignment', [\App\Http\Controllers\TaskAssignmentController::class, 'index']);
    Route::post('/task-assignment', [\App\Http\Controllers\TaskAssignmentController::class, 'store']);
    Route::match(['put', 'post'], '/task-assignment/{id}', [\App\Http\Controllers\TaskAssignmentController::class, 'update']);
    Route::match(['delete', 'post'], '/task-assignment/{id}/delete', [\App\Http\Controllers\TaskAssignmentController::class, 'destroy']);

    Route::get('/performance-logs', [\App\Http\Controllers\PerformanceLogController::class, 'index']);
    Route::post('/performance-logs', [\App\Http\Controllers\PerformanceLogController::class, 'store']);
    Route::match(['put', 'post'], '/performance-logs/{id}', [\App\Http\Controllers\PerformanceLogController::class, 'update']);
    Route::match(['delete', 'post'], '/performance-logs/{id}/delete', [\App\Http\Controllers\PerformanceLogController::class, 'destroy']);

    // Inventory stock in/out transactions routes
    Route::get('/inventory-transactions', [InventoryTransactionController::class, 'index']);
    Route::post('/inventory-transactions', [InventoryTransactionController::class, 'store']);
    Route::match(['put', 'post'], '/inventory-transactions/{id}', [InventoryTransactionController::class, 'update']);
    Route::match(['delete', 'post'], '/inventory-transactions/{id}/delete', [InventoryTransactionController::class, 'destroy']);

    // Billing & Finance routes
    Route::get('/unified-guest-folio', [\App\Http\Controllers\UnifiedGuestFolioController::class, 'index']);
    Route::post('/unified-guest-folio', [\App\Http\Controllers\UnifiedGuestFolioController::class, 'store']);
    Route::get('/unified-guest-folio/{id}', [\App\Http\Controllers\UnifiedGuestFolioController::class, 'show']);
    Route::match(['put', 'post'], '/unified-guest-folio/{id}', [\App\Http\Controllers\UnifiedGuestFolioController::class, 'update']);
    Route::match(['delete', 'post'], '/unified-guest-folio/{id}/delete', [\App\Http\Controllers\UnifiedGuestFolioController::class, 'destroy']);

    Route::get('/split-billing', [\App\Http\Controllers\SplitBillingController::class, 'index']);
    Route::post('/split-billing', [\App\Http\Controllers\SplitBillingController::class, 'store']);
    Route::get('/split-billing/{id}', [\App\Http\Controllers\SplitBillingController::class, 'show']);
    Route::match(['put', 'post'], '/split-billing/{id}', [\App\Http\Controllers\SplitBillingController::class, 'update']);
    Route::match(['delete', 'post'], '/split-billing/{id}/delete', [\App\Http\Controllers\SplitBillingController::class, 'destroy']);

    Route::get('/partial-payments', [\App\Http\Controllers\PartialPaymentsController::class, 'index']);
    Route::post('/partial-payments', [\App\Http\Controllers\PartialPaymentsController::class, 'store']);
    Route::get('/partial-payments/{id}', [\App\Http\Controllers\PartialPaymentsController::class, 'show']);
    Route::match(['put', 'post'], '/partial-payments/{id}', [\App\Http\Controllers\PartialPaymentsController::class, 'update']);
    Route::match(['delete', 'post'], '/partial-payments/{id}/delete', [\App\Http\Controllers\PartialPaymentsController::class, 'destroy']);

    Route::get('/advance-payments', [\App\Http\Controllers\AdvancePaymentsController::class, 'index']);
    Route::post('/advance-payments', [\App\Http\Controllers\AdvancePaymentsController::class, 'store']);
    Route::get('/advance-payments/{id}', [\App\Http\Controllers\AdvancePaymentsController::class, 'show']);
    Route::match(['put', 'post'], '/advance-payments/{id}', [\App\Http\Controllers\AdvancePaymentsController::class, 'update']);
    Route::match(['delete', 'post'], '/advance-payments/{id}/delete', [\App\Http\Controllers\AdvancePaymentsController::class, 'destroy']);

    Route::get('/refund-handling', [\App\Http\Controllers\RefundHandlingController::class, 'index']);
    Route::post('/refund-handling', [\App\Http\Controllers\RefundHandlingController::class, 'store']);
    Route::get('/refund-handling/{id}', [\App\Http\Controllers\RefundHandlingController::class, 'show']);
    Route::match(['put', 'post'], '/refund-handling/{id}', [\App\Http\Controllers\RefundHandlingController::class, 'update']);
    Route::match(['delete', 'post'], '/refund-handling/{id}/delete', [\App\Http\Controllers\RefundHandlingController::class, 'destroy']);

    Route::get('/expense-tracking', [\App\Http\Controllers\ExpenseTrackingController::class, 'index']);
    Route::post('/expense-tracking', [\App\Http\Controllers\ExpenseTrackingController::class, 'store']);
    Route::get('/expense-tracking/{id}', [\App\Http\Controllers\ExpenseTrackingController::class, 'show']);
    Route::match(['put', 'post'], '/expense-tracking/{id}', [\App\Http\Controllers\ExpenseTrackingController::class, 'update']);
    Route::match(['delete', 'post'], '/expense-tracking/{id}/delete', [\App\Http\Controllers\ExpenseTrackingController::class, 'destroy']);

    Route::get('/supplier-payments', [\App\Http\Controllers\SupplierPaymentsController::class, 'index']);
    Route::post('/supplier-payments', [\App\Http\Controllers\SupplierPaymentsController::class, 'store']);
    Route::get('/supplier-payments/{id}', [\App\Http\Controllers\SupplierPaymentsController::class, 'show']);
    Route::match(['put', 'post'], '/supplier-payments/{id}', [\App\Http\Controllers\SupplierPaymentsController::class, 'update']);
    Route::match(['delete', 'post'], '/supplier-payments/{id}/delete', [\App\Http\Controllers\SupplierPaymentsController::class, 'destroy']);

    Route::get('/daily-cash-closing', [\App\Http\Controllers\DailyCashClosingController::class, 'index']);
    Route::post('/daily-cash-closing', [\App\Http\Controllers\DailyCashClosingController::class, 'store']);
    Route::get('/daily-cash-closing/{id}', [\App\Http\Controllers\DailyCashClosingController::class, 'show']);
    Route::match(['put', 'post'], '/daily-cash-closing/{id}', [\App\Http\Controllers\DailyCashClosingController::class, 'update']);
    Route::match(['delete', 'post'], '/daily-cash-closing/{id}/delete', [\App\Http\Controllers\DailyCashClosingController::class, 'destroy']);

    // Reports & Analytics routes
    Route::get('/reports/occupancy-rate', [\App\Http\Controllers\ReportsController::class, 'occupancyRate']);
    Route::get('/reports/daily-monthly-revenue', [\App\Http\Controllers\ReportsController::class, 'dailyMonthlyRevenue']);
    Route::get('/reports/room-type-performance', [\App\Http\Controllers\ReportsController::class, 'roomTypePerformance']);
    Route::get('/reports/restaurant-sales', [\App\Http\Controllers\ReportsController::class, 'restaurantSales']);
    Route::get('/reports/room-service-performance', [\App\Http\Controllers\ReportsController::class, 'roomServicePerformance']);
    Route::get('/reports/amenities-usage', [\App\Http\Controllers\ReportsController::class, 'amenitiesUsage']);
    Route::get('/reports/housekeeping-efficiency', [\App\Http\Controllers\ReportsController::class, 'housekeepingEfficiency']);
    Route::get('/reports/inventory-consumption', [\App\Http\Controllers\ReportsController::class, 'inventoryConsumption']);
    Route::get('/reports/expense-vs-income', [\App\Http\Controllers\ReportsController::class, 'expenseVsIncome']);
    Route::get('/reports/staff-productivity', [\App\Http\Controllers\ReportsController::class, 'staffProductivity']);
    Route::get('/profit-loss-report', [\App\Http\Controllers\ProfitLossReportController::class, 'index']);

    // Settings & White-Labeling routes
    Route::get('/settings/auto-backup', [\App\Http\Controllers\AutoBackupController::class, 'getSettings']);
    Route::post('/settings/auto-backup', [\App\Http\Controllers\AutoBackupController::class, 'saveSettings']);
    Route::get('/backups', [\App\Http\Controllers\AutoBackupController::class, 'index']);
    Route::get('/backups/manual', [\App\Http\Controllers\ManualBackupController::class, 'index']);
    Route::get('/backups/tables', [\App\Http\Controllers\ManualBackupController::class, 'getTables']);
    Route::post('/backups/manual', [\App\Http\Controllers\ManualBackupController::class, 'store']);
    Route::get('/backups/{id}/download', [\App\Http\Controllers\ManualBackupController::class, 'download']);
    Route::delete('/backups/{id}', [\App\Http\Controllers\ManualBackupController::class, 'destroy']);
    Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index']);
    Route::get('/settings/authentication', [\App\Http\Controllers\SecureAuthenticationController::class, 'getSettings']);
    Route::post('/settings/authentication', [\App\Http\Controllers\SecureAuthenticationController::class, 'saveSettings']);

    // Settings & White-Labeling routes
    Route::get('/settings/logo-color-theme', [\App\Http\Controllers\SettingsController::class, 'getLogoColorTheme']);
    Route::post('/settings/logo-color-theme', [\App\Http\Controllers\SettingsController::class, 'saveLogoColorTheme']);
    Route::post('/settings/upload-logo', [\App\Http\Controllers\SettingsController::class, 'uploadLogo']);
    Route::post('/settings/upload-favicon', [\App\Http\Controllers\SettingsController::class, 'uploadFavicon']);
    Route::delete('/settings/remove-logo', [\App\Http\Controllers\SettingsController::class, 'removeLogo']);
    Route::delete('/settings/remove-favicon', [\App\Http\Controllers\SettingsController::class, 'removeFavicon']);
    Route::get('/settings/invoice-templates', [\App\Http\Controllers\SettingsController::class, 'getInvoiceTemplates']);
    Route::post('/settings/invoice-templates', [\App\Http\Controllers\SettingsController::class, 'saveInvoiceTemplates']);
    Route::get('/settings/footer-branding', [\App\Http\Controllers\SettingsController::class, 'getFooterBranding']);
    Route::post('/settings/footer-branding', [\App\Http\Controllers\SettingsController::class, 'saveFooterBranding']);
    Route::get('/settings/modules', [\App\Http\Controllers\SettingsController::class, 'getModules']);
    Route::post('/settings/modules', [\App\Http\Controllers\SettingsController::class, 'saveModules']);
    Route::get('/settings/tax-currency', [\App\Http\Controllers\SettingsController::class, 'getTaxCurrency']);
    Route::post('/settings/tax-currency', [\App\Http\Controllers\SettingsController::class, 'saveTaxCurrency']);
    Route::get('/settings/language', [\App\Http\Controllers\SettingsController::class, 'getLanguage']);
    Route::post('/settings/language', [\App\Http\Controllers\SettingsController::class, 'saveLanguage']);
});

// Installation routes (must be before catch-all route)
Route::prefix('install')->group(function () {
    Route::get('/', function () {
        return view('app');
    });
    Route::get('/{any}', function () {
        return view('app');
    })->where('any', '.*');
});

Route::prefix('api/install')->group(function () {
    Route::get('/check', [\App\Http\Controllers\InstallController::class, 'checkInstallation']);
    Route::post('/check-database', [\App\Http\Controllers\InstallController::class, 'checkDatabase']);
    Route::post('/run-migrations', [\App\Http\Controllers\InstallController::class, 'runMigrations']);
    Route::post('/create-super-admin', [\App\Http\Controllers\InstallController::class, 'createSuperAdmin']);
    Route::post('/complete', [\App\Http\Controllers\InstallController::class, 'completeInstallation']);
});

// Serve Vue.js SPA for all routes
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

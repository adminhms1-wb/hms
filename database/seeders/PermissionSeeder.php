<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management Permissions
            ['name' => 'View Users', 'slug' => 'view_users', 'module' => 'users', 'description' => 'Can view list of users'],
            ['name' => 'Create Users', 'slug' => 'create_users', 'module' => 'users', 'description' => 'Can create new users'],
            ['name' => 'Edit Users', 'slug' => 'edit_users', 'module' => 'users', 'description' => 'Can edit existing users'],
            ['name' => 'Delete Users', 'slug' => 'delete_users', 'module' => 'users', 'description' => 'Can delete users'],

            // Role Management Permissions
            ['name' => 'View Roles', 'slug' => 'view_roles', 'module' => 'roles', 'description' => 'Can view list of roles'],
            ['name' => 'Create Roles', 'slug' => 'create_roles', 'module' => 'roles', 'description' => 'Can create new roles'],
            ['name' => 'Edit Roles', 'slug' => 'edit_roles', 'module' => 'roles', 'description' => 'Can edit existing roles'],
            ['name' => 'Delete Roles', 'slug' => 'delete_roles', 'module' => 'roles', 'description' => 'Can delete roles'],
            ['name' => 'Manage Role Permissions', 'slug' => 'manage_role_permissions', 'module' => 'roles', 'description' => 'Can assign permissions to roles'],

            // Booking Management Permissions
            ['name' => 'View Bookings', 'slug' => 'view_bookings', 'module' => 'bookings', 'description' => 'Can view bookings'],
            ['name' => 'Create Bookings', 'slug' => 'create_bookings', 'module' => 'bookings', 'description' => 'Can create new bookings'],
            ['name' => 'Edit Bookings', 'slug' => 'edit_bookings', 'module' => 'bookings', 'description' => 'Can edit bookings'],
            ['name' => 'Cancel Bookings', 'slug' => 'cancel_bookings', 'module' => 'bookings', 'description' => 'Can cancel bookings'],
            ['name' => 'Check-in Guests', 'slug' => 'checkin_guests', 'module' => 'bookings', 'description' => 'Can check-in guests'],
            ['name' => 'Check-out Guests', 'slug' => 'checkout_guests', 'module' => 'bookings', 'description' => 'Can check-out guests'],

            // Guest Management Permissions
            ['name' => 'View Guests', 'slug' => 'view_guests', 'module' => 'guests', 'description' => 'Can view guest information'],
            ['name' => 'Create Guests', 'slug' => 'create_guests', 'module' => 'guests', 'description' => 'Can create guest profiles'],
            ['name' => 'Edit Guests', 'slug' => 'edit_guests', 'module' => 'guests', 'description' => 'Can edit guest information'],

            // Room Management Permissions
            ['name' => 'View Rooms', 'slug' => 'view_rooms', 'module' => 'rooms', 'description' => 'Can view room information'],
            ['name' => 'Manage Rooms', 'slug' => 'manage_rooms', 'module' => 'rooms', 'description' => 'Can manage room status and details'],

            // Restaurant Permissions
            ['name' => 'View Restaurant Orders', 'slug' => 'view_restaurant_orders', 'module' => 'restaurant', 'description' => 'Can view restaurant orders'],
            ['name' => 'Manage Menu', 'slug' => 'manage_menu', 'module' => 'restaurant', 'description' => 'Can manage restaurant menu'],
            ['name' => 'Process Orders', 'slug' => 'process_orders', 'module' => 'restaurant', 'description' => 'Can process restaurant orders'],

            // Room Service Permissions
            ['name' => 'View Room Service Orders', 'slug' => 'view_room_service', 'module' => 'room_service', 'description' => 'Can view room service orders'],
            ['name' => 'Process Room Service', 'slug' => 'process_room_service', 'module' => 'room_service', 'description' => 'Can process room service orders'],

            // Housekeeping Permissions
            ['name' => 'View Housekeeping Tasks', 'slug' => 'view_housekeeping', 'module' => 'housekeeping', 'description' => 'Can view housekeeping tasks'],
            ['name' => 'Manage Housekeeping', 'slug' => 'manage_housekeeping', 'module' => 'housekeeping', 'description' => 'Can manage housekeeping tasks'],
            ['name' => 'Update Room Status', 'slug' => 'update_room_status', 'module' => 'housekeeping', 'description' => 'Can update room cleaning status'],

            // Finance Permissions
            ['name' => 'View Financial Reports', 'slug' => 'view_financial_reports', 'module' => 'finance', 'description' => 'Can view financial reports'],
            ['name' => 'Manage Payments', 'slug' => 'manage_payments', 'module' => 'finance', 'description' => 'Can process payments'],
            ['name' => 'View Billing', 'slug' => 'view_billing', 'module' => 'finance', 'description' => 'Can view billing information'],
            ['name' => 'Manage Accounts', 'slug' => 'manage_accounts', 'module' => 'finance', 'description' => 'Can manage accounting'],

            // Inventory Permissions
            ['name' => 'View Inventory', 'slug' => 'view_inventory', 'module' => 'inventory', 'description' => 'Can view inventory items'],
            ['name' => 'Manage Inventory', 'slug' => 'manage_inventory', 'module' => 'inventory', 'description' => 'Can manage inventory items'],
            ['name' => 'Manage Suppliers', 'slug' => 'manage_suppliers', 'module' => 'inventory', 'description' => 'Can manage suppliers'],

            // System Admin Permissions
            ['name' => 'System Settings', 'slug' => 'system_settings', 'module' => 'system', 'description' => 'Can access system settings'],
            ['name' => 'View Reports', 'slug' => 'view_reports', 'module' => 'system', 'description' => 'Can view all reports'],
            ['name' => 'Manage System', 'slug' => 'manage_system', 'module' => 'system', 'description' => 'Can manage system configuration'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}



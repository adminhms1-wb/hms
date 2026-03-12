<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all roles
        $superAdmin = Role::where('slug', 'super_admin')->first();
        $frontDesk = Role::where('slug', 'front_desk')->first();
        $restaurantManager = Role::where('slug', 'restaurant_manager')->first();
        $roomService = Role::where('slug', 'room_service')->first();
        $housekeeping = Role::where('slug', 'housekeeping')->first();
        $finance = Role::where('slug', 'finance')->first();
        $inventoryManager = Role::where('slug', 'inventory_manager')->first();
        $systemAdmin = Role::where('slug', 'system_admin')->first();

        // Get all permissions
        $allPermissions = Permission::all();
        $permissions = [];
        foreach ($allPermissions as $perm) {
            $permissions[$perm->slug] = $perm;
        }

        // Super Admin - All permissions
        if ($superAdmin) {
            $superAdmin->permissions()->sync($allPermissions->pluck('id')->toArray());
        }

        // Front Desk / Reception Permissions
        if ($frontDesk) {
            $frontDeskPerms = array_filter([
                $permissions['view_bookings']->id ?? null,
                $permissions['create_bookings']->id ?? null,
                $permissions['edit_bookings']->id ?? null,
                $permissions['checkin_guests']->id ?? null,
                $permissions['checkout_guests']->id ?? null,
                $permissions['view_guests']->id ?? null,
                $permissions['create_guests']->id ?? null,
                $permissions['edit_guests']->id ?? null,
                $permissions['view_rooms']->id ?? null,
                $permissions['view_billing']->id ?? null,
            ]);
            $frontDesk->permissions()->sync($frontDeskPerms);
        }

        // Restaurant Manager Permissions
        if ($restaurantManager) {
            $restaurantPerms = array_filter([
                $permissions['view_restaurant_orders']->id ?? null,
                $permissions['manage_menu']->id ?? null,
                $permissions['process_orders']->id ?? null,
                $permissions['view_reports']->id ?? null,
            ]);
            $restaurantManager->permissions()->sync($restaurantPerms);
        }

        // Room Service Staff Permissions
        if ($roomService) {
            $roomServicePerms = array_filter([
                $permissions['view_room_service']->id ?? null,
                $permissions['process_room_service']->id ?? null,
                $permissions['view_rooms']->id ?? null,
            ]);
            $roomService->permissions()->sync($roomServicePerms);
        }

        // Housekeeping Staff Permissions
        if ($housekeeping) {
            $housekeepingPerms = array_filter([
                $permissions['view_housekeeping']->id ?? null,
                $permissions['manage_housekeeping']->id ?? null,
                $permissions['update_room_status']->id ?? null,
                $permissions['view_rooms']->id ?? null,
            ]);
            $housekeeping->permissions()->sync($housekeepingPerms);
        }

        // Finance / Accounts Permissions
        if ($finance) {
            $financePerms = array_filter([
                $permissions['view_financial_reports']->id ?? null,
                $permissions['manage_payments']->id ?? null,
                $permissions['view_billing']->id ?? null,
                $permissions['manage_accounts']->id ?? null,
                $permissions['view_reports']->id ?? null,
            ]);
            $finance->permissions()->sync($financePerms);
        }

        // Inventory Manager Permissions
        if ($inventoryManager) {
            $inventoryPerms = array_filter([
                $permissions['view_inventory']->id ?? null,
                $permissions['manage_inventory']->id ?? null,
                $permissions['manage_suppliers']->id ?? null,
                $permissions['view_reports']->id ?? null,
            ]);
            $inventoryManager->permissions()->sync($inventoryPerms);
        }

        // System Admin Permissions
        if ($systemAdmin) {
            $systemAdminPerms = array_filter([
                $permissions['view_users']->id ?? null,
                $permissions['create_users']->id ?? null,
                $permissions['edit_users']->id ?? null,
                $permissions['delete_users']->id ?? null,
                $permissions['view_roles']->id ?? null,
                $permissions['create_roles']->id ?? null,
                $permissions['edit_roles']->id ?? null,
                $permissions['delete_roles']->id ?? null,
                $permissions['manage_role_permissions']->id ?? null,
                $permissions['system_settings']->id ?? null,
                $permissions['view_reports']->id ?? null,
                $permissions['manage_system']->id ?? null,
            ]);
            $systemAdmin->permissions()->sync($systemAdminPerms);
        }
    }
}


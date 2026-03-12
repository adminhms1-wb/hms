<template>
    <div class="module-enable-disable-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Module Enable/Disable</h1>
                <p class="page-subtitle">Enable or disable system modules</p>
            </div>
        </div>

        <div class="content-card">
            <div class="modules-section">
                <h3>Available Modules</h3>
                <div class="modules-list">
                    <div v-for="module in modules" :key="module.id" class="module-item">
                        <div class="module-info">
                            <h4>{{ module.name }}</h4>
                            <p>{{ module.description }}</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" v-model="module.enabled" @change="updateModule(module.id, module.enabled)" />
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAlert } from '@/composables/useAlert';

const { success: showSuccess, error: showError } = useAlert();

const modules = ref([
    { id: 'hotel_setup', name: 'Hotel Setup', description: 'Basic hotel configuration and settings', enabled: true },
    { id: 'restaurant_operations', name: 'Restaurant Operations', description: 'Restaurant management and POS system', enabled: true },
    { id: 'room_management', name: 'Room Management', description: 'Room types, rates, and availability', enabled: true },
    { id: 'reservations', name: 'Reservations', description: 'Room booking and reservation management', enabled: true },
    { id: 'front_desk', name: 'Front Desk Operations', description: 'Check-in, check-out, and guest services', enabled: true },
    { id: 'amenities', name: 'Amenities & Services', description: 'Hotel amenities and service management', enabled: true },
    { id: 'housekeeping', name: 'Housekeeping', description: 'Room cleaning and maintenance tracking', enabled: true },
    { id: 'inventory', name: 'Inventory Management', description: 'Stock tracking and supplier management', enabled: true },
    { id: 'staff_management', name: 'Staff Management', description: 'Staff profiles, shifts, and attendance', enabled: true },
    { id: 'billing_finance', name: 'Billing & Finance', description: 'Invoicing, payments, and financial reports', enabled: true },
    { id: 'reports', name: 'Reports & Analytics', description: 'Business intelligence and reporting', enabled: true }
]);

const loadModules = async () => {
    try {
        const response = await window.axios.get('/api/settings/modules');
        if (response.data.modules) {
            const enabledModules = response.data.modules;
            modules.value = modules.value.map(module => ({
                ...module,
                enabled: enabledModules.includes(module.id)
            }));
        }
    } catch (error) {
        console.error('Error loading modules:', error);
    }
};

const updateModule = async (moduleId, enabled) => {
    try {
        await window.axios.post('/api/settings/modules', {
            module_id: moduleId,
            enabled: enabled
        });
        showSuccess(`Module ${enabled ? 'enabled' : 'disabled'} successfully`);
    } catch (error) {
        showError('Failed to update module');
        console.error('Error updating module:', error);
        // Revert the change
        const module = modules.value.find(m => m.id === moduleId);
        if (module) {
            module.enabled = !enabled;
        }
    }
};

onMounted(() => {
    loadModules();
});
</script>

<style scoped>
.module-enable-disable-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.modules-section h3 { font-size: 18px; margin-bottom: 16px; }
.modules-list { display: flex; flex-direction: column; gap: 16px; }
.module-item { display: flex; justify-content: space-between; align-items: center; padding: 16px; border: 1px solid #e0e0e0; border-radius: 8px; }
.module-info h4 { margin: 0 0 4px 0; font-size: 16px; }
.module-info p { margin: 0; color: #666; font-size: 14px; }
.toggle-switch { position: relative; display: inline-block; width: 50px; height: 24px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: 0.4s; border-radius: 24px; }
.slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: 0.4s; border-radius: 50%; }
input:checked + .slider { background-color: #3498db; }
input:checked + .slider:before { transform: translateX(26px); }
</style>

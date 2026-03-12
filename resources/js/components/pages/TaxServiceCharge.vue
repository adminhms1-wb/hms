<template>
    <div class="tax-service-charge-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Tax & Service Charge</h1>
                <p class="page-subtitle">Configure and calculate tax and service charges</p>
            </div>
        </div>

        <div class="content-card">
            <div class="settings-section">
                <h3>Tax & Service Charge Settings</h3>
                <form @submit.prevent="saveSettings" class="settings-form">
                    <div class="form-group">
                        <label>Tax Rate (%)</label>
                        <input type="number" step="0.01" v-model="settings.tax_rate" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Service Charge (%)</label>
                        <input type="number" step="0.01" v-model="settings.service_charge" class="form-control" />
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save Settings' }}</button>
                    </div>
                </form>
            </div>

            <div class="calculation-section">
                <h3>Calculate Tax & Service Charge</h3>
                <form @submit.prevent="calculate" class="calculation-form">
                    <div class="form-group">
                        <label>Subtotal Amount</label>
                        <input type="number" step="0.01" v-model="calculation.subtotal" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Tax Amount</label>
                        <input type="number" step="0.01" v-model="calculation.tax" class="form-control" readonly />
                    </div>
                    <div class="form-group">
                        <label>Service Charge</label>
                        <input type="number" step="0.01" v-model="calculation.service" class="form-control" readonly />
                    </div>
                    <div class="form-group">
                        <label>Total Amount</label>
                        <input type="number" step="0.01" v-model="calculation.total" class="form-control" readonly />
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" @click="resetCalculation">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useAlert } from '@/composables/useAlert';

const { success: showSuccess, error: showError } = useAlert();

const settings = ref({
    tax_rate: 0,
    service_charge: 0,
});

const calculation = ref({
    subtotal: 0,
    tax: 0,
    service: 0,
    total: 0,
});

const saving = ref(false);

const loadSettings = async () => {
    try {
        const response = await window.axios.get('/api/tax-service-charge/settings');
        if (response.data) {
            settings.value.tax_rate = response.data.tax_rate || 0;
            settings.value.service_charge = response.data.service_charge || 0;
        }
    } catch (error) {
        console.error('Error loading settings:', error);
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        await window.axios.post('/api/tax-service-charge/settings', {
            tax_rate: settings.value.tax_rate || 0,
            service_charge: settings.value.service_charge || 0,
        });
        showSuccess('Settings saved successfully');
    } catch (error) {
        showError(error.response?.data?.message || 'Failed to save settings');
    } finally {
        saving.value = false;
    }
};

watch(() => calculation.value.subtotal, (newVal) => {
    const subtotal = parseFloat(newVal || 0);
    calculation.value.tax = (subtotal * parseFloat(settings.value.tax_rate || 0)) / 100;
    calculation.value.service = (subtotal * parseFloat(settings.value.service_charge || 0)) / 100;
    calculation.value.total = subtotal + calculation.value.tax + calculation.value.service;
});

watch(() => [settings.value.tax_rate, settings.value.service_charge], () => {
    const subtotal = parseFloat(calculation.value.subtotal || 0);
    calculation.value.tax = (subtotal * parseFloat(settings.value.tax_rate || 0)) / 100;
    calculation.value.service = (subtotal * parseFloat(settings.value.service_charge || 0)) / 100;
    calculation.value.total = subtotal + calculation.value.tax + calculation.value.service;
});

const resetCalculation = () => {
    calculation.value = { subtotal: 0, tax: 0, service: 0, total: 0 };
};

const calculate = () => {
    // Calculation is done via watchers
};

onMounted(() => {
    loadSettings();
});
</script>

<style scoped>
.tax-service-charge-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.settings-section, .calculation-section { margin-bottom: 32px; }
.settings-section h3, .calculation-section h3 { margin: 0 0 20px 0; font-size: 18px; }
.settings-form, .calculation-form { max-width: 500px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
.form-control { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
.btn-primary { background: #1976d2; color: #fff; }
.btn-secondary { background: #f5f5f5; }
</style>

<template>
    <div class="tax-currency-setup-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Tax & Currency Setup</h1>
                <p class="page-subtitle">Configure tax rates and currency settings</p>
            </div>
        </div>

        <div class="content-card">
            <div class="settings-section">
                <h3>Currency Settings</h3>
                <div class="form-group">
                    <label>Default Currency</label>
                    <select v-model="settings.currency" class="form-input">
                        <option value="USD">USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                        <option value="INR">INR - Indian Rupee</option>
                        <option value="AED">AED - UAE Dirham</option>
                        <option value="SAR">SAR - Saudi Riyal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Currency Symbol</label>
                    <input type="text" v-model="settings.currency_symbol" class="form-input" placeholder="$" />
                </div>
                <div class="form-group">
                    <label>Currency Position</label>
                    <select v-model="settings.currency_position" class="form-input">
                        <option value="before">Before Amount ($100)</option>
                        <option value="after">After Amount (100$)</option>
                    </select>
                </div>
            </div>

            <div class="settings-section">
                <h3>Tax Settings</h3>
                <div class="form-group">
                    <label>Default Tax Rate (%)</label>
                    <input type="number" v-model="settings.default_tax_rate" min="0" max="100" step="0.01" class="form-input" />
                </div>
                <div class="form-group">
                    <label>Service Charge Rate (%)</label>
                    <input type="number" v-model="settings.service_charge_rate" min="0" max="100" step="0.01" class="form-input" />
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="settings.tax_inclusive" />
                        Prices include tax
                    </label>
                </div>
            </div>

            <div class="settings-section">
                <h3>Additional Tax Rates</h3>
                <div class="tax-rates-list">
                    <div v-for="(rate, index) in settings.additional_tax_rates" :key="index" class="tax-rate-item">
                        <input type="text" v-model="rate.name" placeholder="Tax Name" class="form-input" />
                        <input type="number" v-model="rate.rate" min="0" max="100" step="0.01" placeholder="Rate %" class="form-input" />
                        <button class="btn-icon" @click="removeTaxRate(index)" title="Remove">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                <path d="M3 5H17M8 5V3C8 2.44772 8.44772 2 9 2H11C11.5523 2 12 2.44772 12 3V5M15 5V17C15 18.1046 14.1046 19 13 19H7C5.89543 19 5 18.1046 5 17V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <button class="btn btn-secondary" @click="addTaxRate">Add Tax Rate</button>
            </div>

            <button class="btn btn-primary" @click="saveSettings" :disabled="saving">
                {{ saving ? 'Saving...' : 'Save Settings' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAlert } from '@/composables/useAlert';

const { success: showSuccess, error: showError } = useAlert();

const settings = ref({
    currency: 'USD',
    currency_symbol: '$',
    currency_position: 'before',
    default_tax_rate: 0,
    service_charge_rate: 0,
    tax_inclusive: false,
    additional_tax_rates: []
});

const saving = ref(false);

const loadSettings = async () => {
    try {
        const response = await window.axios.get('/api/settings/tax-currency');
        if (response.data.settings) {
            settings.value = { ...settings.value, ...response.data.settings };
            if (!settings.value.additional_tax_rates) {
                settings.value.additional_tax_rates = [];
            }
        }
    } catch (error) {
        console.error('Error loading settings:', error);
    }
};

const addTaxRate = () => {
    settings.value.additional_tax_rates.push({ name: '', rate: 0 });
};

const removeTaxRate = (index) => {
    settings.value.additional_tax_rates.splice(index, 1);
};

const saveSettings = async () => {
    saving.value = true;
    try {
        await window.axios.post('/api/settings/tax-currency', settings.value);
        showSuccess('Settings saved successfully');
    } catch (error) {
        showError('Failed to save settings');
        console.error('Error saving settings:', error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadSettings();
});
</script>

<style scoped>
.tax-currency-setup-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.settings-section { margin-bottom: 32px; }
.settings-section h3 { font-size: 18px; margin-bottom: 16px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
.form-input { width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
.checkbox-label input { margin: 0; }
.tax-rates-list { margin-bottom: 16px; }
.tax-rate-item { display: flex; gap: 12px; align-items: center; margin-bottom: 12px; }
.tax-rate-item .form-input { max-width: 200px; }
.btn-icon { background: none; border: none; cursor: pointer; padding: 4px; color: #e74c3c; }
.btn-icon:hover { color: #c0392b; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-primary { background: #3498db; color: white; }
.btn-primary:hover:not(:disabled) { background: #2980b9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: #95a5a6; color: white; }
.btn-secondary:hover { background: #7f8c8d; }
</style>

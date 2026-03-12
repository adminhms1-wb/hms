<template>
    <div class="language-settings-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Language Settings</h1>
                <p class="page-subtitle">Configure language preferences (Future-ready)</p>
            </div>
        </div>

        <div class="content-card">
            <div class="info-banner">
                <svg width="24" height="24" viewBox="0 0 20 20" fill="none">
                    <path d="M10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M10 6V10M10 14H10.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <div>
                    <h4>Language Support Coming Soon</h4>
                    <p>Multi-language support is planned for a future release. You can configure the default language below.</p>
                </div>
            </div>

            <div class="settings-section">
                <h3>Default Language</h3>
                <div class="form-group">
                    <label>Select Language</label>
                    <select v-model="settings.default_language" class="form-input" disabled>
                        <option value="en">English</option>
                        <option value="es">Spanish (Coming Soon)</option>
                        <option value="fr">French (Coming Soon)</option>
                        <option value="de">German (Coming Soon)</option>
                        <option value="ar">Arabic (Coming Soon)</option>
                        <option value="hi">Hindi (Coming Soon)</option>
                    </select>
                    <small>Currently only English is available. Additional languages will be added in future updates.</small>
                </div>
            </div>

            <div class="settings-section">
                <h3>Date & Time Format</h3>
                <div class="form-group">
                    <label>Date Format</label>
                    <select v-model="settings.date_format" class="form-input">
                        <option value="Y-m-d">YYYY-MM-DD (2026-01-15)</option>
                        <option value="d/m/Y">DD/MM/YYYY (15/01/2026)</option>
                        <option value="m/d/Y">MM/DD/YYYY (01/15/2026)</option>
                        <option value="d-m-Y">DD-MM-YYYY (15-01-2026)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Time Format</label>
                    <select v-model="settings.time_format" class="form-input">
                        <option value="24">24-hour (14:30)</option>
                        <option value="12">12-hour (2:30 PM)</option>
                    </select>
                </div>
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
    default_language: 'en',
    date_format: 'Y-m-d',
    time_format: '24'
});

const saving = ref(false);

const loadSettings = async () => {
    try {
        const response = await window.axios.get('/api/settings/language');
        if (response.data.settings) {
            settings.value = { ...settings.value, ...response.data.settings };
        }
    } catch (error) {
        console.error('Error loading settings:', error);
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        await window.axios.post('/api/settings/language', settings.value);
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
.language-settings-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.info-banner { display: flex; gap: 16px; padding: 16px; background: #e3f2fd; border-radius: 8px; margin-bottom: 24px; }
.info-banner svg { color: #1976d2; flex-shrink: 0; }
.info-banner h4 { margin: 0 0 4px 0; color: #1976d2; }
.info-banner p { margin: 0; color: #666; font-size: 14px; }
.settings-section { margin-bottom: 32px; }
.settings-section h3 { font-size: 18px; margin-bottom: 16px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
.form-input { width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.form-input:disabled { background: #f5f5f5; cursor: not-allowed; }
.form-group small { display: block; margin-top: 4px; color: #666; font-size: 12px; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-primary { background: #3498db; color: white; }
.btn-primary:hover:not(:disabled) { background: #2980b9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>

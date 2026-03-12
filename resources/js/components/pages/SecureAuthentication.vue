<template>
    <div class="secure-authentication-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Secure Authentication</h1>
                <p class="page-subtitle">Configure authentication and security settings</p>
            </div>
        </div>

        <div class="content-card">
            <div class="settings-section">
                <h3>Password Policy</h3>
                <div class="form-group">
                    <label>
                        <input type="checkbox" v-model="settings.password_min_length_enabled" />
                        Enforce Minimum Password Length
                    </label>
                    <input v-if="settings.password_min_length_enabled" type="number" v-model="settings.password_min_length" min="6" max="32" class="form-input" style="margin-top: 8px;" />
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" v-model="settings.password_require_uppercase" />
                        Require Uppercase Letters
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" v-model="settings.password_require_lowercase" />
                        Require Lowercase Letters
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" v-model="settings.password_require_numbers" />
                        Require Numbers
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" v-model="settings.password_require_symbols" />
                        Require Special Characters
                    </label>
                </div>
            </div>

            <div class="settings-section">
                <h3>Session Management</h3>
                <div class="form-group">
                    <label>Session Timeout (minutes)</label>
                    <input type="number" v-model="settings.session_timeout" min="5" max="1440" class="form-input" />
                    <small>Session will expire after inactivity (5-1440 minutes)</small>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" v-model="settings.remember_me_enabled" />
                        Enable "Remember Me" Feature
                    </label>
                </div>
                <div class="form-group" v-if="settings.remember_me_enabled">
                    <label>Remember Me Duration (days)</label>
                    <input type="number" v-model="settings.remember_me_duration" min="1" max="365" class="form-input" />
                </div>
            </div>

            <div class="settings-section">
                <h3>Two-Factor Authentication</h3>
                <div class="form-group">
                    <label>
                        <input type="checkbox" v-model="settings.two_factor_enabled" />
                        Enable Two-Factor Authentication
                    </label>
                </div>
                <div class="form-group" v-if="settings.two_factor_enabled">
                    <label>2FA Method</label>
                    <select v-model="settings.two_factor_method" class="form-input">
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                        <option value="app">Authenticator App</option>
                    </select>
                </div>
            </div>

            <div class="settings-section">
                <h3>Login Security</h3>
                <div class="form-group">
                    <label>
                        <input type="checkbox" v-model="settings.login_attempts_enabled" />
                        Limit Login Attempts
                    </label>
                </div>
                <div class="form-group" v-if="settings.login_attempts_enabled">
                    <label>Max Login Attempts</label>
                    <input type="number" v-model="settings.max_login_attempts" min="3" max="10" class="form-input" />
                </div>
                <div class="form-group" v-if="settings.login_attempts_enabled">
                    <label>Lockout Duration (minutes)</label>
                    <input type="number" v-model="settings.lockout_duration" min="5" max="1440" class="form-input" />
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" v-model="settings.ip_whitelist_enabled" />
                        Enable IP Whitelist
                    </label>
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
    password_min_length_enabled: false,
    password_min_length: 8,
    password_require_uppercase: false,
    password_require_lowercase: false,
    password_require_numbers: false,
    password_require_symbols: false,
    session_timeout: 60,
    remember_me_enabled: true,
    remember_me_duration: 30,
    two_factor_enabled: false,
    two_factor_method: 'email',
    login_attempts_enabled: false,
    max_login_attempts: 5,
    lockout_duration: 15,
    ip_whitelist_enabled: false
});

const saving = ref(false);

const loadSettings = async () => {
    try {
        const response = await window.axios.get('/api/settings/authentication');
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
        await window.axios.post('/api/settings/authentication', settings.value);
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
.secure-authentication-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.settings-section { margin-bottom: 32px; }
.settings-section h3 { font-size: 18px; margin-bottom: 16px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
.form-group label input[type="checkbox"] { margin-right: 8px; }
.form-input { width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.form-group small { display: block; margin-top: 4px; color: #666; font-size: 12px; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-primary { background: #3498db; color: white; }
.btn-primary:hover:not(:disabled) { background: #2980b9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>

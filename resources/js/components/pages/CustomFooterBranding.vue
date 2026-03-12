<template>
    <div class="custom-footer-branding-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Custom Footer Branding</h1>
                <p class="page-subtitle">Customize footer branding and information</p>
            </div>
        </div>

        <div class="content-card">
            <div class="settings-section">
                <h3>Footer Content</h3>
                <div class="form-group">
                    <label>Footer Text</label>
                    <textarea v-model="settings.footer_text" class="form-input" rows="3" placeholder="Enter footer text"></textarea>
                </div>
                <div class="form-group">
                    <label>Copyright Text</label>
                    <input type="text" v-model="settings.copyright_text" class="form-input" placeholder="© 2026 Your Company Name" />
                </div>
            </div>

            <div class="settings-section">
                <h3>Contact Information</h3>
                <div class="form-group">
                    <label>Address</label>
                    <textarea v-model="settings.address" class="form-input" rows="2" placeholder="Enter address"></textarea>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" v-model="settings.phone" class="form-input" placeholder="Enter phone number" />
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" v-model="settings.email" class="form-input" placeholder="Enter email address" />
                </div>
                <div class="form-group">
                    <label>Website</label>
                    <input type="url" v-model="settings.website" class="form-input" placeholder="https://example.com" />
                </div>
            </div>

            <div class="settings-section">
                <h3>Social Media Links</h3>
                <div class="form-group">
                    <label>Facebook</label>
                    <input type="url" v-model="settings.facebook_url" class="form-input" placeholder="https://facebook.com/yourpage" />
                </div>
                <div class="form-group">
                    <label>Twitter</label>
                    <input type="url" v-model="settings.twitter_url" class="form-input" placeholder="https://twitter.com/yourhandle" />
                </div>
                <div class="form-group">
                    <label>Instagram</label>
                    <input type="url" v-model="settings.instagram_url" class="form-input" placeholder="https://instagram.com/yourhandle" />
                </div>
                <div class="form-group">
                    <label>LinkedIn</label>
                    <input type="url" v-model="settings.linkedin_url" class="form-input" placeholder="https://linkedin.com/company/yourcompany" />
                </div>
            </div>

            <div class="settings-section">
                <h3>Display Options</h3>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="settings.show_address" />
                        Show Address
                    </label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="settings.show_contact" />
                        Show Contact Information
                    </label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="settings.show_social_media" />
                        Show Social Media Links
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
    footer_text: '',
    copyright_text: '',
    address: '',
    phone: '',
    email: '',
    website: '',
    facebook_url: '',
    twitter_url: '',
    instagram_url: '',
    linkedin_url: '',
    show_address: true,
    show_contact: true,
    show_social_media: true
});

const saving = ref(false);

const loadSettings = async () => {
    try {
        const response = await window.axios.get('/api/settings/footer-branding');
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
        await window.axios.post('/api/settings/footer-branding', settings.value);
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
.custom-footer-branding-page { padding: 24px; }
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
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-primary { background: #3498db; color: white; }
.btn-primary:hover:not(:disabled) { background: #2980b9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>

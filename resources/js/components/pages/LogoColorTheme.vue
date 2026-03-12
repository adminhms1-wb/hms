<template>
    <div class="logo-color-theme-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Logo, Color Theme & Hotel Name</h1>
                <p class="page-subtitle">Customize your hotel branding</p>
            </div>
        </div>

        <div class="content-card">
            <div class="settings-section">
                <h3>Hotel Information</h3>
                <div class="form-group">
                    <label>Hotel Name</label>
                    <input type="text" v-model="settings.hotel_name" class="form-input" placeholder="Enter hotel name" />
                </div>
                <div class="form-group">
                    <label>Hotel Tagline</label>
                    <input type="text" v-model="settings.hotel_tagline" class="form-input" placeholder="Enter tagline" />
                </div>
            </div>

            <div class="settings-section">
                <h3>Logo Upload</h3>
                <div class="form-group">
                    <label>Main Logo</label>
                    <div class="upload-area" @click="triggerFileInput('logo')">
                        <input type="file" ref="logoInput" @change="handleLogoUpload" accept="image/*" style="display: none;" />
                        <div v-if="settings.logo_url" class="preview-container">
                            <img :src="settings.logo_url" alt="Logo" class="preview-image" />
                            <button class="btn-remove" @click.stop="removeLogo" title="Remove">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 3L3 9M3 3L9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                        <div v-else class="upload-placeholder">
                            <svg width="48" height="48" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2V12M10 12L6 8M10 12L14 8M2 14V16C2 17.1046 2.89543 18 4 18H16C17.1046 18 18 17.1046 18 16V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <p>Click to upload logo</p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Favicon</label>
                    <div class="upload-area" @click="triggerFileInput('favicon')">
                        <input type="file" ref="faviconInput" @change="handleFaviconUpload" accept="image/*" style="display: none;" />
                        <div v-if="settings.favicon_url" class="preview-container">
                            <img :src="settings.favicon_url" alt="Favicon" class="preview-image small" />
                            <button class="btn-remove" @click.stop="removeFavicon" title="Remove">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 3L3 9M3 3L9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                        <div v-else class="upload-placeholder">
                            <svg width="48" height="48" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2V12M10 12L6 8M10 12L14 8M2 14V16C2 17.1046 2.89543 18 4 18H16C17.1046 18 18 17.1046 18 16V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <p>Click to upload favicon</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h3>Color Theme</h3>
                <div class="form-group">
                    <label>Primary Color</label>
                    <div class="color-input-group">
                        <input type="color" v-model="settings.primary_color" class="color-input" />
                        <input type="text" v-model="settings.primary_color" class="form-input" />
                    </div>
                </div>
                <div class="form-group">
                    <label>Secondary Color</label>
                    <div class="color-input-group">
                        <input type="color" v-model="settings.secondary_color" class="color-input" />
                        <input type="text" v-model="settings.secondary_color" class="form-input" />
                    </div>
                </div>
                <div class="form-group">
                    <label>Accent Color</label>
                    <div class="color-input-group">
                        <input type="color" v-model="settings.accent_color" class="color-input" />
                        <input type="text" v-model="settings.accent_color" class="form-input" />
                    </div>
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
    hotel_name: '',
    hotel_tagline: '',
    logo_url: '',
    favicon_url: '',
    primary_color: '#3498db',
    secondary_color: '#2c3e50',
    accent_color: '#e74c3c'
});

const logoInput = ref(null);
const faviconInput = ref(null);
const saving = ref(false);

const loadSettings = async () => {
    try {
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'LogoColorTheme.vue:loadSettings','message':'Function entry','data':{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        
        const response = await window.axios.get('/api/settings/logo-color-theme');
        
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'LogoColorTheme.vue:loadSettings','message':'Response received','data':{'hasSettings':!!response.data.settings,'logo_url':response.data.settings?.logo_url,'favicon_url':response.data.settings?.favicon_url},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        
        if (response.data.settings) {
            const loadedSettings = response.data.settings;
            
            // Ensure logo and favicon URLs have the correct format
            let logoUrl = loadedSettings.logo_url || '';
            let faviconUrl = loadedSettings.favicon_url || '';
            
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'LogoColorTheme.vue:loadSettings','message':'URLs before processing','data':{'logoUrl':logoUrl,'faviconUrl':faviconUrl},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
            
            // Keep app/public in path (do not remove)
            
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'LogoColorTheme.vue:loadSettings','message':'URLs after processing','data':{'logoUrl':logoUrl,'faviconUrl':faviconUrl},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
            
            settings.value = { 
                ...settings.value, 
                ...loadedSettings,
                logo_url: logoUrl,
                favicon_url: faviconUrl
            };
            
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'LogoColorTheme.vue:loadSettings','message':'Settings updated','data':{'finalLogoUrl':settings.value.logo_url,'finalFaviconUrl':settings.value.favicon_url},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
        }
    } catch (error) {
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'LogoColorTheme.vue:loadSettings','message':'Error occurred','data':{'error':error.message},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        console.error('Error loading settings:', error);
    }
};

const triggerFileInput = (type) => {
    if (type === 'logo') {
        logoInput.value?.click();
    } else {
        faviconInput.value?.click();
    }
};

const handleLogoUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('logo', file);

    try {
        const response = await window.axios.post('/api/settings/upload-logo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        // Use the URL directly from the response
        // The backend returns the correct path: /storage/app/public/hotel/filename.png
        settings.value.logo_url = response.data.url;
        showSuccess('Logo uploaded successfully');
    } catch (error) {
        showError('Failed to upload logo');
        console.error('Error uploading logo:', error);
    }
};

const handleFaviconUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('favicon', file);

    try {
        const response = await window.axios.post('/api/settings/upload-favicon', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        // Use the URL directly from the response
        // The backend returns the correct path: /storage/app/public/hotel/filename.png
        settings.value.favicon_url = response.data.url;
        showSuccess('Favicon uploaded successfully');
    } catch (error) {
        showError('Failed to upload favicon');
        console.error('Error uploading favicon:', error);
    }
};

const removeLogo = async () => {
    try {
        await window.axios.delete('/api/settings/remove-logo');
        settings.value.logo_url = '';
        showSuccess('Logo removed successfully');
    } catch (error) {
        showError('Failed to remove logo');
        console.error('Error removing logo:', error);
    }
};

const removeFavicon = async () => {
    try {
        await window.axios.delete('/api/settings/remove-favicon');
        settings.value.favicon_url = '';
        showSuccess('Favicon removed successfully');
    } catch (error) {
        showError('Failed to remove favicon');
        console.error('Error removing favicon:', error);
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        await window.axios.post('/api/settings/logo-color-theme', settings.value);
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
.logo-color-theme-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.settings-section { margin-bottom: 32px; }
.settings-section h3 { font-size: 18px; margin-bottom: 16px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
.form-input { width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.upload-area { border: 2px dashed #e0e0e0; border-radius: 8px; padding: 24px; text-align: center; cursor: pointer; transition: border-color 0.3s; }
.upload-area:hover { border-color: #3498db; }
.upload-placeholder { display: flex; flex-direction: column; align-items: center; gap: 8px; color: #999; }
.preview-container { position: relative; display: inline-block; }
.preview-image { max-width: 200px; max-height: 100px; object-fit: contain; }
.preview-image.small { max-width: 64px; max-height: 64px; }
.btn-remove { position: absolute; top: -8px; right: -8px; background: #e74c3c; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center; padding: 0; }
.btn-remove svg { display: block; }
.color-input-group { display: flex; gap: 12px; align-items: center; }
.color-input { width: 60px; height: 40px; border: 1px solid #e0e0e0; border-radius: 6px; cursor: pointer; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-primary { background: #3498db; color: white; }
.btn-primary:hover:not(:disabled) { background: #2980b9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>

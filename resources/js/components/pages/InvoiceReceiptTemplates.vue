<template>
    <div class="invoice-receipt-templates-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Invoice & Receipt Templates</h1>
                <p class="page-subtitle">Customize invoice and receipt templates</p>
            </div>
        </div>

        <div class="content-card">
            <div class="templates-section">
                <h3>Template Selection</h3>
                <div class="template-grid">
                    <div 
                        v-for="template in templates" 
                        :key="template.id" 
                        class="template-card"
                        :class="{ active: selectedTemplate === template.id }"
                        @click="selectTemplate(template.id)"
                    >
                        <div class="template-preview">
                            <div class="template-preview-content" :style="{ backgroundColor: template.preview_bg }">
                                <div class="preview-header">{{ template.name }}</div>
                                <div class="preview-body">
                                    <div class="preview-line"></div>
                                    <div class="preview-line short"></div>
                                    <div class="preview-line"></div>
                                </div>
                            </div>
                        </div>
                        <div class="template-name">{{ template.name }}</div>
                    </div>
                </div>
            </div>

            <div class="settings-section" v-if="selectedTemplate">
                <h3>Template Customization</h3>
                <div class="form-group">
                    <label>Header Text</label>
                    <input type="text" v-model="templateSettings.header_text" class="form-input" />
                </div>
                <div class="form-group">
                    <label>Footer Text</label>
                    <textarea v-model="templateSettings.footer_text" class="form-input" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Show Logo</label>
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="templateSettings.show_logo" />
                        Display logo on invoices/receipts
                    </label>
                </div>
                <div class="form-group">
                    <label>Show Tax Details</label>
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="templateSettings.show_tax_details" />
                        Display tax breakdown
                    </label>
                </div>
                <div class="form-group">
                    <label>Show Payment Method</label>
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="templateSettings.show_payment_method" />
                        Display payment method
                    </label>
                </div>
            </div>

            <button class="btn btn-primary" @click="saveTemplate" :disabled="saving || !selectedTemplate">
                {{ saving ? 'Saving...' : 'Save Template Settings' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAlert } from '@/composables/useAlert';

const { success: showSuccess, error: showError } = useAlert();

const templates = ref([
    { id: 'default', name: 'Default', preview_bg: '#f8f9fa' },
    { id: 'modern', name: 'Modern', preview_bg: '#e3f2fd' },
    { id: 'classic', name: 'Classic', preview_bg: '#fff3e0' },
    { id: 'minimal', name: 'Minimal', preview_bg: '#f5f5f5' }
]);

const selectedTemplate = ref(null);
const templateSettings = ref({
    header_text: '',
    footer_text: '',
    show_logo: true,
    show_tax_details: true,
    show_payment_method: true
});
const saving = ref(false);

const loadTemplates = async () => {
    try {
        const response = await window.axios.get('/api/settings/invoice-templates');
        if (response.data.template) {
            selectedTemplate.value = response.data.template.id;
            templateSettings.value = { ...templateSettings.value, ...response.data.template.settings };
        }
    } catch (error) {
        console.error('Error loading templates:', error);
    }
};

const selectTemplate = (templateId) => {
    selectedTemplate.value = templateId;
};

const saveTemplate = async () => {
    saving.value = true;
    try {
        await window.axios.post('/api/settings/invoice-templates', {
            template_id: selectedTemplate.value,
            settings: templateSettings.value
        });
        showSuccess('Template settings saved successfully');
    } catch (error) {
        showError('Failed to save template settings');
        console.error('Error saving template:', error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadTemplates();
});
</script>

<style scoped>
.invoice-receipt-templates-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.templates-section { margin-bottom: 32px; }
.templates-section h3 { font-size: 18px; margin-bottom: 16px; }
.template-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
.template-card { border: 2px solid #e0e0e0; border-radius: 8px; padding: 16px; cursor: pointer; transition: all 0.3s; }
.template-card:hover { border-color: #3498db; }
.template-card.active { border-color: #3498db; background: #e3f2fd; }
.template-preview { margin-bottom: 12px; }
.template-preview-content { border-radius: 4px; padding: 12px; min-height: 120px; }
.preview-header { font-weight: 600; margin-bottom: 8px; }
.preview-line { height: 4px; background: rgba(0,0,0,0.1); border-radius: 2px; margin-bottom: 6px; }
.preview-line.short { width: 60%; }
.template-name { text-align: center; font-weight: 500; }
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

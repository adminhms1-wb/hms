<template>
    <div class="hotel-setup-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Hotel Setup & Configuration</h1>
                <p class="page-subtitle">Configure your hotel settings and preferences</p>
            </div>
        </div>

        <div class="setup-grid">
            <!-- Hotel Information -->
            <div class="setup-card">
                <div class="card-header">
                    <h2 class="card-title">Hotel Information</h2>
                </div>
                <div class="card-body">
                    <form @submit.prevent="saveHotelInfo" class="setup-form">
                        <div class="form-group">
                            <label>Hotel Name *</label>
                            <input 
                                v-model="hotelInfo.name" 
                                type="text" 
                                class="form-control" 
                                placeholder="Enter hotel name"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label>Hotel Address</label>
                            <textarea 
                                v-model="hotelInfo.address" 
                                class="form-control" 
                                rows="3"
                                placeholder="Enter hotel address"
                            ></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>City</label>
                                <input 
                                    v-model="hotelInfo.city" 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="City"
                                />
                            </div>
                            <div class="form-group">
                                <label>Country</label>
                                <input 
                                    v-model="hotelInfo.country" 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Country"
                                />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone</label>
                                <input 
                                    v-model="hotelInfo.phone" 
                                    type="tel" 
                                    class="form-control" 
                                    placeholder="+1234567890"
                                />
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input 
                                    v-model="hotelInfo.email" 
                                    type="email" 
                                    class="form-control" 
                                    placeholder="hotel@example.com"
                                />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Website</label>
                            <input 
                                v-model="hotelInfo.website" 
                                type="url" 
                                class="form-control" 
                                placeholder="https://www.hotel.com"
                            />
                        </div>
                        
                        <!-- Hotel Logo Upload -->
                        <div class="form-group">
                            <label>Hotel Logo</label>
                            <div class="image-upload-wrapper">
                                <div class="image-preview-container">
                                    <div v-if="logoPreview" class="image-preview">
                                        <img :src="logoPreview" alt="Hotel Logo" @error="handleImageError" />
                                        <button type="button" class="remove-image-btn" @click="removeLogo" title="Remove logo">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-else class="image-upload-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                            <path d="M21 19V5C21 3.89543 20.1046 3 19 3H5C3.89543 3 3 3.89543 3 5V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19Z" stroke="currentColor" stroke-width="2"/>
                                            <path d="M8.5 10C9.32843 10 10 9.32843 10 8.5C10 7.67157 9.32843 7 8.5 7C7.67157 7 7 7.67157 7 8.5C7 9.32843 7.67157 10 8.5 10Z" stroke="currentColor" stroke-width="2"/>
                                            <path d="M21 15L16 10L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <p>No logo uploaded</p>
                                    </div>
                                </div>
                                <label class="file-upload-btn">
                                    <input 
                                        type="file" 
                                        accept="image/*" 
                                        @change="handleLogoUpload"
                                        ref="logoInput"
                                        style="display: none;"
                                    />
                                    <span>{{ logoPreview ? 'Change Logo' : 'Upload Logo' }}</span>
                                </label>
                                <p class="upload-hint">Recommended size: 200x60px (PNG, JPG, SVG)</p>
                            </div>
                        </div>
                        
                        <!-- Hotel Favicon Upload -->
                        <div class="form-group">
                            <label>Hotel Favicon</label>
                            <div class="image-upload-wrapper">
                                <div class="image-preview-container">
                                    <div v-if="faviconPreview" class="image-preview favicon-preview">
                                        <img :src="faviconPreview" alt="Hotel Favicon" @error="handleImageError" />
                                        <button type="button" class="remove-image-btn" @click="removeFavicon" title="Remove favicon">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-else class="image-upload-placeholder favicon-placeholder">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2Z" stroke="currentColor" stroke-width="2"/>
                                            <path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <p>No favicon uploaded</p>
                                    </div>
                                </div>
                                <label class="file-upload-btn">
                                    <input 
                                        type="file" 
                                        accept="image/*" 
                                        @change="handleFaviconUpload"
                                        ref="faviconInput"
                                        style="display: none;"
                                    />
                                    <span>{{ faviconPreview ? 'Change Favicon' : 'Upload Favicon' }}</span>
                                </label>
                                <p class="upload-hint">Recommended size: 32x32px or 16x16px (ICO, PNG)</p>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Save Hotel Information' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Hotel Settings -->
            <div class="setup-card">
                <div class="card-header">
                    <h2 class="card-title">Hotel Settings</h2>
                </div>
                <div class="card-body">
                    <form @submit.prevent="saveHotelSettings" class="setup-form">
                        <div class="form-group">
                            <label>Currency</label>
                            <select v-model="hotelSettings.currency" class="form-control">
                                <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                                    {{ currency.code }} - {{ currency.name }} ({{ currency.symbol }})
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Time Zone</label>
                            <select v-model="hotelSettings.timezone" class="form-control">
                                <option v-for="tz in timezones" :key="tz.value" :value="tz.value">
                                    {{ tz.label }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Check-in Time <span class="optional-label">(Optional)</span></label>
                            <input 
                                v-model="hotelSettings.checkin_time" 
                                type="time" 
                                class="form-control"
                                placeholder="HH:MM"
                            />
                        </div>
                        <div class="form-group">
                            <label>Check-out Time <span class="optional-label">(Optional)</span></label>
                            <input 
                                v-model="hotelSettings.checkout_time" 
                                type="time" 
                                class="form-control"
                                placeholder="HH:MM"
                            />
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tax Rate (%)</label>
                                <input 
                                    v-model.number="hotelSettings.tax_rate" 
                                    type="number" 
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="form-control" 
                                    placeholder="0.00"
                                />
                            </div>
                            <div class="form-group">
                                <label>Service Charge (%)</label>
                                <input 
                                    v-model.number="hotelSettings.service_charge" 
                                    type="number" 
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="form-control" 
                                    placeholder="0.00"
                                />
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" :disabled="savingSettings">
                            {{ savingSettings ? 'Saving...' : 'Save Settings' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Additional Configuration -->
            <div class="setup-card">
                <div class="card-header">
                    <h2 class="card-title">Additional Configuration</h2>
                </div>
                <div class="card-body">
                    <div class="config-section">
                        <div class="config-item">
                            <div>
                                <h3 class="config-title">Enable Online Booking</h3>
                                <p class="config-desc">Allow guests to book rooms online</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" v-model="additionalConfig.online_booking" />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="config-item">
                            <div>
                                <h3 class="config-title">Email Notifications</h3>
                                <p class="config-desc">Send email notifications for bookings</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" v-model="additionalConfig.email_notifications" />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="config-item">
                            <div>
                                <h3 class="config-title">Auto Check-in</h3>
                                <p class="config-desc">Automatically check-in confirmed reservations</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" v-model="additionalConfig.auto_checkin" />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="config-item">
                            <div>
                                <h3 class="config-title">Maintenance Mode</h3>
                                <p class="config-desc">Enable maintenance mode for system updates</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" v-model="additionalConfig.maintenance_mode" />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    <button type="button" @click="saveAdditionalConfig" class="btn btn-primary" :disabled="savingConfig">
                        {{ savingConfig ? 'Saving...' : 'Save Configuration' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useAlert } from '../../composables/useAlert';
import axios from 'axios';
import { currencies } from '../../utils/currencies';
import { timezones } from '../../utils/timezones';

export default {
    name: 'HotelSetup',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const saving = ref(false);
        const savingSettings = ref(false);
        const savingConfig = ref(false);
        const loading = ref(false);
        const logoInput = ref(null);
        const faviconInput = ref(null);
        const logoPreview = ref(null);
        const faviconPreview = ref(null);
        const logoFile = ref(null);
        const faviconFile = ref(null);
        const initialLogo = ref(null);
        const initialFavicon = ref(null);

        const hotelInfo = ref({
            name: '',
            address: '',
            city: '',
            country: '',
            phone: '',
            email: '',
            website: '',
            logo: null,
            favicon: null
        });

        const hotelSettings = ref({
            currency: 'USD',
            timezone: 'UTC',
            checkin_time: '14:00',
            checkout_time: '11:00',
            tax_rate: 0,
            service_charge: 0
        });

        const additionalConfig = ref({
            online_booking: false,
            email_notifications: true,
            auto_checkin: false,
            maintenance_mode: false
        });

        const handleLogoUpload = (event) => {
            const file = event.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    showError('Please select a valid image file');
                    return;
                }
                
                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showError('Logo file size should be less than 2MB');
                    return;
                }
                
                logoFile.value = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    logoPreview.value = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };

        const handleFaviconUpload = (event) => {
            const file = event.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    showError('Please select a valid image file');
                    return;
                }
                
                // Validate file size (max 500KB)
                if (file.size > 500 * 1024) {
                    showError('Favicon file size should be less than 500KB');
                    return;
                }
                
                faviconFile.value = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    faviconPreview.value = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };

        const removeLogo = () => {
            logoPreview.value = null;
            logoFile.value = null;
            if (logoInput.value) {
                logoInput.value.value = '';
            }
        };

        const removeFavicon = () => {
            faviconPreview.value = null;
            faviconFile.value = null;
            if (faviconInput.value) {
                faviconInput.value.value = '';
            }
        };

        const handleImageError = (event) => {
            const imgSrc = event.target.src;
            
            // Don't show errors for data URLs (preview images from FileReader)
            if (imgSrc && imgSrc.startsWith('data:')) {
                // This is a preview image, just hide it silently
                event.target.style.display = 'none';
                return;
            }
            
            // For server URLs, check if it's a recent upload (within last 2 seconds)
            // This handles cases where the file might not be immediately available
            const isRecentUpload = logoFile.value || faviconFile.value;
            
            if (isRecentUpload) {
                // If we just uploaded, wait a bit and retry
                setTimeout(() => {
                    if (event.target.src) {
                        // Create a new image to test if it loads
                        const testImg = new Image();
                        testImg.onload = () => {
                            // Image is now available, update the src with a cache buster
                            event.target.src = imgSrc + (imgSrc.includes('?') ? '&' : '?') + 't=' + Date.now();
                            event.target.style.display = '';
                        };
                        testImg.onerror = () => {
                            // Still not available, hide it
                            event.target.style.display = 'none';
                        };
                        testImg.src = imgSrc;
                    }
                }, 500);
                return;
            }
            
            // For other cases, just hide the broken image without showing error
            // (The image might have been deleted or moved)
            event.target.style.display = 'none';
        };

        const saveHotelInfo = async () => {
            saving.value = true;
            try {
                const formData = new FormData();
                
                // Add text fields
                formData.append('name', hotelInfo.value.name);
                formData.append('address', hotelInfo.value.address || '');
                formData.append('city', hotelInfo.value.city || '');
                formData.append('country', hotelInfo.value.country || '');
                formData.append('phone', hotelInfo.value.phone || '');
                formData.append('email', hotelInfo.value.email || '');
                formData.append('website', hotelInfo.value.website || '');
                
                // Add image files if uploaded
                if (logoFile.value) {
                    formData.append('logo', logoFile.value);
                } else if (logoPreview.value === null && initialLogo.value !== null) {
                    // If logo was removed (preview is null but we had a logo initially), send a flag to delete it
                    formData.append('remove_logo', '1');
                }
                
                if (faviconFile.value) {
                    formData.append('favicon', faviconFile.value);
                } else if (faviconPreview.value === null && initialFavicon.value !== null) {
                    // If favicon was removed (preview is null but we had a favicon initially), send a flag to delete it
                    formData.append('remove_favicon', '1');
                }
                
                const response = await axios.post('/api/hotel/info', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                
                if (response.data.success) {
                    // Small delay to ensure file is fully written to disk
                    await new Promise(resolve => setTimeout(resolve, 200));
                    
                    // Update previews with saved image URLs if they exist
                    if (response.data.hotel.logo) {
                        // Backend should return full URL from asset(), but handle different formats
                        let logoUrl = response.data.hotel.logo;
                        
                        // If it's a full URL, use it directly
                        if (!logoUrl.startsWith('http')) {
                            // If it's a relative path, construct full URL
                            if (logoUrl.startsWith('/')) {
                                logoUrl = window.location.origin + logoUrl;
                            } else {
                                logoUrl = window.location.origin + '/storage/' + logoUrl;
                            }
                        }
                        
                        // Remove 'app/public' from URL if it exists
                        logoUrl = logoUrl.replace('/storage/app/public/', '/storage/');
                        logoUrl = logoUrl.replace('storage/app/public/', 'storage/');
                        
                        // Remove any existing query params and add cache buster
                        const cleanUrl = logoUrl.split('?')[0];
                        const finalUrl = cleanUrl + '?t=' + Date.now();
                        logoPreview.value = finalUrl;
                        initialLogo.value = cleanUrl; // Store without cache buster for initial state
                        logoFile.value = null; // Clear file since it's saved
                    } else {
                        // Logo was removed
                        logoPreview.value = null;
                        initialLogo.value = null;
                    }
                    
                    if (response.data.hotel.favicon) {
                        // Backend should return full URL from asset(), but handle different formats
                        let faviconUrl = response.data.hotel.favicon;
                        
                        // If it's a full URL, use it directly
                        if (!faviconUrl.startsWith('http')) {
                            // If it's a relative path, construct full URL
                            if (faviconUrl.startsWith('/')) {
                                faviconUrl = window.location.origin + faviconUrl;
                            } else {
                                faviconUrl = window.location.origin + '/storage/' + faviconUrl;
                            }
                        }
                        
                        // Remove 'app/public' from URL if it exists
                        faviconUrl = faviconUrl.replace('/storage/app/public/', '/storage/');
                        faviconUrl = faviconUrl.replace('storage/app/public/', 'storage/');
                        
                        // Remove any existing query params and add cache buster
                        const cleanUrl = faviconUrl.split('?')[0];
                        const finalUrl = cleanUrl + '?t=' + Date.now();
                        faviconPreview.value = finalUrl;
                        initialFavicon.value = cleanUrl; // Store without cache buster for initial state
                        faviconFile.value = null; // Clear file since it's saved
                        
                        // Update favicon in browser (use base URL without cache buster)
                        const faviconLink = document.getElementById('favicon-link');
                        if (faviconLink) {
                            faviconLink.href = cleanUrl;
                        } else {
                            const link = document.createElement('link');
                            link.id = 'favicon-link';
                            link.rel = 'icon';
                            link.type = 'image/x-icon';
                            link.href = cleanUrl;
                            document.head.appendChild(link);
                        }
                    } else {
                        // Favicon was removed
                        faviconPreview.value = null;
                        initialFavicon.value = null;
                        // Remove favicon from browser
                        const faviconLink = document.getElementById('favicon-link');
                        if (faviconLink) {
                            faviconLink.remove();
                        }
                    }
                    
                    // Update page title if hotel name changed
                    if (response.data.hotel.name) {
                        document.title = response.data.hotel.name + ' - Hotel Management System';
                    }
                    
                    // Dispatch event to update Dashboard logo and name
                    window.dispatchEvent(new CustomEvent('hotel-info-updated', {
                        detail: response.data.hotel
                    }));
                    
                    showSuccess(response.data.message || 'Hotel information saved successfully!');
                    // Refresh page after successful save
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showError('Error saving hotel information');
                }
            } catch (error) {
                console.error('Error saving hotel information:', error);
                const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Error saving hotel information';
                showError(errorMessage);
            } finally {
                saving.value = false;
            }
        };

        const saveHotelSettings = async () => {
            savingSettings.value = true;
            try {
                const payload = {
                    currency: hotelSettings.value.currency || null,
                    timezone: hotelSettings.value.timezone || null,
                    tax_rate: hotelSettings.value.tax_rate !== undefined && hotelSettings.value.tax_rate !== null ? hotelSettings.value.tax_rate : null,
                    service_charge: hotelSettings.value.service_charge !== undefined && hotelSettings.value.service_charge !== null ? hotelSettings.value.service_charge : null
                };

                // Only include check-in/check-out times if they have valid values (optional)
                // Check if the value exists, is not null, not empty string, and matches time format
                const checkinTime = hotelSettings.value.checkin_time;
                if (checkinTime && checkinTime !== null && checkinTime !== '' && checkinTime.trim() !== '') {
                    // Validate time format before sending
                    const timeRegex = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
                    if (timeRegex.test(checkinTime.trim())) {
                        payload.checkin_time = checkinTime.trim();
                    }
                }
                
                const checkoutTime = hotelSettings.value.checkout_time;
                if (checkoutTime && checkoutTime !== null && checkoutTime !== '' && checkoutTime.trim() !== '') {
                    // Validate time format before sending
                    const timeRegex = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
                    if (timeRegex.test(checkoutTime.trim())) {
                        payload.checkout_time = checkoutTime.trim();
                    }
                }

                const response = await axios.post('/api/hotel/settings', payload);
                
                if (response.data.success) {
                    // Update local state with saved values
                    if (response.data.settings) {
                        hotelSettings.value = {
                            currency: response.data.settings.currency || hotelSettings.value.currency,
                            timezone: response.data.settings.timezone || hotelSettings.value.timezone,
                            checkin_time: response.data.settings.checkin_time || hotelSettings.value.checkin_time || '',
                            checkout_time: response.data.settings.checkout_time || hotelSettings.value.checkout_time || '',
                            tax_rate: response.data.settings.tax_rate !== undefined ? response.data.settings.tax_rate : hotelSettings.value.tax_rate,
                            service_charge: response.data.settings.service_charge !== undefined ? response.data.settings.service_charge : hotelSettings.value.service_charge
                        };
                    }
                    showSuccess(response.data.message || 'Hotel settings saved successfully!');
                    
                    // Dispatch event to notify other components of currency change
                    window.dispatchEvent(new CustomEvent('currency-changed', {
                        detail: { currency: hotelSettings.value.currency }
                    }));
                    
                    // Refresh page after successful save to update all prices
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showError('Error saving hotel settings');
                }
            } catch (error) {
                console.error('Error saving hotel settings:', error);
                const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Error saving hotel settings';
                showError(errorMessage);
            } finally {
                savingSettings.value = false;
            }
        };

        const saveAdditionalConfig = async () => {
            savingConfig.value = true;
            try {
                const response = await axios.post('/api/hotel/settings', {
                    online_booking: additionalConfig.value.online_booking,
                    email_notifications: additionalConfig.value.email_notifications,
                    auto_checkin: additionalConfig.value.auto_checkin,
                    maintenance_mode: additionalConfig.value.maintenance_mode
                });
                
                if (response.data.success) {
                    // Update local state with saved values
                    if (response.data.settings) {
                        additionalConfig.value = {
                            online_booking: response.data.settings.online_booking ?? additionalConfig.value.online_booking,
                            email_notifications: response.data.settings.email_notifications ?? additionalConfig.value.email_notifications,
                            auto_checkin: response.data.settings.auto_checkin ?? additionalConfig.value.auto_checkin,
                            maintenance_mode: response.data.settings.maintenance_mode ?? additionalConfig.value.maintenance_mode
                        };
                    }
                    showSuccess(response.data.message || 'Configuration saved successfully!');
                    // Refresh page after successful save
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showError('Error saving configuration');
                }
            } catch (error) {
                console.error('Error saving configuration:', error);
                const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Error saving configuration';
                showError(errorMessage);
            } finally {
                savingConfig.value = false;
            }
        };

        const loadHotelInfo = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/api/hotel/info');
                const data = response.data;
                
                hotelInfo.value = {
                    name: data.name || '',
                    address: data.address || '',
                    city: data.city || '',
                    country: data.country || '',
                    phone: data.phone || '',
                    email: data.email || '',
                    website: data.website || '',
                    logo: data.logo !== undefined ? data.logo : undefined,
                    favicon: data.favicon !== undefined ? data.favicon : undefined
                };
                
                // Set previews if images exist
                if (data.logo) {
                    // Ensure we have the full URL
                    let logoUrl = data.logo;
                    
                    // If it's a full URL, use it directly
                    if (!logoUrl.startsWith('http')) {
                        // If it's a relative path, construct full URL
                        if (logoUrl.startsWith('/')) {
                            logoUrl = window.location.origin + logoUrl;
                        } else {
                            logoUrl = window.location.origin + '/storage/' + logoUrl;
                        }
                    }
                    
                    // Remove 'app/public' from URL if it exists
                    logoUrl = logoUrl.replace('/storage/app/public/', '/storage/');
                    logoUrl = logoUrl.replace('storage/app/public/', 'storage/');
                    
                    logoPreview.value = logoUrl;
                    initialLogo.value = logoUrl; // Store initial state
                }
                if (data.favicon) {
                    // Ensure we have the full URL
                    let faviconUrl = data.favicon;
                    
                    // If it's a full URL, use it directly
                    if (!faviconUrl.startsWith('http')) {
                        // If it's a relative path, construct full URL
                        if (faviconUrl.startsWith('/')) {
                            faviconUrl = window.location.origin + faviconUrl;
                        } else {
                            faviconUrl = window.location.origin + '/storage/' + faviconUrl;
                        }
                    }
                    
                    // Remove 'app/public' from URL if it exists
                    faviconUrl = faviconUrl.replace('/storage/app/public/', '/storage/');
                    faviconUrl = faviconUrl.replace('storage/app/public/', 'storage/');
                    
                    faviconPreview.value = faviconUrl;
                    initialFavicon.value = faviconUrl; // Store initial state
                }
            } catch (error) {
                console.error('Error loading hotel information:', error);
            } finally {
                loading.value = false;
            }
        };

        const loadHotelSettings = async () => {
            try {
                const response = await axios.get('/api/hotel/settings');
                const data = response.data;
                
                hotelSettings.value = {
                    currency: data.currency || 'USD',
                    timezone: data.timezone || 'UTC',
                    checkin_time: data.checkin_time || '',
                    checkout_time: data.checkout_time || '',
                    tax_rate: data.tax_rate !== undefined && data.tax_rate !== null ? data.tax_rate : 0,
                    service_charge: data.service_charge !== undefined && data.service_charge !== null ? data.service_charge : 0
                };

                // Load additional configuration
                additionalConfig.value = {
                    online_booking: data.online_booking !== undefined ? data.online_booking : false,
                    email_notifications: data.email_notifications !== undefined ? data.email_notifications : true,
                    auto_checkin: data.auto_checkin !== undefined ? data.auto_checkin : false,
                    maintenance_mode: data.maintenance_mode !== undefined ? data.maintenance_mode : false
                };
            } catch (error) {
                console.error('Error loading hotel settings:', error);
            }
        };

        onMounted(() => {
            loadHotelInfo();
            loadHotelSettings();
        });

        return {
            hotelInfo,
            hotelSettings,
            additionalConfig,
            saving,
            savingSettings,
            savingConfig,
            loading,
            logoInput,
            faviconInput,
            logoPreview,
            faviconPreview,
            currencies,
            timezones,
            handleLogoUpload,
            handleFaviconUpload,
            removeLogo,
            removeFavicon,
            handleImageError,
            saveHotelInfo,
            saveHotelSettings,
            saveAdditionalConfig
        };
    }
}
</script>

<style scoped>
.hotel-setup-page {
    padding: 24px;
}

.page-header {
    margin-bottom: 24px;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
}

.page-subtitle {
    font-size: 14px;
    color: #718096;
}

.setup-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 24px;
}

.setup-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    color: #1a202c;
}

.card-body {
    padding: 24px;
}

.setup-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    font-size: 14px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    transition: all 0.2s;
    background: #fff;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.config-section {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 24px;
}

.config-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
}

.config-item:last-child {
    border-bottom: none;
}

.config-title {
    font-size: 14px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 4px;
}

.config-desc {
    font-size: 12px;
    color: #718096;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 24px;
    cursor: pointer;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e0;
    transition: 0.3s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
    background-color: #667eea;
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #5568d3;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Image Upload Styles */
.image-upload-wrapper {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.image-preview-container {
    width: 100%;
    min-height: 120px;
    border: 2px dashed #e0e0e0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    position: relative;
    overflow: hidden;
}

.image-preview {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.image-preview img {
    max-width: 100%;
    max-height: 100px;
    object-fit: contain;
    border-radius: 4px;
}

.image-preview img[src=""],
.image-preview img:not([src]) {
    display: none;
}

.favicon-preview img {
    max-width: 48px;
    max-height: 48px;
    object-fit: contain;
}

.remove-image-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    z-index: 10;
}

.remove-image-btn:hover {
    background: rgba(220, 53, 69, 1);
    transform: scale(1.1);
}

.image-upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 24px;
    color: #718096;
}

.favicon-placeholder {
    padding: 16px;
}

.image-upload-placeholder svg {
    color: #cbd5e0;
}

.image-upload-placeholder p {
    font-size: 12px;
    margin: 0;
    color: #a0aec0;
}

.file-upload-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    background: #f7f8fc;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
    font-weight: 500;
    color: #495057;
    width: fit-content;
}

.file-upload-btn:hover {
    background: #e9ecef;
    border-color: #667eea;
    color: #667eea;
}

.upload-hint {
    font-size: 12px;
    color: #718096;
    margin: 0;
    font-style: italic;
}

.optional-label {
    font-size: 11px;
    color: #a0aec0;
    font-weight: 400;
    font-style: italic;
    margin-left: 4px;
}

@media (max-width: 768px) {
    .setup-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>


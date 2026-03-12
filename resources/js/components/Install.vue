<template>
    <div class="install-page">
        <div class="install-container">
            <div class="install-header">
                <h1 class="install-title">Hotel Management System</h1>
                <p class="install-subtitle">Installation Wizard</p>
            </div>

            <div class="install-content">
                <!-- Progress Bar -->
                <div class="progress-section">
                    <div class="progress-bar-container">
                        <div class="progress-bar" :style="{ width: progressPercentage + '%' }"></div>
                    </div>
                    <div class="progress-text">{{ currentStep }} of {{ totalSteps }}</div>
                </div>

                <!-- Step 1: Database Configuration -->
                <div v-if="currentStep === 1" class="install-step">
                    <h2 class="step-title">Database Configuration</h2>
                    <p class="step-description">Enter your database connection details</p>

                    <div class="form-group">
                        <label>Database Host</label>
                        <input
                            type="text"
                            v-model="dbConfig.host"
                            class="form-input"
                            placeholder="127.0.0.1"
                            :disabled="installing"
                        />
                    </div>

                    <div class="form-group">
                        <label>Database Port</label>
                        <input
                            type="text"
                            v-model="dbConfig.port"
                            class="form-input"
                            placeholder="3306"
                            :disabled="installing"
                        />
                    </div>

                    <div class="form-group">
                        <label>Database Name</label>
                        <input
                            type="text"
                            v-model="dbConfig.database"
                            class="form-input"
                            placeholder="hms_database"
                            :disabled="installing"
                        />
                    </div>

                    <div class="form-group">
                        <label>Database Username</label>
                        <input
                            type="text"
                            v-model="dbConfig.username"
                            class="form-input"
                            placeholder="root"
                            :disabled="installing"
                        />
                    </div>

                    <div class="form-group">
                        <label>Database Password</label>
                        <input
                            type="password"
                            v-model="dbConfig.password"
                            class="form-input"
                            placeholder=""
                            :disabled="installing"
                        />
                    </div>

                    <div v-if="dbError" class="error-message">{{ dbError }}</div>

                    <button
                        class="btn btn-primary"
                        @click="checkDatabaseConnection"
                        :disabled="installing || !dbConfig.database"
                    >
                        {{ installing ? 'Checking...' : 'Test Connection' }}
                    </button>
                </div>

                <!-- Step 2: Run Migrations -->
                <div v-if="currentStep === 2" class="install-step">
                    <h2 class="step-title">Database Setup</h2>
                    <p class="step-description">Creating database tables and structure</p>

                    <div v-if="migrationStatus === 'running'" class="status-message">
                        <div class="loading-spinner"></div>
                        <p>Running database migrations...</p>
                        <p class="status-detail">{{ migrationMessage }}</p>
                    </div>

                    <div v-if="migrationStatus === 'success'" class="success-message">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M20 6L9 17L4 12" stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <p>Database tables created successfully!</p>
                    </div>

                    <div v-if="migrationStatus === 'error'" class="error-message">
                        <p>{{ migrationError }}</p>
                    </div>

                    <button
                        v-if="migrationStatus === 'idle'"
                        class="btn btn-primary"
                        @click="runMigrations"
                        :disabled="installing"
                    >
                        Create Database Tables
                    </button>

                    <button
                        v-if="migrationStatus === 'success'"
                        class="btn btn-primary"
                        @click="goToStep(3)"
                    >
                        Next
                    </button>
                </div>

                <!-- Step 3: Create Super Admin -->
                <div v-if="currentStep === 3" class="install-step">
                    <h2 class="step-title">Create Super Administrator</h2>
                    <p class="step-description">Create the first admin account to manage the system</p>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input
                            type="text"
                            v-model="adminForm.name"
                            class="form-input"
                            placeholder="John Doe"
                            :disabled="installing"
                        />
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input
                            type="email"
                            v-model="adminForm.email"
                            class="form-input"
                            placeholder="admin@example.com"
                            :disabled="installing"
                        />
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input
                            type="password"
                            v-model="adminForm.password"
                            class="form-input"
                            placeholder="Minimum 8 characters"
                            :disabled="installing"
                        />
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input
                            type="password"
                            v-model="adminForm.password_confirmation"
                            class="form-input"
                            placeholder=""
                            :disabled="installing"
                        />
                    </div>

                    <div v-if="adminError" class="error-message">{{ adminError }}</div>

                    <button
                        class="btn btn-primary"
                        @click="createSuperAdmin"
                        :disabled="installing || !isAdminFormValid"
                    >
                        {{ installing ? 'Creating...' : 'Create Super Admin' }}
                    </button>
                </div>

                <!-- Step 4: Installation Complete -->
                <div v-if="currentStep === 4" class="install-step">
                    <div class="success-screen">
                        <div class="success-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="#27ae60" stroke-width="2"/>
                                <path d="M8 12L11 15L16 9" stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h2 class="step-title">Installation Complete!</h2>
                        <p class="step-description">Your Hotel Management System has been successfully installed.</p>

                        <div class="installation-summary">
                            <div class="summary-item">
                                <span class="summary-label">Database:</span>
                                <span class="summary-value">Configured</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Tables:</span>
                                <span class="summary-value">Created</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Super Admin:</span>
                                <span class="summary-value">{{ adminForm.email }}</span>
                            </div>
                        </div>

                        <button
                            class="btn btn-primary btn-large"
                            @click="goToLogin"
                        >
                            Go to Login Page
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
    name: 'Install',
    setup() {
        const router = useRouter();

        const currentStep = ref(1);
        const totalSteps = 4;
        const installing = ref(false);

        const dbConfig = ref({
            host: '127.0.0.1',
            port: '3306',
            database: '',
            username: 'root',
            password: ''
        });

        const dbError = ref('');
        const migrationStatus = ref('idle'); // idle, running, success, error
        const migrationMessage = ref('');
        const migrationError = ref('');

        const adminForm = ref({
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
        });
        const adminError = ref('');

        const progressPercentage = computed(() => {
            return (currentStep.value / totalSteps) * 100;
        });

        const isAdminFormValid = computed(() => {
            return adminForm.value.name &&
                   adminForm.value.email &&
                   adminForm.value.password &&
                   adminForm.value.password === adminForm.value.password_confirmation &&
                   adminForm.value.password.length >= 8;
        });

        // Warn the user if they try to reload/close the page before installation completes
        const beforeUnloadHandler = (event) => {
            if (currentStep.value < 4) {
                event.preventDefault();
                event.returnValue = '';
                return '';
            }
            return undefined;
        };

        // Check if already installed
        // If there is at least one user in the users table (backend returns installed: true),
        // block access to the installer by redirecting to the login page.
        const checkInstallation = async () => {
            try {
                const response = await axios.get('/api/install/check');
                if (response.data?.installed) {
                    goToLogin();
                }
            } catch (error) {
                // Not installed, continue with installation from step 1
                console.log('Installation check:', error);
            }
        };

        // Check database connection
        const checkDatabaseConnection = async () => {
            installing.value = true;
            dbError.value = '';

            try {
                const response = await axios.post('/api/install/check-database', dbConfig.value);

                if (response.data.success) {
                    // Move to next step
                    currentStep.value = 2;
                } else {
                    dbError.value = response.data.message || 'Database connection failed';
                }
            } catch (error) {
                dbError.value = error.response?.data?.message || 'Failed to connect to database. Please check your credentials.';
            } finally {
                installing.value = false;
            }
        };

        // Run migrations
        const runMigrations = async () => {
            installing.value = true;
            migrationStatus.value = 'running';
            migrationMessage.value = 'Initializing database structure...';
            migrationError.value = '';

            try {
                const response = await axios.post('/api/install/run-migrations', dbConfig.value);

                if (response.data.success) {
                    migrationStatus.value = 'success';
                    migrationMessage.value = 'All database tables created successfully!';
                } else {
                    migrationStatus.value = 'error';
                    migrationError.value = response.data.message || 'Migration failed';
                }
            } catch (error) {
                migrationStatus.value = 'error';
                migrationError.value = error.response?.data?.message || 'Failed to run migrations. Please check your database configuration.';
            } finally {
                installing.value = false;
            }
        };

        // Create super admin
        const createSuperAdmin = async () => {
            // Ensure database import/migrations are completed before creating super admin
            if (migrationStatus.value !== 'success') {
                adminError.value = 'Please complete the database setup (import database) before creating the super admin user.';
                return;
            }

            if (!isAdminFormValid.value) {
                adminError.value = 'Please fill all fields correctly. Password must be at least 8 characters and match confirmation.';
                return;
            }

            installing.value = true;
            adminError.value = '';

            try {
                const response = await axios.post('/api/install/create-super-admin', adminForm.value);

                if (response.data.success) {
                    // Run complete (lock file + seeders)
                    try {
                        await completeInstallation();
                    } catch (e) {
                        // If complete fails (e.g. seeder error), still show finish screen
                    }
                } else {
                    adminError.value = response.data.message || 'Failed to create super admin';
                }
            } catch (error) {
                adminError.value = error.response?.data?.message || 'Failed to create super admin. Please try again.';
            } finally {
                installing.value = false;
            }
        };

        // Complete installation (called after super admin created; redirect is handled in createSuperAdmin)
        const completeInstallation = async () => {
            try {
                const response = await axios.post('/api/install/complete', {
                    seed_data: true
                });
                currentStep.value = 4;
                return response;
            } catch (error) {
                console.error('Error completing installation:', error);
                currentStep.value = 4;
                throw error;
            }
        };

        // Go to login page from router
        const goToLogin = () => {
            router.push('/login');
        };

        const goToStep = (step) => {
            currentStep.value = step;
        };

        onMounted(() => {
            checkInstallation();
            window.addEventListener('beforeunload', beforeUnloadHandler);
        });

        onBeforeUnmount(() => {
            window.removeEventListener('beforeunload', beforeUnloadHandler);
        });

        return {
            currentStep,
            totalSteps,
            progressPercentage,
            installing,
            dbConfig,
            dbError,
            migrationStatus,
            migrationMessage,
            migrationError,
            adminForm,
            adminError,
            isAdminFormValid,
            checkDatabaseConnection,
            runMigrations,
            createSuperAdmin,
            goToLogin,
            goToStep
        };
    }
}
</script>

<style scoped>
.install-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.install-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    max-width: 600px;
    width: 100%;
    overflow: hidden;
}

.install-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 32px;
    text-align: center;
}

.install-title {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.install-subtitle {
    font-size: 16px;
    margin: 0;
    opacity: 0.9;
}

.install-content {
    padding: 32px;
}

.progress-section {
    margin-bottom: 32px;
}

.progress-bar-container {
    width: 100%;
    height: 8px;
    background: #ecf0f1;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
}

.progress-text {
    text-align: center;
    font-size: 14px;
    color: #7f8c8d;
    font-weight: 500;
}

.install-step {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.step-title {
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 8px 0;
}

.step-description {
    font-size: 14px;
    color: #7f8c8d;
    margin: 0 0 24px 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 8px;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
}

.form-input:disabled {
    background: #f7f8fc;
    cursor: not-allowed;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-large {
    padding: 16px 32px;
    font-size: 16px;
    width: 100%;
    margin-top: 24px;
}

.error-message {
    background: #fee;
    color: #c33;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 14px;
}

.status-message {
    text-align: center;
    padding: 32px;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #ecf0f1;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.status-detail {
    color: #7f8c8d;
    font-size: 13px;
    margin-top: 8px;
}

.success-message {
    background: #e8f5e9;
    color: #27ae60;
    padding: 16px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.success-screen {
    text-align: center;
    padding: 20px 0;
}

.success-icon {
    margin: 0 auto 24px;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e8f5e9;
    border-radius: 50%;
}

.installation-summary {
    background: #f7f8fc;
    border-radius: 8px;
    padding: 20px;
    margin: 24px 0;
    text-align: left;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #ecf0f1;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-label {
    font-weight: 500;
    color: #7f8c8d;
}

.summary-value {
    font-weight: 600;
    color: #2c3e50;
}

@media (max-width: 640px) {
    .install-container {
        margin: 0;
        border-radius: 0;
    }

    .install-content {
        padding: 24px;
    }
}
</style>

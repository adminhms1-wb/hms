<template>
    <div class="login-wrapper super-admin-login">
        <div class="login-container">
            <div class="login-left">
                <div class="login-brand">
                    <h1>Hotel Management System</h1>
                    <p>Super Admin Portal</p>
                    <div class="admin-badge">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="currentColor"/>
                        </svg>
                        <span>Hotel Owner Access</span>
                    </div>
                </div>
            </div>
            <div class="login-right">
                <div class="login-card">
                    <div class="login-header">
                        <h2>Super Admin Login</h2>
                        <p>Hotel Owner / Administrator Access</p>
                    </div>

                    <form @submit.prevent="handleLogin" class="login-form">
                        <div class="form-group">
                            <label>Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="form-control"
                                placeholder="Super Admin Email"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="form-control"
                                placeholder="Password"
                                required
                            />
                        </div>

                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" v-model="form.remember" />
                                <span>Remember me</span>
                            </label>
                            <a href="#" class="forgot-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
                            {{ loading ? 'Signing in...' : 'Sign In as Super Admin' }}
                        </button>

                        <div class="login-divider">
                            <span>or</span>
                        </div>

                        <router-link to="/login" class="btn btn-secondary btn-block">
                            Regular Staff Login
                        </router-link>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAlert } from '../composables/useAlert';

export default {
    name: 'SuperAdminLogin',
    setup() {
        const router = useRouter();
        const { error: showError, success: showSuccess } = useAlert();
        const loading = ref(false);
        const form = ref({
            email: '',
            password: '',
            remember: false
        });

        const handleLogin = async () => {
            loading.value = true;
            try {
                const response = await axios.post('/api/super-admin/login', {
                    email: form.value.email,
                    password: form.value.password,
                    remember: form.value.remember
                }, {
                    withCredentials: true
                });

                if (response.data.success) {
                    showSuccess('Super Admin login successful!');
                    // Session is stored on server, redirect to dashboard
                    router.push('/');
                } else {
                    showError(response.data.message || 'Login failed');
                }
            } catch (error) {
                console.error('Login error:', error);
                const errorMessage = error.response?.data?.message || 'Login failed. Please try again.';
                showError(errorMessage);
            } finally {
                loading.value = false;
            }
        };

        return {
            form,
            loading,
            handleLogin
        };
    }
}
</script>

<style scoped>
.super-admin-login .login-left {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.admin-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    backdrop-filter: blur(10px);
}

.admin-badge svg {
    color: #f39c12;
}

.admin-badge span {
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.login-divider {
    text-align: center;
    margin: 20px 0;
    position: relative;
}

.login-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e0e0e0;
}

.login-divider span {
    position: relative;
    background: white;
    padding: 0 12px;
    color: #95a5a6;
    font-size: 12px;
}

.btn-secondary {
    background: #ecf0f1;
    color: #2c3e50;
    border: 1px solid #bdc3c7;
}

.btn-secondary:hover {
    background: #d5dbdb;
    border-color: #95a5a6;
}

.login-wrapper {
    min-height: 100vh;
    background: #f7f8fc;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-container {
    display: flex;
    width: 100%;
    max-width: 1200px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    min-height: 600px;
}

.login-left {
    flex: 1;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px;
    color: white;
}

.login-brand h1 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 12px;
}

.login-brand p {
    font-size: 18px;
    opacity: 0.9;
}

.login-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px;
}

.login-card {
    width: 100%;
    max-width: 400px;
}

.login-header {
    margin-bottom: 32px;
}

.login-header h2 {
    font-size: 28px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.login-header p {
    font-size: 14px;
    color: #6c757d;
}

.login-form {
    margin-top: 32px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
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
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    font-size: 14px;
    color: #6c757d;
    cursor: pointer;
}

.checkbox-wrapper input {
    margin-right: 8px;
    cursor: pointer;
}

.forgot-link {
    font-size: 14px;
    color: #667eea;
    text-decoration: none;
}

.forgot-link:hover {
    text-decoration: underline;
}

.btn {
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
    text-align: center;
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

.btn-block {
    width: 100%;
}

@media (max-width: 768px) {
    .login-container {
        flex-direction: column;
    }

    .login-left {
        padding: 40px;
        min-height: 200px;
    }

    .login-right {
        padding: 40px 20px;
    }
}
</style>


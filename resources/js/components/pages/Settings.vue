<template>
    <div class="settings-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Settings</h1>
                <p class="page-subtitle">Manage your account settings and preferences</p>
            </div>
        </div>
        
        <div class="settings-grid">
            <div class="settings-card">
                <div class="card-header">
                    <h2 class="card-title">Profile Settings</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" class="form-control" placeholder="Enter your full name" />
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" class="form-control" placeholder="Enter your email" />
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" class="form-control" placeholder="Enter your phone number" />
                    </div>
                    <button class="btn btn-primary">Save Changes</button>
                </div>
            </div>
            
            <div class="settings-card">
                <div class="card-header">
                    <h2 class="card-title">Security</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" class="form-control" placeholder="Enter current password" />
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" class="form-control" placeholder="Enter new password" />
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" class="form-control" placeholder="Confirm new password" />
                    </div>
                    <button class="btn btn-primary">Update Password</button>
                </div>
            </div>
            
            <div class="settings-card">
                <div class="card-header">
                    <h2 class="card-title">Preferences</h2>
                </div>
                <div class="card-body">
                    <div class="preference-item">
                        <div>
                            <h3 class="preference-title">Email Notifications</h3>
                            <p class="preference-desc">Receive email notifications about your account activity</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="preference-item">
                        <div>
                            <h3 class="preference-title">Dark Mode</h3>
                            <p class="preference-desc">Switch to dark theme</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="settings-card">
                <div class="card-header">
                    <h2 class="card-title">Account Actions</h2>
                </div>
                <div class="card-body">
                    <div class="action-item">
                        <div>
                            <h3 class="action-title">Export Data</h3>
                            <p class="action-desc">Download all your account data</p>
                        </div>
                        <button class="btn btn-secondary">Export</button>
                    </div>
                    
                    <div class="action-item">
                        <div>
                            <h3 class="action-title">Delete Account</h3>
                            <p class="action-desc">Permanently delete your account and all data</p>
                        </div>
                        <button class="btn btn-danger">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Settings'
}
</script>

<style scoped>
.settings-page {
    max-width: 1400px;
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

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 24px;
}

.settings-card {
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

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #2d3748;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    font-size: 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.2s;
    background: #fff;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.preference-item,
.action-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
}

.preference-item:last-child,
.action-item:last-child {
    border-bottom: none;
}

.preference-title,
.action-title {
    font-size: 14px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 4px;
}

.preference-desc,
.action-desc {
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
    padding: 10px 20px;
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

.btn-primary:hover {
    background: #5568d3;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #f7fafc;
    color: #4a5568;
    border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
    background: #edf2f7;
}

.btn-danger {
    background: #f56565;
    color: white;
}

.btn-danger:hover {
    background: #e53e3e;
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .preference-item,
    .action-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
}
</style>


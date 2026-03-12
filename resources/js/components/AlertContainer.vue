<template>
    <div class="alert-container" v-if="alerts.length > 0">
        <transition-group name="alert" tag="div">
            <div
                v-for="alert in alerts"
                :key="alert.id"
                :class="['alert', 'alert-dismissible', `alert-${alert.type === 'error' ? 'danger' : alert.type}`]"
                role="alert"
            >
                <div class="alert-content">
                    <span class="alert-icon">
                        <svg v-if="alert.type === 'success'" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg v-else-if="alert.type === 'error' || alert.type === 'danger'" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg v-else-if="alert.type === 'warning'" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 2L2 18H18L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 12V8M10 14H10.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <svg v-else width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                            <path d="M10 6V10M10 14H10.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="alert-message">{{ alert.message }}</span>
                </div>
                <button type="button" class="btn-close" @click="removeAlert(alert.id)" aria-label="Close"></button>
            </div>
        </transition-group>
    </div>
</template>

<script>
import { useAlert } from '../composables/useAlert';

export default {
    name: 'AlertContainer',
    setup() {
        const { alerts, removeAlert } = useAlert();
        return {
            alerts,
            removeAlert
        };
    }
}
</script>

<style scoped>
.alert-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 400px;
    pointer-events: none;
}

.alert {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    pointer-events: auto;
    animation: slideIn 0.3s ease-out;
}

.alert-content {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.alert-icon {
    flex-shrink: 0;
}

.alert-message {
    font-size: 14px;
    line-height: 1.5;
    flex: 1;
}

/* Bootstrap 5 Close Button */
.btn-close {
    box-sizing: content-box;
    width: 1em;
    height: 1em;
    padding: 0.25em 0.25em;
    color: #000;
    background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
    border: 0;
    border-radius: 0.375rem;
    opacity: 0.5;
    cursor: pointer;
    flex-shrink: 0;
    margin-left: 12px;
}

.btn-close:hover {
    color: #000;
    text-decoration: none;
    opacity: 0.75;
}

.btn-close:focus {
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    opacity: 1;
}

.alert-success .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}

.alert-danger .btn-close,
.alert-error .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}

.alert-warning .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}

.alert-info .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}

/* Bootstrap 5 Alert Styles */
.alert {
    position: relative;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.5;
}

.alert-success {
    color: #0f5132;
    background-color: #d1e7dd;
    border-color: #badbcc;
    border-left: 4px solid #198754;
}

.alert-error,
.alert-danger {
    color: #842029;
    background-color: #f8d7da;
    border-color: #f5c2c7;
    border-left: 4px solid #dc3545;
}

.alert-warning {
    color: #664d03;
    background-color: #fff3cd;
    border-color: #ffecb5;
    border-left: 4px solid #ffc107;
}

.alert-info {
    color: #055160;
    background-color: #cff4fc;
    border-color: #b6effb;
    border-left: 4px solid #0dcaf0;
}

/* Transitions */
.alert-enter-active {
    transition: all 0.3s ease-out;
}

.alert-leave-active {
    transition: all 0.3s ease-in;
}

.alert-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.alert-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@media (max-width: 768px) {
    .alert-container {
        right: 10px;
        left: 10px;
        max-width: none;
    }
}
</style>


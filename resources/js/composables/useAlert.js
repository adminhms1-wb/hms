import { ref, reactive } from 'vue';

// Shared state across all components
const alerts = ref([]);

export function useAlert() {
    const showAlert = (message, type = 'info', duration = 5000) => {
        const id = Date.now();
        const alert = {
            id,
            message,
            type, // 'success', 'error', 'warning', 'info'
            duration
        };

        alerts.value.push(alert);

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                removeAlert(id);
            }, duration);
        }

        return id;
    };

    const removeAlert = (id) => {
        const index = alerts.value.findIndex(alert => alert.id === id);
        if (index > -1) {
            alerts.value.splice(index, 1);
        }
    };

    const success = (message, duration = 5000) => showAlert(message, 'success', duration);
    const error = (message, duration = 5000) => showAlert(message, 'error', duration);
    const warning = (message, duration = 5000) => showAlert(message, 'warning', duration);
    const info = (message, duration = 5000) => showAlert(message, 'info', duration);

    return {
        alerts,
        showAlert,
        removeAlert,
        success,
        error,
        warning,
        info
    };
}


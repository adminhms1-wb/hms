import axios from 'axios';
window.axios = axios;

// Get base URL from meta tag or auto-detect
const getBaseUrl = () => {
    const metaTag = document.querySelector('meta[name="base-url"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    
    // Auto-detect base URL for subdirectory setups
    const path = window.location.pathname;
    if (path.includes('/public/')) {
        const publicIndex = path.indexOf('/public/');
        return path.substring(0, publicIndex + 7);
    }
    return '';
};

// Set axios base URL
window.axios.defaults.baseURL = getBaseUrl();

// Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
}

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';

// Enable sending cookies with requests for session-based authentication
window.axios.defaults.withCredentials = true;

// Safely ignore local debug ingest calls that are not available in all environments
// This prevents repeated net::ERR_INSUFFICIENT_RESOURCES errors from tools-only URLs
if (typeof window.fetch === 'function') {
    const originalFetch = window.fetch.bind(window);
    const DEBUG_INGEST_PREFIX = 'http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c';

    window.fetch = (...args) => {
        const [input] = args;
        const url = typeof input === 'string' ? input : input && input.url;

        if (url && url.startsWith(DEBUG_INGEST_PREFIX)) {
            // Short‑circuit debug telemetry calls with a successful no‑op response
            return Promise.resolve(new Response(null, { status: 204, statusText: 'No Content' }));
        }

        return originalFetch(...args);
    };
}
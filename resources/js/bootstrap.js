import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set axios base URL from global variable injected by Blade layout (if present)
if (typeof window !== 'undefined' && window.APP_API_BASE) {
    // Ensure no trailing slash
    window.axios.defaults.baseURL = window.APP_API_BASE.replace(/\/$/, '');
}

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Function to update CSRF token
function updateCsrfToken() {
	const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
	if (token) {
		window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
	}
}

// Initial CSRF token setup
updateCsrfToken();

// Global error handler for axios - handle 419 CSRF token expired
window.axios.interceptors.response.use(
	response => response,
	error => {
		if (error.response?.status === 419) {
			console.warn('⚠️ CSRF token expired (419), attempting refresh...');
			// Refresh page to get new token
			window.location.href = window.location.href;
			return Promise.reject(error);
		}
		return Promise.reject(error);
	}
);

// Expose function globally for use in components
window.updateCsrfToken = updateCsrfToken;

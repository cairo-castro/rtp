/**
 * CSRF Protection JavaScript Utility
 * Handles CSRF token management for AJAX requests
 */
class CsrfManager {
    constructor() {
        this.token = this.getTokenFromMeta();
        this.setupAjaxDefaults();
    }

    /**
     * Get CSRF token from meta tag
     */
    getTokenFromMeta() {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        return metaToken ? metaToken.getAttribute('content') : null;
    }

    /**
     * Get CSRF token from form input
     */
    getTokenFromForm(form) {
        const tokenInput = form.querySelector('input[name="csrf_token"]');
        return tokenInput ? tokenInput.value : null;
    }

    /**
     * Add CSRF token to form data
     */
    addTokenToFormData(formData) {
        if (this.token) {
            formData.append('csrf_token', this.token);
        }
        return formData;
    }

    /**
     * Add CSRF token to URL parameters (for GET requests if needed)
     */
    addTokenToUrl(url) {
        if (!this.token) return url;
        
        const separator = url.includes('?') ? '&' : '?';
        return url + separator + 'csrf_token=' + encodeURIComponent(this.token);
    }

    /**
     * Setup default CSRF protection for all AJAX requests
     */
    setupAjaxDefaults() {
        if (!this.token) return;

        // Setup for jQuery if available
        if (typeof $ !== 'undefined' && $.ajaxSetup) {
            $.ajaxSetup({
                beforeSend: (xhr, settings) => {
                    if (settings.type === 'POST' || settings.type === 'PUT' || settings.type === 'DELETE') {
                        xhr.setRequestHeader('X-CSRF-Token', this.token);
                    }
                }
            });
        }

        // Setup for Fetch API
        const originalFetch = window.fetch;
        window.fetch = (url, options = {}) => {
            if (options.method && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(options.method.toUpperCase())) {
                options.headers = options.headers || {};
                
                // Add CSRF token to headers
                if (this.token) {
                    options.headers['X-CSRF-Token'] = this.token;
                }

                // If using FormData, add token to form data
                if (options.body instanceof FormData) {
                    options.body.append('csrf_token', this.token);
                }
                
                // If using JSON, add token to data
                if (options.headers['Content-Type'] === 'application/json' && typeof options.body === 'string') {
                    try {
                        const data = JSON.parse(options.body);
                        data.csrf_token = this.token;
                        options.body = JSON.stringify(data);
                    } catch (e) {
                        console.warn('Could not add CSRF token to JSON body');
                    }
                }
            }
            
            return originalFetch(url, options);
        };
    }

    /**
     * Add CSRF token to all forms on page load
     */
    protectForms() {
        document.addEventListener('DOMContentLoaded', () => {
            const forms = document.querySelectorAll('form[method="POST"], form[method="post"]');
            
            forms.forEach(form => {
                // Check if form already has CSRF token
                if (!form.querySelector('input[name="csrf_token"]')) {
                    this.addTokenToForm(form);
                }
            });
        });
    }

    /**
     * Add CSRF token input to a specific form
     */
    addTokenToForm(form) {
        if (!this.token) return;

        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'csrf_token';
        tokenInput.value = this.token;
        
        form.appendChild(tokenInput);
    }

    /**
     * Refresh CSRF token from server
     */
    async refreshToken() {
        try {
            const response = await fetch('/csrf/refresh', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                this.token = data.token;
                
                // Update meta tag
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                if (metaToken) {
                    metaToken.setAttribute('content', this.token);
                }
                
                // Update all form inputs
                document.querySelectorAll('input[name="csrf_token"]').forEach(input => {
                    input.value = this.token;
                });
            }
        } catch (error) {
            console.error('Failed to refresh CSRF token:', error);
        }
    }

    /**
     * Handle CSRF token errors
     */
    handleTokenError(response) {
        if (response.status === 403) {
            console.warn('CSRF token validation failed, refreshing token...');
            this.refreshToken();
        }
    }

    /**
     * Validate current token format
     */
    isValidToken() {
        return this.token && typeof this.token === 'string' && this.token.length > 10;
    }
}

// Initialize CSRF manager
const csrfManager = new CsrfManager();

// Auto-protect forms when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        csrfManager.protectForms();
    });
} else {
    csrfManager.protectForms();
}

// Export for global use
window.csrfManager = csrfManager;

// Utility functions for backward compatibility
window.getCsrfToken = () => csrfManager.token;
window.addCsrfToForm = (form) => csrfManager.addTokenToForm(form);
window.addCsrfToFormData = (formData) => csrfManager.addTokenToFormData(formData);
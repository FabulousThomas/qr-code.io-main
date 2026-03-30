/**
 * Enhanced Form Validation for QR-Hub.io
 * Provides real-time validation with helpful error messages
 */

class FormValidator {
    constructor() {
        this.validators = {
            url: this.validateUrl.bind(this),
            email: this.validateEmail.bind(this),
            phone: this.validatePhone.bind(this),
            text: this.validateText.bind(this),
            required: this.validateRequired.bind(this),
            barcode: this.validateBarcode.bind(this)
        };
        
        this.init();
    }

    init() {
        // Auto-initialize forms with validation
        document.addEventListener('DOMContentLoaded', () => {
            this.initializeForms();
        });
    }

    initializeForms() {
        const forms = document.querySelectorAll('form[data-validate="true"]');
        forms.forEach(form => this.setupFormValidation(form));
    }

    setupFormValidation(form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            // Add real-time validation
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearError(input));
            
            // Add validation attributes
            this.addValidationAttributes(input);
        });

        // Handle form submission
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
                this.showFormError(form, 'Please fix the errors below before submitting.');
            }
        });
    }

    addValidationAttributes(input) {
        const type = input.type || input.tagName.toLowerCase();
        const required = input.hasAttribute('required');
        
        // Add data-validation attributes based on input type
        switch(type) {
            case 'url':
                input.setAttribute('data-validation', 'url');
                break;
            case 'email':
                input.setAttribute('data-validation', 'email');
                break;
            case 'tel':
                input.setAttribute('data-validation', 'phone');
                break;
            default:
                if (required) {
                    input.setAttribute('data-validation', 'required');
                }
        }
    }

    validateField(input) {
        const validationTypes = (input.getAttribute('data-validation') || '').split(' ');
        let isValid = true;
        let errorMessage = '';

        for (const type of validationTypes) {
            const validator = this.validators[type];
            if (validator) {
                const result = validator(input.value, input);
                if (!result.valid) {
                    isValid = false;
                    errorMessage = result.message;
                    break;
                }
            }
        }

        if (!isValid) {
            this.showError(input, errorMessage);
        } else {
            this.showSuccess(input);
        }

        return isValid;
    }

    validateForm(form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        let isValid = true;

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    // Validation methods
    validateUrl(value) {
        if (!value) return { valid: true, message: '' };
        
        const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
        if (!urlPattern.test(value)) {
            return { 
                valid: false, 
                message: 'Please enter a valid URL (e.g., https://example.com)' 
            };
        }
        
        return { valid: true, message: '' };
    }

    validateEmail(value) {
        if (!value) return { valid: true, message: '' };
        
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
            return { 
                valid: false, 
                message: 'Please enter a valid email address' 
            };
        }
        
        return { valid: true, message: '' };
    }

    validatePhone(value) {
        if (!value) return { valid: true, message: '' };
        
        // Support international formats: +1234567890 or +123 456 7890
        const phonePattern = /^\+[\d\s\-\(\)]+$/;
        if (!phonePattern.test(value)) {
            return { 
                valid: false, 
                message: 'Please enter a valid phone number with country code (e.g., +1234567890)' 
            };
        }
        
        if (value.replace(/\D/g, '').length < 10) {
            return { 
                valid: false, 
                message: 'Phone number must be at least 10 digits' 
            };
        }
        
        return { valid: true, message: '' };
    }

    validateText(value) {
        if (!value) return { valid: true, message: '' };
        
        if (value.trim().length === 0) {
            return { 
                valid: false, 
                message: 'This field cannot be empty' 
            };
        }
        
        return { valid: true, message: '' };
    }

    validateRequired(value) {
        if (!value || value.trim().length === 0) {
            return { 
                valid: false, 
                message: 'This field is required' 
            };
        }
        
        return { valid: true, message: '' };
    }

    validateBarcode(value, input) {
        if (!value) return { valid: true, message: '' };
        
        const format = input.getAttribute('data-format') || 'CODE128';
        const trimmed = value.trim();
        
        switch(format) {
            case 'EAN13':
                if (!/^\d+$/.test(trimmed)) {
                    return { valid: false, message: 'EAN-13 must contain digits only' };
                }
                if (trimmed.length !== 12 && trimmed.length !== 13) {
                    return { valid: false, message: 'EAN-13 must be 12 or 13 digits long' };
                }
                break;
            case 'UPC':
                if (!/^\d+$/.test(trimmed)) {
                    return { valid: false, message: 'UPC-A must contain digits only' };
                }
                if (trimmed.length !== 11 && trimmed.length !== 12) {
                    return { valid: false, message: 'UPC-A must be 11 or 12 digits long' };
                }
                break;
            case 'CODE39':
                const upper = trimmed.toUpperCase();
                if (!/^[A-Z0-9 \-\.\$/\+%]*$/.test(upper)) {
                    return { 
                        valid: false, 
                        message: 'CODE-39 supports A-Z, 0-9 and - . space $ / + % only' 
                    };
                }
                break;
            case 'CODE128':
                if (trimmed.length > 80) {
                    return { 
                        valid: false, 
                        message: 'CODE-128 content is too long. Please use 80 characters or fewer' 
                    };
                }
                break;
        }
        
        return { valid: true, message: '' };
    }

    // UI feedback methods
    showError(input, message) {
        this.clearFeedback(input);
        input.classList.add('is-invalid');
        
        const errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        errorElement.textContent = message;
        
        const parent = input.parentNode;
        parent.appendChild(errorElement);
        
        // Shake animation
        input.style.animation = 'shake 0.5s';
        setTimeout(() => {
            input.style.animation = '';
        }, 500);
    }

    showSuccess(input) {
        this.clearFeedback(input);
        input.classList.add('is-valid');
    }

    clearError(input) {
        input.classList.remove('is-invalid');
        const errorElement = input.parentNode.querySelector('.invalid-feedback');
        if (errorElement) {
            errorElement.remove();
        }
    }

    clearFeedback(input) {
        input.classList.remove('is-invalid', 'is-valid');
        const feedbackElements = input.parentNode.querySelectorAll('.invalid-feedback, .valid-feedback');
        feedbackElements.forEach(el => el.remove());
    }

    showFormError(form, message) {
        // Remove existing form error
        const existingError = form.querySelector('.form-error');
        if (existingError) {
            existingError.remove();
        }

        // Add new form error
        const errorElement = document.createElement('div');
        errorElement.className = 'alert alert-danger form-error mt-3';
        errorElement.innerHTML = `
            <i class="las la-exclamation-triangle me-2"></i>
            ${message}
        `;
        
        form.appendChild(errorElement);
        
        // Scroll to error
        errorElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            errorElement.remove();
        }, 5000);
    }
}

// Add shake animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    
    .is-valid {
        border-color: #28a745 !important;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
    }
    
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }
    
    .valid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #28a745;
    }
    
    .form-error {
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// Initialize the validator
const formValidator = new FormValidator();

// Export for global access
window.FormValidator = FormValidator;

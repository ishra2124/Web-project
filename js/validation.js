/**
 * SkillBridge - Form Validation
 * Client-side form validation utilities
 * 
 * NOTE: These are UI-only validations. Backend validation is still required.
 * API connection points are marked with TODO comments.
 */

/* ===== INITIALIZATION ===== */
function initFormValidation() {
    const forms = document.querySelectorAll('[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation on blur
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            // Clear error on focus
            input.addEventListener('focus', function() {
                clearFieldError(this);
            });
        });
    });
}

/* ===== FORM VALIDATION ===== */
function validateForm(form) {
    const fields = form.querySelectorAll('[required], [data-validate-type]');
    let isValid = true;
    
    fields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.getAttribute('data-validate-type') || field.type;
    const isRequired = field.hasAttribute('required');
    
    // Clear previous error
    clearFieldError(field);
    
    // Required check
    if (isRequired && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Skip further validation if empty and not required
    if (!value && !isRequired) {
        return true;
    }
    
    // Type-specific validation
    switch (type) {
        case 'email':
            if (!isValidEmail(value)) {
                showFieldError(field, 'Please enter a valid email address');
                return false;
            }
            break;
            
        case 'password':
            if (value.length < 8) {
                showFieldError(field, 'Password must be at least 8 characters');
                return false;
            }
            break;
            
        case 'url':
            if (!isValidUrl(value)) {
                showFieldError(field, 'Please enter a valid URL');
                return false;
            }
            break;
            
        case 'phone':
            if (!isValidPhone(value)) {
                showFieldError(field, 'Please enter a valid phone number');
                return false;
            }
            break;
            
        case 'number':
            const min = field.getAttribute('min');
            const max = field.getAttribute('max');
            const num = parseFloat(value);
            
            if (isNaN(num)) {
                showFieldError(field, 'Please enter a valid number');
                return false;
            }
            
            if (min !== null && num < parseFloat(min)) {
                showFieldError(field, `Value must be at least ${min}`);
                return false;
            }
            
            if (max !== null && num > parseFloat(max)) {
                showFieldError(field, `Value must be at most ${max}`);
                return false;
            }
            break;
    }
    
    // Custom validation via data attribute
    const customValidator = field.getAttribute('data-validate-custom');
    if (customValidator && typeof window[customValidator] === 'function') {
        const result = window[customValidator](value, field);
        if (result !== true) {
            showFieldError(field, result || 'Invalid value');
            return false;
        }
    }
    
    // Password confirmation
    if (field.getAttribute('data-validate-match')) {
        const matchFieldId = field.getAttribute('data-validate-match');
        const matchField = document.getElementById(matchFieldId);
        if (matchField && value !== matchField.value) {
            showFieldError(field, 'Passwords do not match');
            return false;
        }
    }
    
    return true;
}

/* ===== ERROR DISPLAY ===== */
function showFieldError(field, message) {
    field.classList.add('form-input-error');
    
    // Create or update error message
    let errorElement = field.parentNode.querySelector('.form-error');
    if (!errorElement) {
        errorElement = document.createElement('span');
        errorElement.className = 'form-error';
        field.parentNode.appendChild(errorElement);
    }
    errorElement.textContent = message;
}

function clearFieldError(field) {
    field.classList.remove('form-input-error');
    
    const errorElement = field.parentNode.querySelector('.form-error');
    if (errorElement) {
        errorElement.remove();
    }
}

function clearFormErrors(form) {
    const errorFields = form.querySelectorAll('.form-input-error');
    errorFields.forEach(field => clearFieldError(field));
}

/* ===== VALIDATION HELPERS ===== */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

function isValidPhone(phone) {
    // Accepts various phone formats
    const phoneRegex = /^[\+]?[(]?[0-9]{1,4}[)]?[-\s\./0-9]*$/;
    return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 10;
}

/* ===== FORM UTILITIES ===== */
/**
 * Get form data as object
 * @param {HTMLFormElement} form 
 * @returns {Object}
 */
function getFormData(form) {
    const formData = new FormData(form);
    const data = {};
    
    for (const [key, value] of formData.entries()) {
        // Handle multiple values (checkboxes)
        if (data[key]) {
            if (Array.isArray(data[key])) {
                data[key].push(value);
            } else {
                data[key] = [data[key], value];
            }
        } else {
            data[key] = value;
        }
    }
    
    return data;
}

/**
 * Reset form to initial state
 * @param {HTMLFormElement} form 
 */
function resetForm(form) {
    form.reset();
    clearFormErrors(form);
}

/* ===== API CONNECTION POINTS ===== */
// TODO: Replace with actual API endpoints
// Example usage:
// async function submitLoginForm(formData) {
//     const response = await fetch('/api/auth/login', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/json' },
//         body: JSON.stringify(formData)
//     });
//     return response.json();
// }

/**
 * SkillBridge - Main JavaScript Entry Point
 * Initializes all UI components and shared functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize UI components
    if (typeof initDropdowns === 'function') initDropdowns();
    if (typeof initModals === 'function') initModals();
    if (typeof initTabs === 'function') initTabs();
    if (typeof initFormValidation === 'function') initFormValidation();
    
    // Initialize Auth
    initAuth();
    
    console.log('SkillBridge initialized');
});

/* ===== API CONFIGURATION ===== */
const API_BASE_URL = window.location.origin + '/skillBridge/backend/api';

/**
 * Generic API Call Helper
 * @param {string} endpoint - e.g., '/auth/login.php'
 * @param {string} method - 'GET', 'POST', etc.
 * @param {Object} data - Body data for POST/PUT
 * @returns {Promise<Object>}
 */
async function apiCall(endpoint, method = 'GET', data = null) {
    const url = `${API_BASE_URL}${endpoint}`;
    
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            // Add Authorization header if needed (using sessions here, so credentials: include)
        },
        // Important for PHP Sessions to work across requests if strictly same-origin isn't possible,
        // but mostly for consistency.
        // credentials: 'include' 
    };

    if (data) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        // Handle non-200 responses (though my PHP returns check success flag usually)
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'Network or Server Error' };
    }
}

/* ===== AUTHENTICATION ===== */
function initAuth() {
    // Login Form
    const loginForm = document.querySelector('form[action="#"][method="post"]'); // Adjust selector if specific ID missing
    // Better to target getting specific IDs if existing HTML was updated or standard forms
    // In login.html I see <form action="#" method="post">. I should definitely give it an ID next time, 
    // but for now selector works if there's only one.
    
    // Actually, let's use the specific logic for Login Page
    if (window.location.pathname.includes('login.html')) {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerText;
                submitBtn.innerText = 'Logging in...';
                submitBtn.disabled = true;

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                const result = await apiCall('/auth/login.php', 'POST', data);

                if (result.success) {
                    // Save info to localStorage for UI updates (API session handles security)
                    localStorage.setItem('user', JSON.stringify(result.data));
                    
                    // Redirect based on role
                    if (result.data.role === 'admin') {
                        window.location.href = '../admin/admin-dashboard.html';
                    } else if (result.data.role === 'freelancer') {
                        window.location.href = '../freelancer/freelancer-dashboard.html';
                    } else if (result.data.role === 'client') {
                        window.location.href = '../client/client-dashboard.html';
                    }
                } else {
                    alert(result.message || 'Login failed');
                    submitBtn.innerText = originalText;
                    submitBtn.disabled = false;
                }
            });
        }
    }

    // Signup Form
    if (window.location.pathname.includes('signup.html')) {
        const form = document.getElementById('signupForm');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Basic Validation (Password Match)
                const password = document.getElementById('signupPassword').value;
                const confirm = document.getElementById('signupConfirmPassword').value;
                if (password !== confirm) {
                    alert("Passwords do not match!");
                    return;
                }

                const submitBtn = document.getElementById('createAccountBtn');
                const originalText = submitBtn.innerText;
                submitBtn.innerText = 'Creating Account...';
                submitBtn.disabled = true;

                // Handle Freelancer vs Client specific fields
                const role = document.querySelector('input[name="role"]:checked').value;
                
                const data = {
                    full_name: document.getElementById('signupName').value,
                    email: document.getElementById('signupEmail').value,
                    password: password,
                    role: role
                };

                // Add role specific data if needed (Register API in Phase 2 only took basics, 
                // but we might want to update profile immediately or later. 
                // The current register.php only takes basic info. 
                // We will stick to basic info for register, and user updates profile later.)

                const result = await apiCall('/auth/register.php', 'POST', data);

                if (result.success) {
                    alert('Account created successfully! Please login.');
                    window.location.href = 'login.html';
                } else {
                    alert(result.message || 'Registration failed');
                    submitBtn.innerText = originalText;
                    submitBtn.disabled = false;
                }
            });
        }
    }

    // Logout
    const logoutLinks = document.querySelectorAll('a[href*="login.html"]'); 
    // This is risky selector. Better to find "Log Out" text or specific class.
    // In dashboard HTML: <a href="../auth/login.html" class="btn btn-secondary btn-sm">Log Out</a>
    
    logoutLinks.forEach(link => {
        if (link.innerText.toLowerCase().includes('log out')) {
            link.addEventListener('click', async function(e) {
                e.preventDefault();
                await apiCall('/auth/logout.php', 'GET');
                localStorage.removeItem('user');
                window.location.href = link.href;
            });
        }
    });
}


/**
 * Utility: Get element by selector with null check
 * @param {string} selector - CSS selector
 * @returns {Element|null}
 */
function getElement(selector) {
    return document.querySelector(selector);
}

/**
 * Utility: Get all elements by selector
 * @param {string} selector - CSS selector
 * @returns {NodeList}
 */
function getElements(selector) {
    return document.querySelectorAll(selector);
}

/**
 * Utility: Add event listener with null check
 * @param {string} selector - CSS selector
 * @param {string} event - Event type
 * @param {Function} handler - Event handler
 */
function addEvent(selector, event, handler) {
    const element = typeof selector === 'string' ? getElement(selector) : selector;
    if (element) {
        element.addEventListener(event, handler);
    }
}

/**
 * Utility: Toggle class on element
 * @param {Element} element - DOM element
 * @param {string} className - Class to toggle
 */
function toggleClass(element, className) {
    if (element) {
        element.classList.toggle(className);
    }
}

/**
 * Utility: Show/hide element
 * @param {Element} element - DOM element
 * @param {boolean} show - Whether to show or hide
 */
function setVisible(element, show) {
    if (element) {
        element.classList.toggle('hidden', !show);
    }
}

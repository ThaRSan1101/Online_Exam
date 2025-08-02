// Index page specific JavaScript functions

// Initialize index page functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeIndexPage();
});

function initializeIndexPage() {
    // Set up form validation
    setupFormValidation();
    
    // Set up keyboard shortcuts
    setupKeyboardShortcuts();
    
    // Focus on email field by default
    focusDefaultField();
}

// Login/Register form toggle functions
function showRegister() {
    document.getElementById('login-form').classList.add('hidden');
    document.getElementById('register-form').classList.remove('hidden');
    document.getElementById('form-title').innerText = "Register";
    
    // Focus on name field when switching to register
    setTimeout(() => {
        const nameField = document.querySelector('#register-form input[name="name"]');
        if (nameField) nameField.focus();
    }, 100);
}

function showLogin() {
    document.getElementById('register-form').classList.add('hidden');
    document.getElementById('login-form').classList.remove('hidden');
    document.getElementById('form-title').innerText = "Login";
    
    // Focus on email field when switching to login
    setTimeout(() => {
        const emailField = document.querySelector('#login-form input[name="email"]');
        if (emailField) emailField.focus();
    }, 100);
}

// Form validation functions
function setupFormValidation() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', validateLoginForm);
    }
    
    if (registerForm) {
        registerForm.addEventListener('submit', validateRegisterForm);
    }
}

function validateLoginForm(e) {
    const email = e.target.querySelector('input[name="email"]').value;
    const password = e.target.querySelector('input[name="password"]').value;
    
    if (!email || !password) {
        e.preventDefault();
        return false;
    }
    
    if (!isValidEmail(email)) {
        e.preventDefault();
        return false;
    }
    
    return true;
}

function validateRegisterForm(e) {
    const name = e.target.querySelector('input[name="name"]').value;
    const email = e.target.querySelector('input[name="email"]').value;
    const password = e.target.querySelector('input[name="password"]').value;
    
    if (!name || !email || !password) {
        e.preventDefault();
        return false;
    }
    
    if (name.length < 2) {
        e.preventDefault();
        return false;
    }
    
    if (!isValidEmail(email)) {
        e.preventDefault();
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        return false;
    }
    
    return true;
}

// Email validation helper
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Keyboard shortcuts
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Alt + R to switch to register
        if (e.altKey && e.key === 'r') {
            e.preventDefault();
            showRegister();
        }
        
        // Alt + L to switch to login
        if (e.altKey && e.key === 'l') {
            e.preventDefault();
            showLogin();
        }
        
        // Escape to clear form
        if (e.key === 'Escape') {
            clearCurrentForm();
        }
    });
}

// Focus on the first input field
function focusDefaultField() {
    setTimeout(() => {
        const emailField = document.querySelector('#login-form input[name="email"]');
        if (emailField) emailField.focus();
    }, 100);
}

// Clear current visible form
function clearCurrentForm() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    if (!loginForm.classList.contains('hidden')) {
        loginForm.reset();
    } else if (!registerForm.classList.contains('hidden')) {
        registerForm.reset();
    }
}

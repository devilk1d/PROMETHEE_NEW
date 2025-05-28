// Login Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const form = document.querySelector('.auth-form');
    const submitBtn = document.querySelector('.btn-submit');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const formWrapper = document.querySelector('.auth-form-wrapper');
    
    // Password toggle functionality
    window.togglePassword = function() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('passwordToggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.className = 'bi bi-eye-slash';
        } else {
            passwordInput.type = 'password';
            toggleIcon.className = 'bi bi-eye';
        }
    };
    
    // Form validation
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function validateForm() {
        let isValid = true;
        
        // Email validation
        if (!emailInput.value.trim()) {
            showFieldError(emailInput, 'Email is required');
            isValid = false;
        } else if (!validateEmail(emailInput.value)) {
            showFieldError(emailInput, 'Please enter a valid email address');
            isValid = false;
        } else {
            clearFieldError(emailInput);
        }
        
        // Password validation
        if (!passwordInput.value.trim()) {
            showFieldError(passwordInput, 'Password is required');
            isValid = false;
        } else if (passwordInput.value.length < 6) {
            showFieldError(passwordInput, 'Password must be at least 6 characters');
            isValid = false;
        } else {
            clearFieldError(passwordInput);
        }
        
        return isValid;
    }
    
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        // Remove existing error message
        const existingError = field.parentElement.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Create new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = `<i class="bi bi-exclamation-circle"></i> ${message}`;
        field.parentElement.insertAdjacentElement('afterend', errorDiv);
    }
    
    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorMessage = field.parentElement.nextElementSibling;
        if (errorMessage && errorMessage.classList.contains('error-message')) {
            errorMessage.remove();
        }
    }
    
    // Real-time validation
    emailInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            if (!validateEmail(this.value)) {
                showFieldError(this, 'Please enter a valid email address');
            } else {
                clearFieldError(this);
            }
        }
    });
    
    emailInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid') && validateEmail(this.value)) {
            clearFieldError(this);
        }
    });
    
    passwordInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid') && this.value.length >= 6) {
            clearFieldError(this);
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        formWrapper.classList.add('loading');
        
        // Simulate form submission delay (remove in production)
        setTimeout(() => {
            // Submit the form
            this.submit();
        }, 1000);
    });
    
    // Input focus effects
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
    
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.modern-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Enter key to submit form when focused on inputs
        if (e.key === 'Enter' && (e.target === emailInput || e.target === passwordInput)) {
            form.dispatchEvent(new Event('submit'));
        }
        
        // Escape key to clear focused input
        if (e.key === 'Escape' && e.target.classList.contains('form-input')) {
            e.target.blur();
        }
    });
    
    // Enhanced accessibility
    function announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }
    
    // Page load animation
    setTimeout(() => {
        document.body.classList.add('loaded');
        formWrapper.style.opacity = '1';
        formWrapper.style.transform = 'translateY(0)';
    }, 100);
    
    // Initial setup
    formWrapper.style.opacity = '0';
    formWrapper.style.transform = 'translateY(20px)';
    formWrapper.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    
    // Form field animations on focus
    inputs.forEach((input, index) => {
        input.style.transform = 'translateY(10px)';
        input.style.opacity = '0';
        input.style.transition = `opacity 0.4s ease ${index * 0.1}s, transform 0.4s ease ${index * 0.1}s`;
        
        setTimeout(() => {
            input.style.opacity = '1';
            input.style.transform = 'translateY(0)';
        }, 200 + (index * 100));
    });
    
    // Button hover effect enhancement
    submitBtn.addEventListener('mouseenter', function() {
        if (!this.disabled) {
            this.style.transform = 'translateY(-3px)';
        }
    });
    
    submitBtn.addEventListener('mouseleave', function() {
        if (!this.disabled) {
            this.style.transform = 'translateY(-2px)';
        }
    });
    
    // Password strength indicator (optional enhancement)
    function checkPasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        return strength;
    }
    
    // Remember me persistence
    const rememberCheckbox = document.getElementById('remember_me');
    const savedEmail = localStorage.getItem('rememberedEmail');
    
    if (savedEmail && rememberCheckbox) {
        emailInput.value = savedEmail;
        rememberCheckbox.checked = true;
    }
    
    if (rememberCheckbox) {
        rememberCheckbox.addEventListener('change', function() {
            if (this.checked && emailInput.value) {
                localStorage.setItem('rememberedEmail', emailInput.value);
            } else {
                localStorage.removeItem('rememberedEmail');
            }
        });
    }
    
    // Prevent common security issues
    form.addEventListener('paste', function(e) {
        const target = e.target;
        if (target === passwordInput) {
            // Optional: warn about pasting passwords
            console.warn('Pasting into password field detected');
        }
    });
    
    // Connection status check
    function checkConnection() {
        if (!navigator.onLine) {
            showConnectionError();
        }
    }
    
    function showConnectionError() {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'modern-alert';
        errorDiv.style.background = 'rgba(239, 68, 68, 0.1)';
        errorDiv.style.color = 'var(--danger-500)';
        errorDiv.innerHTML = '<i class="bi bi-wifi-off"></i> No internet connection. Please check your network.';
        
        const formTitle = document.querySelector('.form-title-section');
        formTitle.insertAdjacentElement('afterend', errorDiv);
        
        setTimeout(() => {
            if (errorDiv.parentElement) {
                errorDiv.remove();
            }
        }, 5000);
    }
    
    window.addEventListener('online', () => {
        const connectionError = document.querySelector('.modern-alert');
        if (connectionError && connectionError.textContent.includes('internet connection')) {
            connectionError.remove();
        }
    });
    
    window.addEventListener('offline', showConnectionError);
});
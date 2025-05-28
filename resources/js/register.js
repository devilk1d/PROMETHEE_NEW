// Register Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const form = document.querySelector('.auth-form');
    const submitBtn = document.querySelector('.btn-submit');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const termsCheckbox = document.getElementById('terms');
    const formWrapper = document.querySelector('.auth-form-wrapper');
    
    // Password toggle functionality
    window.togglePassword = function(inputId) {
        const input = document.getElementById(inputId);
        const iconId = inputId === 'password' ? 'passwordToggleIcon' : 'confirmToggleIcon';
        const toggleIcon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            toggleIcon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            toggleIcon.className = 'bi bi-eye';
        }
    };
    
    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        let feedback = 'Password strength';
        
        if (password.length === 0) {
            return { strength: 0, text: 'Password strength', class: '' };
        }
        
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        switch (strength) {
            case 0:
            case 1:
                feedback = 'Very weak';
                return { strength: 1, text: feedback, class: 'weak' };
            case 2:
                feedback = 'Weak';
                return { strength: 2, text: feedback, class: 'fair' };
            case 3:
                feedback = 'Good';
                return { strength: 3, text: feedback, class: 'good' };
            case 4:
            case 5:
                feedback = 'Strong';
                return { strength: 4, text: feedback, class: 'strong' };
            default:
                return { strength: 0, text: 'Password strength', class: '' };
        }
    }
    
    // Update password strength indicator
    function updatePasswordStrength() {
        const password = passwordInput.value;
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        const result = checkPasswordStrength(password);
        
        // Remove all strength classes
        strengthBar.className = 'strength-bar';
        strengthText.className = 'strength-text';
        
        if (result.class) {
            strengthBar.classList.add(result.class);
            strengthText.classList.add(result.class);
        }
        
        strengthText.textContent = result.text;
    }
    
    // Password match checker
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const matchIndicator = document.getElementById('passwordMatch');
        
        if (confirmPassword.length === 0) {
            matchIndicator.className = 'password-match';
            matchIndicator.textContent = '';
            return;
        }
        
        matchIndicator.classList.add('show');
        
        if (password === confirmPassword) {
            matchIndicator.className = 'password-match show match';
            matchIndicator.innerHTML = '<i class="bi bi-check-circle-fill"></i> Passwords match';
        } else {
            matchIndicator.className = 'password-match show no-match';
            matchIndicator.innerHTML = '<i class="bi bi-x-circle-fill"></i> Passwords do not match';
        }
    }
    
    // Form validation
    function validateName(name) {
        return name.trim().length >= 2;
    }
    
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function validatePassword(password) {
        return password.length >= 8 && 
               /[a-z]/.test(password) && 
               /[A-Z]/.test(password) && 
               /[0-9]/.test(password);
    }
    
    function validateForm() {
        let isValid = true;
        
        // Name validation
        if (!validateName(nameInput.value)) {
            showFieldError(nameInput, 'Name must be at least 2 characters long');
            isValid = false;
        } else {
            clearFieldError(nameInput);
            nameInput.classList.add('is-valid');
        }
        
        // Email validation
        if (!emailInput.value.trim()) {
            showFieldError(emailInput, 'Email is required');
            isValid = false;
        } else if (!validateEmail(emailInput.value)) {
            showFieldError(emailInput, 'Please enter a valid email address');
            isValid = false;
        } else {
            clearFieldError(emailInput);
            emailInput.classList.add('is-valid');
        }
        
        // Password validation
        if (!passwordInput.value.trim()) {
            showFieldError(passwordInput, 'Password is required');
            isValid = false;
        } else if (!validatePassword(passwordInput.value)) {
            showFieldError(passwordInput, 'Password must be at least 8 characters with uppercase, lowercase, and numbers');
            isValid = false;
        } else {
            clearFieldError(passwordInput);
            passwordInput.classList.add('is-valid');
        }
        
        // Confirm password validation
        if (passwordInput.value !== confirmPasswordInput.value) {
            showFieldError(confirmPasswordInput, 'Passwords do not match');
            isValid = false;
        } else if (confirmPasswordInput.value.length > 0) {
            clearFieldError(confirmPasswordInput);
            confirmPasswordInput.classList.add('is-valid');
        }
        
        // Terms validation
        if (!termsCheckbox.checked) {
            showFieldError(termsCheckbox, 'You must agree to the terms and conditions');
            isValid = false;
        } else {
            clearFieldError(termsCheckbox);
        }
        
        return isValid;
    }
    
    function showFieldError(field, message) {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        
        // Remove existing error message
        const existingError = field.closest('.form-group').querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Create new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = `<i class="bi bi-exclamation-circle"></i> ${message}`;
        
        // Insert error message
        if (field === termsCheckbox) {
            field.closest('.form-group').appendChild(errorDiv);
        } else {
            field.closest('.form-group').appendChild(errorDiv);
        }
    }
    
    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorMessage = field.closest('.form-group').querySelector('.error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }
    
    // Real-time validation
    nameInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid') && validateName(this.value)) {
            clearFieldError(this);
            this.classList.add('is-valid');
        }
    });
    
    emailInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            if (!validateEmail(this.value)) {
                showFieldError(this, 'Please enter a valid email address');
            } else {
                clearFieldError(this);
                this.classList.add('is-valid');
            }
        }
    });
    
    emailInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid') && validateEmail(this.value)) {
            clearFieldError(this);
            this.classList.add('is-valid');
        }
    });
    
    passwordInput.addEventListener('input', function() {
        updatePasswordStrength();
        checkPasswordMatch();
        
        if (this.classList.contains('is-invalid') && validatePassword(this.value)) {
            clearFieldError(this);
            this.classList.add('is-valid');
        }
    });
    
    confirmPasswordInput.addEventListener('input', function() {
        checkPasswordMatch();
        
        if (this.classList.contains('is-invalid') && 
            this.value === passwordInput.value && 
            this.value.length > 0) {
            clearFieldError(this);
            this.classList.add('is-valid');
        }
    });
    
    termsCheckbox.addEventListener('change', function() {
        if (this.classList.contains('is-invalid') && this.checked) {
            clearFieldError(this);
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            // Focus on first invalid field
            const firstInvalid = this.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
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
        }, 1500);
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
    
    // Email availability check (simulated)
    let emailCheckTimeout;
    emailInput.addEventListener('input', function() {
        const email = this.value;
        
        clearTimeout(emailCheckTimeout);
        
        if (validateEmail(email)) {
            emailCheckTimeout = setTimeout(() => {
                // Simulate email availability check
                checkEmailAvailability(email);
            }, 1000);
        }
    });
    
    function checkEmailAvailability(email) {
        // This would typically make an API call
        // For demo purposes, we'll simulate it
        const unavailableEmails = ['test@example.com', 'admin@test.com'];
        
        if (unavailableEmails.includes(email.toLowerCase())) {
            showFieldError(emailInput, 'This email is already registered');
        }
    }
    
    // Enhanced password requirements display
    function createPasswordRequirements() {
        const requirements = document.createElement('div');
        requirements.className = 'password-requirements';
        requirements.innerHTML = `
            <div class="requirement" data-rule="length">
                <i class="bi bi-circle"></i>
                <span>At least 8 characters</span>
            </div>
            <div class="requirement" data-rule="lowercase">
                <i class="bi bi-circle"></i>
                <span>One lowercase letter</span>
            </div>
            <div class="requirement" data-rule="uppercase">
                <i class="bi bi-circle"></i>
                <span>One uppercase letter</span>
            </div>
            <div class="requirement" data-rule="number">
                <i class="bi bi-circle"></i>
                <span>One number</span>
            </div>
        `;
        
        // Add CSS for requirements
        const style = document.createElement('style');
        style.textContent = `
            .password-requirements {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.5rem;
                margin-top: 0.5rem;
                padding: 0.75rem;
                background: var(--gray-50);
                border-radius: 8px;
                font-size: 0.75rem;
            }
            
            .requirement {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: var(--gray-500);
                transition: var(--transition);
            }
            
            .requirement.met {
                color: var(--success-500);
            }
            
            .requirement.met i {
                color: var(--success-500);
            }
            
            .requirement i {
                font-size: 0.75rem;
            }
        `;
        document.head.appendChild(style);
        
        return requirements;
    }
    
    // Add password requirements
    const passwordGroup = passwordInput.closest('.form-group');
    const requirements = createPasswordRequirements();
    passwordGroup.appendChild(requirements);
    
    // Update requirements on password input
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const rules = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password)
        };
        
        Object.keys(rules).forEach(rule => {
            const requirement = requirements.querySelector(`[data-rule="${rule}"]`);
            if (rules[rule]) {
                requirement.classList.add('met');
                requirement.querySelector('i').className = 'bi bi-check-circle-fill';
            } else {
                requirement.classList.remove('met');
                requirement.querySelector('i').className = 'bi bi-circle';
            }
        });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Enter key to submit form when focused on inputs
        if (e.key === 'Enter' && e.target.classList.contains('form-input')) {
            if (e.target === confirmPasswordInput || e.target === passwordInput) {
                form.dispatchEvent(new Event('submit'));
            }
        }
        
        // Escape key to clear focused input
        if (e.key === 'Escape' && e.target.classList.contains('form-input')) {
            e.target.blur();
        }
    });
    
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
    
    // Form progress indicator
    function updateFormProgress() {
        const fields = [nameInput, emailInput, passwordInput, confirmPasswordInput];
        const validFields = fields.filter(field => field.classList.contains('is-valid'));
        const termsValid = termsCheckbox.checked;
        
        const progress = ((validFields.length + (termsValid ? 1 : 0)) / 5) * 100;
        
        // You could add a progress bar here
        if (progress === 100) {
            submitBtn.classList.add('ready');
        } else {
            submitBtn.classList.remove('ready');
        }
    }
    
    // Add event listeners for progress tracking
    [nameInput, emailInput, passwordInput, confirmPasswordInput].forEach(input => {
        input.addEventListener('input', updateFormProgress);
        input.addEventListener('blur', updateFormProgress);
    });
    
    termsCheckbox.addEventListener('change', updateFormProgress);
    
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
        errorDiv.style.padding = '1rem';
        errorDiv.style.borderRadius = '12px';
        errorDiv.style.marginBottom = '1rem';
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
    
    // Initial password strength update
    updatePasswordStrength();
});
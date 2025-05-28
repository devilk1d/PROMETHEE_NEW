<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PROMETHEE SPK</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    @vite(['resources/css/auth/register.css'])
</head>
<body>
    <!-- Background Elements -->
    <div class="auth-background">
        <div class="bg-circle bg-circle-1"></div>
        <div class="bg-circle bg-circle-2"></div>
        <div class="bg-circle bg-circle-3"></div>
        <div class="bg-gradient"></div>
    </div>

    <!-- Main Container -->
    <div class="auth-container">
        <!-- Left Side - Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <!-- Back to Home -->
                <div class="form-header">
                    <a href="{{ route('welcome') }}" class="back-link">
                        <i class="bi bi-arrow-left"></i>
                        Back to Home
                    </a>
                </div>

                <!-- Form Title -->
                <div class="form-title-section">
                    <h2 class="form-title">Create your account</h2>
                    <p class="form-subtitle">Start making smarter decisions with PROMETHEE analysis</p>
                </div>

                <!-- Register Form -->
                <form method="POST" action="{{ route('register') }}" class="auth-form">
                    @csrf

                    <!-- Name Field -->
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="bi bi-person"></i>
                            Full Name
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   class="form-input @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Enter your full name"
                                   required 
                                   autofocus>
                            <div class="input-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        @error('name')
                            <div class="error-message">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i>
                            Email Address
                        </label>
                        <div class="input-wrapper">
                            <input type="email" 
                                   class="form-input @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Enter your email"
                                   required>
                            <div class="input-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                        </div>
                        @error('email')
                            <div class="error-message">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i>
                            Password
                        </label>
                        <div class="input-wrapper">
                            <input type="password" 
                                   class="form-input @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Create a strong password"
                                   required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="bi bi-eye" id="passwordToggleIcon"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-meter">
                                <div class="strength-bar" id="strengthBar"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Password strength</span>
                        </div>
                        @error('password')
                            <div class="error-message">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">
                            <i class="bi bi-key"></i>
                            Confirm Password
                        </label>
                        <div class="input-wrapper">
                            <input type="password" 
                                   class="form-input @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Confirm your password"
                                   required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class="bi bi-eye" id="confirmToggleIcon"></i>
                            </button>
                        </div>
                        <div class="password-match" id="passwordMatch"></div>
                        @error('password_confirmation')
                            <div class="error-message">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Terms Agreement -->
                    <div class="form-group">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" 
                                   class="form-checkbox" 
                                   id="terms" 
                                   name="terms"
                                   required>
                            <label for="terms" class="checkbox-label">
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">
                                    I agree to the 
                                    <a href="#" class="terms-link">Terms of Service</a> 
                                    and 
                                    <a href="#" class="terms-link">Privacy Policy</a>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-person-plus"></i>
                        Create Account
                        <div class="btn-loading">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </button>
                </form>

                <!-- Login Link -->
                <div class="form-footer">
                    <p class="footer-text">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="login-link">
                            Sign in here
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side - Branding -->
        <div class="auth-branding">
            <div class="branding-content">
                <div class="brand-logo">
                    <div class="brand-icon">
                        <i class="bi bi-bar-chart-line-fill"></i>
                    </div>
                    <h1 class="brand-title">PROMETHEE SPK</h1>
                </div>
                
                <div class="branding-text">
                    <h2 class="branding-title">
                        Join thousands of 
                        <span class="text-gradient">smart decision makers</span>
                    </h2>
                    <p class="branding-description">
                        Start your journey with advanced multi-criteria decision analysis. 
                        Make data-driven decisions with confidence using our powerful PROMETHEE methodology.
                    </p>
                </div>

                <div class="branding-benefits">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Free to Start</h4>
                            <p>Begin with unlimited basic analysis</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Advanced Analytics</h4>
                            <p>Comprehensive PROMETHEE calculations</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Secure & Private</h4>
                            <p>Your data is protected and isolated</p>
                        </div>
                    </div>
                </div>

                <div class="branding-stats">
                    <div class="stat">
                        <span class="stat-number">10K+</span>
                        <span class="stat-label">Decisions Made</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">99%</span>
                        <span class="stat-label">Satisfaction</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Available</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    @vite(['resources/js/register.js'])
</body>
</html>
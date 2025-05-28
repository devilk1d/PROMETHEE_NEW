<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PROMETHEE SPK</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    @vite(['resources/css/auth/login.css'])
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
        <!-- Left Side - Branding -->
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
                        Welcome back to 
                        <span class="text-gradient">smart decisions</span>
                    </h2>
                    <p class="branding-description">
                        Access your decision support system and continue making data-driven choices 
                        with advanced PROMETHEE analysis.
                    </p>
                </div>

                <div class="branding-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <span>Secure Analysis</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <span>Advanced Analytics</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <span>Fast Results</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
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
                    <h2 class="form-title">Sign in to your account</h2>
                    <p class="form-subtitle">Enter your credentials to access your dashboard</p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success modern-alert">
                        <i class="bi bi-check-circle"></i>
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="auth-form">
                    @csrf

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
                                   required 
                                   autofocus>
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
                                   placeholder="Enter your password"
                                   required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye" id="passwordToggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="error-message">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="form-options">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" 
                                   class="form-checkbox" 
                                   id="remember_me" 
                                   name="remember">
                            <label for="remember_me" class="checkbox-label">
                                <span class="checkbox-custom"></span>
                                Remember me
                            </label>
                        </div>
                        
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Sign In
                        <div class="btn-loading">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </button>
                </form>

                <!-- Register Link -->
                <div class="form-footer">
                    <p class="footer-text">
                        Don't have an account? 
                        <a href="{{ route('register') }}" class="register-link">
                            Create one here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    @vite(['resources/js/login.js'])
</body>
</html>
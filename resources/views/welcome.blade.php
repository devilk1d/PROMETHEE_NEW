<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PROMETHEE SPK - Decision Support System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    @vite(['resources/css/auth/welcome.css'])
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <div class="nav-content">
                <div class="nav-brand">
                    <div class="brand-icon">
                        <i class="bi bi-bar-chart-line-fill"></i>
                    </div>
                    <span class="brand-text">PROMETHEE SPK</span>
                </div>
                
                @if (Route::has('login'))
                    <div class="nav-actions">
                        @auth
                            <a href="{{ route('home') }}" class="btn-nav btn-primary">
                                <i class="bi bi-speedometer2"></i>
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-nav btn-outline">
                                <i class="bi bi-box-arrow-in-right"></i>
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-nav btn-primary">
                                    <i class="bi bi-person-plus"></i>
                                    Get Started
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-background">
            <div class="hero-circle hero-circle-1"></div>
            <div class="hero-circle hero-circle-2"></div>
            <div class="hero-circle hero-circle-3"></div>
        </div>
        
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="bi bi-lightning-fill"></i>
                    Advanced Decision Support System
                </div>
                
                <h1 class="hero-title">
                    Make Better Decisions with
                    <span class="hero-highlight">PROMETHEE</span>
                    Analysis
                </h1>
                
                <p class="hero-description">
                    Empower your decision-making process with sophisticated multi-criteria analysis. 
                    Our platform combines advanced PROMETHEE methodology with intuitive design 
                    to help you evaluate complex scenarios and make informed choices.
                </p>

                @guest
                    <div class="hero-actions">
                        <a href="{{ route('register') }}" class="btn-hero btn-primary">
                            <i class="bi bi-rocket-takeoff"></i>
                            Start Your Analysis
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ route('login') }}" class="btn-hero btn-outline">
                            <i class="bi bi-play-circle"></i>
                            Login Now!
                        </a>
                    </div>
                @else
                    <div class="hero-actions">
                        <a href="{{ route('home') }}" class="btn-hero btn-primary">
                            <i class="bi bi-speedometer2"></i>
                            Go to Dashboard
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @endguest

                <!-- Stats -->
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number">99%</span>
                            <span class="stat-label">Accuracy</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number">Fast</span>
                            <span class="stat-label">Analysis</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number">Secure</span>
                            <span class="stat-label">Data</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header">
                <div class="section-badge">
                    <i class="bi bi-stars"></i>
                    Features
                </div>
                <h2 class="section-title">
                    Everything you need for
                    <span class="text-gradient">smart decisions</span>
                </h2>
                <p class="section-description">
                    Powerful tools designed to simplify complex decision-making processes
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-card feature-card-primary">
                    <div class="feature-icon">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <h3 class="feature-title">Multi-Criteria Analysis</h3>
                    <p class="feature-description">
                        Define and manage multiple evaluation criteria with custom weights 
                        and preference functions for comprehensive analysis.
                    </p>
                    <div class="feature-highlight">
                        <i class="bi bi-check-circle-fill"></i>
                        6 Preference Functions
                    </div>
                </div>

                <div class="feature-card feature-card-success">
                    <div class="feature-icon">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </div>
                    <h3 class="feature-title">Alternative Comparison</h3>
                    <p class="feature-description">
                        Compare unlimited alternatives against your defined criteria 
                        using the robust PROMETHEE methodology.
                    </p>
                    <div class="feature-highlight">
                        <i class="bi bi-check-circle-fill"></i>
                        Unlimited Alternatives
                    </div>
                </div>

                <div class="feature-card feature-card-info">
                    <div class="feature-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h3 class="feature-title">Visual Insights</h3>
                    <p class="feature-description">
                        Get clear visual insights with interactive charts, rankings, 
                        and detailed reports to support your decisions.
                    </p>
                    <div class="feature-highlight">
                        <i class="bi bi-check-circle-fill"></i>
                        Interactive Charts
                    </div>
                </div>

                <div class="feature-card feature-card-warning">
                    <div class="feature-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h3 class="feature-title">Secure & Private</h3>
                    <p class="feature-description">
                        Your data is protected with enterprise-grade security. 
                        Each user has isolated access to their own cases.
                    </p>
                    <div class="feature-highlight">
                        <i class="bi bi-check-circle-fill"></i>
                        Data Isolation
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="process-section">
        <div class="container">
            <div class="section-header">
                <div class="section-badge">
                    <i class="bi bi-gear"></i>
                    Process
                </div>
                <h2 class="section-title">
                    How it <span class="text-gradient">works</span>
                </h2>
                <p class="section-description">
                    Simple steps to get powerful insights
                </p>
            </div>

            <div class="process-steps">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4 class="step-title">Create Case</h4>
                        <p class="step-description">Start by creating a new decision case and defining your scenario.</p>
                    </div>
                </div>

                <div class="process-connector"></div>

                <div class="process-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4 class="step-title">Define Criteria</h4>
                        <p class="step-description">Set up evaluation criteria with weights and preference functions.</p>
                    </div>
                </div>

                <div class="process-connector"></div>

                <div class="process-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4 class="step-title">Add Alternatives</h4>
                        <p class="step-description">Input your decision alternatives and their values for each criterion.</p>
                    </div>
                </div>

                <div class="process-connector"></div>

                <div class="process-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4 class="step-title">Get Results</h4>
                        <p class="step-description">Run PROMETHEE analysis and view detailed rankings and insights.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">
                    Ready to make better decisions?
                </h2>
                <p class="cta-description">
                    Join thousands of professionals using PROMETHEE SPK for smarter decision-making
                </p>
                
                @guest
                    <div class="cta-actions">
                        <a href="{{ route('register') }}" class="btn-cta btn-primary">
                            <i class="bi bi-rocket-takeoff"></i>
                            Get Started Free
                        </a>
                        <a href="{{ route('login') }}" class="btn-cta btn-outline">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Sign In
                        </a>
                    </div>
                @else
                    <div class="cta-actions">
                        <a href="{{ route('home') }}" class="btn-cta btn-primary">
                            <i class="bi bi-speedometer2"></i>
                            Go to Dashboard
                        </a>
                    </div>
                @endguest
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="brand-icon">
                        <i class="bi bi-bar-chart-line-fill"></i>
                    </div>
                    <span class="brand-text">PROMETHEE SPK</span>
                </div>
                <p class="footer-text">
                    Advanced Decision Support System for Multi-Criteria Analysis
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    @vite(['resources/js/welcome.js'])
</body>
</html>
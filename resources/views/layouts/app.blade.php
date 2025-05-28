<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROMETHEE SPK - @yield('title')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite CSS -->
    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a class="sidebar-brand" href="{{ route('home') }}">
                    <div class="sidebar-brand-icon">
                        <i class="bi bi-bar-chart-line"></i>
                    </div>
                    <span class="sidebar-brand-text">PROMETHEE</span>
                </a>
                <button class="sidebar-toggle d-none d-lg-block" id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <div class="sidebar-nav">
                <div class="nav-group-title">Main Menu</div>
                <ul class="list-unstyled">
                    <li class="sidebar-item {{ request()->routeIs('home') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('home') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span class="sidebar-link-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="sidebar-item {{ request()->routeIs('cases.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('cases.index') }}">
                            <i class="bi bi-folder"></i>
                            <span class="sidebar-link-text">Cases</span>
                        </a>
                    </li>
                    
                    @if(isset($case) && $case->id)
                    <div class="sidebar-divider"></div>
                    <div class="current-case-indicator">
                        <span class="current-case-label">Current Case</span>
                        <span class="current-case-name">{{ $case->name }}</span>
                    </div>
                    
                    <div class="nav-group-title">Analysis Tools</div>
                    <li class="sidebar-item {{ request()->routeIs('criteria.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('criteria.index', ['case' => $case->id]) }}">
                            <i class="bi bi-list-check"></i>
                            <span class="sidebar-link-text">Criteria</span>
                        </a>
                    </li>
                    
                    <li class="sidebar-item {{ request()->routeIs('alternatives.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('alternatives.index', ['case' => $case->id]) }}">
                            <i class="bi bi-grid-3x3-gap"></i>
                            <span class="sidebar-link-text">Alternatives</span>
                        </a>
                    </li>
                    
                    <li class="sidebar-item {{ request()->routeIs('decisions.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('decisions.index', ['case' => $case->id]) }}">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                            <span class="sidebar-link-text">Results</span>
                        </a>
                    </li>
                    
                    <div class="sidebar-divider"></div>
                    
                    <div class="sidebar-cta">
                        <p class="text-white-50 mb-2" style="font-size: 0.75rem;">Ready to analyze?</p>
                        <a class="sidebar-cta-btn" href="{{ route('decisions.calculate', ['case' => $case->id]) }}">
                            <i class="bi bi-calculator"></i> Run Analysis
                        </a>
                    </div>
                    @endif
                </ul>
            </div>

            <!-- User Menu -->
            <div class="sidebar-user">
                <div class="dropdown">
                    <div class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="user-info">
                            <div class="user-name">{{ Auth::user()->name }}</div>
                            <div class="user-email">{{ Auth::user()->email }}</div>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end w-100">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person"></i> Profile
                            </a>
                        </li>
                    
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay"></div>

        <!-- Main Content -->
        <main class="main" id="main">
            <div class="container-fluid p-0">
                @yield('content')
            </div>
        </main>
        
        <!-- Mobile menu button -->
        <button class="mobile-toggle d-lg-none" id="mobileToggle">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Vite JS -->
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
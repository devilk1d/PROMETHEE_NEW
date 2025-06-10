@extends('layouts.app')

@section('title', 'Decisions')

@section('scripts')
    @vite(['resources/js/decisions/index.js'])

@section('styles')
    @vite(['resources/css/decisions/index.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">Decision Analysis</h1>
        <p class="main-subtitle">PROMETHEE analysis results and calculations</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('decisions.calculate') }}" class="btn-modern btn-primary-modern">
            <i class="bi bi-calculator"></i>
            New Analysis
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show modern-alert" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show modern-alert" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="content-card">
    @if($decisions->count() > 0)
        <div class="decisions-grid">
            @foreach($decisions as $decision)
            <div class="decision-card">
                <div class="decision-header">
                    <div class="decision-title-row">
                        <h4 class="decision-title">{{ $decision->name }}</h4>
                        <div class="decision-status">
                            <span class="status-badge status-completed">
                                <i class="bi bi-check-circle"></i>
                                Completed
                            </span>
                        </div>
                    </div>
                    <p class="decision-description">{{ Str::limit($decision->description, 100) ?: 'No description provided.' }}</p>
                </div>
                
                <div class="decision-stats">
                    <div class="stat-item">
                        <div class="stat-icon stat-icon-primary">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Best Alternative</span>
                            <span class="stat-value">
                                @if(isset($decision->ranking) && !empty($decision->ranking))
                                    {{ array_values($decision->ranking)[0]['name'] ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon stat-icon-info">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Alternatives</span>
                            <span class="stat-value">{{ count($decision->ranking ?? []) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="decision-meta">
                    <div class="meta-item">
                        <i class="bi bi-calendar3"></i>
                        <span>{{ $decision->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-clock"></i>
                        <span>{{ $decision->created_at->format('H:i') }}</span>
                    </div>
                </div>
                
                <div class="decision-footer">
                    <div class="decision-actions">
                        <a href="{{ route('decisions.result', ['decision' => $decision->id]) }}" class="btn-modern btn-primary-modern btn-sm">
                            <i class="bi bi-eye"></i>
                            View Results
                        </a>
                        <div class="dropdown">
                            <button class="btn-icon btn-icon-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('decisions.result', ['decision' => $decision->id]) }}">
                                    <i class="bi bi-eye"></i> View Details
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('decisions.destroy', ['decision' => $decision->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <h4 class="empty-title">No analysis results yet</h4>
            <p class="empty-description">Start your first PROMETHEE analysis to see decision results and rankings here.</p>
            <a href="{{ route('decisions.calculate') }}" class="btn-modern btn-primary-modern">
                <i class="bi bi-calculator"></i>
                Calculate PROMETHEE
            </a>
        </div>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
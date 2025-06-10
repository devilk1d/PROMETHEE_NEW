@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
    @vite(['resources\css\dashboard\style.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">Dashboard</h1>
        <p class="main-subtitle">Welcome back! Here's what's happening with your decision analysis.</p>
    </div>
    <div class="header-actions">
        @if(Auth::user()->isAdmin())
            <a href="{{ route('criteria.create') }}" class="btn-modern btn-secondary-modern">
                <i class="bi bi-list-check"></i>
                Add Criteria
            </a>
        @endif
        <a href="{{ route('alternatives.create') }}" class="btn-modern btn-primary-modern">
            <i class="bi bi-plus-circle"></i>
            Add Alternative
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="row g-4 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="stat-card blue">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $totalCriteria }}</div>
                    <div class="stat-label">Total Criteria</div>
                    <div class="stat-change">
                        <i class="bi bi-list-check"></i>
                        Evaluation Parameters
                    </div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-list-check"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="stat-card emerald">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $totalAlternatives }}</div>
                    <div class="stat-label">Alternatives</div>
                    <div class="stat-change">
                        <i class="bi bi-grid-3x3-gap"></i>
                        Decision Options
                    </div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-grid-3x3-gap"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="stat-card purple">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $totalDecisions }}</div>
                    <div class="stat-label">Decisions</div>
                    <div class="stat-change">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        Analysis Conducted
                    </div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Card -->
<div class="content-card">
    <div class="card-header-modern">
        <div>
            <h3 class="card-title">Recent Activity</h3>
            <p class="card-subtitle">Latest decision analysis results</p>
        </div>
        <a href="{{ route('decisions.index') }}" class="btn-modern btn-ghost">
            <i class="bi bi-arrow-right"></i>
            View All
        </a>
    </div>
    
    @if($recentDecisions->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <h4 class="empty-title">No analysis results yet</h4>
            <p class="empty-description">Start by creating criteria and alternatives to see analysis results here.</p>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('criteria.create') }}" class="btn-modern btn-secondary-modern me-2">
                    <i class="bi bi-list-check"></i>
                    Add Criteria
                </a>
            @endif
            <a href="{{ route('alternatives.create') }}" class="btn-modern btn-primary-modern">
                <i class="bi bi-plus-circle"></i>
                Add Alternative
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="border: none; background: transparent; color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Type</th>
                        <th style="border: none; background: transparent; color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Name</th>
                        <th style="border: none; background: transparent; color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Date</th>
                        <th style="border: none; background: transparent;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentDecisions as $decision)
                    <tr style="border: none;">
                        <td style="border: none; padding: 1rem 0;">
                            <span class="badge" style="background: var(--blue-500); color: white; padding: 0.375rem 0.75rem; border-radius: 8px; font-weight: 500;">Decision</span>
                        </td>
                        <td style="border: none; padding: 1rem 0; font-weight: 500; color: var(--gray-900);">{{ $decision->name }}</td>
                        <td style="border: none; padding: 1rem 0; color: var(--gray-500); font-size: 0.875rem;">{{ $decision->created_at->diffForHumans() }}</td>
                        <td style="border: none; padding: 1rem 0; text-align: right;">
                            <a href="{{ route('decisions.result', ['decision' => $decision->id]) }}" class="btn-modern btn-ghost" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Quick Actions Grid -->
<div class="content-card">
    <div class="card-header-modern">
        <div>
            <h3 class="card-title">Quick Actions</h3>
            <p class="card-subtitle">Manage your decision analysis components</p>
        </div>
    </div>
    
    <div class="row g-4">
        @if(Auth::user()->isAdmin())
        <div class="col-lg-4 col-md-6">
            <div class="action-card">
                <div class="action-icon action-icon-primary">
                    <i class="bi bi-list-check"></i>
                </div>
                <div class="action-content">
                    <h4>Manage Criteria</h4>
                    <p>Define evaluation criteria with weights and preferences</p>
                </div>
                <div class="action-footer">
                    <a href="{{ route('criteria.index') }}" class="btn-modern btn-primary-modern btn-sm">
                        <i class="bi bi-arrow-right"></i> Go
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="col-lg-4 col-md-6">
            <div class="action-card">
                <div class="action-icon action-icon-info">
                    <i class="bi bi-list-check"></i>
                </div>
                <div class="action-content">
                    <h4>View Criteria</h4>
                    <p>See available evaluation criteria</p>
                </div>
                <div class="action-footer">
                    <a href="{{ route('criteria.index') }}" class="btn-modern btn-info-modern btn-sm">
                        <i class="bi bi-eye"></i> View
                    </a>
                </div>
            </div>
        </div>
        @endif
        
        <div class="col-lg-4 col-md-6">
            <div class="action-card">
                <div class="action-icon action-icon-success">
                    <i class="bi bi-grid-3x3-gap"></i>
                </div>
                <div class="action-content">
                    <h4>Manage Alternatives</h4>
                    <p>Create and edit decision alternatives</p>
                </div>
                <div class="action-footer">
                    <a href="{{ route('alternatives.index') }}" class="btn-modern btn-success-modern btn-sm">
                        <i class="bi bi-arrow-right"></i> Go
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="action-card">
                <div class="action-icon action-icon-warning">
                    <i class="bi bi-calculator"></i>
                </div>
                <div class="action-content">
                    <h4>Run Analysis</h4>
                    <p>Execute PROMETHEE decision analysis</p>
                </div>
                <div class="action-footer">
                    <a href="{{ route('decisions.calculate') }}" class="btn-modern btn-warning-modern btn-sm">
                        <i class="bi bi-play"></i> Start
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
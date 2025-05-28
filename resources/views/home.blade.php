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
        <a href="{{ route('cases.create') }}" class="btn-modern btn-primary-modern">
            <i class="bi bi-plus-circle"></i>
            Add New Case
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card blue">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $totalCases }}</div>
                    <div class="stat-label">Total Cases</div>
                    <div class="stat-change">
                        <i class="bi bi-folder"></i>
                        Decision Support Systems
                    </div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-folder"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="stat-card emerald">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $totalAlternatives }}</div>
                    <div class="stat-label">Alternatives</div>
                    <div class="stat-change">
                        <i class="bi bi-grid-3x3-gap"></i>
                        Across all cases
                    </div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-grid-3x3-gap"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="stat-card purple">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $totalCriteria ?? 'N/A' }}</div>
                    <div class="stat-label">Criteria</div>
                    <div class="stat-change">
                        <i class="bi bi-list-check"></i>
                        Analysis parameters
                    </div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-list-check"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="stat-card amber">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $totalDecisions }}</div>
                    <div class="stat-label">Decisions</div>
                    <div class="stat-change">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        Analysis conducted
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
            <p class="card-subtitle">Latest decision analysis and case updates</p>
        </div>
        <a href="{{ route('cases.index') }}" class="btn-modern btn-ghost">
            <i class="bi bi-arrow-right"></i>
            View All
        </a>
    </div>
    
    @if($recentDecisions->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <h4 class="empty-title">No recent activity yet</h4>
            <p class="empty-description">Start by creating your first case to see analysis results here.</p>
            <a href="{{ route('cases.create') }}" class="btn-modern btn-primary-modern">
                <i class="bi bi-plus-circle"></i>
                Create First Case
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="border: none; background: transparent; color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Type</th>
                        <th style="border: none; background: transparent; color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Name</th>
                        <th style="border: none; background: transparent; color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">Case</th>
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
                        <td style="border: none; padding: 1rem 0; color: var(--gray-600);">{{ $decision->case->name }}</td>
                        <td style="border: none; padding: 1rem 0; color: var(--gray-500); font-size: 0.875rem;">{{ $decision->created_at->diffForHumans() }}</td>
                        <td style="border: none; padding: 1rem 0; text-align: right;">
                            <a href="{{ route('decisions.result', ['case' => $decision->case_id, 'decision' => $decision->id]) }}" class="btn-modern btn-ghost" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
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

<!-- Cases Grid -->
<div class="content-card">
    <div class="card-header-modern">
        <div>
            <h3 class="card-title">Your Cases</h3>
            <p class="card-subtitle">Manage your decision analysis cases</p>
        </div>
        <a href="{{ route('cases.create') }}" class="btn-modern btn-primary-modern">
            <i class="bi bi-plus-circle"></i>
            New Case
        </a>
    </div>
    
    @if($cases->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-folder"></i>
            </div>
            <h4 class="empty-title">No cases found yet</h4>
            <p class="empty-description">Create your first case to start using the PROMETHEE decision analysis.</p>
            <a href="{{ route('cases.create') }}" class="btn-modern btn-primary-modern">
                <i class="bi bi-plus-circle"></i>
                Create First Case
            </a>
        </div>
    @else
        <div class="cases-grid">
            @foreach($cases as $case)
            <div class="case-card">
                <div class="case-header">
                    <h4 class="case-title">{{ $case->name }}</h4>
                    <p class="case-description">{{ Str::limit($case->description, 100) ?: 'No description provided.' }}</p>
                </div>
                <div class="case-stats">
                    <div class="case-stat">
                        <i class="bi bi-list-check"></i>
                        {{ $case->criteria_count ?? 0 }} Criteria
                    </div>
                    <div class="case-stat">
                        <i class="bi bi-grid-3x3-gap"></i>
                        {{ $case->alternatives_count ?? 0 }} Alternatives
                    </div>
                    <div class="case-stat">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        {{ $case->decisions_count ?? 0 }} Decisions
                    </div>
                </div>
                <div class="case-footer">
                    <span class="case-date">
                        @if($case->updated_at)
                            Updated {{ $case->updated_at->diffForHumans() }}
                        @else
                            Recently created
                        @endif
                    </span>
                    <a href="{{ route('cases.show', $case->id) }}" class="btn-modern btn-ghost" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                        <i class="bi bi-arrow-right"></i>
                        Open
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
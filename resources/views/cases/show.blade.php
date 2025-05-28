@extends('layouts.app')

@section('title', $case->name)

@section('styles')
    @vite(['resources/css/cases/show.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div class="header-main">
        <div class="title-section">
            <h1 class="main-title">{{ $case->name }}</h1>
            <p class="main-subtitle">
                {{ $case->description ?: 'Comprehensive analysis for selecting the best product among multiple alternatives based on various criteria including price, quality, features, warranty, distance, and reputation.' }}
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('cases.edit', $case->id) }}" class="btn-modern btn-secondary-modern">
                <i class="bi bi-pencil"></i> Edit Case
            </a>
            <a href="{{ route('decisions.calculate', ['case' => $case->id]) }}" class="btn-modern btn-primary-modern">
                <i class="bi bi-calculator"></i> New Analysis
            </a>
        </div>
    </div>
    
    <div class="case-meta">
        <span class="meta-item">
            <i class="bi bi-calendar3"></i>
            Created {{ $case->created_at->format('M d, Y') }}
        </span>
        <span class="meta-item">
            <i class="bi bi-clock-history"></i>
            Updated {{ $case->updated_at->diffForHumans() }}
        </span>
    </div>
</div>

<!-- Statistics Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-icon stat-icon-primary">
                <i class="bi bi-list-check"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $criteriaCount }}</div>
                <div class="stat-label">Criteria</div>
            </div>
        </div>
        <a href="{{ route('criteria.index', ['case' => $case->id]) }}" class="stat-link">
            Manage Criteria <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-icon stat-icon-success">
                <i class="bi bi-grid-3x3-gap"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $alternativeCount }}</div>
                <div class="stat-label">Alternatives</div>
            </div>
        </div>
        <a href="{{ route('alternatives.index', ['case' => $case->id]) }}" class="stat-link">
            Manage Alternatives <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-icon stat-icon-info">
                <i class="bi bi-file-earmark-bar-graph"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $decisionCount }}</div>
                <div class="stat-label">Decisions</div>
            </div>
        </div>
        <a href="{{ route('decisions.index', ['case' => $case->id]) }}" class="stat-link">
            View Results <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

<!-- Quick Actions -->
<div class="content-card">
    <h3 class="section-title">
        <i class="bi bi-lightning"></i> Quick Actions
    </h3>
    <div class="actions-grid">
        <a href="{{ route('criteria.create', ['case' => $case->id]) }}" class="action-card">
            <div class="action-icon action-icon-primary">
                <i class="bi bi-plus-circle"></i>
            </div>
            <div class="action-content">
                <h4>Add Criteria</h4>
                <p>Define evaluation criteria with weights</p>
            </div>
        </a>

        <a href="{{ route('alternatives.create', ['case' => $case->id]) }}" class="action-card">
            <div class="action-icon action-icon-success">
                <i class="bi bi-plus-circle"></i>
            </div>
            <div class="action-content">
                <h4>Add Alternatives</h4>
                <p>Create decision alternatives</p>
            </div>
        </a>

        <a href="{{ route('decisions.calculate', ['case' => $case->id]) }}" 
           class="action-card {{ $criteriaCount > 0 && $alternativeCount >= 2 ? '' : 'disabled' }}">
            <div class="action-icon action-icon-info">
                <i class="bi bi-calculator"></i>
            </div>
            <div class="action-content">
                <h4>Run PROMETHEE</h4>
                <p>Execute decision analysis</p>
            </div>
        </a>
    </div>
</div>

<!-- Recent Decisions -->
<div class="content-card">
    <div class="section-header">
        <h3 class="section-title">
            <i class="bi bi-clock-history"></i> Recent Decisions
        </h3>
        @if($decisionCount > 0)
        <a href="{{ route('decisions.index', ['case' => $case->id]) }}" class="btn-modern btn-ghost">
            View All <i class="bi bi-arrow-right"></i>
        </a>
        @endif
    </div>

    @if($recentDecisions->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <h4>No analysis results yet</h4>
            <p>
                @if($criteriaCount === 0)
                    Start by adding criteria to define your evaluation parameters.
                @elseif($alternativeCount < 2)
                    Add at least 2 alternatives to compare different options.
                @else
                    Run your first PROMETHEE analysis to see results here.
                @endif
            </p>
            <div class="empty-actions">
                @if($criteriaCount === 0)
                    <a href="{{ route('criteria.create', ['case' => $case->id]) }}" class="btn-modern btn-primary-modern">
                        <i class="bi bi-list-check"></i> Add Criteria
                    </a>
                @elseif($alternativeCount < 2)
                    <a href="{{ route('alternatives.create', ['case' => $case->id]) }}" class="btn-modern btn-primary-modern">
                        <i class="bi bi-grid-3x3-gap"></i> Add Alternatives
                    </a>
                @else
                    <a href="{{ route('decisions.calculate', ['case' => $case->id]) }}" class="btn-modern btn-primary-modern">
                        <i class="bi bi-calculator"></i> Run Analysis
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="decisions-list">
            @foreach($recentDecisions as $decision)
            <div class="decision-item">
                <div class="decision-content">
                    <h4 class="decision-title">{{ $decision->name }}</h4>
                    @if($decision->description)
                        <p class="decision-description">{{ Str::limit($decision->description, 80) }}</p>
                    @endif
                    <div class="decision-meta">
                        <span><i class="bi bi-calendar3"></i> {{ $decision->created_at->format('M d, Y') }}</span>
                        <span><i class="bi bi-bar-chart"></i> {{ count($decision->ranking ?? []) }} alternatives</span>
                    </div>
                </div>
                <a href="{{ route('decisions.result', ['case' => $case->id, 'decision' => $decision->id]) }}" 
                   class="btn-modern btn-ghost btn-sm">
                    <i class="bi bi-eye"></i> View
                </a>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
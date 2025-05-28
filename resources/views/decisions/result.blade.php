@extends('layouts.app')

@section('title', 'PROMETHEE Result - ' . $case->name)

@section('styles')
    @vite(['resources/css/decisions/results-style.css'])

@section('content')
<!-- Header -->
<div class="result-header">
    <div class="header-main">
        <div class="title-section">
            <h1 class="result-title">{{ $decision->name }}</h1>
            <p class="result-subtitle">{{ $case->name }} - PROMETHEE analysis results and rankings</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('decisions.index', ['case' => $case->id]) }}" class="btn-modern btn-secondary-modern">
                <i class="bi bi-arrow-left"></i>
                Back to Decisions
            </a>
        </div>
    </div>
    
    <div class="result-meta">
        <div class="meta-item">
            <i class="bi bi-calendar3"></i>
            <span>Analyzed on {{ $decision->created_at->format('M d, Y') }}</span>
        </div>
        <div class="meta-item">
            <i class="bi bi-clock"></i>
            <span>{{ $decision->created_at->format('H:i') }}</span>
        </div>
        <div class="meta-item">
            <i class="bi bi-bar-chart"></i>
            <span>{{ count($decision->ranking ?? []) }} alternatives analyzed</span>
        </div>
    </div>
</div>

<!-- Results Layout -->
<div class="results-layout">
    <!-- Left Column - Rankings -->
    <div class="rankings-column">
        <div class="rankings-card">
            <h3 class="section-title">
                <i class="bi bi-trophy"></i>
                Final Rankings
            </h3>
            
            <div class="rankings-list">
                @foreach($decision->ranking as $altId => $data)
                <div class="ranking-item rank-{{ $loop->iteration }}">
                    <div class="ranking-position">
                        <span class="position-number">{{ $loop->iteration }}</span>
                        @if($loop->iteration == 1)
                            <i class="bi bi-trophy-fill position-icon winner"></i>
                        @elseif($loop->iteration == 2)
                            <i class="bi bi-award-fill position-icon second"></i>
                        @elseif($loop->iteration == 3)
                            <i class="bi bi-award position-icon third"></i>
                        @else
                            <i class="bi bi-circle-fill position-icon other"></i>
                        @endif
                    </div>
                    
                    <div class="ranking-content">
                        <h4 class="alternative-name">{{ $data['name'] }}</h4>
                        <div class="flow-metrics">
                            <div class="flow-item">
                                <span class="flow-label">Net Flow</span>
                                <span class="flow-value net-flow {{ $data['net_flow'] >= 0 ? 'positive' : 'negative' }}">
                                    {{ number_format($data['net_flow'], 4) }}
                                </span>
                            </div>
                            <div class="flow-item">
                                <span class="flow-label">Positive Flow</span>
                                <span class="flow-value positive-flow">
                                    {{ number_format($decision->flows[$altId]['positive'], 4) }}
                                </span>
                            </div>
                            <div class="flow-item">
                                <span class="flow-label">Negative Flow</span>
                                <span class="flow-value negative-flow">
                                    {{ number_format($decision->flows[$altId]['negative'], 4) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="ranking-score">
                        <div class="score-circle">
                            <span class="score-value">{{ number_format($data['net_flow'], 3) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column - Visualizations -->
    <div class="charts-column">
        <!-- Net Flow Chart -->
        <div class="chart-card">
            <h3 class="section-title">
                <i class="bi bi-bar-chart"></i>
                Net Flow Analysis
            </h3>
            <div class="chart-container">
                <canvas id="netFlowChart" height="300"></canvas>
            </div>
        </div>

        <!-- Flow Comparison Chart -->
        <div class="chart-card">
            <h3 class="section-title">
                <i class="bi bi-graph-up"></i>
                Flow Comparison
            </h3>
            <div class="chart-container">
                <canvas id="flowComparisonChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Table -->
<div class="table-card">
    <h3 class="section-title">
        <i class="bi bi-table"></i>
        Detailed Results Table
    </h3>
    
    <div class="table-container">
        <table class="results-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Alternative</th>
                    <th>Net Flow (Φ)</th>
                    <th>Positive Flow (Φ+)</th>
                    <th>Negative Flow (Φ-)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($decision->ranking as $altId => $data)
                <tr class="table-row rank-{{ $loop->iteration }}">
                    <td class="rank-cell">
                        <div class="rank-display">
                            <span class="rank-number">{{ $loop->iteration }}</span>
                            @if($loop->iteration <= 3)
                                <i class="bi bi-{{ $loop->iteration == 1 ? 'trophy-fill' : 'award' }} rank-icon"></i>
                            @endif
                        </div>
                    </td>
                    <td class="alternative-cell">
                        <span class="alternative-name">{{ $data['name'] }}</span>
                    </td>
                    <td class="flow-cell">
                        <span class="flow-value {{ $data['net_flow'] >= 0 ? 'positive' : 'negative' }}">
                            {{ number_format($data['net_flow'], 4) }}
                        </span>
                    </td>
                    <td class="flow-cell">
                        <span class="flow-value positive">
                            {{ number_format($decision->flows[$altId]['positive'], 4) }}
                        </span>
                    </td>
                    <td class="flow-cell">
                        <span class="flow-value negative">
                            {{ number_format($decision->flows[$altId]['negative'], 4) }}
                        </span>
                    </td>
                    <td class="status-cell">
                        @if($loop->iteration == 1)
                            <span class="status-badge winner">Best Choice</span>
                        @elseif($loop->iteration <= 3)
                            <span class="status-badge good">Top {{ $loop->iteration }}</span>
                        @else
                            <span class="status-badge normal">Alternative</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Hidden configuration element -->
<div id="js-config" 
     data-ranking="{{ json_encode($decision->ranking ?? []) }}"
     data-flows="{{ json_encode($decision->flows ?? []) }}"
     style="display: none;"></div>

@endsection

@push('scripts')
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <!-- Our chart implementation -->
    @vite(['resources/js/chart.js'])
@endpush
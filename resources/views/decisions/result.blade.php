@extends('layouts.app')

@section('title', 'PROMETHEE Result')

@section('styles')
    @vite(['resources/css/decisions/results-style.css'])

@section('content')
<!-- Header -->
<div class="result-header">
    <div class="header-main">
        <div class="title-section">
            <h1 class="result-title">{{ $decision->name }}</h1>
            <p class="result-subtitle">PROMETHEE analysis results and rankings</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('decisions.index') }}" class="btn-modern btn-secondary-modern">
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
            <span>{{ count($decision->result_data['ranking'] ?? []) }} alternatives analyzed</span>
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
                @if(isset($decision->result_data['ranking']) && is_array($decision->result_data['ranking']))
                    @php
                        $rankings = $decision->result_data['ranking'];
                        $flows = $decision->result_data['flows'] ?? [];
                        
                        // Convert to array format for easier handling
                        $rankingArray = [];
                        foreach($rankings as $altId => $rankData) {
                            $rankingArray[] = array_merge($rankData, ['id' => $altId]);
                        }
                        
                        // Sort by rank
                        usort($rankingArray, function($a, $b) {
                            return ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999);
                        });
                    @endphp
                    
                    @foreach($rankingArray as $index => $data)
                    <div class="ranking-item rank-{{ $data['rank'] ?? ($index + 1) }}">
                        <div class="ranking-position">
                            <span class="position-number">{{ $data['rank'] ?? ($index + 1) }}</span>
                            @if(($data['rank'] ?? ($index + 1)) == 1)
                                <i class="bi bi-trophy-fill position-icon winner"></i>
                            @elseif(($data['rank'] ?? ($index + 1)) == 2)
                                <i class="bi bi-award-fill position-icon second"></i>
                            @elseif(($data['rank'] ?? ($index + 1)) == 3)
                                <i class="bi bi-award position-icon third"></i>
                            @else
                                <i class="bi bi-circle-fill position-icon other"></i>
                            @endif
                        </div>
                        
                        <div class="ranking-content">
                            <h4 class="alternative-name">{{ $data['name'] ?? 'Unknown Alternative' }}</h4>
                            <div class="flow-metrics">
                                <div class="flow-item">
                                    <span class="flow-label">Net Flow</span>
                                    <span class="flow-value net-flow {{ ($data['net_flow'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                        {{ sprintf('%g', $data['net_flow'] ?? 0) }}
                                    </span>
                                </div>
                                <div class="flow-item">
                                    <span class="flow-label">Positive Flow</span>
                                    <span class="flow-value positive-flow">
                                        {{ sprintf('%g', $data['positive_flow'] ?? 0) }}
                                    </span>
                                </div>
                                <div class="flow-item">
                                    <span class="flow-label">Negative Flow</span>
                                    <span class="flow-value negative-flow">
                                        {{ sprintf('%g', $data['negative_flow'] ?? 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="ranking-score">
                            <div class="score-circle">
                                <span class="score-value">{{ sprintf('%g', $data['net_flow'] ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="no-data">
                        <p>No ranking data available.</p>
                    </div>
                @endif
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
                @if(isset($rankingArray))
                    @foreach($rankingArray as $index => $data)
                    <tr class="table-row rank-{{ $data['rank'] ?? ($index + 1) }}">
                        <td class="rank-cell">
                            <div class="rank-display">
                                <span class="rank-number">{{ $data['rank'] ?? ($index + 1) }}</span>
                                @if(($data['rank'] ?? ($index + 1)) <= 3)
                                    <i class="bi bi-{{ ($data['rank'] ?? ($index + 1)) == 1 ? 'trophy-fill' : 'award' }} rank-icon"></i>
                                @endif
                            </div>
                        </td>
                        <td class="alternative-cell">
                            <span class="alternative-name">{{ $data['name'] ?? 'Unknown Alternative' }}</span>
                        </td>
                        <td class="flow-cell">
                            <span class="flow-value {{ ($data['net_flow'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ sprintf('%g', $data['net_flow'] ?? 0) }}
                            </span>
                        </td>
                        <td class="flow-cell">
                            <span class="flow-value positive">
                                {{ sprintf('%g', $data['positive_flow'] ?? 0) }}
                            </span>
                        </td>
                        <td class="flow-cell">
                            <span class="flow-value negative">
                                {{ sprintf('%g', $data['negative_flow'] ?? 0) }}
                            </span>
                        </td>
                        <td class="status-cell">
                            @if(($data['rank'] ?? ($index + 1)) == 1)
                                <span class="status-badge winner">Best Choice</span>
                            @elseif(($data['rank'] ?? ($index + 1)) <= 3)
                                <span class="status-badge good">Top {{ $data['rank'] ?? ($index + 1) }}</span>
                            @else
                                <span class="status-badge normal">Alternative</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- PROMETHEE Calculation Steps -->
<div class="calculation-steps">
    <h2 class="section-title">
        <i class="bi bi-calculator"></i>
        PROMETHEE Calculation Steps
    </h2>

    @if(isset($decision->result_data['calculation_steps']))
        @php $steps = $decision->result_data['calculation_steps']; @endphp

        <!-- Step 1: Decision Matrix -->
        @if(isset($steps['decision_matrix']))
        <div class="step-card">
            <div class="step-header" onclick="toggleStep('step1')">
                <h4><i class="bi bi-1-circle"></i> Decision Matrix</h4>
                <span class="step-toggle" id="toggle-step1">▼</span>
            </div>
            <div class="step-content" id="step1">
                <div class="step-description">
                    <p>Matriks keputusan berisi nilai asli dari setiap alternatif untuk masing-masing kriteria. Ini adalah titik awal dalam analisis metode PROMETHEE.</p>
                </div>
                
                @if(isset($steps['decision_matrix']['criteria']))
                <div class="criteria-info">
                    @foreach($steps['decision_matrix']['criteria'] as $criterion)
                    <div class="criteria-card">
                        <h5>{{ $criterion['name'] }}</h5>
                        <div class="criteria-details">
                            <div><strong>Type:</strong> {{ ucfirst($criterion['type']) }}</div>
                            <div><strong>Weight:</strong> {{ sprintf('%g', $criterion['weight']) }}</div>
                            <div><strong>Function:</strong> {{ ucfirst(str_replace('_', ' ', $criterion['preference_function'])) }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="matrix-container">
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th>Alternative</th>
                                @if(isset($steps['decision_matrix']['criteria']))
                                    @foreach($steps['decision_matrix']['criteria'] as $criterion)
                                        <th>{{ $criterion['name'] }}</th>
                                    @endforeach
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($steps['decision_matrix']['values']))
                                @foreach($steps['decision_matrix']['values'] as $altId => $values)
                                <tr>
                                    <td><strong>{{ $steps['decision_matrix']['alternatives'][$altId] ?? 'Alternative ' . $altId }}</strong></td>
                                    @foreach($values as $value)
                                        <td>{{ sprintf('%g', $value) }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Step 2: Normalized Matrix -->
        @if(isset($steps['normalized_matrix']))
        <div class="step-card">
            <div class="step-header" onclick="toggleStep('step2')">
                <h4><i class="bi bi-2-circle"></i> Normalized Decision Matrix</h4>
                <span class="step-toggle" id="toggle-step2">▼</span>
            </div>
            <div class="step-content" id="step2">
                <div class="step-description">
                    <p>Matriks normalisasi mengubah semua nilai kriteria ke skala 0–1. Untuk kriteria manfaat (benefit), nilai yang lebih tinggi lebih baik. Sementara untuk kriteria biaya (cost), nilai yang lebih rendah lebih baik.</p>
                </div>

                <div class="matrix-container">
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th>Alternative</th>
                                @if(isset($steps['normalized_matrix']['criteria']))
                                    @foreach($steps['normalized_matrix']['criteria'] as $criterion)
                                        <th>{{ $criterion['name'] }}</th>
                                    @endforeach
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($steps['normalized_matrix']['values']))
                                @foreach($steps['normalized_matrix']['values'] as $altId => $values)
                                <tr>
                                    <td><strong>{{ $steps['normalized_matrix']['alternatives'][$altId] ?? 'Alternative ' . $altId }}</strong></td>
                                    @foreach($values as $value)
                                        <td>{{ sprintf('%g', $value) }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Step 3: Preference Matrices -->
        @if(isset($steps['preference_matrices']))
        <div class="step-card">
            <div class="step-header" onclick="toggleStep('step3')">
                <h4><i class="bi bi-3-circle"></i> Preference Matrices by Criteria</h4>
                <span class="step-toggle" id="toggle-step3">▼</span>
            </div>
            <div class="step-content" id="step3">
                <div class="step-description">
                    <p>Untuk setiap kriteria, akan dihitung matriks preferensi yang menunjukkan seberapa besar suatu alternatif lebih disukai dibandingkan alternatif lainnya, berdasarkan fungsi preferensi yang digunakan.</p>
                </div>

                @foreach($steps['preference_matrices'] as $criteriaId => $matrix)
                <div style="margin-bottom: 2rem;">
                    <h5>{{ $matrix['criterion_name'] }} (Weight: {{ sprintf('%g', $matrix['weight']) }})</h5>
                    
                    <div class="matrix-container">
                        <table class="matrix-table">
                            <thead>
                                <tr>
                                    <th>From \ To</th>
                                    @foreach($matrix['alternatives'] as $altId => $altName)
                                        <th>{{ $altName }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($matrix['values'] as $fromAltId => $preferences)
                                <tr>
                                    <td><strong>{{ $matrix['alternatives'][$fromAltId] }}</strong></td>
                                    @foreach($preferences as $toAltId => $preference)
                                        <td class="{{ $preference > 0 ? 'matrix-highlight' : '' }}">
                                            {{ sprintf('%g', $preference) }}
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Step 4: Global Preference Matrix -->
        @if(isset($steps['global_preference_matrix']))
        <div class="step-card">
            <div class="step-header" onclick="toggleStep('step4')">
                <h4><i class="bi bi-4-circle"></i> Global Preference Matrix</h4>
                <span class="step-toggle" id="toggle-step4">▼</span>
            </div>
            <div class="step-content" id="step4">
                <div class="step-description">
                    <p>Matriks preferensi global merupakan hasil penjumlahan tertimbang dari semua matriks preferensi individu. Matriks ini menggambarkan tingkat preferensi keseluruhan antar alternatif.</p>
                </div>

                <div class="matrix-container">
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th>From \ To</th>
                                @foreach($steps['global_preference_matrix']['alternatives'] as $altId => $altName)
                                    <th>{{ $altName }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($steps['global_preference_matrix']['values'] as $fromAltId => $preferences)
                            <tr>
                                <td><strong>{{ $steps['global_preference_matrix']['alternatives'][$fromAltId] }}</strong></td>
                                @foreach($preferences as $toAltId => $preference)
                                    <td class="{{ $preference > 0.3 ? 'matrix-highlight' : '' }}">
                                        {{ sprintf('%g', $preference) }}
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Step 5: PROMETHEE Flows -->
        @if(isset($steps['flows']))
        <div class="step-card">
            <div class="step-header" onclick="toggleStep('step5')">
                <h4><i class="bi bi-5-circle"></i> PROMETHEE Flows Calculation</h4>
                <span class="step-toggle" id="toggle-step5">▼</span>
            </div>
            <div class="step-content" id="step5">
                <div class="step-description">
                    <p>Aliran PROMETHEE (PROMETHEE flows) dihitung dari matriks preferensi global.

Aliran positif (Φ⁺) menunjukkan seberapa besar suatu alternatif mengungguli alternatif lainnya.

Aliran negatif (Φ⁻) menunjukkan seberapa besar alternatif tersebut dikalahkan oleh alternatif lainnya.

Aliran bersih (Φ) adalah selisih antara aliran positif dan negatif, dan digunakan untuk menentukan peringkat akhir dari alternatif.</p>
                </div>

                <div class="flow-summary">
                    @foreach($steps['flows'] as $altId => $flow)
                    <div class="flow-card">
                        <h5>{{ $flow['name'] }}</h5>
                        <div>
                            <strong>Φ+ (Positive):</strong>
                            <div class="flow-value-large positive">{{ sprintf('%g', $flow['positive']) }}</div>
                        </div>
                        <div>
                            <strong>Φ- (Negative):</strong>
                            <div class="flow-value-large negative">{{ sprintf('%g', $flow['negative']) }}</div>
                        </div>
                        <div>
                            <strong>Φ (Net):</strong>
                            <div class="flow-value-large {{ $flow['net'] > 0 ? 'positive' : ($flow['net'] < 0 ? 'negative' : 'neutral') }}">
                                {{ sprintf('%g', $flow['net']) }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="matrix-container" style="margin-top: 1.5rem;">
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th>Alternative</th>
                                <th>Positive Flow (Φ+)</th>
                                <th>Negative Flow (Φ-)</th>
                                <th>Net Flow (Φ)</th>
                                <th>Rank</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $flowsForRanking = collect($steps['flows'])->map(function($flow, $altId) {
                                    return array_merge($flow, ['alt_id' => $altId]);
                                })->sortByDesc('net')->values();
                            @endphp
                            @foreach($flowsForRanking as $index => $flow)
                            <tr>
                                <td><strong>{{ $flow['name'] }}</strong></td>
                                <td class="positive">{{ sprintf('%g', $flow['positive']) }}</td>
                                <td class="negative">{{ sprintf('%g', $flow['negative']) }}</td>
                                <td class="flow-value {{ $flow['net'] > 0 ? 'positive' : ($flow['net'] < 0 ? 'negative' : 'neutral') }}">
                                    {{ sprintf('%g', $flow['net']) }}
                                </td>
                                <td><strong>{{ $index + 1 }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    @else
        <div class="step-card">
            <div class="step-content" style="display: block;">
                <p>Detailed calculation steps are not available for this analysis.</p>
            </div>
        </div>
    @endif
</div>

<!-- Hidden configuration element -->
<div id="js-config" 
     data-ranking="{{ json_encode($decision->result_data['ranking'] ?? []) }}"
     data-flows="{{ json_encode($decision->result_data['flows'] ?? []) }}"
     style="display: none;"></div>

@endsection

@push('scripts')
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <!-- Our chart implementation -->
    @vite(['resources/js/chart.js'])
    
    <script>
        function toggleStep(stepId) {
            const content = document.getElementById(stepId);
            const toggle = document.getElementById('toggle-' + stepId);
            
            if (content.classList.contains('active')) {
                content.classList.remove('active');
                toggle.classList.remove('expanded');
            } else {
                content.classList.add('active');
                toggle.classList.add('expanded');
            }
        }
        
        // Optional: Auto-expand first step
        document.addEventListener('DOMContentLoaded', function() {
            const firstStep = document.getElementById('step1');
            const firstToggle = document.getElementById('toggle-step1');
            if (firstStep && firstToggle) {
                firstStep.classList.add('active');
                firstToggle.classList.add('expanded');
            }
        });
    </script>
@endpush
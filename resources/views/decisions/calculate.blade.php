@extends('layouts.app')

@section('title', 'PROMETHEE Calculation')

@section('styles')
    @vite(['resources/css/decisions/calculate.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">PROMETHEE Analysis</h1>
        <p class="main-subtitle">Configure and execute multi-criteria decision analysis</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('decisions.index') }}" class="btn-modern btn-secondary-modern">
            <i class="bi bi-arrow-left"></i>
            Back to Results
        </a>
    </div>
</div>

@if($alternatives->isEmpty() || $criterias->isEmpty())
    <div class="alert alert-warning modern-alert" role="alert">
        <div class="alert-content">
            <i class="bi bi-exclamation-triangle alert-icon"></i>
            <div class="alert-text">
                <h4>Setup Required</h4>
                @if($alternatives->isEmpty())
                    <p>No alternatives found. Please add alternatives first.</p>
                @endif
                @if($criterias->isEmpty())
                    <p>No criteria found. Please add criteria first.</p>
                @endif
            </div>
        </div>
        <div class="alert-actions">
            @if($criterias->isEmpty())
                <a href="{{ route('criteria.create') }}" class="btn-modern btn-primary-modern">
                    <i class="bi bi-list-check"></i> Add Criteria
                </a>
            @endif
            @if($alternatives->isEmpty())
                <a href="{{ route('alternatives.create') }}" class="btn-modern btn-primary-modern">
                    <i class="bi bi-grid-3x3-gap"></i> Add Alternatives
                </a>
            @endif
        </div>
    </div>
@else

<!-- Calculation Form -->
<div class="calculation-layout">
    <!-- Left Column - Configuration -->
    <div class="config-column">
        <div class="config-card">
            <h3 class="section-title">
                <i class="bi bi-gear"></i>
                Analysis Configuration
            </h3>
            
            <form method="POST" action="{{ route('decisions.process') }}" id="calculationForm">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="bi bi-tag label-icon"></i>
                        Analysis Name <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           placeholder="e.g., Analysis 2024-01, Comparison Study"
                           required>
                    @error('name')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="bi bi-text-paragraph label-icon"></i>
                        Description <span class="optional">(Optional)</span>
                    </label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3"
                              placeholder="Describe the purpose and context of this analysis...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Alternative Selection -->
                <div class="selection-section">
                    <h4 class="selection-title">
                        <i class="bi bi-check2-square"></i>
                        Select Alternatives 
                        <span class="selection-count">(Select at least 2)</span>
                    </h4>
                    
                    <div class="selection-controls">
                        <button type="button" class="btn-small btn-outline" id="selectAll">
                            <i class="bi bi-check-all"></i> Select All
                        </button>
                        <button type="button" class="btn-small btn-outline" id="deselectAll">
                            <i class="bi bi-x-square"></i> Deselect All
                        </button>
                    </div>

                    <div class="alternatives-selection">
                        @foreach($alternatives as $alternative)
                        <div class="selection-card">
                            <input type="checkbox" 
                                   class="selection-checkbox" 
                                   id="alternative_{{ $alternative->id }}" 
                                   name="selected_alternatives[]" 
                                   value="{{ $alternative->id }}"
                                   {{ is_array(old('selected_alternatives')) && in_array($alternative->id, old('selected_alternatives')) ? 'checked' : '' }}>
                            <label for="alternative_{{ $alternative->id }}" class="selection-label">
                                <div class="selection-indicator">
                                    <i class="bi bi-check"></i>
                                </div>
                                <div class="selection-content">
                                    <h5 class="selection-name">{{ $alternative->name }}</h5>
                                    @if($alternative->description)
                                        <p class="selection-description">{{ Str::limit($alternative->description, 80) }}</p>
                                    @endif
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    
                    @error('selected_alternatives')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-modern btn-primary-modern btn-large">
                        <i class="bi bi-calculator"></i>
                        Calculate PROMETHEE
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column - Decision Matrix Preview -->
    <div class="preview-column">
        <div class="preview-card">
            <h3 class="section-title">
                <i class="bi bi-table"></i>
                Decision Matrix Preview
            </h3>
            
            <div class="matrix-container">
                <div class="matrix-scroll">
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th class="matrix-header-alt">Alternative</th>
                                @foreach($criterias as $criteria)
                                    <th class="matrix-header-criteria">
                                        <div class="criteria-header-content">
                                            <span class="criteria-name">{{ Str::limit($criteria->name, 12) }}</span>
                                            <div class="criteria-meta">
                                                <span class="criteria-type {{ $criteria->type }}">
                                                    <i class="bi bi-{{ $criteria->type === 'benefit' ? 'arrow-up' : 'arrow-down' }}"></i>
                                                    {{ ucfirst($criteria->type) }}
                                                </span>
                                                <span class="criteria-weight">W: {{ $criteria->weight }}</span>
                                            </div>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alternatives as $alternative)
                            <tr class="matrix-row" data-alt-id="{{ $alternative->id }}">
                                <td class="matrix-alt-name">
                                    <div class="alt-name-content">
                                        <span class="alt-name">{{ $alternative->name }}</span>
                                        <span class="alt-status">
                                            <i class="bi bi-circle"></i>
                                            Not Selected
                                        </span>
                                    </div>
                                </td>
                                @foreach($criterias as $criteria)
                                    @php
                                        $alternativeCriteria = $alternative->criteriaValues->where('criteria_id', $criteria->id)->first();
                                    @endphp
                                    @if($alternativeCriteria && $alternativeCriteria->is_selected)
                                        <td class="matrix-value">
                                            <span class="value-number">{{ $alternativeCriteria->value }}</span>
                                        </td>
                                    @else
                                        <td>-</td> {{-- Display a dash or empty if not selected --}}
                                    @endif
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Analysis Summary -->
            <div class="analysis-summary">
                <h4 class="summary-title">
                    <i class="bi bi-info-circle"></i>
                    Analysis Summary
                </h4>
                <div class="summary-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Alternatives</span>
                        <span class="stat-value">{{ $alternatives->count() }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Criteria</span>
                        <span class="stat-value">{{ $criterias->count() }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Selected for Analysis</span>
                        <span class="stat-value" id="selectedCount">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.selection-checkbox');
    const selectAllBtn = document.getElementById('selectAll');
    const deselectAllBtn = document.getElementById('deselectAll');
    const selectedCountSpan = document.getElementById('selectedCount');
    const matrixRows = document.querySelectorAll('.matrix-row');
    
    // Update selection count and matrix highlighting
    function updateSelectionDisplay() {
        const selectedCount = document.querySelectorAll('.selection-checkbox:checked').length;
        selectedCountSpan.textContent = selectedCount;
        
        // Update matrix rows
        matrixRows.forEach(row => {
            const altId = row.getAttribute('data-alt-id');
            const checkbox = document.querySelector(`input[value="${altId}"]`);
            const altStatus = row.querySelector('.alt-status');
            
            if (checkbox && checkbox.checked) {
                row.classList.add('selected');
                if (altStatus) {
                    altStatus.classList.add('selected');
                    altStatus.innerHTML = '<i class="bi bi-check-circle-fill"></i> Selected';
                }
            } else {
                row.classList.remove('selected');
                if (altStatus) {
                    altStatus.classList.remove('selected');
                    altStatus.innerHTML = '<i class="bi bi-circle"></i> Not Selected';
                }
            }
        });
    }
    
    // Add event listeners to checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionDisplay);
    });
    
    // Select all button
    selectAllBtn.addEventListener('click', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        updateSelectionDisplay();
    });
    
    // Deselect all button
    deselectAllBtn.addEventListener('click', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectionDisplay();
    });
    
    // Initial update
    updateSelectionDisplay();
});
</script>
@endpush
@endsection
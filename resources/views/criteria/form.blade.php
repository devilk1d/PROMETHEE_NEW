@extends('layouts.app')

@section('title', $criterion->id ? 'Edit Criteria' : 'Create Criteria')

@section('styles')
    @vite(['resources/css/criteria/form.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">{{ $criterion->id ? 'Edit' : 'Create' }} Criteria</h1>
        <p class="main-subtitle">{{ $case->name }} - Define evaluation criteria for PROMETHEE analysis</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('criteria.index', ['case' => $case->id]) }}" class="btn-modern btn-secondary-modern">
            <i class="bi bi-arrow-left"></i>
            Back to Criteria
        </a>
    </div>
</div>

<!-- Form Card -->
<div class="content-card">
    <form method="POST" action="{{ $criterion->id ? route('criteria.update', ['case' => $case->id, 'criterion' => $criterion->id]) : route('criteria.store', ['case' => $case->id]) }}" class="modern-form">
        @csrf
        @if($criterion->id)
            @method('PUT')
        @endif

        <!-- Basic Information -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="bi bi-info-circle"></i>
                Basic Information
            </h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="bi bi-tag label-icon"></i>
                        Criteria Name <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $criterion->name) }}" 
                           placeholder="e.g., Cost, Quality, Performance"
                           required>
                    @error('name')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="weight" class="form-label">
                        <i class="bi bi-percent label-icon"></i>
                        Weight <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" 
                               step="0.01" 
                               min="0" 
                               max="1" 
                               class="form-control @error('weight') is-invalid @enderror" 
                               id="weight" 
                               name="weight" 
                               value="{{ old('weight', $criterion->weight) }}" 
                               placeholder="0.00 - 1.00"
                               required>
                        <span class="input-addon">
                            <i class="bi bi-percent"></i>
                        </span>
                    </div>
                    @error('weight')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                    <div class="form-hint">Value between 0 and 1 representing importance</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-arrow-up-down label-icon"></i>
                    Criteria Type <span class="required">*</span>
                </label>
                <div class="radio-group">
                    <div class="radio-card">
                        <input type="radio" 
                               name="type" 
                               id="type_benefit" 
                               value="benefit" 
                               {{ old('type', $criterion->type) === 'benefit' ? 'checked' : '' }} 
                               required>
                        <label for="type_benefit" class="radio-label">
                            <div class="radio-icon benefit">
                                <i class="bi bi-arrow-up"></i>
                            </div>
                            <div class="radio-content">
                                <span class="radio-title">Benefit</span>
                                <span class="radio-description">Higher values are better</span>
                            </div>
                        </label>
                    </div>
                    <div class="radio-card">
                        <input type="radio" 
                               name="type" 
                               id="type_cost" 
                               value="cost" 
                               {{ old('type', $criterion->type) === 'cost' ? 'checked' : '' }}>
                        <label for="type_cost" class="radio-label">
                            <div class="radio-icon cost">
                                <i class="bi bi-arrow-down"></i>
                            </div>
                            <div class="radio-content">
                                <span class="radio-title">Cost</span>
                                <span class="radio-description">Lower values are better</span>
                            </div>
                        </label>
                    </div>
                </div>
                @error('type')
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
                          placeholder="Describe what this criteria measures and how it should be evaluated...">{{ old('description', $criterion->description) }}</textarea>
                @error('description')
                    <div class="error-message">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <!-- Preference Function -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="bi bi-graph-up"></i>
                Preference Function
            </h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="preference_function" class="form-label">
                        <i class="bi bi-diagram-3 label-icon"></i>
                        Function Type <span class="required">*</span>
                    </label>
                    <select class="form-control @error('preference_function') is-invalid @enderror" 
                            id="preference_function" 
                            name="preference_function" 
                            required>
                        <option value="">Select Preference Function</option>
                        @foreach(\App\Models\Criteria::preferenceFunctions() as $value => $label)
                            <option value="{{ $value }}" 
                                    {{ old('preference_function', $criterion->preference_function) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('preference_function')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="threshold-section" id="thresholdSection">
                <div class="form-grid">
                    <div class="form-group" id="pGroup">
                        <label for="p" class="form-label">
                            <i class="bi bi-graph-up-arrow label-icon"></i>
                            Preference Threshold (p)
                        </label>
                        <input type="number" 
                               step="0.01" 
                               min="0" 
                               class="form-control @error('p') is-invalid @enderror" 
                               id="p" 
                               name="p" 
                               value="{{ old('p', $criterion->p) }}"
                               placeholder="0.00">
                        @error('p')
                            <div class="error-message">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-hint">Required for Linear, Level, and Gaussian functions</div>
                    </div>
                    
                    <div class="form-group" id="qGroup">
                        <label for="q" class="form-label">
                            <i class="bi bi-dash-circle label-icon"></i>
                            Indifference Threshold (q)
                        </label>
                        <input type="number" 
                               step="0.01" 
                               min="0" 
                               class="form-control @error('q') is-invalid @enderror" 
                               id="q" 
                               name="q" 
                               value="{{ old('q', $criterion->q) }}"
                               placeholder="0.00">
                        @error('q')
                            <div class="error-message">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-hint">Required for Quasi, Level, and Linear with Indifference functions</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="action-group">
                <button type="submit" class="btn-modern btn-primary-modern">
                    <i class="bi bi-save"></i>
                    {{ $criterion->id ? 'Update Criteria' : 'Create Criteria' }}
                </button>
                <a href="{{ route('criteria.index', ['case' => $case->id]) }}" class="btn-modern btn-secondary-modern">
                    <i class="bi bi-x"></i>
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const preferenceFunction = document.getElementById('preference_function');
    const pGroup = document.getElementById('pGroup');
    const qGroup = document.getElementById('qGroup');
    
    function toggleThresholdFields() {
        const selectedFunction = preferenceFunction.value;
        
        // Hide all threshold fields by default
        pGroup.style.display = 'none';
        qGroup.style.display = 'none';
        
        // Show fields based on selected function
        switch(selectedFunction) {
            case 'usual':
                // No thresholds needed
                break;
            case 'quasi':
                qGroup.style.display = 'block';
                break;
            case 'linear':
                pGroup.style.display = 'block';
                break;
            case 'level':
            case 'linear_quasi':
                pGroup.style.display = 'block';
                qGroup.style.display = 'block';
                break;
            case 'gaussian':
                pGroup.style.display = 'block';
                break;
        }
    }
    
    // Initial toggle
    toggleThresholdFields();
    
    // Add event listener for changes
    preferenceFunction.addEventListener('change', toggleThresholdFields);
});
</script>
@endpush
@endsection
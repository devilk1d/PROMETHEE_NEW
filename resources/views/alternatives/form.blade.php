@extends('layouts.app')

@section('title', isset($alternative->id) ? 'Edit Alternative' : 'Create Alternative')

@section('styles')
    @vite(['resources/css/alternatives/form.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">{{ $alternative->id ? 'Edit' : 'Create' }} Alternative</h1>
        <p class="main-subtitle">{{ $case->name }} - Define decision alternative for PROMETHEE analysis</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('alternatives.index', ['case' => $case->id]) }}" class="btn-modern btn-secondary-modern">
            <i class="bi bi-arrow-left"></i>
            Back to Alternatives
        </a>
    </div>
</div>

@if($criterias->isEmpty())
    <div class="alert alert-warning modern-alert" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        No criteria available. Please <a href="{{ route('criteria.create', ['case' => $case->id]) }}" class="alert-link">create criteria</a> first before adding alternatives.
    </div>
@else

<!-- Form Layout -->
<div class="form-layout">
    <!-- Left Column - Basic Information -->
    <div class="form-column">
        <h3 class="section-title">
            <i class="bi bi-info-circle"></i>
            Basic Information
        </h3>
        
        <form method="POST" action="{{ isset($alternative->id) ? route('alternatives.update', ['case' => $case->id, 'alternative' => $alternative->id]) : route('alternatives.store', ['case' => $case->id]) }}" class="modern-form" id="alternativeForm">
            @csrf
            @if(isset($alternative->id))
                @method('PUT')
            @endif

            <div class="form-group">
                <label for="name" class="form-label">
                    <i class="bi bi-tag label-icon"></i>
                    Alternative Name <span class="required">*</span>
                </label>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $alternative->name) }}" 
                       placeholder="e.g., Option A, Product X, Solution 1"
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
                          rows="6"
                          placeholder="Describe this alternative, its features, benefits, and characteristics...">{{ old('description', $alternative->description) }}</textarea>
                @error('description')
                    <div class="error-message">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-modern btn-primary-modern">
                    <i class="bi bi-save"></i>
                    {{ $alternative->id ? 'Update Alternative' : 'Create Alternative' }}
                </button>
                <a href="{{ route('alternatives.index', ['case' => $case->id]) }}" class="btn-modern btn-secondary-modern">
                    <i class="bi bi-x"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Right Column - Criteria Values -->
    <div class="form-column">
        <h3 class="section-title">
            <i class="bi bi-sliders"></i>
            Criteria Values
        </h3>
        
        <div class="criteria-grid">
            @foreach($criterias as $criteria)
            <div class="criteria-card">
                <div class="criteria-header">
                    <div class="criteria-info">
                        <h4 class="criteria-name">{{ $criteria->name }}</h4>
                        <div class="criteria-meta">
                            <span class="criteria-type {{ $criteria->type }}">
                                <i class="bi bi-{{ $criteria->type === 'benefit' ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ ucfirst($criteria->type) }}
                            </span>
                            <span class="criteria-weight">
                                <i class="bi bi-percent"></i>
                                Weight: {{ $criteria->weight }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="criteria-input">
                    <label for="criteria_{{ $criteria->id }}" class="input-label">
                        Value <span class="required">*</span>
                    </label>
                    <input type="number" 
                           step="0.01" 
                           class="form-control criteria-value @error('criteria_values.'.$criteria->id) is-invalid @enderror" 
                           id="criteria_{{ $criteria->id }}" 
                           name="criteria_values[{{ $criteria->id }}]" 
                           value="{{ old('criteria_values.'.$criteria->id, $alternative->getCriteriaValue($criteria->id)) }}"
                           placeholder="Enter value"
                           form="alternativeForm"
                           required>
                    <input type="hidden" name="selected_criteria[{{ $criteria->id }}]" value="1" form="alternativeForm">
                    @error('criteria_values.'.$criteria->id)
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                @if($criteria->description)
                <div class="criteria-description">
                    <p>{{ $criteria->description }}</p>
                </div>
                @endif

                <div class="criteria-function">
                    <small class="function-text">
                        <i class="bi bi-diagram-3"></i>
                        {{ \App\Models\Criteria::preferenceFunctions()[$criteria->preference_function] ?? $criteria->preference_function }}
                    </small>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Summary Card -->
        <div class="summary-card">
            <h4 class="summary-title">
                <i class="bi bi-calculator"></i>
                Values Summary
            </h4>
            <div class="summary-stats">
                <div class="stat-item">
                    <span class="stat-label">Total Criteria</span>
                    <span class="stat-value">{{ $criterias->count() }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Benefit Criteria</span>
                    <span class="stat-value">{{ $criterias->where('type', 'benefit')->count() }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Cost Criteria</span>
                    <span class="stat-value">{{ $criterias->where('type', 'cost')->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endif

@endsection
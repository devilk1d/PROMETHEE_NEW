@extends('layouts.app')

@section('title', isset($case->id) ? 'Edit Case' : 'Create Case')

@section('styles')
    @vite(['resources/css/cases/form.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">{{ isset($case->id) ? 'Edit' : 'Create' }} Case</h1>
        <p class="main-subtitle">{{ isset($case->id) ? 'Modify' : 'Create new' }} decision analysis case for PROMETHEE evaluation</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('cases.index') }}" class="btn-modern btn-secondary-modern">
            <i class="bi bi-arrow-left"></i>
            Back to Cases
        </a>
    </div>
</div>

<!-- Form Card -->
<div class="content-card">
    <div class="form-container">
        <form method="POST" action="{{ isset($case->id) ? route('cases.update', $case->id) : route('cases.store') }}" class="modern-form">
            @csrf
            @if(isset($case->id))
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="bi bi-folder label-icon"></i>
                        Case Name
                        <span class="required-indicator">*</span>
                    </label>
                    <input type="text" 
                           class="form-control modern-input @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $case->name) }}" 
                           placeholder="Enter a descriptive name for your case"
                           required>
                    @error('name')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                    <div class="form-hint">
                        Choose a clear, descriptive name that identifies your decision scenario
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="description" class="form-label">
                        <i class="bi bi-text-paragraph label-icon"></i>
                        Description
                        <span class="optional-indicator">(Optional)</span>
                    </label>
                    <textarea class="form-control modern-textarea @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="4"
                              placeholder="Provide additional details about this decision case, objectives, and context...">{{ old('description', $case->description) }}</textarea>
                    @error('description')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                    <div class="form-hint">
                        Describe the decision context, objectives, and any relevant background information
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <div class="action-group">
                    <button type="submit" class="btn-modern btn-primary-modern btn-action">
                        <i class="bi bi-save"></i>
                        {{ isset($case->id) ? 'Update Case' : 'Create Case' }}
                    </button>
                    <a href="{{ route('cases.index') }}" class="btn-modern btn-secondary-modern btn-action">
                        <i class="bi bi-x"></i>
                        Cancel
                    </a>
                </div>
                
                @if(isset($case->id))
                <div class="form-info">
                    <div class="info-item">
                        <i class="bi bi-calendar3"></i>
                        <span>Created {{ $case->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-clock-history"></i>
                        <span>Last updated {{ $case->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

@if(isset($case->id))
<!-- Next Steps Card -->
<div class="content-card">
    <div class="next-steps">
        <h3 class="next-steps-title">
            <i class="bi bi-signpost-2"></i>
            Next Steps
        </h3>
        <p class="next-steps-description">After saving your case, you can proceed with setting up your decision analysis</p>
        
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-icon step-icon-primary">
                    <i class="bi bi-list-check"></i>
                </div>
                <div class="step-content">
                    <h4 class="step-title">1. Define Criteria</h4>
                    <p class="step-description">Set up evaluation criteria with weights and preference functions</p>
                    <a href="{{ route('criteria.index', ['case' => $case->id]) }}" class="step-link">
                        Manage Criteria <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="step-card">
                <div class="step-icon step-icon-success">
                    <i class="bi bi-grid-3x3-gap"></i>
                </div>
                <div class="step-content">
                    <h4 class="step-title">2. Add Alternatives</h4>
                    <p class="step-description">Create alternatives and assign values for each criterion</p>
                    <a href="{{ route('alternatives.index', ['case' => $case->id]) }}" class="step-link">
                        Manage Alternatives <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="step-card">
                <div class="step-icon step-icon-info">
                    <i class="bi bi-calculator"></i>
                </div>
                <div class="step-content">
                    <h4 class="step-title">3. Run Analysis</h4>
                    <p class="step-description">Execute PROMETHEE calculation and view results</p>
                    <a href="{{ route('decisions.calculate', ['case' => $case->id]) }}" class="step-link">
                        Calculate PROMETHEE <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
@extends('layouts.app')

@section('title', 'Batch Manage Alternatives - ' . $case->name)

@section('styles')
    @vite(['resources/css/alternatives/batch.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">Batch Manage Alternatives</h1>
        <p class="main-subtitle">{{ $case->name }} - Efficiently manage multiple alternatives with criteria values</p>
    </div>
    <div class="header-actions">
        <button type="button" id="addAlternativeRow" class="btn-modern btn-success-modern">
            <i class="bi bi-plus-circle"></i>
            Add Row
        </button>
        <a href="{{ route('alternatives.index', ['case' => $case->id]) }}" class="btn-modern btn-secondary-modern">
            <i class="bi bi-arrow-left"></i>
            Back to List
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

@if($criterias->isEmpty())
    <div class="alert alert-warning modern-alert" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        No criteria available. Please <a href="{{ route('criteria.create', ['case' => $case->id]) }}" class="alert-link">create criteria</a> first before managing alternatives.
    </div>
@else

<!-- Batch Form -->
<div class="content-card">
    <div class="batch-header">
        <h3 class="section-title">
            <i class="bi bi-table"></i>
            Alternatives Management Table
        </h3>
        <p class="section-subtitle">Add, edit, or remove multiple alternatives with criteria values</p>
    </div>

    <!-- Criteria Legend -->
    <div class="criteria-legend">
        <h4 class="legend-title">
            <i class="bi bi-info-circle"></i>
            Criteria Legend
        </h4>
        <div class="legend-items">
            @foreach($criterias as $criteria)
            <div class="legend-item">
                <span class="legend-icon {{ $criteria->type }}">
                    <i class="bi bi-{{ $criteria->type === 'benefit' ? 'arrow-up' : 'arrow-down' }}"></i>
                </span>
                <div class="legend-content">
                    <span class="legend-name">{{ $criteria->name }}</span>
                    <span class="legend-meta">{{ ucfirst($criteria->type) }} ({{ $criteria->weight }})</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('alternatives.batchStore', ['case' => $case->id]) }}" id="alternativesBatchForm">
        @csrf
        
        <div class="table-container">
            <table class="batch-table" id="alternativesTable">
                <thead>
                    <tr>
                        <th width="15%">Name</th>
                        <th width="20%">Description</th>
                        @foreach($criterias as $criteria)
                            <th width="{{ 65 / $criterias->count() }}%" class="criteria-header">
                                <div class="criteria-header-content">
                                    <span class="criteria-name">{{ Str::limit($criteria->name, 12) }}</span>
                                    <span class="criteria-type {{ $criteria->type }}">
                                        <i class="bi bi-{{ $criteria->type === 'benefit' ? 'arrow-up' : 'arrow-down' }}"></i>
                                        {{ ucfirst($criteria->type) }}
                                    </span>
                                </div>
                            </th>
                        @endforeach
                        <th width="8%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alternatives as $index => $alternative)
                    <tr class="alternative-row" data-id="{{ $alternative->id }}">
                        <td>
                            <input type="hidden" name="alternatives[{{ $index }}][id]" value="{{ $alternative->id }}">
                            <input type="text" 
                                   class="form-control table-input" 
                                   name="alternatives[{{ $index }}][name]" 
                                   value="{{ $alternative->name }}" 
                                   placeholder="Alternative name" 
                                   required>
                        </td>
                        <td>
                            <textarea class="form-control table-textarea" 
                                      name="alternatives[{{ $index }}][description]" 
                                      rows="2" 
                                      placeholder="Description">{{ $alternative->description }}</textarea>
                        </td>
                        @foreach($criterias as $criteria)
                            <td>
                                <input type="number" 
                                       step="0.01" 
                                       class="form-control table-input criteria-input" 
                                       name="alternatives[{{ $index }}][criteria_values][{{ $criteria->id }}]" 
                                       value="{{ $alternative->getCriteriaValue($criteria->id) }}"
                                       placeholder="0.00">
                                <input type="hidden" name="alternatives[{{ $index }}][selected_criteria][{{ $criteria->id }}]" value="1">
                            </td>
                        @endforeach
                        <td>
                            <button type="button" 
                                    class="btn-icon btn-icon-danger delete-row" 
                                    title="Delete Row">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    
                    @if(count($alternatives) === 0)
                    <tr class="alternative-row new-row">
                        <td>
                            <input type="text" 
                                   class="form-control table-input" 
                                   name="alternatives[0][name]" 
                                   placeholder="Alternative name" 
                                   required>
                        </td>
                        <td>
                            <textarea class="form-control table-textarea" 
                                      name="alternatives[0][description]" 
                                      rows="2" 
                                      placeholder="Description"></textarea>
                        </td>
                        @foreach($criterias as $criteria)
                            <td>
                                <input type="number" 
                                       step="0.01" 
                                       class="form-control table-input criteria-input" 
                                       name="alternatives[0][criteria_values][{{ $criteria->id }}]" 
                                       value="0"
                                       placeholder="0.00">
                                <input type="hidden" name="alternatives[0][selected_criteria][{{ $criteria->id }}]" value="1">
                            </td>
                        @endforeach
                        <td>
                            <button type="button" 
                                    class="btn-icon btn-icon-danger delete-row" 
                                    title="Delete Row">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-modern btn-primary-modern">
                <i class="bi bi-save"></i>
                Save All Alternatives
            </button>
        </div>
    </form>
</div>

@endif

@push('scripts')
    @vite(['resources/js/batch.js'])
    <!-- Pass Laravel data to JavaScript via data attributes -->
    <div id="js-config" 
         data-initial-count="{{ count($alternatives) ?: 0 }}"
         data-criterias="{{ $criterias->toJson() }}"
         style="display: none;">
    </div>
@endpush
@endsection
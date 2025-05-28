@extends('layouts.app')

@section('title', 'Batch Manage Criteria - ' . $case->name)

@section('styles')
    @vite(['resources/css/criteria/batch.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">Batch Manage Criteria</h1>
        <p class="main-subtitle">{{ $case->name }} - Efficiently manage multiple criteria at once</p>
    </div>
    <div class="header-actions">
        <button type="button" id="addCriteriaRow" class="btn-modern btn-success-modern">
            <i class="bi bi-plus-circle"></i>
            Add Row
        </button>
        <a href="{{ route('criteria.index', ['case' => $case->id]) }}" class="btn-modern btn-secondary-modern">
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

<!-- Batch Form -->
<div class="content-card">
    <div class="batch-header">
        <h3 class="section-title">
            <i class="bi bi-table"></i>
            Criteria Management Table
        </h3>
        <p class="section-subtitle">Add, edit, or remove multiple criteria efficiently</p>
    </div>

    <form method="POST" action="{{ route('criteria.batchStore', ['case' => $case->id]) }}" id="criteriaBatchForm">
        @csrf
        
        <!-- Add a hidden config element for JavaScript -->
        <div id="js-config" 
             data-initial-count="{{ count($criterias) ?: 1 }}" 
             style="display: none;"></div>
        
        <div class="table-container">
            <table class="batch-table" id="criteriaTable">
                <thead>
                    <tr>
                        <th width="20%">Name</th>
                        <th width="10%">Weight</th>
                        <th width="10%">Type</th>
                        <th width="15%">Preference Function</th>
                        <th width="8%">P</th>
                        <th width="8%">Q</th>
                        <th width="20%">Description</th>
                        <th width="9%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($criterias as $index => $criteria)
                    <tr class="criteria-row" data-id="{{ $criteria->id }}">
                        <td>
                            <input type="hidden" name="criteria[{{ $index }}][id]" value="{{ $criteria->id }}">
                            <input type="text" 
                                   class="form-control table-input" 
                                   name="criteria[{{ $index }}][name]" 
                                   value="{{ $criteria->name }}" 
                                   placeholder="Criteria name" 
                                   required>
                        </td>
                        <td>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   max="1" 
                                   class="form-control table-input" 
                                   name="criteria[{{ $index }}][weight]" 
                                   value="{{ $criteria->weight }}" 
                                   placeholder="0.00" 
                                   required>
                        </td>
                        <td>
                            <select class="form-control table-select" name="criteria[{{ $index }}][type]" required>
                                <option value="benefit" {{ $criteria->type === 'benefit' ? 'selected' : '' }}>Benefit</option>
                                <option value="cost" {{ $criteria->type === 'cost' ? 'selected' : '' }}>Cost</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-control table-select preference-function" name="criteria[{{ $index }}][preference_function]" required>
                                @foreach(\App\Models\Criteria::preferenceFunctions() as $value => $label)
                                    <option value="{{ $value }}" 
                                        {{ $criteria->preference_function == $value ? 'selected' : '' }}
                                        data-needs-p="{{ in_array($value, ['linear', 'level', 'linear_quasi', 'gaussian']) ? '1' : '0' }}"
                                        data-needs-q="{{ in_array($value, ['quasi', 'level', 'linear_quasi']) ? '1' : '0' }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   class="form-control table-input p-field" 
                                   name="criteria[{{ $index }}][p]" 
                                   value="{{ $criteria->p }}"
                                   placeholder="0.00"
                                   {{ in_array($criteria->preference_function, ['linear', 'level', 'linear_quasi', 'gaussian']) ? '' : 'disabled' }}>
                        </td>
                        <td>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   class="form-control table-input q-field"
                                   name="criteria[{{ $index }}][q]" 
                                   value="{{ $criteria->q }}"
                                   placeholder="0.00"
                                   {{ in_array($criteria->preference_function, ['quasi', 'level', 'linear_quasi']) ? '' : 'disabled' }}>
                        </td>
                        <td>
                            <div class="description-container">
                                <textarea class="form-control table-textarea d-none" 
                                          name="criteria[{{ $index }}][description]" 
                                          rows="2">{{ $criteria->description }}</textarea>
                                <button type="button" 
                                        class="btn-icon btn-icon-info edit-description" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#descriptionModal" 
                                        data-index="{{ $index }}"
                                        title="Edit Description">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <button type="button" 
                                    class="btn-icon btn-icon-danger delete-row" 
                                    title="Delete Row">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    
                    @if(count($criterias) === 0)
                    <tr class="criteria-row new-row">
                        <td>
                            <input type="text" 
                                   class="form-control table-input" 
                                   name="criteria[0][name]" 
                                   placeholder="Criteria name" 
                                   required>
                        </td>
                        <td>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   max="1" 
                                   class="form-control table-input" 
                                   name="criteria[0][weight]" 
                                   value="1" 
                                   placeholder="0.00" 
                                   required>
                        </td>
                        <td>
                            <select class="form-control table-select" name="criteria[0][type]" required>
                                <option value="benefit">Benefit</option>
                                <option value="cost">Cost</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-control table-select preference-function" name="criteria[0][preference_function]" required>
                                @foreach(\App\Models\Criteria::preferenceFunctions() as $value => $label)
                                    <option value="{{ $value }}" 
                                        data-needs-p="{{ in_array($value, ['linear', 'level', 'linear_quasi', 'gaussian']) ? '1' : '0' }}"
                                        data-needs-q="{{ in_array($value, ['quasi', 'level', 'linear_quasi']) ? '1' : '0' }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   class="form-control table-input p-field" 
                                   name="criteria[0][p]" 
                                   placeholder="0.00" 
                                   disabled>
                        </td>
                        <td>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   class="form-control table-input q-field"
                                   name="criteria[0][q]" 
                                   placeholder="0.00" 
                                   disabled>
                        </td>
                        <td>
                            <div class="description-container">
                                <textarea class="form-control table-textarea d-none" 
                                          name="criteria[0][description]" 
                                          rows="2"></textarea>
                                <button type="button" 
                                        class="btn-icon btn-icon-info edit-description" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#descriptionModal" 
                                        data-index="0"
                                        title="Edit Description">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </td>
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
                Save All Criteria
            </button>
        </div>
    </form>
</div>

<!-- Description Modal -->
<div class="modal fade" id="descriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i>
                    Edit Description
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="criteriaDescription" class="form-label">Description</label>
                    <textarea class="form-control" 
                              id="criteriaDescription" 
                              rows="4" 
                              placeholder="Describe what this criteria measures and how it should be evaluated..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn-modern btn-primary-modern" id="saveDescription">
                    Save Description
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/criteria/batch.js'])
@endpush
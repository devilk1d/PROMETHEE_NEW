@extends('layouts.app')

@section('title', 'Edit Criteria Value')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
    <h1 class="h3 fw-bold">Edit Criteria Value - {{ $case->name }}</h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">Alternative</label>
                    <p>{{ $alternative->name }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">Criteria</label>
                    <p>
                        {{ $criteria->name }}
                        <span class="badge bg-{{ $criteria->type === 'benefit' ? 'success' : 'danger' }}">
                            {{ ucfirst($criteria->type) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('criteria_values.update', ['case' => $case->id, 'alternative' => $alternative->id, 'criteria' => $criteria->id]) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="value" class="form-label">Value</label>
                <input type="number" step="0.01" class="form-control @error('value') is-invalid @enderror" 
                       id="value" name="value" value="{{ old('value', $value->value ?? 0) }}" required>
                @error('value')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('alternatives.index', ['case' => $case->id]) }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
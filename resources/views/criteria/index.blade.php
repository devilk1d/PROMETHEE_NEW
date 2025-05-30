@extends('layouts.app')

@section('title', 'Criteria - ' . $case->name)

@section('scripts')
    @vite(['resources/js/criteria/index.js'])

@section('styles')
    @vite(['resources/css/criteria/index.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">Criteria Management</h1>
        <p class="main-subtitle">{{ $case->name }} - Define evaluation criteria for decision analysis</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('criteria.batch', ['case' => $case->id]) }}" class="btn-modern btn-secondary-modern">
            <i class="bi bi-list-check"></i>
            Batch Manage
        </a>
        <a href="{{ route('criteria.create', ['case' => $case->id]) }}" class="btn-modern btn-primary-modern">
            <i class="bi bi-plus-circle"></i>
            Add New Criteria
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

<div class="content-card">
    @if(isset($criterias) && count($criterias) > 0)
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 20%">Name</th>
                        <th style="width: 10%">Weight</th>
                        <th style="width: 10%">Type</th>
                        <th style="width: 15%">Preference Function</th>
                        <th>Description</th>
                        <th style="width: 15%" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($criterias as $criteria)
                    <tr>
                        <td>
                            <span class="row-number">{{ $loop->iteration }}</span>
                        </td>
                        <td>
                            <span class="item-name">{{ $criteria->name }}</span>
                        </td>
                        <td>
                            <span class="weight-badge">{{ $criteria->weight }}</span>
                        </td>
                        <td>
                            <span class="badge-modern badge-{{ $criteria->type === 'benefit' ? 'success' : 'danger' }}">
                                {{ ucfirst($criteria->type) }}
                            </span>
                        </td>
                        <td>
                            <div class="function-info">
                                <span class="function-name">{{ \App\Models\Criteria::preferenceFunctions()[$criteria->preference_function] ?? $criteria->preference_function }}</span>
                                @if($criteria->p || $criteria->q)
                                    <small class="function-params">
                                        @if($criteria->p) p:{{ $criteria->p }} @endif
                                        @if($criteria->q) q:{{ $criteria->q }} @endif
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="description-text">{{ Str::limit($criteria->description, 50) ?: 'No description' }}</span>
                        </td>
                        <td class="text-end">
                            <div class="action-buttons">
                                <a href="{{ route('criteria.edit', ['case' => $case->id, 'criterion' => $criteria->id]) }}" class="btn-icon btn-icon-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('criteria.destroy', ['case' => $case->id, 'criterion' => $criteria->id]) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-icon-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-list-check"></i>
            </div>
            <h4 class="empty-title">No criteria found yet</h4>
            <p class="empty-description">Create criteria to define how alternatives will be evaluated in your decision analysis.</p>
            <a href="{{ route('criteria.create', ['case' => $case->id]) }}" class="btn-modern btn-primary-modern">
                <i class="bi bi-plus-circle"></i>
                Add First Criteria
            </a>
        </div>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
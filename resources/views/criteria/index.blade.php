@extends('layouts.app')

@section('title', 'Criteria Management')

@section('styles')
    @vite(['resources/css/criteria/index.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">Criteria Management</h1>
        <p class="main-subtitle">{{ Auth::user()->isAdmin() ? 'Define evaluation criteria for decision analysis' : 'View evaluation criteria' }}</p>
    </div>
    <div class="header-actions">
        @if(Auth::user()->isAdmin())
            <a href="{{ route('criteria.batch') }}" class="btn-modern btn-secondary-modern">
                <i class="bi bi-list-check"></i>
                Batch Manage
            </a>
            <a href="{{ route('criteria.create') }}" class="btn-modern btn-primary-modern">
                <i class="bi bi-plus-circle"></i>
                Add New Criteria
            </a>
        @else
            <div class="role-info">
                <span class="badge-modern badge-info">
                    <i class="bi bi-eye"></i> View Only
                </span>
            </div>
        @endif
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

@if(Auth::user()->isUser())
    <div class="alert alert-info modern-alert" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Information:</strong> As a user, you can view criteria but cannot add, edit, or delete them. You can use these criteria when creating alternatives.
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
                        @if(Auth::user()->isAdmin())
                            <th style="width: 15%" class="text-end">Actions</th>
                        @endif
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
                        @if(Auth::user()->isAdmin())
                        <td class="text-end">
                            <div class="action-buttons">
                                <a href="{{ route('criteria.edit', $criteria->id) }}" class="btn-icon btn-icon-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('criteria.destroy', $criteria->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-icon-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
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
            @if(Auth::user()->isAdmin())
                <p class="empty-description">Create criteria to define how alternatives will be evaluated in your decision analysis.</p>
                <a href="{{ route('criteria.create') }}" class="btn-modern btn-primary-modern">
                    <i class="bi bi-plus-circle"></i>
                    Add First Criteria
                </a>
            @else
                <p class="empty-description">No criteria have been defined yet. Contact your administrator to add criteria.</p>
            @endif
        </div>
    @endif
</div>

@if(Auth::user()->isAdmin())
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endif
@endsection
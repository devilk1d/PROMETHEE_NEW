@extends('layouts.app')

@section('title', 'Case Management')

@section('scripts')
    @vite(['resources/js/cases/index.js'])

@section('styles')
    @vite(['resources/css/cases/index.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">Case Management</h1>
        <p class="main-subtitle">Manage and organize your decision analysis cases</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('cases.create') }}" class="btn-modern btn-primary-modern">
            <i class="bi bi-plus-circle"></i>
            Add New Case
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
    @if(isset($cases) && count($cases) > 0)
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 25%">Name</th>
                        <th>Description</th>
                        <th style="width: 15%">Stats</th>
                        <th style="width: 15%">Last Updated</th>
                        <th style="width: 20%" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cases as $case)
                    <tr>
                        <td>
                            <span class="row-number">{{ $loop->iteration }}</span>
                        </td>
                        <td>
                            <span class="item-name">{{ $case->name }}</span>
                        </td>
                        <td>
                            <span class="description-text">{{ Str::limit($case->description, 100) ?: 'No description provided' }}</span>
                        </td>
                        <td>
                            <div class="stats-column">
                                <div class="stat-item">
                                    <i class="bi bi-list-check stat-icon"></i>
                                    <span class="stat-count">{{ $case->criteria_count ?? 0 }}</span>
                                    <span class="stat-label">Criteria</span>
                                </div>
                                <div class="stat-item">
                                    <i class="bi bi-grid-3x3-gap stat-icon"></i>
                                    <span class="stat-count">{{ $case->alternatives_count ?? 0 }}</span>
                                    <span class="stat-label">Alternatives</span>
                                </div>
                                <div class="stat-item">
                                    <i class="bi bi-file-earmark-bar-graph stat-icon"></i>
                                    <span class="stat-count">{{ $case->decisions_count ?? 0 }}</span>
                                    <span class="stat-label">Decisions</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="date-text">
                                @if($case->updated_at)
                                    {{ $case->updated_at->diffForHumans() }}
                                @else
                                    Recently created
                                @endif
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="action-buttons">
                                <a href="{{ route('cases.show', $case->id) }}" class="btn-icon btn-icon-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('cases.edit', $case->id) }}" class="btn-icon btn-icon-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('cases.destroy', $case->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-icon-danger" title="Delete">
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
                <i class="bi bi-folder"></i>
            </div>
            <h4 class="empty-title">No cases found yet</h4>
            <p class="empty-description">Create your first case to start using the PROMETHEE decision analysis.</p>
            <a href="{{ route('cases.create') }}" class="btn-modern btn-primary-modern">
                <i class="bi bi-plus-circle"></i>
                Create First Case
            </a>
        </div>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection
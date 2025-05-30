@extends('layouts.app')

@section('title', 'Alternatives - ' . $case->name)

@section('scripts')
    @vite(['resources/js/alternatives/index.js'])

@section('styles')
    @vite(['resources/css/alternatives/index.css'])

@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">Alternatives Management</h1>
        <p class="main-subtitle">{{ $case->name }} - Define decision alternatives for evaluation</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('alternatives.batch', ['case' => $case->id]) }}" class="btn-modern btn-secondary-modern">
            <i class="bi bi-list-check"></i>
            Batch Manage
        </a>
        <a href="{{ route('alternatives.create', ['case' => $case->id]) }}" class="btn-modern btn-primary-modern">
            <i class="bi bi-plus-circle"></i>
            Add New Alternative
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
    @if(isset($alternatives) && count($alternatives) > 0)
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 25%">Name</th>
                        <th>Description</th>
                        <th style="width: 20%" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alternatives as $alternative)
                    <tr>
                        <td>
                            <span class="row-number">{{ $loop->iteration }}</span>
                        </td>
                        <td>
                            <span class="item-name">{{ $alternative->name }}</span>
                        </td>
                        <td>
                            <span class="description-text">{{ Str::limit($alternative->description, 100) ?: 'No description provided' }}</span>
                        </td>
                        <td class="text-end">
                            <div class="action-buttons">
                                <a href="{{ route('alternatives.edit', ['case' => $case->id, 'alternative' => $alternative->id]) }}" class="btn-icon btn-icon-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('alternatives.destroy', ['case' => $case->id, 'alternative' => $alternative->id]) }}" method="POST" class="d-inline delete-form">
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
                <i class="bi bi-grid-3x3-gap"></i>
            </div>
            <h4 class="empty-title">No alternatives found yet</h4>
            <p class="empty-description">Create alternatives that represent the different options you want to evaluate in your decision analysis.</p>
            <a href="{{ route('alternatives.create', ['case' => $case->id]) }}" class="btn-modern btn-primary-modern">
                <i class="bi bi-plus-circle"></i>
                Add First Alternative
            </a>
        </div>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
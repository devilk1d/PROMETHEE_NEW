@extends('layouts.app')

@section('title', 'Profile Settings')

@section('styles')
    @vite(['resources/css/profile/edit.css'])


@section('content')
<!-- Header -->
<div class="main-header">
    <div>
        <h1 class="main-title">Profile Settings</h1>
        <p class="main-subtitle">Manage your account information and security settings</p>
    </div>
</div>

<!-- Profile Layout -->
<div class="profile-layout">
    <!-- Left Column - Profile Info -->
    <div class="profile-column">
        <div class="profile-card">
            <div class="profile-avatar-section">
                <div class="profile-avatar">
                    <span class="avatar-text">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                <div class="profile-info">
                    <h3 class="profile-name">{{ $user->name }}</h3>
                    <p class="profile-email">{{ $user->email }}</p>
                    <p class="profile-member-since">
                        <i class="bi bi-calendar3"></i>
                        Member since {{ $user->created_at->format('M Y') }}
                    </p>
                </div>
            </div>

            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="bi bi-folder"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value">{{ $alternativeCount }}</span>
                        <span class="stat-label">Alternatives Created</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value">{{ $decisionCount }}</span>
                        <span class="stat-label">Analyses Run</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Settings Forms -->
    <div class="settings-column">
        <!-- Profile Information Form -->
        <div class="settings-card">
            <h3 class="section-title">
                <i class="bi bi-person"></i>
                Profile Information
            </h3>
            
            <form method="post" action="{{ route('profile.update') }}" class="settings-form">
                @csrf
                @method('patch')

                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="bi bi-person label-icon"></i>
                        Full Name <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}" 
                           required>
                    @error('name')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope label-icon"></i>
                        Email Address <span class="required">*</span>
                    </label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}" 
                           required>
                    @error('email')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="verification-notice">
                            <i class="bi bi-exclamation-triangle"></i>
                            <div class="notice-content">
                                <span>Your email address is unverified.</span>
                                <button form="send-verification" class="verify-link">
                                    Click here to re-send the verification email.
                                </button>
                            </div>
                        </div>

                        @if (session('status') === 'verification-link-sent')
                            <div class="success-notice">
                                <i class="bi bi-check-circle"></i>
                                A new verification link has been sent to your email address.
                            </div>
                        @endif
                    @endif
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="bi bi-save"></i>
                        Save Changes
                    </button>
                </div>

                @if (session('status') === 'profile-updated')
                    <div class="success-notice">
                        <i class="bi bi-check-circle"></i>
                        Profile updated successfully.
                    </div>
                @endif
            </form>
        </div>

        <!-- Password Update Form -->
        <div class="settings-card">
            <h3 class="section-title">
                <i class="bi bi-shield-lock"></i>
                Update Password
            </h3>
            
            <form method="post" action="{{ route('password.update') }}" class="settings-form">
                @csrf
                @method('put')

                <div class="form-group">
                    <label for="current_password" class="form-label">
                        <i class="bi bi-lock label-icon"></i>
                        Current Password <span class="required">*</span>
                    </label>
                    <input type="password" 
                           class="form-control @error('current_password') is-invalid @enderror" 
                           id="current_password" 
                           name="current_password">
                    @error('current_password')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="bi bi-key label-icon"></i>
                        New Password <span class="required">*</span>
                    </label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password">
                    @error('password')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                    <div class="form-hint">
                        Password must be at least 8 characters long and contain a mix of letters and numbers
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        <i class="bi bi-key-fill label-icon"></i>
                        Confirm Password <span class="required">*</span>
                    </label>
                    <input type="password" 
                           class="form-control @error('password_confirmation') is-invalid @enderror" 
                           id="password_confirmation" 
                           name="password_confirmation">
                    @error('password_confirmation')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="bi bi-shield-lock"></i>
                        Update Password
                    </button>
                </div>

                @if (session('status') === 'password-updated')
                    <div class="success-notice">
                        <i class="bi bi-check-circle"></i>
                        Password updated successfully.
                    </div>
                @endif
            </form>
        </div>

        <!-- Danger Zone -->
        <div class="settings-card danger-card">
            <h3 class="section-title danger-title">
                <i class="bi bi-exclamation-triangle"></i>
                Danger Zone
            </h3>
            
            <div class="danger-content">
                <div class="danger-info">
                    <h4 class="danger-heading">Delete Account</h4>
                    <p class="danger-description">
                        Once your account is deleted, all of its resources and data will be permanently deleted. 
                        Before deleting your account, please download any data or information that you wish to retain.
                    </p>
                </div>

                <button type="button" 
                        class="btn-modern btn-danger-modern" 
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteAccountModal">
                    <i class="bi bi-trash"></i>
                    Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                    Are you sure you want to delete your account?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-warning">
                    <p>Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.</p>
                </div>

                <form method="post" action="{{ route('profile.destroy') }}" id="deleteAccountForm">
                    @csrf
                    @method('delete')

                    <div class="form-group">
                        <label for="password_delete" class="form-label">
                            <i class="bi bi-lock label-icon"></i>
                            Password <span class="required">*</span>
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password_delete" 
                               name="password" 
                               placeholder="Enter your password to confirm"
                               required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="submit" form="deleteAccountForm" class="btn-modern btn-danger-modern">
                    <i class="bi bi-trash"></i>
                    Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Verification Email Form -->
@if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
    <form id="send-verification" method="post" action="{{ route('verification.send') }}" style="display: none;">
        @csrf
    </form>
@endif
@endsection
@extends('layouts.app')

@section('title', 'Profile – Pump Endless')

@section('content')
<div class="section" style="max-width: 720px; padding-top: 20px;">
    <div class="widget-card" style="margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
            <div style="position: relative;">
                @if($user->avatar && file_exists(public_path($user->avatar)))
                    <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);">
                @else
                    <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(to bottom right, var(--accent-green), #065f46); display: grid; place-items: center; font-size: 1.8rem; font-weight: 800; color: #000;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div style="flex: 1; min-width: 200px;">
                <div style="font-size: 1.4rem; font-weight: 800;">{{ $user->name }}</div>
                <div style="color: var(--text-muted); font-size: 0.85rem; font-family: var(--font-mono);">{{ $user->email }}</div>
                <div style="color: var(--text-muted); font-size: 0.8rem; font-family: var(--font-mono); margin-top: 4px;">
                    Wallet: {{ $user->wallet_address ?? 'No wallet set' }}
                </div>
            </div>
        </div>
    </div>

    <div class="widget-card" style="margin-bottom: 20px;">
        <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 18px;"><i class="fa-solid fa-user-pen"></i> Profile Picture</h3>

        <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="input-group">
                <label class="input-label">Upload New Picture</label>
                <div class="input-field-wrap" style="padding: 8px 14px;">
                    <input type="file" name="avatar" accept="image/*" required>
                </div>
            </div>
            @error('avatar')
                <div style="color: var(--accent-red); font-size: 0.8rem; margin: 8px 0;">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px; margin-top: 6px;">Upload Picture</button>
        </form>
    </div>

    <div class="widget-card" style="margin-bottom: 20px;">
        <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 18px;"><i class="fa-solid fa-id-card"></i> Account Details</h3>

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf

            <div class="input-group">
                <label class="input-label">Display Name</label>
                <div class="input-field-wrap">
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="50">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Email Address</label>
                <div class="input-field-wrap">
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Wallet Address</label>
                <div class="input-field-wrap">
                    <input type="text" name="wallet_address" value="{{ old('wallet_address', $user->wallet_address) }}" maxlength="100" style="font-size: 0.85rem;">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Current Password <span style="color: var(--text-muted); font-weight: 400;">(required to save changes)</span></label>
                <div class="input-field-wrap">
                    <input type="password" name="current_password" required autocomplete="current-password">
                </div>
            </div>

            @error('current_password')
                <div style="color: var(--accent-red); font-size: 0.8rem; margin: 8px 0;">{{ $message }}</div>
            @enderror
            @error('email')
                <div style="color: var(--accent-red); font-size: 0.8rem; margin: 8px 0;">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-primary" style="padding: 12px 24px; margin-top: 6px;">Save Changes</button>
        </form>
    </div>

    <div class="widget-card">
        <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 18px;"><i class="fa-solid fa-lock"></i> Change Password</h3>

        <form action="{{ route('profile.password') }}" method="POST">
            @csrf

            <div class="input-group">
                <label class="input-label">Current Password</label>
                <div class="input-field-wrap">
                    <input type="password" name="current_password" required autocomplete="current-password">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">New Password</label>
                <div class="input-field-wrap">
                    <input type="password" name="password" required autocomplete="new-password" minlength="8">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Confirm New Password</label>
                <div class="input-field-wrap">
                    <input type="password" name="password_confirmation" required autocomplete="new-password" minlength="8">
                </div>
            </div>

            @error('password')
                <div style="color: var(--accent-red); font-size: 0.8rem; margin: 8px 0;">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-primary" style="padding: 12px 24px; margin-top: 6px;">Update Password</button>
        </form>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Sign In – Pump Endless')

@section('content')
<div class="section" style="max-width: 480px; padding-top: 40px;">
    <div class="widget-card" style="padding: 32px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 2.5rem; margin-bottom: 8px;"><i class="fa-solid fa-rocket"></i></div>
            <h1 style="font-size: 1.8rem; font-weight: 800;">Welcome Back</h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Sign in to your Pump Endless trader account</p>
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="input-group">
                <label class="input-label">Email Address</label>
                <div class="input-field-wrap">
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Password</label>
                <div class="input-field-wrap">
                    <input type="password" name="password" required autocomplete="current-password">
                </div>
            </div>

            @error('email')
                <div style="color: var(--accent-red); font-size: 0.85rem; margin-bottom: 12px;">{{ $message }}</div>
            @enderror

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; font-size: 0.85rem;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: var(--radius-md);">
                Sign In
            </button>
        </form>

        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); text-align: center;">
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Don't have an account? <a href="{{ route('register') }}" style="color: var(--accent-green); font-weight: 600;">Sign up</a>
            </div>
        </div>
    </div>
</div>
@endsection
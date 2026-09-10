@extends('layouts.app')

@section('title', 'Create Account – Pump Endless')

@section('content')
<div class="section" style="max-width: 480px; padding-top: 40px;">
    <div class="widget-card" style="padding: 32px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 2.5rem; margin-bottom: 8px;"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
            <h1 style="font-size: 1.8rem; font-weight: 800;">Create Account</h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Join Pump Endless and start trading meme coins</p>
        </div>

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="input-group">
                <label class="input-label">Display Name</label>
                <div class="input-field-wrap">
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. MoonDegen" required autofocus>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Email Address</label>
                <div class="input-field-wrap">
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="trader@example.com" required autocomplete="email">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Password</label>
                <div class="input-field-wrap">
                    <input type="password" name="password" required autocomplete="new-password" minlength="8">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Confirm Password</label>
                <div class="input-field-wrap">
                    <input type="password" name="password_confirmation" required autocomplete="new-password" minlength="8">
                </div>
            </div>

            @if($errors->any())
                <div style="color: var(--accent-red); font-size: 0.85rem; margin-bottom: 12px;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: var(--radius-md); margin-top: 10px;">
                Create Account
            </button>
        </form>

        <div style="margin-top: 20px; text-align: center; font-size: 0.85rem; color: var(--text-secondary);">
            Already have an account? <a href="{{ route('login') }}" style="color: var(--accent-green); font-weight: 600;">Sign in</a>
        </div>
    </div>
</div>
@endsection
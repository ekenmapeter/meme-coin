<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Login – Pump Endless</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #07090e;">

    <div class="widget-card" style="width: 100%; max-width: 440px; padding: 36px; box-shadow: 0 20px 50px rgba(0,0,0,0.8); border-color: rgba(0, 240, 118, 0.3);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div class="brand-icon" style="margin: 0 auto 16px; width: 48px; height: 48px; font-size: 1.5rem;">👑</div>
            <h1 style="font-size: 1.8rem; font-weight: 800; letter-spacing: -0.5px;">Admin Control Center</h1>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">Pump Endless Management Portal</p>
        </div>

        @if(session('error'))
            <div class="alert alert-error" style="font-size: 0.85rem; padding: 10px 14px;">
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-error" style="font-size: 0.85rem; padding: 10px 14px;">
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf

            <div class="input-group">
                <label class="input-label">Administrator Email</label>
                <div class="input-field-wrap">
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Password</label>
                <div class="input-field-wrap">
                    <input type="password" name="password" required autocomplete="current-password">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; cursor: pointer; color: var(--text-muted);">
                    <input type="checkbox" name="remember">
                    <span>Keep admin session active</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: var(--radius-md);">
                Authenticate & Enter Dashboard →
            </button>
        </form>

        <div style="margin-top: 24px; text-align: center;">
            <a href="{{ route('home') }}" style="color: var(--text-muted); font-size: 0.85rem;">← Return to Main Website</a>
        </div>
    </div>

</body>
</html>

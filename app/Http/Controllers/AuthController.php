<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->intended(
                Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard')
            );
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (Auth::user()->isSuspended()) {
                Auth::logout();

                return back()->with('error', 'Your account has been suspended. Please contact support.');
            }

            $request->session()->regenerate();

            return redirect()->intended(
                Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard')
            );
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->intended(
                Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard')
            );
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::default()],
        ]);

        $wallet = '0x'.Str::random(16);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'wallet_address' => $wallet,
            'role' => User::ROLE_USER,
        ]);

        // New accounts start with no funds; balances are only granted
        // via confirmed deposits or admin balance adjustments.
        $user->sol_balance = 0.0;
        $user->btc_balance = 0.0;
        $user->usd_balance = 0.0;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Account created! Welcome to Pump Endless.');
    }

    public function showAdminLogin()
    {
        if (Auth::check()) {
            return redirect()->intended(
                Auth::user()->isAdmin() ? route('admin.dashboard') : route('home')
            );
        }

        return view('admin.login');
    }

    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (Auth::user()->isSuspended()) {
                Auth::logout();

                return back()->with('error', 'Your administrator account has been suspended.');
            }

            $request->session()->regenerate();

            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            Auth::logout();

            return back()->with('error', 'You are not authorized to access the Admin Panel.');
        }

        return back()->withErrors([
            'email' => 'Invalid administrator credentials.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}

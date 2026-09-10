<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    protected const ALLOWED_AVATARS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    public function show()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:50',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'wallet_address' => 'nullable|string|max:100',
            'current_password' => 'required|string',
        ]);

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
        }

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->wallet_address = $request->input('wallet_address');
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('success', 'Password changed successfully.');
    }

    public function updateAvatar(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'avatar' => 'required|image|mimes:'.implode(',', self::ALLOWED_AVATARS).'|max:2048',
        ]);

        $directory = public_path('images/avatars');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = $request->file('avatar')->guessExtension();
        if (! in_array($extension, self::ALLOWED_AVATARS, true)) {
            $extension = 'png';
        }

        $fileName = 'avatar_'.$user->id.'_'.time().'.'.$extension;
        $request->file('avatar')->move($directory, $fileName);

        if ($user->avatar && is_file(public_path($user->avatar))) {
            @unlink(public_path($user->avatar));
        }

        $user->avatar = 'images/avatars/'.$fileName;
        $user->save();

        return back()->with('success', 'Profile picture updated successfully.');
    }
}

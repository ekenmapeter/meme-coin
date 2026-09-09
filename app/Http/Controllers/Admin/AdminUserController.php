<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::withCount(['holdings', 'deposits', 'withdrawals', 'swaps'])
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $user->load(['holdings.coin', 'deposits', 'withdrawals', 'swaps.coin']);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:'.User::ROLE_USER.','.User::ROLE_ADMIN],
        ]);

        // Never allow an admin to demote themselves (would lock the panel out).
        if ($user->is(Auth::user()) && $request->input('role') !== User::ROLE_ADMIN) {
            return back()->with('error', 'You cannot remove your own administrator role.');
        }

        $old = ['name' => $user->name, 'email' => $user->email, 'role' => $user->role];

        $user->name = $request->input('name');
        $user->email = strtolower($request->input('email'));
        $user->role = $request->input('role');
        $user->save();

        AuditLogger::record('user.updated', $user, ['from' => $old, 'to' => ['name' => $user->name, 'email' => $user->email, 'role' => $user->role]]);

        return back()->with('success', "User {$user->name} updated successfully.");
    }

    public function updateBalance(Request $request, User $user)
    {
        $request->validate([
            'btc_balance' => 'required|numeric|min:0',
            'sol_balance' => 'required|numeric|min:0',
            'usd_balance' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $user) {
            $old = [
                'btc_balance' => $user->btc_balance,
                'sol_balance' => $user->sol_balance,
                'usd_balance' => $user->usd_balance,
            ];

            $user->btc_balance = (float) $request->input('btc_balance');
            $user->sol_balance = (float) $request->input('sol_balance');
            $user->usd_balance = (float) $request->input('usd_balance');
            $user->save();

            AuditLogger::record('user.balance.updated', $user, [
                'from' => $old,
                'to' => [
                    'btc_balance' => $user->btc_balance,
                    'sol_balance' => $user->sol_balance,
                    'usd_balance' => $user->usd_balance,
                ],
            ]);
        });

        return back()->with('success', "Balances for user {$user->name} updated successfully.");
    }

    public function toggleSuspend(User $user)
    {
        // Prevent suspending yourself.
        if ($user->is(Auth::user())) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        $user->is_suspended = ! $user->is_suspended;
        $user->save();

        AuditLogger::record($user->is_suspended ? 'user.suspended' : 'user.unsuspended', $user);

        $action = $user->is_suspended ? 'suspended' : 'unsuspended';

        return back()->with('success', "User {$user->name} has been {$action}.");
    }

    public function toggleRestrict(User $user)
    {
        $user->is_restricted = ! $user->is_restricted;
        $user->save();

        AuditLogger::record($user->is_restricted ? 'user.restricted' : 'user.unrestricted', $user);

        $action = $user->is_restricted ? 'restricted from trading' : 'unrestricted';

        return back()->with('success', "User {$user->name} has been {$action}.");
    }

    public function destroy(User $user)
    {
        if ($user->is(Auth::user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isAdmin()) {
            return back()->with('error', 'Administrator accounts cannot be deleted from the panel. Demote the role first.');
        }

        $name = $user->name;
        $user->delete();

        AuditLogger::record('user.deleted', null, ['user_id' => $user->id, 'name' => $name, 'email' => $user->email]);

        return redirect()->route('admin.users.index')->with('success', "User {$name} has been deleted.");
    }
}

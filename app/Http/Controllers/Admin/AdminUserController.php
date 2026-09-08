<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}

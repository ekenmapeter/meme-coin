<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\DepositMethod;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDepositController extends Controller
{
    /**
     * Maps a deposit currency to the user balance column it is credited to.
     */
    protected const BALANCE_MAP = [
        'BTC' => 'btc_balance',
        'SOL' => 'sol_balance',
    ];

    /**
     * Strict one-way state machine: pending -> confirmed | rejected.
     */
    protected const TRANSITIONS = [
        'pending' => ['confirmed', 'rejected'],
    ];

    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Deposit::with(['user', 'depositMethod'])->latest();
        if ($status && in_array($status, ['pending', 'confirmed', 'rejected'])) {
            $query->where('status', $status);
        }

        $deposits = $query->paginate(15);
        $pendingCount = Deposit::where('status', 'pending')->count();
        $confirmedCount = Deposit::where('status', 'confirmed')->count();
        $rejectedCount = Deposit::where('status', 'rejected')->count();

        $depositMethods = DepositMethod::all();

        return view('admin.deposits.index', compact(
            'deposits',
            'status',
            'pendingCount',
            'confirmedCount',
            'rejectedCount',
            'depositMethods'
        ));
    }

    public function updateStatus(Request $request, Deposit $deposit)
    {
        $request->validate([
            'status' => 'required|in:confirmed,rejected',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $newStatus = $request->input('status');
        $oldStatus = $deposit->status;

        if (! in_array($newStatus, self::TRANSITIONS[$oldStatus] ?? [], true)) {
            return back()->with('error', "Deposit #{$deposit->id} cannot transition from '{$oldStatus}' to '{$newStatus}'.");
        }

        if ($newStatus === 'confirmed' && ! isset(self::BALANCE_MAP[$deposit->currency])) {
            return back()->with('error', "Unsupported deposit currency '{$deposit->currency}'. No balance is mapped for it.");
        }

        DB::transaction(function () use ($deposit, $newStatus, $oldStatus, $request) {
            // Lock the deposit row so concurrent requests cannot double-process it.
            $locked = Deposit::whereKey($deposit->getKey())->lockForUpdate()->firstOrFail();
            $user = $locked->user()->lockForUpdate()->firstOrFail();

            if ($newStatus === 'confirmed') {
                $user->increment(self::BALANCE_MAP[$locked->currency], $locked->amount);
            }

            $locked->status = $newStatus;
            $locked->admin_notes = $request->input('admin_notes');
            $locked->save();

            AuditLogger::record('deposit.status.updated', $locked, [
                'from' => $oldStatus,
                'to' => $newStatus,
                'currency' => $locked->currency,
                'amount' => $locked->amount,
            ]);
        });

        return back()->with('success', "Deposit #{$deposit->id} marked as ".ucfirst($newStatus).'.');
    }

    public function updateMethod(Request $request, DepositMethod $depositMethod)
    {
        $request->validate([
            'wallet_address' => 'required|string|max:255',
            'min_deposit' => 'required|string|max:50',
            'notes' => 'nullable|string|max:2000',
        ]);

        $depositMethod->wallet_address = $request->input('wallet_address');
        $depositMethod->min_deposit = $request->input('min_deposit');
        $depositMethod->notes = $request->input('notes');
        $depositMethod->is_active = $request->boolean('is_active');
        $depositMethod->save();

        AuditLogger::record('deposit_method.updated', $depositMethod, [
            'currency' => $depositMethod->currency,
            'is_active' => $depositMethod->is_active,
        ]);

        return back()->with('success', "Deposit address for {$depositMethod->currency} updated successfully.");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWithdrawalController extends Controller
{
    /**
     * Strict one-way state machine.
     *
     * pending  -> approved | rejected (refund)
     * approved -> completed (txid required) | rejected (refund)
     * completed / rejected are terminal.
     */
    protected const TRANSITIONS = [
        'pending' => ['approved', 'rejected'],
        'approved' => ['completed', 'rejected'],
        'completed' => [],
        'rejected' => [],
    ];

    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Withdrawal::with('user')->latest();
        if ($status && in_array($status, ['pending', 'approved', 'completed', 'rejected'])) {
            $query->where('status', $status);
        }

        $withdrawals = $query->paginate(15);
        $pendingCount = Withdrawal::where('status', 'pending')->count();
        $approvedCount = Withdrawal::where('status', 'approved')->count();
        $completedCount = Withdrawal::where('status', 'completed')->count();
        $rejectedCount = Withdrawal::where('status', 'rejected')->count();

        return view('admin.withdrawals.index', compact(
            'withdrawals',
            'status',
            'pendingCount',
            'approvedCount',
            'completedCount',
            'rejectedCount'
        ));
    }

    public function updateStatus(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'status' => 'required|in:approved,completed,rejected',
            'txid' => 'nullable|string|max:120',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $newStatus = $request->input('status');
        $oldStatus = $withdrawal->status;

        if (! in_array($newStatus, self::TRANSITIONS[$oldStatus] ?? [], true)) {
            return back()->with('error', "Withdrawal #{$withdrawal->id} cannot transition from '{$oldStatus}' to '{$newStatus}'.");
        }

        if ($newStatus === 'completed' && ! $request->filled('txid')) {
            return back()->with('error', "Withdrawal #{$withdrawal->id} cannot be completed without a transaction ID (txid).");
        }

        DB::transaction(function () use ($withdrawal, $newStatus, $oldStatus, $request) {
            // Lock the withdrawal row so concurrent requests cannot double-refund it.
            $locked = Withdrawal::whereKey($withdrawal->getKey())->lockForUpdate()->firstOrFail();
            $user = $locked->user()->lockForUpdate()->firstOrFail();

            // Refund only when moving from a non-terminal state into rejected.
            if ($newStatus === 'rejected') {
                $user->increment('btc_balance', $locked->amount);
            }

            $locked->status = $newStatus;
            if ($request->filled('txid')) {
                $locked->txid = $request->input('txid');
            }
            $locked->admin_notes = $request->input('admin_notes');
            $locked->save();

            AuditLogger::record('withdrawal.status.updated', $locked, [
                'from' => $oldStatus,
                'to' => $newStatus,
                'amount' => $locked->amount,
                'txid' => $locked->txid,
            ]);
        });

        return back()->with('success', "Withdrawal #{$withdrawal->id} status updated to ".ucfirst($newStatus).'.');
    }
}

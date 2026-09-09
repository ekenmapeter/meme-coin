<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('actor')->latest();

        if ($event = $request->query('event')) {
            $query->where('event', 'like', "%{$event}%");
        }

        $logs = $query->paginate(25);
        $events = AuditLog::query()->distinct()->pluck('event')->sort()->values();

        return view('admin.audit-logs.index', compact('logs', 'events', 'event'));
    }
}

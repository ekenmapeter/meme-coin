<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Records a lightweight audit trail entry for privileged actions.
     */
    public static function record(string $event, mixed $subject = null, array $meta = []): void
    {
        AuditLog::create([
            'actor_id' => Auth::id(),
            'actor_role' => Auth::user()?->role,
            'event' => $event,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'meta' => $meta ?: null,
            'ip_address' => Request::ip(),
        ]);
    }
}

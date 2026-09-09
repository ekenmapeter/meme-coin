@extends('layouts.admin')

@section('title', 'Audit Logs – Pump Endless Admin')
@section('header_title', 'Administrator Audit Log')

@section('content')

<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 24px;">
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary btn-sm {{ empty($event) ? 'btn-primary' : '' }}">All Events</a>
        @foreach($events as $e)
            <a href="{{ route('admin.audit-logs.index', ['event' => $e]) }}" class="btn btn-secondary btn-sm {{ $event === $e ? 'btn-primary' : '' }}">
                {{ str_replace('_', ' ', $e) }}
            </a>
        @endforeach
    </div>
    <div style="color: var(--text-muted); font-size: 0.85rem;">
        <i class="fa-solid fa-circle-info"></i> Every privileged action by administrators is recorded here.
    </div>
</div>

<div class="widget-card">
    <div style="overflow-x: auto;">
        <table class="trades-table">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Administrator</th>
                    <th>Event</th>
                    <th>Subject</th>
                    <th>Details</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="color: var(--text-muted); font-size: 0.8rem; white-space: nowrap;">
                            {{ $log->created_at->format('M d, H:i:s') }}
                            <div style="font-size: 0.7rem;">{{ $log->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 700; font-size: 0.9rem;">{{ $log->actor->name ?? 'System' }}</div>
                            <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $log->actor->email ?? '—' }}</div>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ str_replace('_', ' ', $log->event) }}</span>
                        </td>
                        <td style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-muted);">
                            @if($log->subject_id)
                                {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                            @else
                                —
                            @endif
                        </td>
                        <td style="font-size: 0.8rem; max-width: 320px;">
                            @if($log->meta)
                                <span style="color: var(--text-secondary); font-family: var(--font-mono); font-size: 0.75rem;">
                                    {{ collect($log->meta)->map(fn ($v, $k) => "$k: " . (is_scalar($v) ? $v : json_encode($v)))->implode(' • ') }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted);">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            No audit entries found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $logs->links() }}
    </div>
</div>

@endsection
@extends('layouts.admin')

@section('title', 'Coins Management – Pump Endless Admin')
@section('header_title', 'Coins Management')

@section('content')

<div class="widget-card">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
        <div>
            <h3 style="font-size: 1.25rem; font-weight: 800;">All Meme Coins</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Manage prices, simulated bonding curves, auto movements, and featured badges.</p>
        </div>

        <a href="{{ route('admin.coins.create') }}" class="btn btn-primary">
            + Create New Coin
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table class="trades-table">
            <thead>
                <tr>
                    <th>Coin</th>
                    <th>Price</th>
                    <th>24h Change</th>
                    <th>Market Cap</th>
                    <th>Simulation Mode</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($coins as $coin)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="{{ $coin->logo_url }}" alt="{{ $coin->name }}" style="width: 32px; height: 32px; border-radius: 50%;" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                                <div>
                                    <div style="font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                        <span>{{ $coin->name }}</span>
                                        @if($coin->is_featured)
                                            <span style="color: #f59e0b; font-size: 0.8rem;" title="Featured"><i class="fa-solid fa-star"></i></span>
                                        @endif
                                    </div>
                                    <div style="color: var(--text-muted); font-size: 0.75rem;">{{ $coin->ticker }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-family: var(--font-mono); font-weight: 700;">
                            {{ $coin->formatted_price }}
                        </td>
                        <td>
                            <span class="badge-change {{ $coin->change_24h >= 0 ? 'up' : 'down' }}">
                                {{ $coin->change_24h >= 0 ? '+' : '' }}{{ number_format($coin->change_24h, 2) }}%
                            </span>
                        </td>
                        <td style="font-family: var(--font-mono);">{{ $coin->formatted_market_cap }}</td>
                        <td>
                            <span class="badge {{ $coin->price_movement_mode === 'auto_up' ? 'badge-success' : ($coin->price_movement_mode === 'auto_down' ? 'badge-danger' : ($coin->price_movement_mode === 'auto_volatile' ? 'badge-info' : 'badge-warning')) }}">
                                {{ str_replace('_', ' ', strtoupper($coin->price_movement_mode)) }}
                            </span>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                {{ $coin->auto_step_percent }}% / {{ $coin->auto_interval_seconds }}s
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $coin->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $coin->is_active ? 'Active' : 'Paused' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px; align-items: center;">
                                <a href="{{ route('admin.coins.edit', $coin->id) }}" class="btn btn-secondary btn-sm" style="padding: 5px 10px;">
                                    Control & Edit
                                </a>
                                <form action="{{ route('admin.coins.togglePause', $coin->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-sm" style="padding: 5px 8px;" title="Pause/Resume">
                                        {{ $coin->is_active ? '⏸' : '▶' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.coins.deactivate', $coin->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Deactivate {{ $coin->ticker }}? Users keep their holdings but new trades are blocked. Use Pause to keep it visible but static.');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding: 5px 8px;" title="Deactivate (block new trades)"><i class="fa-solid fa-ban"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $coins->links() }}
    </div>
</div>

@endsection

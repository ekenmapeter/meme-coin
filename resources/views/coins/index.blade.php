@extends('layouts.app')

@section('title', 'All Coins – ' . \App\Models\PlatformSetting::get('site_name', 'Pump Endless'))

@section('content')

<div class="section">
    <div class="section-header" style="flex-wrap: wrap; gap: 14px;">
        <h2 class="section-title">
            <span><i class="fa-solid fa-layer-group"></i></span>
            <span>All Coins</span>
        </h2>
        <div style="color: var(--text-muted); font-size: 0.85rem;">
            {{ $coins->total() }} active markets • live simulated prices
        </div>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow-x: auto;">
        <table class="trades-table" style="min-width: 700px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Coin</th>
                    <th>Price</th>
                    <th>24h Change</th>
                    <th>Market Cap</th>
                    <th>24h Volume</th>
                    <th>Holders</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($coins as $i => $coin)
                    <tr data-ticker="{{ $coin->ticker }}">
                        <td style="font-family: var(--font-mono); color: var(--text-muted);">
                            {{ $coins->firstItem() + $i }}
                        </td>
                        <td>
                            <a href="{{ route('coins.show', $coin->ticker) }}" style="display: flex; align-items: center; gap: 12px;">
                                <img src="{{ $coin->logo_url }}" alt="{{ $coin->name }}" style="width: 32px; height: 32px; border-radius: 50%;" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                                <div>
                                    <div style="font-weight: 700;">{{ $coin->name }}</div>
                                    <div style="color: var(--text-muted); font-size: 0.75rem;">{{ $coin->ticker }}</div>
                                </div>
                            </a>
                        </td>
                        <td style="font-family: var(--font-mono); font-weight: 700;">{{ $coin->formatted_price }}</td>
                        <td>
                            <span class="badge-change {{ $coin->change_24h >= 0 ? 'up' : 'down' }}">
                                {{ $coin->change_24h >= 0 ? '+' : '' }}{{ number_format($coin->change_24h, 2) }}%
                            </span>
                        </td>
                        <td style="font-family: var(--font-mono);">{{ $coin->formatted_market_cap }}</td>
                        <td style="font-family: var(--font-mono);">{{ $coin->formatted_volume }}</td>
                        <td style="font-family: var(--font-mono);">{{ $coin->formatted_holders }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('coins.show', $coin->ticker) }}" class="btn btn-primary btn-sm">Trade</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 24px;">
        {{ $coins->links() }}
    </div>
</div>

@endsection
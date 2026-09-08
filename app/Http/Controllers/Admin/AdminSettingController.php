<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'swap_fee_percent' => PlatformSetting::get('swap_fee_percent', '1.00'),
            'btc_withdrawal_fee' => PlatformSetting::get('btc_withdrawal_fee', '0.000300'),
            'btc_usd_price' => PlatformSetting::get('btc_usd_price', '66450.00'),
            'sol_usd_price' => PlatformSetting::get('sol_usd_price', '142.50'),
            'platform_name' => PlatformSetting::get('platform_name', 'Pump Endless'),
            'platform_announcement' => PlatformSetting::get('platform_announcement', 'Welcome to Pump Endless Demo Platform!'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'swap_fee_percent' => 'required|numeric|min:0|max:50',
            'btc_withdrawal_fee' => 'required|numeric|min:0',
            'btc_usd_price' => 'required|numeric|gt:0',
            'sol_usd_price' => 'required|numeric|gt:0',
            'platform_name' => 'required|string|max:50',
            'platform_announcement' => 'nullable|string',
        ]);

        PlatformSetting::set('swap_fee_percent', $request->input('swap_fee_percent'));
        PlatformSetting::set('btc_withdrawal_fee', $request->input('btc_withdrawal_fee'));
        PlatformSetting::set('btc_usd_price', $request->input('btc_usd_price'));
        PlatformSetting::set('sol_usd_price', $request->input('sol_usd_price'));
        PlatformSetting::set('platform_name', $request->input('platform_name'));
        PlatformSetting::set('platform_announcement', $request->input('platform_announcement'));

        AuditLogger::record('settings.updated', null, $request->only([
            'swap_fee_percent',
            'btc_withdrawal_fee',
            'btc_usd_price',
            'sol_usd_price',
            'platform_name',
        ]));

        return back()->with('success', 'Platform settings and fees updated successfully.');
    }
}

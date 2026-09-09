<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    protected const ALLOWED_BRANDING = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    public function index()
    {
        $settings = [
            'swap_fee_percent' => PlatformSetting::get('swap_fee_percent', '1.00'),
            'btc_withdrawal_fee' => PlatformSetting::get('btc_withdrawal_fee', '0.000300'),
            'btc_usd_price' => PlatformSetting::get('btc_usd_price', '66450.00'),
            'sol_usd_price' => PlatformSetting::get('sol_usd_price', '142.50'),
            'platform_name' => PlatformSetting::get('platform_name', 'Pump Endless'),
            'platform_announcement' => PlatformSetting::get('platform_announcement', 'Welcome to Pump Endless Demo Platform!'),
            'site_name' => PlatformSetting::get('site_name', 'Pump Endless'),
            'site_description' => PlatformSetting::get('site_description', 'Pump Endless – a high-speed demo meme-coin launch & trading platform.'),
            'site_logo' => PlatformSetting::get('site_logo'),
            'site_icon' => PlatformSetting::get('site_icon'),
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
            'site_name' => 'required|string|max:60',
            'site_description' => 'nullable|string|max:500',
            'site_logo' => 'nullable|image|mimes:'.implode(',', self::ALLOWED_BRANDING).'|max:2048',
            'site_icon' => 'nullable|image|mimes:'.implode(',', self::ALLOWED_BRANDING).'|max:2048',
        ]);

        PlatformSetting::set('swap_fee_percent', $request->input('swap_fee_percent'));
        PlatformSetting::set('btc_withdrawal_fee', $request->input('btc_withdrawal_fee'));
        PlatformSetting::set('btc_usd_price', $request->input('btc_usd_price'));
        PlatformSetting::set('sol_usd_price', $request->input('sol_usd_price'));
        PlatformSetting::set('platform_name', $request->input('platform_name'));
        PlatformSetting::set('platform_announcement', $request->input('platform_announcement'));
        PlatformSetting::set('site_name', $request->input('site_name'));
        PlatformSetting::set('site_description', $request->input('site_description'));

        if ($request->hasFile('site_logo')) {
            PlatformSetting::set('site_logo', $this->storeBrandingFile($request->file('site_logo'), 'logo'));
        }
        if ($request->hasFile('site_icon')) {
            PlatformSetting::set('site_icon', $this->storeBrandingFile($request->file('site_icon'), 'icon'));
        }

        AuditLogger::record('settings.updated', null, $request->only([
            'swap_fee_percent',
            'btc_withdrawal_fee',
            'btc_usd_price',
            'sol_usd_price',
            'platform_name',
            'site_name',
            'site_description',
        ]));

        return back()->with('success', 'Platform settings, fees and website branding updated successfully.');
    }

    protected function storeBrandingFile($file, string $type): string
    {
        $extension = $file->guessExtension();
        if (! in_array($extension, self::ALLOWED_BRANDING, true)) {
            $extension = 'png';
        }

        $directory = public_path('images/branding');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileName = $type.'_'.time().'.'.$extension;
        $file->move($directory, $fileName);

        return 'images/branding/'.$fileName;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationSettingController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $settings = $user->notification_settings ?? $this->defaultSettings();

        return Inertia::render('Notifications/Settings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'low_stock_alert'         => 'boolean',
            'low_stock_threshold'     => 'nullable|integer|min:0',
            'stock_in_notification'   => 'boolean',
            'stock_out_notification'  => 'boolean',
            'email_notifications'     => 'boolean',
            'browser_notifications'   => 'boolean',
            'daily_report'            => 'boolean',
            'daily_report_time'       => 'nullable|string|regex:/^\d{2}:\d{2}$/',
        ]);

        $user = auth()->user();
        $user->update(['notification_settings' => $data]);

        return back()->with('success', 'Pengaturan notifikasi disimpan.');
    }

    private function defaultSettings(): array
    {
        return [
            'low_stock_alert'        => true,
            'low_stock_threshold'    => 10,
            'stock_in_notification'  => true,
            'stock_out_notification' => true,
            'email_notifications'    => false,
            'browser_notifications'  => true,
            'daily_report'           => false,
            'daily_report_time'      => '08:00',
        ];
    }
}

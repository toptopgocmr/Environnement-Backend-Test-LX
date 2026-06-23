<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Royalty, Category, WithdrawalRequest};
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class SettingsController extends Controller
{
    public function index()
    {
        $settings = PlatformSetting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'platform_fee_percent'    => 'required|numeric|min:0|max:50',
            'min_withdrawal_amount'   => 'required|numeric|min:1000',
            'max_downloads_per_order' => 'required|integer|min:1|max:20',
            'maintenance_mode'        => 'boolean',
            'platform_name'           => 'required|string|max:100',
            'support_email'           => 'required|email',
            'support_phone'           => 'nullable|string|max:20',
        ]);
        foreach ($data as $key => $value) {
            PlatformSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return back()->with('success', 'Paramètres mis à jour.');
    }
}

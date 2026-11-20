<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', ['settings' => Setting::all_settings()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => 'required|string|max:100',
            'site_title' => 'required|string|max:255',
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'nullable|string|max:1000',
            'meta_description' => 'nullable|string|max:500',
            'rates_source' => 'nullable|string|max:100',
            'usd_bdt' => 'nullable|numeric|min:0',
            'gold_int_usd' => 'nullable|numeric|min:0',
            'silver_int_usd' => 'nullable|numeric|min:0',
            'vat_percent' => 'required|numeric|min:0|max:100',
            'default_making_percent' => 'required|numeric|min:0|max:100',
            'vhori_grams' => 'required|numeric|min:0.001',
            'compare_note' => 'nullable|string|max:500',
            'footer_text' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
        ]);

        Setting::put($data);

        return back()->with('success', 'Settings saved.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => Hash::make($request->input('password'))]);

        return back()->with('success', 'Password changed.');
    }
}

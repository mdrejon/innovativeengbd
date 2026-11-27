<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    /**
     * Scalar settings editable on this screen. Structured settings (menus,
     * header, footer, widgets) are edited in Site Layout.
     *
     * @var array<int, string>
     */
    public const KEYS = [
        'site_name', 'brand_name', 'site_tagline', 'site_logo', 'site_logo_dark', 'site_favicon', 'established_year',
        'sales_email', 'support_email', 'phone', 'phone2', 'whatsapp_number', 'emergency_text',
        'head_office', 'branch_office', 'facebook_url', 'messenger_url',
        'currency_symbol', 'currency_code', 'domain_currency_symbol', 'domain_currency_code',
        'google_analytics_id', 'header_scripts', 'footer_scripts', 'robots_txt',
    ];

    public function index(): Response
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => array_intersect_key(Setting::getAllCached(), array_flip(self::KEYS)),
            'keys' => self::KEYS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'brand_name' => ['required', 'string', 'max:100'],
            'sales_email' => ['nullable', 'email'],
            'support_email' => ['nullable', 'email'],
            'abuse_email' => ['nullable', 'email'],
            'established_year' => ['nullable', 'string', 'max:10'],
            'currency_symbol' => ['sometimes', 'required', 'string', 'max:8'],
            'currency_code' => ['sometimes', 'required', 'string', 'max:5'],
            'domain_currency_symbol' => ['sometimes', 'required', 'string', 'max:8'],
            'domain_currency_code' => ['sometimes', 'required', 'string', 'max:5'],
        ]);

        foreach (self::KEYS as $key) {
            if ($request->has($key)) {
                Setting::set($key, (string) ($validated[$key] ?? $request->input($key) ?? ''));
            }
        }

        return back()->with('success', 'Global settings updated successfully.');
    }
}

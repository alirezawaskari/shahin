<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Services\Ai\HttpLlmClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsAiSettingsController extends Controller
{
    /**
     * Display LLM / AI Configuration Screen.
     */
    public function index(): View
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'دسترسی به تنظیمات هوش مصنوعی منحصراً در اختیار مدیر ارشد سامانه است.');
        }

        $setting = AiSetting::first();

        if (!$setting) {
            $setting = new AiSetting([
                'provider' => 'gemini',
                'model' => 'google/gemini-2.5-pro-preview',
                'base_url' => AiSetting::PROVIDERS['gemini']['default_base_url'] ?? '',
                'temperature' => 0.70,
                'max_tokens' => 2000,
                'role' => "شما کپی‌رایتر و مشاور بازاریابی پیامکی در سامانه شاهین (لاله کارت) هستید. وظیفه شما تولید متن‌های جذاب، متقاعدکننده و فارسی روان با رعایت اصول نگارش و ترغیب مخاطب به اقدام (CTA) است.",
                'is_active' => true,
            ]);
        }

        $providers = AiSetting::PROVIDERS;

        return view('Admin.sms.settings.ai.index', compact('setting', 'providers'));
    }

    /**
     * Update LLM / AI Configuration.
     */
    public function update(Request $request): RedirectResponse
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'provider' => 'required|string',
            'model' => 'required|string|max:255',
            'api_key' => 'nullable|string',
            'base_url' => 'nullable|string|max:500',
            'temperature' => 'required|numeric|min:0|max:2',
            'max_tokens' => 'required|integer|min:50|max:32000',
            'role' => 'nullable|string|max:5000',
            'is_active' => 'nullable|boolean',
        ]);

        $setting = AiSetting::first();

        // If api_key input is empty and we already had an api_key, preserve existing
        if (empty($validated['api_key']) && $setting && !empty($setting->api_key)) {
            unset($validated['api_key']);
        }

        $validated['is_active'] = $request->has('is_active');

        if ($setting) {
            $setting->update($validated);
        } else {
            AiSetting::create($validated);
        }

        return redirect()->back()->with('success', 'تنظیمات اتصال به هوش مصنوعی با موفقیت ذخیره شد.');
    }

    /**
     * Test connection to configured LLM endpoint via AJAX.
     */
    public function testConnection(Request $request): JsonResponse
    {
        if (!auth()->user()?->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'عدم دسترسی.'], 403);
        }

        $provider = $request->input('provider', 'gemini');
        $model = $request->input('model', 'google/gemini-2.5-pro-preview');
        $apiKey = $request->input('api_key');
        $baseUrl = $request->input('base_url');

        // Fall back to saved API key if user didn't re-type it
        if (empty($apiKey)) {
            $savedSetting = AiSetting::first();
            if ($savedSetting && !empty($savedSetting->api_key)) {
                $apiKey = $savedSetting->api_key;
            }
        }

        $result = HttpLlmClient::testConnection($provider, $model, $apiKey, $baseUrl);

        return response()->json($result);
    }
}

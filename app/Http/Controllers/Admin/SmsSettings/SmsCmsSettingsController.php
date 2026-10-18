<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Services\SmsSettings\SmsCmsContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super-admin-only text editor for the SMS module's regulatory rules text
 * and contract text (task.md §2-4/§2-6). Plain textareas — this is content
 * management, not a rich CMS. Category/subcategory CRUD lives in
 * SmsMessageGroupController, already wired into the same settings area.
 */
class SmsCmsSettingsController extends Controller
{
    public function __construct(
        protected SmsCmsContentService $content
    ) {
    }

    public function index(): RedirectResponse
    {
        return redirect()->route('admin.sms.settings.contracts.index');
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'این بخش فقط برای مدیر ارشد سامانه (superAdmin) در دسترس است.');

        $validated = $request->validate([
            'regulatory_rules_text' => 'nullable|string',
            'contract_text' => 'nullable|string',
        ]);

        $this->content->setRegulatoryRulesText($validated['regulatory_rules_text'] ?? '');
        $this->content->setContractText($validated['contract_text'] ?? '');

        return redirect()->route('admin.sms.settings.contracts.index')
            ->with('success', 'متون حقوقی و ضوابط پیامک با موفقیت ذخیره شد.');
    }
}

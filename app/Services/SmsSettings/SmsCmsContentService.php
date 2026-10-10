<?php

namespace App\Services\SmsSettings;

use App\Models\SmsCmsContent;
use Illuminate\Support\Facades\Cache;

/**
 * Super-admin-editable plain text content for the SMS module (task.md
 * §2-4 / §2-6): the telecom-regulatory rules text and the usage
 * contract/agreement text. Neither is known/final yet — the real legal and
 * category copy must come from the client — so this is deliberately a
 * plain key-value text store (SmsCmsContent / sms_cms_contents table)
 * rather than a rich CMS, and the getters below return an honest
 * "not configured yet" notice instead of placeholder legal text.
 */
class SmsCmsContentService
{
    public const KEY_REGULATORY_RULES = 'sms_regulatory_rules_text';
    public const KEY_CONTRACT_TEXT = 'sms_contract_text';

    protected const CACHE_PREFIX = 'sms_cms_content_';

    public function get(string $key, string $default = ''): string
    {
        $value = Cache::rememberForever(self::CACHE_PREFIX . $key, function () use ($key) {
            return SmsCmsContent::where('key', $key)->value('value');
        });

        return $value !== null && $value !== '' ? $value : $default;
    }

    public function set(string $key, string $value): void
    {
        SmsCmsContent::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'updated_by' => auth()->id()]
        );

        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Telecom-regulatory restrictions on message content. Rendered on the
     * contract page and as a read-only panel on the single/bulk send
     * screens. NOT YET PROVIDED BY THE CLIENT — returns an explicit
     * "not configured" notice until a superAdmin fills it in here.
     */
    public function regulatoryRulesText(): string
    {
        return $this->get(
            self::KEY_REGULATORY_RULES,
            'متن ضوابط و مقررات مخابراتی هنوز توسط مدیر سامانه در این بخش تنظیم نشده است.'
        );
    }

    public function setRegulatoryRulesText(string $value): void
    {
        $this->set(self::KEY_REGULATORY_RULES, $value);
    }

    /**
     * The usage contract/agreement text shown on the one-time sign page.
     * NOT YET PROVIDED BY THE CLIENT — returns an explicit "not configured"
     * notice until a superAdmin fills it in here.
     */
    public function contractText(): string
    {
        return $this->get(
            self::KEY_CONTRACT_TEXT,
            'متن تعهدنامه استفاده از سرویس پیامک هنوز توسط مدیر سامانه در این بخش تنظیم نشده است.'
        );
    }

    public function setContractText(string $value): void
    {
        $this->set(self::KEY_CONTRACT_TEXT, $value);
    }
}

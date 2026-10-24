<?php

namespace App\Services\SmsSettings;

use App\Exceptions\Sms\SmsPanelDisabledException;
use App\Models\GhorfeOnlineList;
use App\Models\User;
use DomainException;

/**
 * Per-business switch for the two SMS panels a business sends through: OTP and advertising.
 *
 * The state lives in the business's own settings (ghorfe_settings, key sms_panel_disabled_<type>) as JSON:
 * disabled, reason, by_id, by_name, at. Disabling needs a reason; enabling keeps the last change in the row.
 * The system's own OTP (login, contract signing) is never checked here, it is not a business panel.
 */
class SmsPanelAccess
{
    public const TYPE_OTP = 'otp';

    public const TYPE_ADVERTISING = 'advertising';

    public const TYPES = [self::TYPE_OTP, self::TYPE_ADVERTISING];

    /** Persian label of a panel, as shown to staff and to the business. */
    public static function label(string $type): string
    {
        return $type === self::TYPE_OTP ? 'خدماتی (OTP)' : 'تبلیغاتی';
    }

    /** The panel a send goes through: every service type that is not OTP is the advertising panel. */
    public function panelTypeFor(?string $serviceType): string
    {
        return $serviceType === self::TYPE_OTP ? self::TYPE_OTP : self::TYPE_ADVERTISING;
    }

    public function settingKey(string $type): string
    {
        $this->assertType($type);

        return 'sms_panel_disabled_' . $type;
    }

    /**
     * @return array{disabled: bool, reason: ?string, by_id: ?int, by_name: ?string, at: ?string}
     */
    public function state(GhorfeOnlineList $business, string $type): array
    {
        $raw = $business->settings()->where('key', $this->settingKey($type))->value('value');
        $data = is_string($raw) ? json_decode($raw, true) : null;
        $data = is_array($data) ? $data : [];

        return [
            'disabled' => (bool) ($data['disabled'] ?? false),
            'reason' => $data['reason'] ?? null,
            'by_id' => $data['by_id'] ?? null,
            'by_name' => $data['by_name'] ?? null,
            'at' => $data['at'] ?? null,
        ];
    }

    public function isDisabled(GhorfeOnlineList $business, string $type): bool
    {
        return $this->state($business, $type)['disabled'];
    }

    public function disable(GhorfeOnlineList $business, string $type, string $reason, User $user): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException('دلیل غیرفعال‌سازی پنل الزامی است.');
        }

        $this->write($business, $type, $user, true, $reason);
    }

    public function enable(GhorfeOnlineList $business, string $type, User $user): void
    {
        $this->write($business, $type, $user, false, null);
    }

    /**
     * Refuses a send through a disabled panel. Called before any message, recipient or charge is created.
     */
    public function assertCanSend(GhorfeOnlineList $business, ?string $serviceType): void
    {
        $type = $this->panelTypeFor($serviceType);

        if ($this->isDisabled($business, $type)) {
            throw new SmsPanelDisabledException(
                $type,
                'پنل ' . self::label($type) . ' این کسب‌وکار توسط مدیریت غیرفعال شده است؛ ارسال پیامک از این پنل امکان‌پذیر نیست.'
            );
        }
    }

    private function write(GhorfeOnlineList $business, string $type, User $user, bool $disabled, ?string $reason): void
    {
        $business->settings()->updateOrCreate(
            ['key' => $this->settingKey($type)],
            ['value' => json_encode([
                'disabled' => $disabled,
                'reason' => $reason,
                'by_id' => $user->id,
                'by_name' => trim(($user->name ?? '') . ' ' . ($user->family ?? '')),
                'at' => now()->toDateTimeString(),
            ], JSON_UNESCAPED_UNICODE)],
        );
    }

    private function assertType(string $type): void
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new DomainException('نوع پنل نامعتبر است.');
        }
    }
}

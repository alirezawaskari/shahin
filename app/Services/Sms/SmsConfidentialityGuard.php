<?php

namespace App\Services\Sms;

use App\Models\SmsMessage;
use App\Models\User;

/**
 * Central gate for SMS message confidentiality (task.md §13: مالی/حقوقی
 * groups are confidential; viewing their text requires a dedicated
 * permission). Every place that returns message content — list/report
 * views, AJAX endpoints, and spreadsheet exports — must route the content
 * field through this guard instead of reading $message->content directly,
 * so the body is omitted server-side for users without the permission
 * rather than merely hidden client-side.
 */
class SmsConfidentialityGuard
{
    public const PERMISSION = 'view-confidential-sms';

    public static function canViewConfidential(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        if (method_exists($user, 'isAbleTo')) {
            return (bool) $user->isAbleTo(self::PERMISSION);
        }

        return false;
    }

    /**
     * Resolve the content that is safe to expose to the given viewer.
     * Returns null (not a masked string) when the message is confidential
     * and the viewer lacks permission, so the field is actually omitted
     * from JSON/export output rather than ending up as a client-visible
     * placeholder that still round-trips through the network as text.
     */
    public static function visibleContent(SmsMessage $message, ?User $viewer): ?string
    {
        if ($message->isConfidential() && !self::canViewConfidential($viewer)) {
            return null;
        }

        return $message->content;
    }

    /**
     * Redact a message's `content` attribute in place (for contexts, such
     * as a server-rendered Blade list, that read the Eloquent attribute
     * directly rather than going through a resource/array transform).
     */
    public static function redact(SmsMessage $message, ?User $viewer): SmsMessage
    {
        if ($message->isConfidential() && !self::canViewConfidential($viewer)) {
            $message->setAttribute('content', null);
            $message->setAttribute('content_redacted', true);
        } else {
            $message->setAttribute('content_redacted', false);
        }

        return $message;
    }

    /**
     * Redact every item of a collection/paginator of SmsMessage models.
     *
     * @param iterable<SmsMessage> $messages
     */
    public static function redactMany(iterable $messages, ?User $viewer): iterable
    {
        foreach ($messages as $message) {
            self::redact($message, $viewer);
        }

        return $messages;
    }
}

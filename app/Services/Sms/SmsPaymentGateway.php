<?php

namespace App\Services\Sms;

use App\Models\SmsLog;

/**
 * Boundary between the SMS module and the project's payment module.
 *
 * SMS never talks to a bank itself. An implementation (registered in the
 * container as this interface) asks the payment module to open a payment for
 * the given pending row and returns the gateway URL the user must be sent to.
 * When the payment module later verifies the payment it must call
 * SmsTopUpPaymentService::settleVerified() / settleFailed().
 */
interface SmsPaymentGateway
{
    /**
     * Start a payment for $payment and return the gateway redirect URL.
     * Implementations should store the gateway token on $payment->authority
     * (and ->gateway) so the callback can be matched back to the row.
     *
     * @throws \App\Exceptions\Sms\SmsPaymentUnavailableException when no payment can be started
     */
    public function start(object $payment): string;
}

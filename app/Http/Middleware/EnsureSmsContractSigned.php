<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * task.md §2-6: a business must sign the SMS usage contract/agreement once
 * (checkbox + signature) before any SMS feature is usable. Redirects an
 * unsigned business to the sign page instead of letting it reach a send,
 * report, financial, or log screen.
 *
 * Only gates when a business context is actually resolved (ghorfe() is not
 * null) — a superAdmin browsing without a selected business (e.g. a
 * cross-business report) is not blocked by a single business's acceptance
 * state. The settings/CMS area is intentionally NOT behind this middleware:
 * it is where the sign page's own rules text lives and where superAdmin
 * configures the module regardless of any one business's contract state.
 */
class EnsureSmsContractSigned
{
    public function handle(Request $request, Closure $next): Response
    {
        $business = ghorfe();

        if ($business && !$business->sms_contract_accepted) {
            if ($request->routeIs('admin.sms.contract.*')) {
                return $next($request);
            }

            redirect()->setIntendedUrl($request->fullUrl());

            return redirect()
                ->route('admin.sms.contract.sign')
                ->with('warning', 'برای استفاده از سرویس پیامک، ابتدا باید تعهدنامه استفاده از این سرویس را مطالعه و تایید نمایید.');
        }

        return $next($request);
    }
}

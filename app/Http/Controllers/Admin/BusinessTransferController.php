<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusinessTransferRequest;
use App\Services\Admin\BusinessTransferService;
use Illuminate\Http\RedirectResponse;

class BusinessTransferController extends Controller
{
    public function store(
        BusinessTransferRequest $request,
        BusinessTransferService $transferService
    ): RedirectResponse {
        // FormRequest::authorize is the direct-request permission gate; keep this explicit
        // second check at the mutation boundary so later request-class changes cannot weaken it.
        abort_unless($request->user()?->hasPermission('transfer_to_business') === true, 403);

        $validated = $request->validated();
        $result = $transferService->transfer(
            $validated['entity'],
            $validated['ids'],
            (int) $validated['destination_business_id'],
            optional(ghorfe())->id
        );

        return redirect()->back()->with([
            'business_transfer_success' => sprintf(
                '%s رکورد با موفقیت به کسب و کار «%s» منتقل شد.',
                number_format($result['count']),
                $result['destination']->title
            ),
        ]);
    }
}

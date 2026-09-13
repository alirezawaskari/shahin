<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Admin\BusinessTransferService;
use Illuminate\Http\Request;

/**
 * «انتقال به کسب و کار» به صورت یک فیلد معمولی در فرم ویرایش هر رکورد.
 *
 * فیلد فقط شناسه‌ی کسب و کار مقصد را می‌فرستد؛ نوع رکورد و شناسه‌ی آن را خود
 * کنترلر می‌داند، پس هیچ‌کدام از ورودی درخواست خوانده نمی‌شوند. مجوز مستقل
 * transfer_to_business اینجا هم بررسی می‌شود تا ارسال دستیِ درخواست هم بی‌اثر
 * باشد، و خود انتقال داخل تراکنش BusinessTransferService انجام می‌شود.
 */
trait TransfersRecordsToBusiness
{
    /**
     * @param  array<int, int|string>  $ids                 رکوردهایی که با هم منتقل می‌شوند
     * @param  array<int, int|string>  $currentBusinessIds  کسب و کار فعلی رکورد
     * @return array{count: int, destination: \App\Models\GhorfeOnlineList}|null
     */
    protected function applyInlineBusinessTransfer(
        Request $request,
        string $entity,
        array $ids,
        array $currentBusinessIds
    ): ?array {
        if (! $request->filled('destination_business_id')) {
            return null;
        }

        $destinationId = (int) $request->input('destination_business_id');
        $currentIds = array_map('intval', $currentBusinessIds);

        // انتخاب همان کسب و کار فعلی یعنی «بدون تغییر».
        if ($destinationId <= 0 || in_array($destinationId, $currentIds, true)) {
            return null;
        }

        abort_unless($request->user()?->hasPermission('transfer_to_business') === true, 403);

        return app(BusinessTransferService::class)->transfer(
            $entity,
            array_map('intval', $ids),
            $destinationId,
            optional(ghorfe())->id
        );
    }

    /** پیام موفقیت انتقال برای فلش کردن کنار پیام ذخیره‌ی فرم */
    protected function businessTransferFlash(?array $result): array
    {
        if (! $result) {
            return [];
        }

        return [
            'business_transfer_success' => sprintf(
                '%s رکورد با موفقیت به کسب و کار «%s» منتقل شد.',
                number_format($result['count']),
                $result['destination']->title
            ),
        ];
    }
}

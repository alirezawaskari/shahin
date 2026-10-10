<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SmsAdvertisingReportExport;
use App\Exports\SmsOtpReportExport;
use App\Exports\SmsSuperAdminReportExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\SmsMessageResource;
use App\Models\GhorfeOnlineList;
use App\Services\Sms\SmsConfidentialityGuard;
use App\Services\Sms\SmsReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SmsReportController extends Controller
{
    public function __construct(
        protected SmsReportService $reportService
    ) {
    }

    /**
     * Confidentiality enforcement (task.md §13): redact the body of every
     * confidential (مالی/حقوقی) message in a report's paginated `messages`
     * collection for viewers without view-confidential-sms, server-side,
     * before the data reaches either the JSON branch or the Blade view.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function redactReportMessages(array $data, Request $request): array
    {
        $viewer = auth()->user();

        if (isset($data['messages']) && method_exists($data['messages'], 'getCollection')) {
            SmsConfidentialityGuard::redactMany($data['messages']->getCollection(), $viewer);

            if ($request->wantsJson()) {
                $data['messages'] = SmsMessageResource::collection($data['messages']->getCollection())->toArray($request);
            }
        }

        return $data;
    }

    /**
     * Resolve the current business context.
     */
    protected function resolveBusiness(Request $request): ?GhorfeOnlineList
    {
        $business = ghorfe();

        if (!$business && auth()->user()?->isSuperAdmin() && $request->filled('business_id')) {
            $business = GhorfeOnlineList::find($request->input('business_id'));
        }

        return $business ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
    }

    /**
     * OTP report for current business.
     */
    public function otpReport(Request $request): View|JsonResponse
    {
        $business = $this->resolveBusiness($request);

        if (!$business) {
            abort(404, 'هیچ کسب‌وکاری در سیستم تعریف نشده است.');
        }

        $filters = $request->only(['status', 'sms_message_template_id', 'sms_message_group_id', 'search', 'date_from', 'date_to']);
        $data = $this->reportService->getOtpReport($business, $filters);
        $data = $this->redactReportMessages($data, $request);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        }

        return view('Admin.sms.reports.otp', $data);
    }

    /**
     * Export OTP report to Excel.
     */
    public function otpReportExport(Request $request): BinaryFileResponse
    {
        $business = $this->resolveBusiness($request);

        if (!$business) {
            abort(404, 'کسب‌وکاری یافت نشد.');
        }

        $filters = $request->only(['status', 'sms_message_template_id', 'sms_message_group_id', 'search', 'date_from', 'date_to']);
        $query = $this->reportService->buildOtpReportQuery($business, $filters);

        $fileName = 'otp-sms-report-' . now()->format('Y-m-d-His') . '.xlsx';
        return Excel::download(new SmsOtpReportExport($query), $fileName);
    }

    /**
     * Advertising report (per-sender, campaigns + messages).
     */
    public function advertisingReport(Request $request): View|JsonResponse
    {
        $business = $this->resolveBusiness($request);

        if (!$business) {
            abort(404, 'هیچ کسب‌وکاری در سیستم تعریف نشده است.');
        }

        $filters = $request->only(['sender_number_id', 'sms_campaign_id', 'status', 'sms_message_group_id', 'search', 'date_from', 'date_to']);
        $data = $this->reportService->getAdvertisingReport($business, $filters);
        $data = $this->redactReportMessages($data, $request);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        }

        return view('Admin.sms.reports.advertising', $data);
    }

    /**
     * Export Advertising messages report to Excel.
     */
    public function advertisingReportExport(Request $request): BinaryFileResponse
    {
        $business = $this->resolveBusiness($request);

        if (!$business) {
            abort(404, 'کسب‌وکاری یافت نشد.');
        }

        $filters = $request->only(['sender_number_id', 'sms_campaign_id', 'status', 'sms_message_group_id', 'search', 'date_from', 'date_to']);
        $query = $this->reportService->buildAdvertisingMessagesQuery($business, $filters);

        $fileName = 'advertising-sms-report-' . now()->format('Y-m-d-His') . '.xlsx';
        return Excel::download(new SmsAdvertisingReportExport($query), $fileName);
    }

    /**
     * Super Admin cross-business report with filters.
     * Gated behind superAdmin role-check pattern.
     */
    public function superAdminReport(Request $request): View|JsonResponse
    {
        $user = auth()->user();

        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'تنها مدیر ارشد سامانه (superAdmin) مجاز به مشاهده گزارشات جامع بین‌سازمانی پیامک می‌باشد.');
        }

        $filters = $request->only([
            'business',
            'ghorfe_online_list_id',
            'sender',
            'sender_number_id',
            'panel_type',
            'message_type',
            'service_type',
            'date_from',
            'date_to',
            'status',
            'group',
            'sms_message_group_id',
            'search',
        ]);

        $data = $this->reportService->getSuperAdminReport($filters);
        $data = $this->redactReportMessages($data, $request);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        }

        return view('Admin.sms.reports.superadmin', $data);
    }

    /**
     * Export Super Admin cross-business report to Excel.
     */
    public function superAdminReportExport(Request $request): BinaryFileResponse
    {
        $user = auth()->user();

        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'تنها مدیر ارشد سامانه (superAdmin) مجاز به خروجی اکسل گزارشات جامع می‌باشد.');
        }

        $filters = $request->only([
            'business',
            'ghorfe_online_list_id',
            'sender',
            'sender_number_id',
            'panel_type',
            'message_type',
            'service_type',
            'date_from',
            'date_to',
            'status',
            'group',
            'sms_message_group_id',
            'search',
        ]);

        $query = $this->reportService->buildSuperAdminReportQuery($filters);

        $fileName = 'cross-business-sms-report-' . now()->format('Y-m-d-His') . '.xlsx';
        return Excel::download(new SmsSuperAdminReportExport($query), $fileName);
    }
}

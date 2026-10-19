<?php

namespace Tests\Feature;

use App\Exports\SmsAdvertisingReportExport;
use App\Exports\SmsOtpReportExport;
use App\Exports\SmsSuperAdminReportExport;
use App\Models\Category;
use App\Models\Service;
use App\Models\SmsMessage;
use App\Services\Sms\SmsReportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\BuildsSmsFixture;
use Tests\TestCase;

/**
 * Every SMS report (OTP, advertising, cross-business) and its Excel export prints a request as
 * "Top > Sub > Service" instead of a raw id.
 */
class SmsReportBreadcrumbTest extends TestCase
{
    use BuildsSmsFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildSmsFixture();

        Schema::create('categories', function (Blueprint $t) {
            $t->increments('id');
            $t->string('title');
            $t->unsignedInteger('parent_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('services', function (Blueprint $t) {
            $t->increments('id');
            $t->string('title');
            $t->unsignedInteger('category_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('callcenterables', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('callcenter_id');
            $t->unsignedBigInteger('callcenterable_id');
            $t->string('callcenterable_type');
            $t->timestamps();
        });
        DB::table('categories')->insert([
            ['id' => 2, 'title' => 'خدمات', 'parent_id' => null],
            ['id' => 10, 'title' => 'تعمیرات', 'parent_id' => 2],
        ]);
        DB::table('services')->insert([['id' => 100, 'title' => 'تعمیر پکیج', 'category_id' => 10]]);
        DB::table('callcenterables')->insert([
            ['callcenter_id' => 7, 'callcenterable_id' => 100, 'callcenterable_type' => Service::class],
        ]);

        $this->actingAs($this->adminUser);
    }

    protected function message(string $type, array $extra = []): SmsMessage
    {
        return SmsMessage::create(array_merge([
            'ghorfe_online_list_id' => $this->businessA->id, 'direction' => 'outbound', 'service_type' => $type,
            'sender_number_id' => $type === 'otp' ? $this->otpSender->id : $this->advSender1->id,
            'sender_phone' => '90001', 'recipient_phone' => '09123334455', 'content' => 'متن', 'status' => 'sent',
            'cost' => 1200, 'tracking_id' => 'T' . random_int(1, 9999999), 'callcenter_id' => 7,
        ], $extra));
    }

    public function test_otp_report_service_and_view_show_breadcrumb_not_raw_id(): void
    {
        $this->message('otp', ['otp_category' => 'sales']);
        $this->message('otp', ['callcenter_id' => 999, 'otp_category' => 'sales']);

        $data = app(SmsReportService::class)->getOtpReport($this->businessA);
        $labels = $data['messages']->getCollection()->pluck('request_breadcrumb')->all();
        $this->assertContains('خدمات > تعمیرات > تعمیر پکیج', $labels);
        $this->assertContains('#999', $labels); // unknown ids degrade gracefully

        $this->get(route('admin.sms.reports.otp.index', ['business_id' => $this->businessA->id]))
            ->assertOk()->assertSee('خدمات &gt; تعمیرات &gt; تعمیر پکیج', false)->assertSee('درخواست');
    }

    public function test_advertising_report_view_shows_breadcrumb(): void
    {
        $this->message('advertising');

        $this->get(route('admin.sms.reports.advertising.index', ['business_id' => $this->businessA->id]))
            ->assertOk()->assertSee('خدمات &gt; تعمیرات &gt; تعمیر پکیج', false);
    }

    public function test_superadmin_report_view_shows_breadcrumb_column(): void
    {
        $this->message('advertising');
        $this->actingAs($this->superAdminUser);

        $this->get(route('admin.sms.reports.superadmin.index'))
            ->assertOk()->assertSee('خدمات &gt; تعمیرات &gt; تعمیر پکیج', false);
    }

    public function test_exports_carry_the_breadcrumb_column(): void
    {
        $this->message('otp', ['otp_category' => 'legal']);
        $this->message('advertising');
        $report = app(SmsReportService::class);

        $otp = new SmsOtpReportExport($report->buildOtpReportQuery($this->businessA));
        $adv = new SmsAdvertisingReportExport($report->buildAdvertisingMessagesQuery($this->businessA));
        $sup = new SmsSuperAdminReportExport($report->buildSuperAdminReportQuery());

        foreach ([$otp, $sup] as $export) {
            $this->assertContains('درخواست', $export->headings());
            $this->assertSame('خدمات > تعمیرات > تعمیر پکیج', $export->collection()->first()['request']);
        }
        $this->assertContains('درخواست', $adv->headings());
        $this->assertSame('خدمات > تعمیرات > تعمیر پکیج', $adv->collection()->first()['request']);
        $this->assertCount(count($adv->headings()), $adv->collection()->first());
    }

    public function test_batch_lookup_is_not_n_plus_one(): void
    {
        foreach (range(1, 12) as $i) {
            $this->message('otp', ['otp_category' => 'finance']);
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $export = new SmsOtpReportExport(app(SmsReportService::class)->buildOtpReportQuery($this->businessA));
        $rows = $export->collection();
        $catalogQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'callcenterables') || str_contains($q['query'], '"services"') || str_contains($q['query'], '"categories"'))->count();

        $this->assertCount(12, $rows);
        $this->assertLessThanOrEqual(5, $catalogQueries);
    }
}

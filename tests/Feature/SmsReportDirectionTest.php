<?php

namespace Tests\Feature;

use App\Exports\SmsAdvertisingReportExport;
use App\Exports\SmsOtpReportExport;
use App\Exports\SmsSuperAdminReportExport;
use App\Models\SmsMessage;
use App\Services\Sms\SmsReportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\BuildsSmsFixture;
use Tests\TestCase;

/**
 * Receiving center inside the reports: direction filter, sent-vs-received tiles, outbound-only cost,
 * OTP isolation, exports and the navigation shortcut.
 */
class SmsReportDirectionTest extends TestCase
{
    use BuildsSmsFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildSmsFixture();
        $this->actingAs($this->adminUser);
    }

    protected function msg(string $direction, string $type, array $extra = []): SmsMessage
    {
        return SmsMessage::create(array_merge([
            'ghorfe_online_list_id' => $this->businessA->id, 'direction' => $direction, 'service_type' => $type,
            'sender_number_id' => $direction === 'outbound' ? ($type === 'otp' ? $this->otpSender->id : $this->advSender1->id) : null,
            'sender_phone' => $direction === 'inbound' ? '09120001122' : '90001',
            'recipient_phone' => $direction === 'inbound' ? $this->advSender1->number : '09123334455',
            'content' => $direction . '-' . $type . '-body', 'status' => $direction === 'inbound' ? 'delivered' : 'sent',
            'cost' => $direction === 'inbound' ? 0 : 1000, 'tracking_id' => 'T' . random_int(1, 99999999),
        ], $extra));
    }

    protected function seed3(): void
    {
        $this->msg('outbound', 'advertising');
        $this->msg('outbound', 'advertising');
        $this->msg('inbound', 'normal');
        $this->msg('inbound', 'advertising');
        $this->msg('outbound', 'otp');
        $this->msg('inbound', 'otp');
    }

    public function test_advertising_report_direction_filter_and_routing(): void
    {
        $this->seed3();
        $svc = app(SmsReportService::class);

        $all = $svc->getAdvertisingReport($this->businessA);
        $this->assertSame(4, $all['messages']->total()); // 2 out adv + 2 inbound non-otp; no otp rows
        $this->assertSame(2, $all['summary']['outbound_count']);
        $this->assertSame(2, $all['summary']['inbound_count']);

        $in = $svc->getAdvertisingReport($this->businessA, ['direction' => 'inbound']);
        $this->assertSame(2, $in['messages']->total());
        $this->assertTrue($in['messages']->getCollection()->every(fn ($m) => $m->direction === 'inbound' && $m->service_type !== 'otp'));

        $out = $svc->getAdvertisingReport($this->businessA, ['direction' => 'outbound']);
        $this->assertSame(2, $out['messages']->total());
        $this->assertTrue($out['messages']->getCollection()->every(fn ($m) => $m->direction === 'outbound'));
    }

    public function test_otp_report_direction_filter(): void
    {
        $this->seed3();
        $svc = app(SmsReportService::class);

        $this->assertSame(2, $svc->getOtpReport($this->businessA)['messages']->total());
        $in = $svc->getOtpReport($this->businessA, ['direction' => 'inbound']);
        $this->assertSame(1, $in['messages']->total());
        $this->assertSame('otp', $in['messages']->first()->service_type);
        $this->assertSame(1, $svc->getOtpReport($this->businessA, ['direction' => 'outbound'])['messages']->total());
    }

    public function test_superadmin_report_direction_filter_and_each_message_on_one_report(): void
    {
        $this->seed3();
        $svc = app(SmsReportService::class);

        $this->assertSame(6, $svc->getSuperAdminReport()['messages']->total());
        $this->assertSame(3, $svc->getSuperAdminReport(['direction' => 'inbound'])['messages']->total());
        $this->assertSame(3, $svc->getSuperAdminReport(['direction' => 'outbound'])['messages']->total());

        $otpIds = $svc->buildOtpReportQuery($this->businessA)->pluck('id');
        $advIds = $svc->buildAdvertisingMessagesQuery($this->businessA)->pluck('id');
        $this->assertCount(0, $otpIds->intersect($advIds));
        $this->assertSame(6 - 0, $otpIds->count() + $advIds->count());
    }

    public function test_inbound_rows_do_not_distort_cost_or_delivery_stats(): void
    {
        $this->seed3();
        $this->msg('inbound', 'normal', ['cost' => 500]); // even a stray non-zero cost on a received row is ignored
        $s = app(SmsReportService::class)->getAdvertisingReport($this->businessA)['summary'];

        $this->assertSame(2000.0, $s['total_cost']);
        $this->assertSame(2, $s['total_messages']);
        $this->assertSame(3, $s['inbound_count']);
        $this->assertEquals(100.0, $s['delivery_rate']);

        $sup = app(SmsReportService::class)->getSuperAdminReport()['summary'];
        $this->assertSame(3000.0, $sup['total_cost']);
    }

    public function test_non_otp_user_never_sees_otp_rows(): void
    {
        $this->seed3();
        \DB::table('users')->where('id', $this->adminUser->id)->update(['access_tier' => 'silver']);
        $this->adminUser->setAttribute('access_tier', 'silver');
        $this->actingAs($this->adminUser);

        $this->get(route('admin.sms.reports.otp.index', ['business_id' => $this->businessA->id, 'direction' => 'inbound']))->assertStatus(403);

        $html = $this->get(route('admin.sms.reports.advertising.index', ['business_id' => $this->businessA->id]))->assertOk()->getContent();
        $this->assertStringNotContainsString('inbound-otp-body', $html);
        $this->assertStringNotContainsString('outbound-otp-body', $html);
        $this->assertStringContainsString('inbound-normal-body', $html);
        $this->assertStringNotContainsString('sms-inbound-otp-link', $html);
    }

    public function test_exports_honor_direction_and_have_direction_column(): void
    {
        $this->seed3();
        $svc = app(SmsReportService::class);

        $adv = new SmsAdvertisingReportExport($svc->buildAdvertisingMessagesQuery($this->businessA, ['direction' => 'inbound']));
        $this->assertContains('جهت', $adv->headings());
        $rows = $adv->collection();
        $this->assertCount(2, $rows);
        $this->assertTrue($rows->every(fn ($r) => $r['direction'] === 'دریافتی'));
        $this->assertCount(count($adv->headings()), $rows->first());

        $otp = new SmsOtpReportExport($svc->buildOtpReportQuery($this->businessA, ['direction' => 'outbound']));
        $this->assertContains('جهت', $otp->headings());
        $this->assertSame(['ارسالی'], $otp->collection()->pluck('direction')->unique()->values()->all());
        $this->assertCount(count($otp->headings()), $otp->collection()->first());

        $sup = new SmsSuperAdminReportExport($svc->buildSuperAdminReportQuery(['direction' => 'inbound']));
        $this->assertContains('جهت', $sup->headings());
        $this->assertCount(3, $sup->collection());
        $this->assertCount(count($sup->headings()), $sup->collection()->first());
    }

    public function test_pages_render_direction_ui_nav_link_and_keep_query_in_pagination(): void
    {
        $this->seed3();
        foreach (range(1, 30) as $i) {
            $this->msg('inbound', 'normal');
        }

        $html = $this->get(route('admin.sms.reports.advertising.index', ['business_id' => $this->businessA->id, 'direction' => 'inbound']))
            ->assertOk()->assertSee('جهت پیام')->assertSee('پیامک‌های دریافتی')->getContent();
        $this->assertStringContainsString('sms-inbound-link', $html);
        $this->assertStringContainsString('direction=inbound', $html);
        $this->assertMatchesRegularExpression('/messages_page=2[^"]*direction=inbound|direction=inbound[^"]*messages_page=2/', html_entity_decode($html));

        $this->get(route('admin.sms.reports.otp.index', ['business_id' => $this->businessA->id]))
            ->assertOk()->assertSee('sms-inbound-otp-link', false);

        $this->actingAs($this->superAdminUser);
        $this->get(route('admin.sms.reports.superadmin.index', ['direction' => 'inbound']))->assertOk()->assertSee('جهت پیام');
    }

    public function test_legacy_list_redirects_land_on_reports(): void
    {
        Schema::create('groupsms', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        $id = \DB::table('groupsms')->insertGetId(['ghorfe_online_list_id' => $this->businessA->id]);

        $this->delete(route('groupsms.destroy', $id))
            ->assertRedirect(route('admin.sms.reports.otp.index')); // titanium admin -> OTP-capable report

        \DB::table('users')->where('id', $this->adminUser->id)->update(['access_tier' => 'silver']);
        $this->adminUser->setAttribute('access_tier', 'silver');
        $this->actingAs($this->adminUser);
        $id2 = \DB::table('groupsms')->insertGetId(['ghorfe_online_list_id' => $this->businessA->id]);
        $this->delete(route('groupsms.destroy', $id2))
            ->assertRedirect(route('admin.sms.reports.advertising.index'));
    }

    public function test_outbound_normal_rows_route_to_advertising_report_only(): void
    {
        $this->seed3();
        $reply = $this->msg('outbound', 'normal', ['content' => 'پاسخ آزمایشی']);
        $svc = app(SmsReportService::class);

        $adv = $svc->getAdvertisingReport($this->businessA);
        $this->assertContains($reply->id, $adv['messages']->getCollection()->pluck('id')->all());
        $this->assertSame(3, $adv['summary']['outbound_count']); // 2 advertising + 1 normal
        $this->assertSame(3000.0, $adv['summary']['total_cost']);

        $otp = $svc->getOtpReport($this->businessA);
        $this->assertNotContains($reply->id, $otp['messages']->getCollection()->pluck('id')->all());

        // exactly one report per message
        $advIds = $svc->buildAdvertisingMessagesQuery($this->businessA)->pluck('id')->all();
        $otpIds = $svc->buildOtpReportQuery($this->businessA)->pluck('id')->all();
        $this->assertSame([], array_intersect($advIds, $otpIds));
        $this->assertSame(
            SmsMessage::where('ghorfe_online_list_id', $this->businessA->id)->count(),
            count($advIds) + count($otpIds)
        );
    }

    public function test_advertising_service_type_filter_page_and_export(): void
    {
        $this->seed3();
        $reply = $this->msg('outbound', 'normal', ['content' => 'پاسخ آزمایشی']);
        $svc = app(SmsReportService::class);

        $normal = $svc->buildAdvertisingMessagesQuery($this->businessA, ['service_type' => 'normal'])->pluck('id')->all();
        $this->assertContains($reply->id, $normal);
        $this->assertCount(2, $normal); // reply + inbound normal

        $adv = $svc->buildAdvertisingMessagesQuery($this->businessA, ['service_type' => 'advertising']);
        $this->assertSame(['advertising'], $adv->pluck('service_type')->unique()->values()->all());

        $this->get(route('admin.sms.reports.advertising.index', ['business_id' => $this->businessA->id, 'service_type' => 'normal']))
            ->assertOk()->assertSee('نوع پیام')->assertSee('پاسخ آزمایشی')->assertDontSee('outbound-advertising-body');

        $export = new SmsAdvertisingReportExport($svc->buildAdvertisingMessagesQuery($this->businessA, ['service_type' => 'normal']));
        $this->assertContains('نوع پیام', $export->headings());
        $this->assertSame(['عادی'], $export->collection()->pluck('service_type')->unique()->values()->all());
        $this->assertCount(count($export->headings()), $export->collection()->first());
    }
}

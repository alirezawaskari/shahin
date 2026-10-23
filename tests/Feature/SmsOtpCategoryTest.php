<?php

namespace Tests\Feature;

use App\Enums\SmsOtpCategory;
use App\Models\Groupsms;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\Sms\SingleSmsSendService;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsSmsFixture;
use Tests\TestCase;

class SmsOtpCategoryTest extends TestCase
{
    use BuildsSmsFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildSmsFixture();

        app(SingleSmsSendService::class)->setGatewayDispatcher(function () {});
        $this->seedOtpBalance($this->businessA->id, 500000);
        $this->seedAdvertisingBalance($this->businessA->id, 500000);

        foreach (User::all() as $u) {
            DB::table('employees')->where('user_id', $u->id)->exists()
                || DB::table('employees')->insert(['user_id' => $u->id, 'ghorfe_online_list_id' => $this->businessA->id, 'work_title' => 'test']);
        }
    }

    protected function lowerTierUser(string $tier = 'silver'): User
    {
        $user = User::create(['name' => 'پایین', 'phone' => '0912' . random_int(1000000, 9999999), 'level' => 'admin', 'access_tier' => $tier, 'active' => true]);
        DB::table('employees')->insert(['user_id' => $user->id, 'ghorfe_online_list_id' => $this->businessA->id, 'work_title' => 'test']);

        return $user;
    }

    protected function otpPayload(array $extra = []): array
    {
        return array_merge([
            'recipient_phone' => '09123334455',
            'message' => 'کد تایید 1234',
            'service_type' => 'otp',
        ], $extra);
    }

    protected function templatePayload(string $type, array $extra = []): array
    {
        return array_merge([
            'title' => 'الگو ' . $type . random_int(1, 99999),
            'content' => 'متن الگو',
            'service_type' => $type,
            'status' => 'active',
            'business_id' => $this->businessA->id,
        ], $extra);
    }

    public function test_enum_exposes_exactly_the_four_fixed_categories_with_persian_labels(): void
    {
        $this->assertSame(['auth_password_reset', 'sales', 'finance', 'legal'], SmsOtpCategory::values());
        $this->assertSame('احراز هویت / بازیابی رمز عبور', SmsOtpCategory::labelFor('auth_password_reset'));
        $this->assertSame('فروش', SmsOtpCategory::labelFor('sales'));
        $this->assertSame('مالی', SmsOtpCategory::labelFor('finance'));
        $this->assertSame('حقوقی', SmsOtpCategory::labelFor('legal'));
        // An unknown slug (a settings group that was removed after the message was sent) is shown as stored:
        // the logs and template views and SmsMessageResource render labelFor() directly.
        $this->assertSame('nope', SmsOtpCategory::labelFor('nope'));
    }

    // ---- templates ----

    public function test_otp_template_requires_a_valid_category(): void
    {
        $this->actingAs($this->adminUser);

        $this->post(route('admin.sms.settings.templates.store'), $this->templatePayload('otp'))
            ->assertSessionHasErrors('otp_category');
        $this->post(route('admin.sms.settings.templates.store'), $this->templatePayload('otp', ['otp_category' => 'marketing']))
            ->assertSessionHasErrors('otp_category');
        $this->assertSame(0, SmsTemplate::where('service_type', 'otp')->count());

        $this->post(route('admin.sms.settings.templates.store'), $this->templatePayload('otp', ['title' => 'کد مالی', 'otp_category' => 'finance']))
            ->assertSessionHasNoErrors();
        $this->assertSame('finance', SmsTemplate::where('name', 'کد مالی')->value('otp_category'));
    }

    public function test_advertising_template_ignores_a_posted_otp_category(): void
    {
        $this->actingAs($this->adminUser);

        $this->post(route('admin.sms.settings.templates.store'), $this->templatePayload('advertising', ['title' => 'تبلیغ', 'otp_category' => 'legal']))
            ->assertSessionHasNoErrors();
        $this->assertNull(SmsTemplate::where('name', 'تبلیغ')->value('otp_category'));
    }

    public function test_switching_a_template_away_from_otp_clears_its_category(): void
    {
        $this->actingAs($this->adminUser);
        $tpl = SmsTemplate::create([
            'title' => 'قدیمی', 'content' => 'x', 'service_type' => 'otp', 'otp_category' => 'sales', 'status' => 'active',
            'ghorfe_online_list_id' => $this->businessA->id, 'user_id' => $this->adminUser->id,
        ]);

        $this->put(route('admin.sms.settings.templates.update', $tpl), $this->templatePayload('advertising', ['title' => 'قدیمی', 'otp_category' => 'sales']))
            ->assertSessionHasNoErrors();
        $this->assertNull($tpl->fresh()->otp_category);
    }

    public function test_template_form_shows_category_select_to_staff_only(): void
    {
        $this->actingAs($this->adminUser);
        $this->get(route('admin.sms.settings.templates.create', ['business_id' => $this->businessA->id]))
            ->assertOk()->assertSee('name="otp_category"', false)->assertSee('حقوقی');

        $this->actingAs($this->lowerTierUser());
        $this->get(route('admin.sms.settings.templates.create', ['business_id' => $this->businessA->id]))
            ->assertOk()->assertDontSee('name="otp_category"', false);
    }

    public function test_lower_tier_cannot_create_otp_template_even_with_a_category(): void
    {
        $this->actingAs($this->lowerTierUser());

        $this->post(route('admin.sms.settings.templates.store'), $this->templatePayload('otp', ['otp_category' => 'sales']))
            ->assertSessionHasErrors('service_type');
        $this->assertSame(0, SmsTemplate::count());
    }

    // ---- single send ----

    public function test_otp_send_requires_a_category(): void
    {
        $this->actingAs($this->adminUser)
            ->postJson(route('admin.sms.single.send'), $this->otpPayload())
            ->assertStatus(422)->assertJsonValidationErrors(['otp_category']);
        $this->actingAs($this->adminUser)
            ->postJson(route('admin.sms.single.send'), $this->otpPayload(['otp_category' => 'bogus']))
            ->assertStatus(422)->assertJsonValidationErrors(['otp_category']);

        $this->assertSame(0, Groupsms::count());
    }

    public function test_otp_send_stores_the_category_on_the_message(): void
    {
        foreach (SmsOtpCategory::values() as $slug) {
            $this->actingAs($this->adminUser)
                ->postJson(route('admin.sms.single.send'), $this->otpPayload(['otp_category' => $slug]))
                ->assertOk();
        }

        $this->assertEqualsCanonicalizing(SmsOtpCategory::values(), Groupsms::pluck('otp_category')->all());
    }

    public function test_otp_send_with_a_template_uses_the_template_category_and_rejects_a_mismatch(): void
    {
        $tpl = SmsTemplate::create([
            'title' => 'کد حقوقی', 'content' => 'x', 'service_type' => 'otp', 'otp_category' => 'legal', 'status' => 'active',
            'ghorfe_online_list_id' => $this->businessA->id, 'user_id' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->postJson(route('admin.sms.single.send'), $this->otpPayload(['template_id' => $tpl->id, 'otp_category' => 'sales']))
            ->assertStatus(422)->assertJsonValidationErrors(['otp_category']);

        $this->actingAs($this->adminUser)
            ->postJson(route('admin.sms.single.send'), $this->otpPayload(['template_id' => $tpl->id]))
            ->assertOk();
        $this->assertSame('legal', Groupsms::firstOrFail()->otp_category);
    }

    public function test_service_layer_enforces_the_category_even_without_the_http_request(): void
    {
        $this->expectException(\DomainException::class);
        app(SingleSmsSendService::class)->send([
            'recipient_phone' => '09123334455', 'message' => 'کد', 'service_type' => 'otp',
        ], $this->businessA, $this->adminUser);
    }

    public function test_advertising_send_never_stores_an_otp_category(): void
    {
        $this->actingAs($this->adminUser)
            ->postJson(route('admin.sms.single.send'), [
                'recipient_phone' => '09123334455', 'message' => 'تخفیف', 'service_type' => 'advertising',
                'sender_number_id' => $this->advSender1->id, 'otp_category' => 'sales',
            ])
            ->assertOk();

        $this->assertNull(Groupsms::firstOrFail()->otp_category);
    }

    public function test_lower_tier_cannot_send_otp_at_all(): void
    {
        $this->actingAs($this->lowerTierUser('gold'))
            ->postJson(route('admin.sms.single.send'), $this->otpPayload(['otp_category' => 'sales']))
            ->assertForbidden();

        $this->assertSame(0, Groupsms::count());
    }

    public function test_single_send_form_shows_the_category_select_to_staff_only(): void
    {
        $this->actingAs($this->adminUser)->get(route('admin.sms.single.index'))
            ->assertOk()->assertSee('name="otp_category"', false);

        $this->actingAs($this->lowerTierUser())->get(route('admin.sms.single.index'))
            ->assertOk()->assertDontSee('name="otp_category"', false);
    }

    // ---- bulk ----

    public function test_bulk_requests_accept_an_otp_category_for_the_otp_panel(): void
    {
        // The OTP panel of the bulk page takes the same fixed category as the single page (the bulk page was
        // synced with the single page later). Promotional bulk sends ignore it (see SendBulkSmsRequest).
        $this->assertArrayHasKey('otp_category', (new \App\Http\Requests\Admin\Sms\SendBulkSmsRequest)->rules());
        $this->assertArrayHasKey('otp_category', (new \App\Http\Requests\Admin\Sms\PreviewBulkSmsRequest)->rules());
        $this->assertStringContainsString('resolveOtpCategory', file_get_contents(app_path('Services/Sms/BulkSmsSendService.php')));
    }

    // ---- logs ----

    protected function logMessage(array $attrs): Groupsms
    {
        return Groupsms::create(array_merge([
            'ghorfe_online_list_id' => $this->businessA->id, 'direction' => 'outbound', 'service_type' => 'otp',
            'sender_number_id' => $this->otpSender->id, 'sender_phone' => '90001', 'recipient_phone' => '09123334455',
            'content' => 'محتوا', 'status' => 'sent', 'cost' => 1200, 'tracking_id' => 'T' . random_int(1, 999999),
        ], $attrs));
    }

    public function test_logs_show_the_category_label_and_filter_by_it_for_staff(): void
    {
        $this->logMessage(['otp_category' => 'finance', 'content' => 'پیام مالی']);
        $this->logMessage(['otp_category' => 'sales', 'content' => 'پیام فروش']);

        $this->actingAs($this->adminUser);
        $this->get(route('admin.sms.logs.index', ['business_id' => $this->businessA->id]))
            ->assertOk()->assertSee('name="otp_category"', false)->assertSee('مالی')->assertSee('فروش');

        $res = $this->get(route('admin.sms.logs.index', ['business_id' => $this->businessA->id, 'otp_category' => 'finance']));
        $res->assertOk()->assertSee('پیام مالی')->assertDontSee('پیام فروش');
    }

    public function test_lower_tiers_never_see_otp_rows_or_the_category_filter(): void
    {
        $this->logMessage(['otp_category' => 'finance', 'content' => 'پیام محرمانه OTP']);
        $this->logMessage(['service_type' => 'advertising', 'content' => 'پیام تبلیغ عادی', 'otp_category' => null]);

        $this->actingAs($this->lowerTierUser('gold'));
        $res = $this->get(route('admin.sms.logs.index', ['business_id' => $this->businessA->id, 'otp_category' => 'finance', 'service_type' => 'otp']));
        $res->assertOk()->assertDontSee('name="otp_category"', false)->assertDontSee('پیام محرمانه OTP');

        $this->get(route('admin.sms.logs.index', ['business_id' => $this->businessA->id]))
            ->assertSee('پیام تبلیغ عادی')->assertDontSee('پیام محرمانه OTP');
    }
}

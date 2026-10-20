<?php

namespace Tests\Feature;

use App\Models\AdvertisingBalance;
use App\Models\BusinessSenderNumber;
use App\Models\GhorfeOnlineList;
use App\Models\OtpBalance;
use App\Models\SenderNumber;
use App\Models\SmsTopUpPayment;
use App\Models\User;
use App\Services\Sms\SmsPaymentGateway;
use App\Services\Sms\SmsTopUpPaymentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Lib\BankVerifier;
use App\Lib\saman;
use App\Lib\zarinpal;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * SMS balances are credited only after the payment module reports a verified success.
 */
class SmsTopUpPaymentTest extends TestCase
{
    use \Tests\Concerns\SeedsSmsBalances;

    protected User $adminUser;
    protected User $superAdminUser;
    protected GhorfeOnlineList $businessA;
    protected GhorfeOnlineList $businessB;
    protected SenderNumber $otpSender;
    protected SenderNumber $advSender1;
    protected SenderNumber $advSender2;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists('App\Models\SmsTopUpPayment')) {
            $this->markTestSkipped('SmsTopUpPayment and parallel payment tables were deleted when reverting db to main branch.');
        }

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

        // Create prerequisites needed by foreign keys
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('family')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('level')->default('user');
            $table->string('access_tier')->default('silver');
            $table->boolean('active')->default(true);
            $table->string('token')->nullable();
            $table->string('api_token')->nullable();
            $table->string('avatar')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('ghorfe_online_lists', function (Blueprint $table) {
            $table->id();
            $table->boolean('sms_contract_accepted')->default(true); // tests exercise the module, not the contract gate
            $table->string('title');
            $table->string('slug')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $table->string('work_title')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('display_name')->nullable();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('user_id');
            $table->string('user_type');
            $table->primary(['user_id', 'role_id', 'user_type']);
        });

        Schema::create('category_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('display_name')->nullable();
            $table->unsignedInteger('cat_id')->nullable();
            $table->boolean('isMenu')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('permission_user', function (Blueprint $table) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('user_id');
            $table->string('user_type');
            $table->primary(['user_id', 'permission_id', 'user_type']);
        });

        Schema::create('employee_terms', function (Blueprint $table) {
            $table->id();
            $table->string('role_category')->unique();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('contexts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('province_id')->unsigned()->nullable();
            $table->timestamps();
        });

        // Run SMS migrations
        $migrationFiles = [
            'database/migrations/2026_10_07_000001_create_sender_numbers_table.php',
            'database/migrations/2026_10_07_000002_create_sms_message_groups_table.php',
            'database/migrations/2026_10_07_000003_create_sms_message_templates_table.php',
            'database/migrations/2026_10_07_000004_create_sms_panels_table.php',
            'database/migrations/2026_10_07_000005_create_business_sender_number_table.php',
            'database/migrations/2026_10_07_000006_create_otp_balances_table.php',
            'database/migrations/2026_10_07_000007_create_advertising_balances_table.php',
            'database/migrations/2026_10_07_000008_create_sms_message_template_histories_table.php',
            'database/migrations/2026_10_07_000009_create_sms_campaigns_table.php',
            'database/migrations/2026_10_07_000010_create_sms_messages_table.php',
            'database/migrations/2026_10_07_000011_create_sms_transactions_table.php',
            'database/migrations/2026_10_07_000012_create_sms_campaign_recipients_table.php',
            'database/migrations/2026_10_11_000017_add_classification_to_sms_messages_and_campaigns.php',
            'database/migrations/2026_10_12_000002_separate_categories_and_templates_per_business.php',
            'database/migrations/2026_10_15_000001_make_advertising_balance_per_business.php',
            'database/migrations/2026_10_16_000001_add_callcenter_id_to_sms_messages_and_recipients.php',
            'database/migrations/2026_10_18_000001_create_sms_audit_logs_and_sender_role.php',
            'database/migrations/2026_10_20_000001_add_otp_category_to_sms_templates_and_messages.php',
            'database/migrations/2026_10_21_000001_create_sms_top_up_payments_table.php',
        ];

        foreach ($migrationFiles as $file) {
            $migration = require base_path($file);
            $migration->up();
        }

        // Setup test data
        $this->adminUser = User::create([
            'name' => 'ادمین',
            'family' => 'کسب‌وکار',
            'phone' => '09121111111',
            'level' => 'admin',
            'access_tier' => 'titanium', // OTP credit/reports are Titanium-only
            'active' => true,
        ]);

        $this->superAdminUser = User::create([
            'name' => 'مدیر',
            'family' => 'ارشد کل',
            'phone' => '09120000000',
            'level' => 'superAdmin',
            'active' => true,
        ]);

        $this->businessA = GhorfeOnlineList::create(['title' => 'فروشگاه آلفا', 'slug' => 'alfa']);
        $this->businessB = GhorfeOnlineList::create(['title' => 'فروشگاه بتا', 'slug' => 'beta']);

        \App\Models\Employee::create([
            'user_id' => $this->adminUser->id,
            'ghorfe_online_list_id' => $this->businessA->id,
            'work_title' => 'مدیر فروشگاه',
        ]);

        \App\Models\Employee::create([
            'user_id' => $this->superAdminUser->id,
            'ghorfe_online_list_id' => null,
            'work_title' => 'مدیر ارشد سامانه',
        ]);

        // OTP Sender Number (singleton)
        $this->otpSender = SenderNumber::create([
            'number' => '90001',
            'type' => 'otp',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

        // Advertising Sender Numbers
        $this->advSender1 = SenderNumber::create([
            'number' => '30001111',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

        $this->advSender2 = SenderNumber::create([
            'number' => '30002222',
            'type' => 'advertising',
            'sms_panel' => 'NeginSms',
            'status' => 'active',
        ]);

        // Assign advertising numbers to Business A
        $this->businessA->senderNumbers()->attach([
            $this->advSender1->id => ['assigned_at' => now()],
            $this->advSender2->id => ['assigned_at' => now()],
        ]);
    }

    protected function fakeGateway(): void
    {
        $this->app->instance(SmsPaymentGateway::class, new class implements SmsPaymentGateway {
            public int $n = 0;

            public function start(SmsTopUpPayment $payment): string
            {
                $payment->update(['gateway' => 'fake', 'authority' => 'AUTH-' . $payment->id . '-' . (++$this->n)]);

                return 'https://gateway.test/pay/' . $payment->authority;
            }
        });
    }

    protected function otpBal(GhorfeOnlineList $b): float
    {
        return (float) OtpBalance::where('ghorfe_online_list_id', $b->id)->value('balance');
    }

    protected function advBal(GhorfeOnlineList $b): float
    {
        return (float) AdvertisingBalance::where('ghorfe_online_list_id', $b->id)->value('balance');
    }

    public function test_direct_post_never_credits_without_gateway(): void
    {
        // The real adapter is bound; the bank never answers (no real HTTP), so nothing can start.
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response([], 500)]);
        $this->actingAs($this->adminUser);

        foreach (['admin.sms.financial.otp.topup', 'admin.sms.financial.advertising.topup'] as $route) {
            $res = $this->postJson(route($route), ['business_id' => $this->businessA->id, 'amount' => 500000, 'tracking_id' => 'FORGED-1']);
            $res->assertStatus(503)->assertJson(['success' => false]);
        }

        $this->assertDatabaseCount('sms_transactions', 0);
        $this->assertEquals(0, $this->otpBal($this->businessA));
        $this->assertEquals(0, $this->advBal($this->businessA));
        $this->assertDatabaseMissing('sms_top_up_payments', ['status' => 'paid']);
    }

    public function test_post_starts_payment_and_redirects_to_gateway_without_crediting(): void
    {
        $this->fakeGateway();
        $this->actingAs($this->adminUser);

        $res = $this->postJson(route('admin.sms.financial.advertising.topup'), [
            'business_id' => $this->businessA->id, 'amount' => 300000, 'tracking_id' => 'FORGED-2',
        ]);

        $res->assertOk()->assertJson(['success' => true]);
        $this->assertStringStartsWith('https://gateway.test/pay/', $res->json('redirect_url'));
        $this->assertDatabaseHas('sms_top_up_payments', [
            'ghorfe_online_list_id' => $this->businessA->id, 'credit_type' => 'advertising', 'status' => 'pending',
        ]);
        $this->assertEquals(0, $this->advBal($this->businessA));
        $this->assertDatabaseCount('sms_transactions', 0);

        // Non-JSON form post: browser is redirected to the gateway.
        $this->post(route('admin.sms.financial.otp.topup'), ['business_id' => $this->businessA->id, 'amount' => 200000])
            ->assertRedirectContains('https://gateway.test/pay/');
        $this->assertEquals(0, $this->otpBal($this->businessA));
    }

    public function test_verified_success_credits_exactly_once_and_is_idempotent(): void
    {
        $this->fakeGateway();
        $this->actingAs($this->adminUser);
        $this->postJson(route('admin.sms.financial.otp.topup'), ['business_id' => $this->businessA->id, 'amount' => 250000]);
        $payment = SmsTopUpPayment::firstOrFail();
        $svc = app(SmsTopUpPaymentService::class);

        $paid = $svc->settleVerified($payment->authority, 250000, 'REF-1');
        $this->assertTrue($paid->isPaid());
        $this->assertEquals(250000, $this->otpBal($this->businessA));
        $this->assertEquals(0, $this->advBal($this->businessA));
        $this->assertDatabaseHas('sms_transactions', [
            'ghorfe_online_list_id' => $this->businessA->id, 'credit_type' => 'otp', 'charge_amount' => 250000,
            'tracking_id' => 'PAY-' . $payment->id . '-REF-1',
        ]);

        // callback replay / double click
        $svc->settleVerified($payment->authority, 250000, 'REF-1');
        $svc->settleVerified($payment->id, 250000, 'REF-1');
        $this->assertEquals(250000, $this->otpBal($this->businessA));
        $this->assertDatabaseCount('sms_transactions', 1);

        // a later failure signal cannot undo or double anything
        $svc->settleFailed($payment->id, 'late');
        $this->assertTrue($payment->refresh()->isPaid());
    }

    public function test_credit_goes_to_stored_business_and_type_only(): void
    {
        $this->fakeGateway();
        $this->actingAs($this->adminUser);
        // forged business_id by a non-super admin is ignored: ghorfe() decides
        $this->postJson(route('admin.sms.financial.advertising.topup'), ['business_id' => $this->businessB->id, 'amount' => 100000]);
        $payment = SmsTopUpPayment::firstOrFail();
        $this->assertSame($this->businessA->id, (int) $payment->ghorfe_online_list_id);

        app(SmsTopUpPaymentService::class)->settleVerified($payment->authority, 100000, 'R');

        $this->assertEquals(100000, $this->advBal($this->businessA));
        $this->assertEquals(0, $this->advBal($this->businessB));
        $this->assertEquals(0, $this->otpBal($this->businessA));
    }

    public function test_wrong_amount_or_unknown_authority_does_not_credit(): void
    {
        $this->fakeGateway();
        $this->actingAs($this->adminUser);
        $this->postJson(route('admin.sms.financial.otp.topup'), ['business_id' => $this->businessA->id, 'amount' => 100000]);
        $payment = SmsTopUpPayment::firstOrFail();
        $svc = app(SmsTopUpPaymentService::class);

        $row = $svc->settleVerified($payment->id, 1000, 'R');
        $this->assertSame('failed', $row->status);
        $this->assertEquals(0, $this->otpBal($this->businessA));

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $svc->settleVerified('FORGED-AUTHORITY', 100000, 'R');
    }

    public function test_failed_or_cancelled_payment_does_not_credit(): void
    {
        $this->fakeGateway();
        $this->actingAs($this->adminUser);
        $svc = app(SmsTopUpPaymentService::class);

        $this->postJson(route('admin.sms.financial.otp.topup'), ['amount' => 100000]);
        $this->postJson(route('admin.sms.financial.advertising.topup'), ['amount' => 100000]);
        [$p1, $p2] = SmsTopUpPayment::orderBy('id')->get()->all();

        $this->assertSame('failed', $svc->settleFailed($p1->id, 'bank_declined')->status);
        $this->assertSame('cancelled', $svc->settleFailed($p2->authority, 'user_cancelled', true)->status);

        // a failed payment cannot be verified later
        $this->expectException(\DomainException::class);
        try {
            $svc->settleVerified($p1->id, 100000, 'R');
        } finally {
            $this->assertEquals(0, $this->otpBal($this->businessA));
            $this->assertEquals(0, $this->advBal($this->businessA));
            $this->assertDatabaseCount('sms_transactions', 0);
        }
    }

    public function test_result_page_is_read_only_and_scoped_to_the_business(): void
    {
        $this->fakeGateway();
        $this->actingAs($this->adminUser);
        $this->postJson(route('admin.sms.financial.otp.topup'), ['business_id' => $this->businessA->id, 'amount' => 100000]);
        $payment = SmsTopUpPayment::firstOrFail();

        $this->get(route('admin.sms.financial.topup.result', $payment))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEquals(0, $this->otpBal($this->businessA), 'visiting the result page never credits');

        $foreign = SmsTopUpPayment::create([
            'ghorfe_online_list_id' => $this->businessB->id, 'credit_type' => 'otp', 'amount' => 1000, 'status' => 'pending',
        ]);
        $this->get(route('admin.sms.financial.topup.result', $foreign))->assertForbidden();
    }

    // ---- payment module wiring (callback + adapter), all bank calls faked ----

    protected function pendingPayment(string $authority = 'A-100', float $amount = 100000, string $type = 'otp'): SmsTopUpPayment
    {
        return SmsTopUpPayment::create([
            'ghorfe_online_list_id' => $this->businessA->id, 'user_id' => $this->adminUser->id, 'credit_type' => $type,
            'amount' => $amount, 'status' => 'pending', 'gateway' => 'zarinpal', 'authority' => $authority,
        ]);
    }

    protected function fakeVerifier(array $zarinpal = [], array $saman = []): object
    {
        $fake = new class($zarinpal, $saman) extends BankVerifier {
            public array $calls = [];
            public function __construct(public array $z, public array $s) {}
            public function zarinpal(string $authority, $amount) { $this->calls[] = ['z', $authority, $amount]; return $this->z; }
            public function saman($refNum) { $this->calls[] = ['s', $refNum]; return $this->s; }
        };
        $this->app->instance(BankVerifier::class, $fake);

        return $fake;
    }

    public function test_zarinpal_callback_credits_once_and_replay_does_not(): void
    {
        $p = $this->pendingPayment('A-100', 100000);
        $v = $this->fakeVerifier(['Status' => 100, 'RefID' => '777']);

        $res = $this->get('/callback?Authority=A-100&Status=OK');
        $res->assertRedirect(route('admin.sms.financial.topup.result', $p->id));
        $this->assertEquals(100000, $this->otpBal($this->businessA));
        $this->assertEquals(10000, $v->calls[0][2], 'zarinpal is verified in toman');
        $this->assertTrue($p->refresh()->isPaid());

        $this->get('/callback?Authority=A-100&Status=OK')->assertRedirect(route('admin.sms.financial.topup.result', $p->id));
        $this->assertEquals(100000, $this->otpBal($this->businessA));
        $this->assertCount(1, $v->calls, 'replay does not hit the bank again');
        $this->assertDatabaseCount('sms_transactions', 1);
    }

    public function test_zarinpal_failed_or_cancelled_callback_does_not_credit(): void
    {
        $this->pendingPayment('A-1');
        $this->pendingPayment('A-2');
        $this->fakeVerifier(['Status' => -22]);

        $this->get('/callback?Authority=A-1&Status=OK')->assertRedirect();
        $this->get('/callback?Authority=A-2&Status=NOK')->assertRedirect();

        $this->assertEquals(0, $this->otpBal($this->businessA));
        $this->assertSame('failed', SmsTopUpPayment::where('authority', 'A-1')->value('status'));
        $this->assertSame('cancelled', SmsTopUpPayment::where('authority', 'A-2')->value('status'));
    }

    public function test_saman_callback_amount_mismatch_does_not_credit_and_match_does(): void
    {
        $this->pendingPayment('TOK-1', 100000);
        $this->pendingPayment('TOK-2', 200000);
        $this->fakeVerifier([], ['Success' => true, 'TransactionDetail' => ['RefNum' => 'R1', 'OrginalAmount' => 5000]]);
        $this->post('/callback', ['Token' => 'TOK-1', 'State' => 'OK', 'RefNum' => 'R1'])->assertRedirect();
        $this->assertSame('failed', SmsTopUpPayment::where('authority', 'TOK-1')->value('status'));
        $this->assertEquals(0, $this->otpBal($this->businessA));

        $this->fakeVerifier([], ['Success' => true, 'TransactionDetail' => ['RefNum' => 'R2', 'OrginalAmount' => 200000]]);
        $this->post('/callback', ['Token' => 'TOK-2', 'State' => 'OK', 'RefNum' => 'R2'])->assertRedirect();
        $this->assertEquals(200000, $this->otpBal($this->businessA));
        $this->assertSame('R2', SmsTopUpPayment::where('authority', 'TOK-2')->value('ref_number'));
    }

    public function test_non_sms_token_still_takes_the_invoice_path(): void
    {
        Schema::create('factors', function (Blueprint $table) {
            $table->id();
            $table->string('factorable_type')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->softDeletes();
        });
        $p = $this->pendingPayment('A-100');
        $v = $this->fakeVerifier(['Status' => 100, 'RefID' => '1']);

        // Unknown to SMS -> old behaviour: Factor::firstOrFail() => 404, no SMS side effects.
        $this->get('/callback?Authority=NOT-SMS&Status=OK')->assertNotFound();
        $this->assertSame([], $v->calls);
        $this->assertTrue($p->refresh()->isPending());
        $this->assertDatabaseCount('sms_transactions', 0);
    }

    public function test_adapter_builds_zarinpal_request_and_stores_authority(): void
    {
        config()->set('services.zarinpal.id', 'MERCHANT');
        $captured = [];
        $this->app->instance(zarinpal::class, new class($captured) extends zarinpal {
            public function __construct(public &$c) {}
            public function pay($amount, $description = 'x') { $this->c = [$amount, $description]; return 'AUTH-Z'; }
        });
        $p = SmsTopUpPayment::create([
            'ghorfe_online_list_id' => $this->businessA->id, 'credit_type' => 'otp', 'amount' => 250000, 'status' => 'pending',
        ]);
        request()->merge(['gateway' => 'zarinpal']);

        $url = app(SmsPaymentGateway::class)->start($p);

        $this->assertSame(config('services.zarinpal.url') . '/pg/StartPay/AUTH-Z', $url);
        $this->assertEquals([25000, \App\Services\Sms\PaymentModuleSmsGateway::DESCRIPTION], $captured);
        $this->assertSame('AUTH-Z', $p->refresh()->authority);
        $this->assertSame('zarinpal', $p->gateway);
    }

    public function test_adapter_defaults_to_saman_and_builds_token_request(): void
    {
        Http::fake(['sep.shaparak.ir/onlinepg/onlinepg' => Http::response(['status' => 1, 'token' => 'SAMAN-TOK'])]);
        $p = SmsTopUpPayment::create([
            'ghorfe_online_list_id' => $this->businessA->id, 'user_id' => $this->adminUser->id, 'credit_type' => 'advertising',
            'amount' => 300000, 'status' => 'pending',
        ]);

        $url = app(SmsPaymentGateway::class)->start($p);

        $this->assertSame('https://sep.shaparak.ir/OnlinePG/SendToken?token=SAMAN-TOK', $url);
        Http::assertSent(fn ($r) => $r['Amount'] == 300000 && str_starts_with($r['ResNum'], 'SMS' . $p->id . '-'));
        $this->assertSame('SAMAN-TOK', $p->refresh()->authority);
        $this->assertSame('saman', $p->gateway);
    }

    public function test_adapter_fails_closed_when_bank_refuses(): void
    {
        Http::fake(['*' => Http::response([], 500)]);
        $p = SmsTopUpPayment::create([
            'ghorfe_online_list_id' => $this->businessA->id, 'credit_type' => 'otp', 'amount' => 100000, 'status' => 'pending',
        ]);
        $this->expectException(\App\Exceptions\Sms\SmsPaymentUnavailableException::class);
        app(SmsPaymentGateway::class)->start($p);
    }
}

<?php

namespace Tests\Feature;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\Sms\BalanceGuard;
use App\Services\Sms\SmsFinancialService;
use App\Services\Sms\SmsReportService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmsFinancialAndReportTest extends TestCase
{
    use \Tests\Concerns\SeedsSmsBalances;

    protected User $adminUser;
    protected User $superAdminUser;
    protected GhorfeOnlineList $businessA;
    protected GhorfeOnlineList $businessB;
    protected Setting $otpSender;
    protected Setting $advSender1;
    protected Setting $advSender2;

    protected function setUp(): void
    {
        parent::setUp();

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
            $table->boolean('sms_contract_accepted')->default(true);
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

        Schema::create('groupsms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->integer('status')->default(0);
            $table->integer('flag')->nullable();
            $table->string('send_at')->nullable();
            $table->string('type')->default('single');
            $table->unsignedInteger('phone_list_id')->nullable();
            $table->text('phones')->nullable();
            $table->timestamps();
        });

        Schema::create('sms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mid')->nullable();
            $table->string('recipient_number')->nullable();
            $table->string('sender_number')->nullable();
            $table->string('body')->nullable();
            $table->dateTime('date')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('name')->length(121)->nullable();
            $table->text('content')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        Schema::create('sms_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone', 20)->nullable();
            $table->string('type', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('ghorfe_settings', function (Blueprint $table) {
            $table->id();
            $table->text('value')->nullable();
            $table->string('key')->nullable();
            $table->nullableMorphs('settingable');
            $table->string('number', 50)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('sms_panel', 50)->nullable();
            $table->string('status', 50)->default('active');
            $table->timestamps();
        });

        Schema::create('callcenterables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('callcenter_id');
            $table->unsignedBigInteger('callcenterable_id');
            $table->string('callcenterable_type');
            $table->timestamps();
        });

        $migration = require base_path('database/migrations/2026_10_07_000001_add_sms_module_columns_to_existing_tables.php');
        $migration->up();

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
        $this->otpSender = Setting::create([
            'key' => 'sms_sender_number',
            'number' => '90001',
            'type' => 'otp',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

        // Advertising Sender Numbers
        $this->advSender1 = Setting::create([
            'key' => 'sms_sender_number',
            'settingable_type' => GhorfeOnlineList::class,
            'settingable_id' => $this->businessA->id,
            'number' => '30001111',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

        $this->advSender2 = Setting::create([
            'key' => 'sms_sender_number',
            'settingable_type' => GhorfeOnlineList::class,
            'settingable_id' => $this->businessB->id,
            'number' => '30002222',
            'type' => 'advertising',
            'sms_panel' => 'NeginSms',
            'status' => 'active',
        ]);
    }

    // ==========================================
    // 1. BALANCE GUARD TESTS
    // ==========================================

    public function test_balance_guard_otp_warning_and_hard_stop(): void
    {
        $guard = app(BalanceGuard::class);

        // Scenario 1: Initial zero balance -> HARD STOP
        $checkZero = $guard->checkOtp($this->businessA, 1200);
        $this->assertFalse($checkZero['allowed']);
        $this->assertEquals(0, $checkZero['balance']);
        $this->assertTrue($checkZero['is_low_balance']);
        $this->assertNotNull($checkZero['error']);
        $this->assertStringContainsString('توقف', $checkZero['error']);

        // Enforce zero balance must throw InsufficientBalanceException
        $this->expectException(InsufficientBalanceException::class);
        $guard->enforceOtp($this->businessA, 1200);
    }

    public function test_balance_guard_otp_low_balance_warning(): void
    {
        $guard = app(BalanceGuard::class);

        // Set balance above zero but below threshold (50,000 <= 100,000 threshold)
        $this->seedOtpBalance($this->businessA->id, 50000, 100000);

        $checkLow = $guard->checkOtp($this->businessA, 1200);
        $this->assertTrue($checkLow['allowed'], 'Should still allow sending when balance > 0 and >= cost');
        $this->assertTrue($checkLow['is_low_balance']);
        $this->assertNotNull($checkLow['warning']);
        $this->assertStringContainsString('هشدار', $checkLow['warning']);
        $this->assertNull($checkLow['error']);

        // enforceOtp succeeds
        $model = $guard->enforceOtp($this->businessA, 1200);
        $this->assertInstanceOf(GhorfeOnlineList::class, $model);
        $this->assertEquals(50000, (float) $model->otp_balance);
    }

    public function test_balance_guard_otp_normal_balance(): void
    {
        $guard = app(BalanceGuard::class);

        // Set balance above threshold
        $this->seedOtpBalance($this->businessA->id, 500000, 100000);

        $checkNormal = $guard->checkOtp($this->businessA, 1200);
        $this->assertTrue($checkNormal['allowed']);
        $this->assertFalse($checkNormal['is_low_balance']);
        $this->assertNull($checkNormal['warning']);
        $this->assertNull($checkNormal['error']);
    }

    public function test_balance_guard_advertising_shortfall_blocks_every_line_of_the_business(): void
    {
        $guard = app(BalanceGuard::class);

        // Business A's advertising panel is short; business B has plenty of credit.
        $this->seedAdvertisingBalance($this->businessA->id, 500);
        $this->seedAdvertisingBalance($this->businessB->id, 50000);

        $requiredCost = 5000;

        $check = $guard->checkAdvertising($this->businessA, $requiredCost);

        $this->assertFalse($check['allowed']);
        $this->assertEquals(500, $check['balance']);
        $this->assertEquals($requiredCost, $check['required']);

        try {
            $guard->enforceAdvertising($this->businessA, $requiredCost);
            $this->fail('Expected InsufficientBalanceException was not thrown');
        } catch (InsufficientBalanceException $e) {
            $this->assertEquals(500, $e->currentBalance);
            $this->assertEquals($requiredCost, $e->requiredAmount);
        }

        // Tenancy: business B's credit is untouched and unusable by A.
        $this->assertEquals(50000, (float) GhorfeOnlineList::whereKey($this->businessB->id)->value('advertising_balance'));
    }

    public function test_balance_guard_advertising_sufficient_balance_is_allowed(): void
    {
        $guard = app(BalanceGuard::class);

        $this->seedAdvertisingBalance($this->businessA->id, 50000);

        $check = $guard->checkAdvertising($this->businessA, 5000);

        $this->assertTrue($check['allowed']);
        $this->assertNull($check['error']);
        $this->assertInstanceOf(GhorfeOnlineList::class, $guard->enforceAdvertising($this->businessA, 5000));
    }

    // ==========================================
    // 2. FINANCIAL SERVICE & TOP-UP TESTS
    // ==========================================

    public function test_otp_topup_adds_only_to_business_otp_row_without_cross_credit(): void
    {
        $financialService = app(SmsFinancialService::class);

        // Initial balances
        $this->seedOtpBalance($this->businessA->id, 10000);
        $this->seedOtpBalance($this->businessB->id, 20000);
        $this->seedAdvertisingBalance($this->businessA->id, 5000);

        // Top up Business A OTP with 100,000
        $tx = $financialService->topUpOtp($this->businessA, 100000, $this->adminUser, 'TOPUP-TEST-1');

        $this->assertInstanceOf(SmsLog::class, $tx);
        $this->assertEquals('otp', $tx->credit_type);
        $this->assertEquals(10000, (float) $tx->balance_before);
        $this->assertEquals(110000, (float) $tx->balance_after);
        $this->assertEquals(100000, (float) $tx->charge_amount);
        $this->assertNull($tx->sender_number_id);

        // Verify Business A OTP balance updated
        $this->assertEquals(110000, (float) GhorfeOnlineList::whereKey($this->businessA->id)->value('otp_balance'));

        // Verify Business B OTP was NEVER touched
        $this->assertEquals(20000, (float) GhorfeOnlineList::whereKey($this->businessB->id)->value('otp_balance'));

        // Verify Business A Advertising balance was NEVER touched
        $this->assertEquals(5000, (float) GhorfeOnlineList::whereKey($this->businessA->id)->value('advertising_balance'));
    }

    public function test_advertising_topup_credits_only_the_business_panel_without_cross_credit(): void
    {
        $financialService = app(SmsFinancialService::class);

        $this->seedAdvertisingBalance($this->businessA->id, 1000);
        $this->seedAdvertisingBalance($this->businessB->id, 2000);
        $this->seedOtpBalance($this->businessA->id, 50000);

        // Top up Business A Advertising with 80,000
        $tx = $financialService->topUpAdvertising($this->businessA, 80000, $this->adminUser, 'TOPUP-TEST-2');

        $this->assertInstanceOf(SmsLog::class, $tx);
        $this->assertEquals('advertising', $tx->credit_type);
        $this->assertEquals(1000, (float) $tx->balance_before);
        $this->assertEquals(81000, (float) $tx->balance_after);
        $this->assertEquals(80000, (float) $tx->charge_amount);

        $this->assertEquals(81000, (float) GhorfeOnlineList::whereKey($this->businessA->id)->value('advertising_balance'));

        // Another business' advertising balance was NOT changed
        $this->assertEquals(2000, (float) GhorfeOnlineList::whereKey($this->businessB->id)->value('advertising_balance'));

        // OTP balance was NOT changed
        $this->assertEquals(50000, (float) GhorfeOnlineList::whereKey($this->businessA->id)->value('otp_balance'));
    }

    public function test_financial_service_otp_data_shape(): void
    {
        $financialService = app(SmsFinancialService::class);

        $this->seedOtpBalance($this->businessA->id, 45000);

        // Create transactions and messages
        SmsLog::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'credit_type' => 'otp',
            'type' => 'transaction',
            'charge_amount' => 50000,
            'consume_amount' => 0,
            'balance_before' => 0,
            'balance_after' => 50000,
            'created_by' => $this->adminUser->id,
        ]);
        SmsLog::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'credit_type' => 'otp',
            'type' => 'transaction',
            'charge_amount' => 0,
            'consume_amount' => 5000,
            'balance_before' => 50000,
            'balance_after' => 45000,
            'created_by' => $this->adminUser->id,
        ]);

        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'service_type' => 'otp',
            'phones' => '09121111111',
            'message' => 'کد تایید شما: 1234',
            'status' => 'delivered',
            'cost' => 5000,
        ]);

        $data = $financialService->getOtpFinancialData($this->businessA);

        $this->assertEquals(45000, $data['balance']);
        $this->assertEquals(50000, $data['total_charged']);
        $this->assertEquals(5000, $data['total_consumed']);
        $this->assertEquals(1, $data['message_count']);
        $this->assertNotEmpty($data['history']);
        $this->assertCount(2, $data['transactions']);
    }

    // ==========================================
    // 3. HTTP ENDPOINT TESTS (FINANCIAL)
    // ==========================================

    public function test_http_otp_view_and_topup(): void
    {
        $this->actingAs($this->adminUser);

        // 1. View
        $response = $this->get(route('admin.sms.financial.otp.index', ['business_id' => $this->businessA->id]));
        $response->assertOk();
        $response->assertViewIs('Admin.sms.financial.otp');
        $response->assertViewHas(['balance', 'total_charged', 'total_consumed', 'message_count', 'transactions']);

        // 2. Top-up POST starts a payment only: no gateway is bound, so nothing is credited.
        $topupResponse = $this->post(route('admin.sms.financial.otp.topup'), [
            'business_id' => $this->businessA->id,
            'amount' => 250000,
            'tracking_id' => 'BANK-OTP-999',
        ]);

        $topupResponse->assertRedirect();
        $this->assertDatabaseMissing('sms_logs', ['tracking_id' => 'BANK-OTP-999']);
        $this->assertEquals(0, (float) GhorfeOnlineList::whereKey($this->businessA->id)->value('otp_balance'));
    }

    public function test_http_advertising_view_and_topup(): void
    {
        $this->actingAs($this->adminUser);

        // 1. View
        $response = $this->get(route('admin.sms.financial.advertising.index', [
            'business_id' => $this->businessA->id,
        ]));
        $response->assertOk();
        $response->assertViewIs('Admin.sms.financial.advertising');
        $response->assertViewHas(['balance', 'total_charged', 'total_consumed', 'message_count', 'transactions']);
        $response->assertViewMissing('senders_summary');

        // 2. Top-up POST starts a payment only: no gateway is bound, so nothing is credited.
        $topupResponse = $this->post(route('admin.sms.financial.advertising.topup'), [
            'business_id' => $this->businessA->id,
            'amount' => 300000,
            'tracking_id' => 'BANK-ADV-888',
        ]);

        $topupResponse->assertRedirect();
        $this->assertDatabaseMissing('sms_logs', ['tracking_id' => 'BANK-ADV-888']);
        $this->assertEquals(0, (float) GhorfeOnlineList::whereKey($this->businessA->id)->value('advertising_balance'));
    }

    public function test_http_balance_check_api(): void
    {
        $this->actingAs($this->adminUser);

        // Test OTP check
        $otpCheckRes = $this->postJson(route('admin.sms.financial.check'), [
            'business_id' => $this->businessA->id,
            'type' => 'otp',
            'cost' => 1200,
        ]);
        $otpCheckRes->assertOk()->assertJsonStructure(['success', 'status' => ['allowed', 'balance', 'is_low_balance']]);

        // Test Advertising check
        $advCheckRes = $this->postJson(route('admin.sms.financial.check'), [
            'business_id' => $this->businessA->id,
            'type' => 'advertising',
            'cost' => 5000,
        ]);
        $advCheckRes->assertOk()->assertJsonStructure(['success', 'status' => ['allowed', 'balance']]);
    }

    // ==========================================
    // 4. REPORTS TESTS
    // ==========================================

    public function test_otp_report_aggregation_and_filters(): void
    {
        $this->actingAs($this->adminUser);

        // Create sample messages
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'service_type' => 'otp',
            'phones' => '09121111111',
            'message' => 'کد تایید ورود 1111',
            'status' => 'delivered',
            'cost' => 1200,
            'created_at' => Carbon::parse('2026-10-01 10:00:00'),
        ]);
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'service_type' => 'otp',
            'phones' => '09122222222',
            'message' => 'کد تایید ورود 2222',
            'status' => 'failed',
            'cost' => 1200,
            'created_at' => Carbon::parse('2026-10-02 10:00:00'),
        ]);

        $reportService = app(SmsReportService::class);
        $reportData = $reportService->getOtpReport($this->businessA);

        $this->assertEquals(2, $reportData['summary']['total_messages']);
        $this->assertEquals(2400, $reportData['summary']['total_cost']);
        $this->assertEquals(1, $reportData['summary']['delivered_count']);
        $this->assertEquals(1, $reportData['summary']['failed_count']);
        $this->assertEquals(50.0, $reportData['summary']['delivery_rate']);

        // Test status filter
        $deliveredReport = $reportService->getOtpReport($this->businessA, ['status' => 'delivered']);
        $this->assertEquals(1, $deliveredReport['summary']['total_messages']);

        // Test HTTP endpoint
        $response = $this->get(route('admin.sms.reports.otp.index', ['business_id' => $this->businessA->id]));
        $response->assertOk();
        $response->assertViewIs('Admin.sms.reports.otp');
        $response->assertViewHas(['summary', 'messages']);
    }

    public function test_advertising_report_aggregation_and_campaigns(): void
    {
        $this->actingAs($this->adminUser);

        // Create campaign
        $campaign = Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
            'type' => 'bulk',
            'is_bulk' => true,
            'total_recipients' => 100,
            'sent_count' => 100,
            'success_count' => 95,
            'failed_count' => 5,
            'status' => 'completed',
            'cost_estimate' => 120000,
            'created_by' => $this->adminUser->id,
        ]);

        // Create sample messages
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
            'service_type' => 'advertising',
            'sms_campaign_id' => $campaign->id,
            'phones' => '09123333333',
            'message' => 'تخفیف ویژه پاییزه',
            'status' => 'delivered',
            'cost' => 1200,
        ]);

        $reportService = app(SmsReportService::class);
        $data = $reportService->getAdvertisingReport($this->businessA);

        $this->assertEquals(1, $data['summary']['total_campaigns']);
        $this->assertEquals(1, $data['summary']['total_messages']);
        $this->assertEquals(1200, $data['summary']['total_cost']);
        $this->assertNotEmpty($data['sender_breakdown']);

        // Test HTTP endpoint
        $response = $this->get(route('admin.sms.reports.advertising.index', ['business_id' => $this->businessA->id]));
        $response->assertOk();
        $response->assertViewIs('Admin.sms.reports.advertising');
    }

    public function test_super_admin_cross_business_report_gate(): void
    {
        // 1. Regular admin user should be FORBIDDEN (403)
        $this->actingAs($this->adminUser);
        $forbiddenRes = $this->get(route('admin.sms.reports.superadmin.index'));
        $forbiddenRes->assertForbidden();

        // 2. Super admin user should get 200 OK
        $this->actingAs($this->superAdminUser);
        $okRes = $this->get(route('admin.sms.reports.superadmin.index'));
        $okRes->assertOk();
        $okRes->assertViewIs('Admin.sms.reports.superadmin');
        $okRes->assertViewHas(['summary', 'messages', 'message_type_breakdown', 'business_breakdown', 'sender_breakdown']);
    }

    public function test_super_admin_report_filters(): void
    {
        $this->actingAs($this->superAdminUser);

        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->otpSender->id,
            'service_type' => 'otp',
            'group' => 'auth',
            'phones' => '09121111111',
            'message' => 'کد تایید ورود',
            'status' => 'delivered',
            'cost' => 1200,
            'created_at' => Carbon::parse('2026-10-05 10:00:00'),
        ]);

        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessB->id,
            'sender_number_id' => $this->advSender2->id,
            'service_type' => 'advertising',
            'group' => 'fest',
            'phones' => '09122222222',
            'message' => 'تخفیف ویژه برای مشتریان بتا',
            'status' => 'queued',
            'cost' => 2400,
            'created_at' => Carbon::parse('2026-10-06 12:00:00'),
        ]);

        $reportService = app(SmsReportService::class);

        // 1. Unfiltered: 2 messages across 2 businesses
        $unfiltered = $reportService->getSuperAdminReport();
        $this->assertEquals(2, $unfiltered['summary']['total_messages']);
        $this->assertEquals(2, $unfiltered['summary']['businesses_count']);
        $this->assertEquals(3600, $unfiltered['summary']['total_cost']);

        // 2. Filter by Business A
        $filteredBusiness = $reportService->getSuperAdminReport(['business' => $this->businessA->id]);
        $this->assertEquals(1, $filteredBusiness['summary']['total_messages']);
        $this->assertEquals('otp', $filteredBusiness['messages']->first()->service_type);

        // 3. Filter by Message Type (advertising)
        $filteredType = $reportService->getSuperAdminReport(['message_type' => 'advertising']);
        $this->assertEquals(1, $filteredType['summary']['total_messages']);
        $this->assertEquals($this->businessB->id, $filteredType['messages']->first()->ghorfe_online_list_id);

        // 4. Filter by Sender (advSender2)
        $filteredSender = $reportService->getSuperAdminReport(['sender' => $this->advSender2->id]);
        $this->assertEquals(1, $filteredSender['summary']['total_messages']);

        // 5. Filter by Status (delivered)
        $filteredStatus = $reportService->getSuperAdminReport(['status' => 'delivered']);
        $this->assertEquals(1, $filteredStatus['summary']['total_messages']);

        // 6. Filter by Group (fest)
        $filteredGroup = $reportService->getSuperAdminReport(['group' => 'fest']);
        $this->assertEquals(1, $filteredGroup['summary']['total_messages']);
    }

    public function test_excel_exports_respond_successfully(): void
    {
        $this->actingAs($this->superAdminUser);

        // Create sample messages
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->otpSender->id,
            'service_type' => 'otp',
            'phones' => '09121111111',
            'message' => 'کد تایید ورود',
            'status' => 'delivered',
            'cost' => 1200,
        ]);
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
            'service_type' => 'advertising',
            'phones' => '09122222222',
            'message' => 'تخفیف ویژه',
            'status' => 'sent',
            'cost' => 1200,
        ]);

        // 1. OTP export
        $otpRes = $this->get(route('admin.sms.reports.otp.export', ['business_id' => $this->businessA->id]));
        $otpRes->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $otpRes->headers->get('Content-Type'));

        // 2. Advertising export
        $advRes = $this->get(route('admin.sms.reports.advertising.export', ['business_id' => $this->businessA->id]));
        $advRes->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $advRes->headers->get('Content-Type'));

        // 3. Super Admin export
        $superRes = $this->get(route('admin.sms.reports.superadmin.export'));
        $superRes->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $superRes->headers->get('Content-Type'));
    }

    public function test_exports_without_business_id_redirect_super_admin_instead_of_403(): void
    {
        $this->actingAs($this->superAdminUser);

        foreach (['admin.sms.reports.otp.export', 'admin.sms.reports.advertising.export'] as $name) {
            $res = $this->get(route($name, ['status' => 'sent']));
            $res->assertRedirect();
            $location = $res->headers->get('Location');
            $this->assertStringContainsString('business_id=' . GhorfeOnlineList::query()->value('id'), $location);
            $this->assertStringContainsString('status=sent', $location);
            $this->get($location)->assertOk();
        }
    }

    public function test_export_without_any_business_is_still_forbidden_for_non_super_admin(): void
    {
        $loner = User::create([
            'name' => 'بی', 'family' => 'کسب‌وکار', 'phone' => '09125550000',
            'level' => 'admin', 'access_tier' => 'titanium', 'active' => true,
        ]);
        $this->actingAs($loner);

        $this->get(route('admin.sms.reports.advertising.export', ['business_id' => $this->businessB->id]))
            ->assertForbidden();
        $this->get(route('admin.sms.reports.advertising.export'))->assertForbidden();
    }
}

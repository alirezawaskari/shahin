<?php

namespace Tests\Feature;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Models\AdvertisingBalance;
use App\Models\BusinessSenderNumber;
use App\Models\GhorfeOnlineList;
use App\Models\OtpBalance;
use App\Models\SenderNumber;
use App\Models\SmsCampaign;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\SmsPanel;
use App\Models\SmsTransaction;
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
            'database/migrations/2026_10_07_000002_create_business_sender_number_table.php',
            'database/migrations/2026_10_07_000003_create_otp_balances_table.php',
            'database/migrations/2026_10_07_000004_create_advertising_balances_table.php',
            'database/migrations/2026_10_07_000005_create_sms_message_groups_table.php',
            'database/migrations/2026_10_07_000006_create_sms_message_templates_table.php',
            'database/migrations/2026_10_07_000007_create_sms_message_template_histories_table.php',
            'database/migrations/2026_10_07_000008_create_sms_campaigns_table.php',
            'database/migrations/2026_10_07_000009_create_sms_messages_table.php',
            'database/migrations/2026_10_07_000010_create_sms_transactions_table.php',
            'database/migrations/2026_10_07_000011_create_sms_campaign_recipients_table.php',
            'database/migrations/2026_10_07_000013_create_sms_panels_table.php',
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
        OtpBalance::updateOrCreate(
            ['ghorfe_online_list_id' => $this->businessA->id],
            ['balance' => 50000, 'low_balance_threshold' => 100000]
        );

        $checkLow = $guard->checkOtp($this->businessA, 1200);
        $this->assertTrue($checkLow['allowed'], 'Should still allow sending when balance > 0 and >= cost');
        $this->assertTrue($checkLow['is_low_balance']);
        $this->assertNotNull($checkLow['warning']);
        $this->assertStringContainsString('هشدار', $checkLow['warning']);
        $this->assertNull($checkLow['error']);

        // enforceOtp succeeds
        $model = $guard->enforceOtp($this->businessA, 1200);
        $this->assertInstanceOf(OtpBalance::class, $model);
        $this->assertEquals(50000, (float) $model->balance);
    }

    public function test_balance_guard_otp_normal_balance(): void
    {
        $guard = app(BalanceGuard::class);

        // Set balance above threshold
        OtpBalance::updateOrCreate(
            ['ghorfe_online_list_id' => $this->businessA->id],
            ['balance' => 500000, 'low_balance_threshold' => 100000]
        );

        $checkNormal = $guard->checkOtp($this->businessA, 1200);
        $this->assertTrue($checkNormal['allowed']);
        $this->assertFalse($checkNormal['is_low_balance']);
        $this->assertNull($checkNormal['warning']);
        $this->assertNull($checkNormal['error']);
    }

    public function test_balance_guard_advertising_surfaces_alternative_sender(): void
    {
        $guard = app(BalanceGuard::class);

        // Sender 1: low balance (500 Rials)
        AdvertisingBalance::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
            'balance' => 500,
        ]);

        // Sender 2: sufficient balance (50,000 Rials)
        AdvertisingBalance::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender2->id,
            'balance' => 50000,
        ]);

        $requiredCost = 5000;

        // Check Sender 1: should be blocked, BUT surface Sender 2 as alternative!
        $check = $guard->checkAdvertising($this->businessA, $this->advSender1, $requiredCost);

        $this->assertFalse($check['allowed']);
        $this->assertEquals(500, $check['balance']);
        $this->assertNotNull($check['alternative_sender']);
        $this->assertEquals($this->advSender2->id, $check['alternative_sender']->id);
        $this->assertStringContainsString($this->advSender2->number, $check['error']);

        // Check enforceAdvertising throws InsufficientBalanceException carrying the alternative sender
        try {
            $guard->enforceAdvertising($this->businessA, $this->advSender1, $requiredCost);
            $this->fail('Expected InsufficientBalanceException was not thrown');
        } catch (InsufficientBalanceException $e) {
            $this->assertNotNull($e->alternativeSender);
            $this->assertEquals($this->advSender2->id, $e->alternativeSender->id);
            $this->assertEquals(500, $e->currentBalance);
            $this->assertEquals($requiredCost, $e->requiredAmount);
        }
    }

    public function test_balance_guard_advertising_when_no_alternative_available(): void
    {
        $guard = app(BalanceGuard::class);

        // Both Sender 1 and Sender 2 have insufficient balance
        AdvertisingBalance::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
            'balance' => 100,
        ]);
        AdvertisingBalance::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender2->id,
            'balance' => 200,
        ]);

        $requiredCost = 5000;
        $check = $guard->checkAdvertising($this->businessA, $this->advSender1, $requiredCost);

        $this->assertFalse($check['allowed']);
        $this->assertNull($check['alternative_sender']);
        $this->assertEmpty($check['alternative_senders']);
        $this->assertStringContainsString('هیچ خط جایگزین', $check['error']);
    }

    // ==========================================
    // 2. FINANCIAL SERVICE & TOP-UP TESTS
    // ==========================================

    public function test_otp_topup_adds_only_to_business_otp_row_without_cross_credit(): void
    {
        $financialService = app(SmsFinancialService::class);

        // Initial balances
        OtpBalance::create(['ghorfe_online_list_id' => $this->businessA->id, 'balance' => 10000]);
        OtpBalance::create(['ghorfe_online_list_id' => $this->businessB->id, 'balance' => 20000]);
        AdvertisingBalance::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
            'balance' => 5000,
        ]);

        // Top up Business A OTP with 100,000
        $tx = $financialService->topUpOtp($this->businessA, 100000, $this->adminUser, 'TOPUP-TEST-1');

        $this->assertInstanceOf(SmsTransaction::class, $tx);
        $this->assertEquals('otp', $tx->credit_type);
        $this->assertEquals(10000, (float) $tx->balance_before);
        $this->assertEquals(110000, (float) $tx->balance_after);
        $this->assertEquals(100000, (float) $tx->charge_amount);
        $this->assertNull($tx->sender_number_id);

        // Verify Business A OTP balance updated
        $this->assertEquals(110000, (float) OtpBalance::where('ghorfe_online_list_id', $this->businessA->id)->value('balance'));

        // Verify Business B OTP was NEVER touched
        $this->assertEquals(20000, (float) OtpBalance::where('ghorfe_online_list_id', $this->businessB->id)->value('balance'));

        // Verify Business A Advertising balance was NEVER touched
        $this->assertEquals(5000, (float) AdvertisingBalance::where('ghorfe_online_list_id', $this->businessA->id)
            ->where('sender_number_id', $this->advSender1->id)->value('balance'));
    }

    public function test_advertising_topup_adds_only_to_chosen_sender_row_without_cross_credit(): void
    {
        $financialService = app(SmsFinancialService::class);

        // Initial balances
        AdvertisingBalance::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
            'balance' => 1000,
        ]);
        AdvertisingBalance::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender2->id,
            'balance' => 2000,
        ]);
        OtpBalance::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'balance' => 50000,
        ]);

        // Top up Business A Sender 1 with 80,000
        $tx = $financialService->topUpAdvertising($this->businessA, $this->advSender1, 80000, $this->adminUser);

        $this->assertInstanceOf(SmsTransaction::class, $tx);
        $this->assertEquals('advertising', $tx->credit_type);
        $this->assertEquals($this->advSender1->id, $tx->sender_number_id);
        $this->assertEquals(1000, (float) $tx->balance_before);
        $this->assertEquals(81000, (float) $tx->balance_after);
        $this->assertEquals(80000, (float) $tx->charge_amount);

        // Verify Sender 1 balance updated
        $this->assertEquals(81000, (float) AdvertisingBalance::where('ghorfe_online_list_id', $this->businessA->id)
            ->where('sender_number_id', $this->advSender1->id)->value('balance'));

        // Verify Sender 2 balance was NOT changed
        $this->assertEquals(2000, (float) AdvertisingBalance::where('ghorfe_online_list_id', $this->businessA->id)
            ->where('sender_number_id', $this->advSender2->id)->value('balance'));

        // Verify OTP balance was NOT changed
        $this->assertEquals(50000, (float) OtpBalance::where('ghorfe_online_list_id', $this->businessA->id)->value('balance'));
    }

    public function test_financial_service_otp_data_shape(): void
    {
        $financialService = app(SmsFinancialService::class);

        OtpBalance::create(['ghorfe_online_list_id' => $this->businessA->id, 'balance' => 45000]);

        // Create transactions and messages
        SmsTransaction::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'credit_type' => 'otp',
            'charge_amount' => 50000,
            'consume_amount' => 0,
            'balance_before' => 0,
            'balance_after' => 50000,
            'created_by' => $this->adminUser->id,
        ]);
        SmsTransaction::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'credit_type' => 'otp',
            'charge_amount' => 0,
            'consume_amount' => 5000,
            'balance_before' => 50000,
            'balance_after' => 45000,
            'created_by' => $this->adminUser->id,
        ]);

        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'service_type' => 'otp',
            'recipient_phone' => '09121111111',
            'content' => 'کد تایید شما: 1234',
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

        // 2. Top-up POST
        $topupResponse = $this->post(route('admin.sms.financial.otp.topup'), [
            'business_id' => $this->businessA->id,
            'amount' => 250000,
            'tracking_id' => 'BANK-OTP-999',
        ]);

        $topupResponse->assertRedirect();
        $this->assertDatabaseHas('sms_transactions', [
            'ghorfe_online_list_id' => $this->businessA->id,
            'credit_type' => 'otp',
            'charge_amount' => 250000,
            'tracking_id' => 'BANK-OTP-999',
        ]);
        $this->assertEquals(250000, (float) OtpBalance::where('ghorfe_online_list_id', $this->businessA->id)->value('balance'));
    }

    public function test_http_advertising_view_and_topup(): void
    {
        $this->actingAs($this->adminUser);

        // 1. View
        $response = $this->get(route('admin.sms.financial.advertising.index', [
            'business_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
        ]));
        $response->assertOk();
        $response->assertViewIs('Admin.sms.financial.advertising');
        $response->assertViewHas(['assigned_senders', 'senders_summary', 'sender_details']);

        // 2. Top-up POST
        $topupResponse = $this->post(route('admin.sms.financial.advertising.topup'), [
            'business_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
            'amount' => 300000,
            'tracking_id' => 'BANK-ADV-888',
        ]);

        $topupResponse->assertRedirect();
        $this->assertDatabaseHas('sms_transactions', [
            'ghorfe_online_list_id' => $this->businessA->id,
            'credit_type' => 'advertising',
            'sender_number_id' => $this->advSender1->id,
            'charge_amount' => 300000,
            'tracking_id' => 'BANK-ADV-888',
        ]);
        $this->assertEquals(300000, (float) AdvertisingBalance::where('ghorfe_online_list_id', $this->businessA->id)
            ->where('sender_number_id', $this->advSender1->id)->value('balance'));
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
            'sender_number_id' => $this->advSender1->id,
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
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'service_type' => 'otp',
            'recipient_phone' => '09121111111',
            'content' => 'کد تایید ورود 1111',
            'status' => 'delivered',
            'cost' => 1200,
            'created_at' => Carbon::parse('2026-10-01 10:00:00'),
        ]);
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'service_type' => 'otp',
            'recipient_phone' => '09122222222',
            'content' => 'کد تایید ورود 2222',
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
        $campaign = SmsCampaign::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
            'type' => 'bulk',
            'total_recipients' => 100,
            'sent_count' => 100,
            'success_count' => 95,
            'failed_count' => 5,
            'status' => 'completed',
            'cost_estimate' => 120000,
            'created_by' => $this->adminUser->id,
        ]);

        // Create sample messages
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
            'service_type' => 'advertising',
            'sms_campaign_id' => $campaign->id,
            'recipient_phone' => '09123333333',
            'content' => 'تخفیف ویژه پاییزه',
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

        // Seed messages across Business A and Business B
        $groupA = SmsMessageGroup::create(['name' => 'ورود و احراز', 'slug' => 'auth']);
        $groupB = SmsMessageGroup::create(['name' => 'تبلیغات جشنواره', 'slug' => 'fest']);

        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->otpSender->id,
            'service_type' => 'otp',
            'sms_message_group_id' => $groupA->id,
            'recipient_phone' => '09121111111',
            'content' => 'کد تایید ورود',
            'status' => 'delivered',
            'cost' => 1200,
            'created_at' => Carbon::parse('2026-10-05 10:00:00'),
        ]);

        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessB->id,
            'sender_number_id' => $this->advSender2->id,
            'service_type' => 'advertising',
            'sms_message_group_id' => $groupB->id,
            'recipient_phone' => '09122222222',
            'content' => 'تخفیف ویژه برای مشتریان بتا',
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

        // 6. Filter by Group (groupB)
        $filteredGroup = $reportService->getSuperAdminReport(['group' => $groupB->id]);
        $this->assertEquals(1, $filteredGroup['summary']['total_messages']);
    }

    public function test_excel_exports_respond_successfully(): void
    {
        $this->actingAs($this->superAdminUser);

        // Create sample messages
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->otpSender->id,
            'service_type' => 'otp',
            'recipient_phone' => '09121111111',
            'content' => 'کد تایید ورود',
            'status' => 'delivered',
            'cost' => 1200,
        ]);
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_number_id' => $this->advSender1->id,
            'service_type' => 'advertising',
            'recipient_phone' => '09122222222',
            'content' => 'تخفیف ویژه',
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
}

<?php

namespace Tests\Feature;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Models\AdvertisingBalance;
use App\Models\BusinessSenderNumber;
use App\Models\GhorfeOnlineList;
use App\Models\History;
use App\Models\OtpBalance;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\SmsTransaction;
use App\Models\User;
use App\Services\Sms\SingleSmsSendService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmsSingleSendTest extends TestCase
{
    protected GhorfeOnlineList $business;
    protected SenderNumber $otpSender;
    protected SenderNumber $advSender;
    protected SingleSmsSendService $service;

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

        // Prerequisites
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('family')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('level')->default('user');
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
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('representations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fa_name')->nullable();
            $table->string('en_name')->nullable();
            $table->string('slug')->nullable();
            $table->integer('status')->default(1);
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

        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('display_name')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_user', function (Blueprint $table) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('user_id');
            $table->string('user_type');
            $table->primary(['user_id', 'permission_id', 'user_type']);
        });

        Schema::create('histories', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('historiable', 'historiable_index');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('callcenter_id')->unsigned()->nullable();
            $table->integer('status')->default(0);
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        // SMS migrations
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
        ];

        foreach ($migrationFiles as $file) {
            $migration = require base_path($file);
            $migration->up();
        }

        $this->business = GhorfeOnlineList::create(['title' => 'فروشگاه تستی']);

        $this->otpSender = SenderNumber::create([
            'number' => '90001111',
            'type' => 'otp',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

        $this->advSender = SenderNumber::create([
            'number' => '50002222',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

        // Attach advertising number to business
        $this->business->senderNumbers()->attach($this->advSender->id, ['assigned_at' => now()]);

        $this->service = app(SingleSmsSendService::class);
    }

    /**
     * Test character and page calculation correctness for Persian strings vs ASCII strings.
     */
    public function test_character_and_page_calculation_for_persian_vs_ascii(): void
    {
        // 1. Detection
        $this->assertTrue($this->service->isUnicode('سلام'));
        $this->assertTrue($this->service->isUnicode('Hello سلام'));
        $this->assertFalse($this->service->isUnicode('Hello World 123!'));
        $this->assertFalse($this->service->isUnicode(''));

        // 2. Persian (Unicode) segmentation rules:
        // Page 1: 1 - 70 chars
        // Page 2: 71 - 134 chars (70 + 64)
        // Page 3: 135 - 201 chars (134 + 67)
        // Page 4: 202 - 268 chars (201 + 67)

        // Empty string
        $res0 = $this->service->calculateSegmentation('');
        $this->assertEquals(0, $res0['character_count']);
        $this->assertEquals(1, $res0['page_count']);

        // 70 Persian chars -> exactly 1 page, 0 remaining
        $persian70 = str_repeat('س', 70);
        $res70 = $this->service->calculateSegmentation($persian70);
        $this->assertEquals(70, $res70['character_count']);
        $this->assertEquals(1, $res70['page_count']);
        $this->assertEquals(0, $res70['remaining_characters']);
        $this->assertTrue($res70['is_unicode']);

        // 71 Persian chars -> 2 pages, 134 - 71 = 63 remaining
        $persian71 = str_repeat('س', 71);
        $res71 = $this->service->calculateSegmentation($persian71);
        $this->assertEquals(71, $res71['character_count']);
        $this->assertEquals(2, $res71['page_count']);
        $this->assertEquals(63, $res71['remaining_characters']);

        // 134 Persian chars -> exactly 2 pages, 0 remaining
        $persian134 = str_repeat('س', 134);
        $res134 = $this->service->calculateSegmentation($persian134);
        $this->assertEquals(134, $res134['character_count']);
        $this->assertEquals(2, $res134['page_count']);
        $this->assertEquals(0, $res134['remaining_characters']);

        // 135 Persian chars -> 3 pages, 201 - 135 = 66 remaining
        $persian135 = str_repeat('س', 135);
        $res135 = $this->service->calculateSegmentation($persian135);
        $this->assertEquals(135, $res135['character_count']);
        $this->assertEquals(3, $res135['page_count']);
        $this->assertEquals(66, $res135['remaining_characters']);

        // 201 Persian chars -> exactly 3 pages, 0 remaining
        $persian201 = str_repeat('س', 201);
        $res201 = $this->service->calculateSegmentation($persian201);
        $this->assertEquals(201, $res201['character_count']);
        $this->assertEquals(3, $res201['page_count']);
        $this->assertEquals(0, $res201['remaining_characters']);

        // 202 Persian chars -> 4 pages, 268 - 202 = 66 remaining
        $persian202 = str_repeat('س', 202);
        $res202 = $this->service->calculateSegmentation($persian202);
        $this->assertEquals(202, $res202['character_count']);
        $this->assertEquals(4, $res202['page_count']);
        $this->assertEquals(66, $res202['remaining_characters']);

        // 268 Persian chars -> exactly 4 pages, 0 remaining
        $persian268 = str_repeat('س', 268);
        $res268 = $this->service->calculateSegmentation($persian268);
        $this->assertEquals(268, $res268['character_count']);
        $this->assertEquals(4, $res268['page_count']);
        $this->assertEquals(0, $res268['remaining_characters']);

        // 3. ASCII (GSM-7) segmentation rules:
        // Page 1: 1 - 160 chars
        // Page 2: 161 - 306 chars (153 * 2)
        // Page 3: 307 - 459 chars (153 * 3)
        // Page 4: 460 - 612 chars (153 * 4)

        // 160 ASCII chars -> exactly 1 page, 0 remaining
        $ascii160 = str_repeat('a', 160);
        $resAscii160 = $this->service->calculateSegmentation($ascii160);
        $this->assertEquals(160, $resAscii160['character_count']);
        $this->assertEquals(1, $resAscii160['page_count']);
        $this->assertEquals(0, $resAscii160['remaining_characters']);
        $this->assertFalse($resAscii160['is_unicode']);

        // 161 ASCII chars -> 2 pages, 306 - 161 = 145 remaining
        $ascii161 = str_repeat('a', 161);
        $resAscii161 = $this->service->calculateSegmentation($ascii161);
        $this->assertEquals(161, $resAscii161['character_count']);
        $this->assertEquals(2, $resAscii161['page_count']);
        $this->assertEquals(145, $resAscii161['remaining_characters']);

        // 306 ASCII chars -> exactly 2 pages, 0 remaining
        $ascii306 = str_repeat('a', 306);
        $resAscii306 = $this->service->calculateSegmentation($ascii306);
        $this->assertEquals(306, $resAscii306['character_count']);
        $this->assertEquals(2, $resAscii306['page_count']);
        $this->assertEquals(0, $resAscii306['remaining_characters']);

        // 307 ASCII chars -> 3 pages, 459 - 307 = 152 remaining
        $ascii307 = str_repeat('a', 307);
        $resAscii307 = $this->service->calculateSegmentation($ascii307);
        $this->assertEquals(307, $resAscii307['character_count']);
        $this->assertEquals(3, $resAscii307['page_count']);
        $this->assertEquals(152, $resAscii307['remaining_characters']);

        // 459 ASCII chars -> exactly 3 pages, 0 remaining
        $ascii459 = str_repeat('a', 459);
        $resAscii459 = $this->service->calculateSegmentation($ascii459);
        $this->assertEquals(459, $resAscii459['character_count']);
        $this->assertEquals(3, $resAscii459['page_count']);
        $this->assertEquals(0, $resAscii459['remaining_characters']);

        // 460 ASCII chars -> 4 pages, 612 - 460 = 152 remaining
        $ascii460 = str_repeat('a', 460);
        $resAscii460 = $this->service->calculateSegmentation($ascii460);
        $this->assertEquals(460, $resAscii460['character_count']);
        $this->assertEquals(4, $resAscii460['page_count']);
        $this->assertEquals(152, $resAscii460['remaining_characters']);

        // 4. Auto-append "لغو 11" for advertising messages
        $advText = 'تخفیف ویژه لاله کارت';
        $resAdv = $this->service->calculateSegmentation($advText, serviceType: 'advertising');
        $this->assertStringContainsString('لغو 11', $resAdv['text']);
        $this->assertTrue($resAdv['appended_cancel']);
        $this->assertEquals(mb_strlen($resAdv['text'], 'UTF-8'), $resAdv['character_count']);

        // If "لغو 11" is already present, do not duplicate
        $advWithCancel = 'تخفیف ویژه لاله کارت لغو 11';
        $resAdvDup = $this->service->calculateSegmentation($advWithCancel, serviceType: 'advertising');
        $this->assertFalse($resAdvDup['appended_cancel']);
    }

    /**
     * Test that sending SMS is hard blocked when balance is insufficient.
     */
    public function test_insufficient_balance_hard_block(): void
    {
        // 1. Advertising send with insufficient balance
        AdvertisingBalance::create([
            'ghorfe_online_list_id' => $this->business->id,
            'sender_number_id' => $this->advSender->id,
            'balance' => 500.00, // Cost is 1200 per page, so 500 is insufficient for 1 page
        ]);

        $this->expectException(InsufficientBalanceException::class);

        $this->service->send([
            'recipient_phone' => '09121234567',
            'message' => 'پیام تست تبلیغاتی',
            'service_type' => 'advertising',
            'sender_number_id' => $this->advSender->id,
            'auto_append_cancel' => false,
        ], $this->business);

        // Assert nothing was created
        $this->assertDatabaseCount('sms_messages', 0);
        $this->assertDatabaseCount('sms_transactions', 0);
    }

    /**
     * Test insufficient balance for OTP send.
     */
    public function test_insufficient_balance_hard_block_for_otp(): void
    {
        OtpBalance::create([
            'ghorfe_online_list_id' => $this->business->id,
            'balance' => 0.00,
        ]);

        $this->expectException(InsufficientBalanceException::class);

        $this->service->send([
            'recipient_phone' => '09121234567',
            'message' => 'کد تایید شما: 12345',
            'service_type' => 'otp',
        ], $this->business);

        $this->assertDatabaseCount('sms_messages', 0);
        $this->assertDatabaseCount('sms_transactions', 0);
    }

    /**
     * Test that OTP sends NEVER allow a sender-number override.
     */
    public function test_otp_sends_never_allow_sender_number_override(): void
    {
        // Set sufficient OTP balance
        OtpBalance::create([
            'ghorfe_online_list_id' => $this->business->id,
            'balance' => 50000.00,
        ]);

        $dispatchedPanel = null;
        $dispatchedPhone = null;
        $dispatchedContent = null;

        $this->service->setGatewayDispatcher(function ($panel, $phone, $content) use (&$dispatchedPanel, &$dispatchedPhone, &$dispatchedContent) {
            $dispatchedPanel = $panel;
            $dispatchedPhone = $phone;
            $dispatchedContent = $content;
        });

        // Request passes advertising sender id, attempting to override the OTP sender
        $message = $this->service->send([
            'recipient_phone' => '09129998877',
            'message' => 'کد ورود شما: 987654',
            'service_type' => 'otp',
            'sender_number_id' => $this->advSender->id, // Malicious / accidental override attempt
        ], $this->business);

        // Must strictly use the OTP sender number, NOT the requested advertising number!
        $this->assertEquals($this->otpSender->id, $message->sender_number_id);
        $this->assertEquals($this->otpSender->number, $message->sender_phone);
        $this->assertNotEquals($this->advSender->id, $message->sender_number_id);
        $this->assertNotEquals($this->advSender->number, $message->sender_phone);

        // Transaction must also be logged for OTP credit type and OTP sender number
        $transaction = SmsTransaction::where('sms_message_id', $message->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('otp', $transaction->credit_type);
        $this->assertEquals($this->otpSender->id, $transaction->sender_number_id);

        // Gateway was dispatched
        $this->assertEquals('09129998877', $dispatchedPhone);
        $this->assertEquals('کد ورود شما: 987654', $dispatchedContent);
    }

    /**
     * Test successful send, tracking_id generation, balance deduction, and transaction logging.
     */
    public function test_successful_advertising_send_and_deduction(): void
    {
        $advBalance = AdvertisingBalance::create([
            'ghorfe_online_list_id' => $this->business->id,
            'sender_number_id' => $this->advSender->id,
            'balance' => 10000.00,
        ]);

        $dispatched = false;
        $this->service->setGatewayDispatcher(function () use (&$dispatched) {
            $dispatched = true;
        });

        $message = $this->service->send([
            'recipient_phone' => '09351234567',
            'message' => 'تخفیف ویژه نوروزی',
            'service_type' => 'advertising',
            'sender_number_id' => $this->advSender->id,
            'auto_append_cancel' => true,
        ], $this->business);

        $this->assertTrue($dispatched);
        $this->assertNotNull($message->tracking_id);
        $this->assertStringStartsWith('TRK-', $message->tracking_id);
        $this->assertEquals(1200.00, (float) $message->cost);
        $this->assertStringContainsString('لغو 11', $message->content);

        // Check balance decremented
        $this->assertEquals(8800.00, (float) $advBalance->fresh()->balance);

        // Check transaction row
        $transaction = SmsTransaction::where('sms_message_id', $message->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(10000.00, (float) $transaction->balance_before);
        $this->assertEquals(8800.00, (float) $transaction->balance_after);
        $this->assertEquals(1200.00, (float) $transaction->consume_amount);
        $this->assertEquals($message->tracking_id, $transaction->tracking_id);
    }

    /**
     * Test template placeholder resolution via DynamicParameterResolver.
     */
    public function test_template_dynamic_placeholder_substitution(): void
    {
        $contact = User::create([
            'name' => 'سهراب',
            'family' => 'سپهری',
            'phone' => '09121112233',
        ]);

        $resolved = $this->service->resolvePlaceholders(
            'سلام {نام کاربر} {نام خانوادگی} عزیز، فاکتور شما صادر شد.',
            [],
            $contact
        );

        $this->assertEquals('سلام سهراب سپهری عزیز، فاکتور شما صادر شد.', $resolved);
    }

    /**
     * Test contact search and inline creation.
     */
    public function test_contact_search_and_inline_store(): void
    {
        $user = User::create([
            'name' => 'بابک',
            'family' => 'خرمدین',
            'phone' => '09125556677',
        ]);

        $results = $this->service->searchContacts('بابک');
        $this->assertCount(1, $results);
        $this->assertEquals('09125556677', $results[0]['phone']);

        // Inline create contact
        $newContact = $this->service->storeContact([
            'name' => 'نیما',
            'family' => 'یوشیج',
            'phone' => '09110009988',
        ]);

        $this->assertEquals('نیما', $newContact->name);
        $this->assertEquals('09110009988', $newContact->phone);
        $this->assertDatabaseHas('users', ['phone' => '09110009988', 'name' => 'نیما']);
    }

    /**
     * Test message forwarding with referrer, timestamp, and history logged.
     */
    public function test_message_forwarding_and_referral_history(): void
    {
        $advBalance = AdvertisingBalance::create([
            'ghorfe_online_list_id' => $this->business->id,
            'sender_number_id' => $this->advSender->id,
            'balance' => 20000.00,
        ]);

        $original = SmsMessage::create([
            'ghorfe_online_list_id' => $this->business->id,
            'direction' => 'outbound',
            'service_type' => 'advertising',
            'sender_number_id' => $this->advSender->id,
            'recipient_phone' => '09120000001',
            'sender_phone' => $this->advSender->number,
            'content' => 'پیامک مهم برای پیگیری سفارش',
            'character_count' => 27,
            'page_count' => 1,
            'status' => 'sent',
            'cost' => 1200.00,
            'tracking_id' => 'TRK-ORIG1',
        ]);

        $referrer = User::create([
            'name' => 'کارشناس',
            'family' => 'پشتیبانی',
            'phone' => '09129990000',
        ]);

        $this->service->setGatewayDispatcher(function () {});

        $referredTime = Carbon::now();
        $forwarded = $this->service->forwardMessage(
            $original,
            '09120000002',
            targetUserId: null,
            referrerId: $referrer->id,
            timestamp: $referredTime,
            business: $this->business
        );

        $this->assertNotNull($forwarded);
        $this->assertEquals('09120000002', $forwarded->recipient_phone);
        $this->assertEquals($original->content, $forwarded->content);

        // Check history row logged
        $history = History::where('historiable_type', SmsMessage::class)
            ->where('historiable_id', $forwarded->id)
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals($referrer->id, $history->user_id);
        $this->assertStringContainsString('ارجاع پیامک', $history->description);
        $this->assertStringContainsString((string) $original->id, $history->description);
    }

    /**
     * Test HTTP endpoint: insufficient balance returns 422 error JSON.
     */
    public function test_http_endpoint_insufficient_balance_returns_422(): void
    {
        $admin = User::create([
            'name' => 'ادمین',
            'phone' => '09120001122',
            'level' => 'admin',
            'active' => true,
        ]);

        AdvertisingBalance::create([
            'ghorfe_online_list_id' => $this->business->id,
            'sender_number_id' => $this->advSender->id,
            'balance' => 0.00,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.sms.single.send'), [
                'recipient_phone' => '09123334455',
                'message' => 'پیامک تستی کمبود شارژ',
                'service_type' => 'advertising',
                'sender_number_id' => $this->advSender->id,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'error_type' => 'insufficient_balance',
        ]);
    }

    /**
     * Test HTTP endpoint: OTP send strictly prevents sender-number override.
     */
    public function test_http_endpoint_otp_send_prevents_sender_override(): void
    {
        $admin = User::create([
            'name' => 'ادمین',
            'phone' => '09120001133',
            'level' => 'admin',
            'active' => true,
        ]);

        OtpBalance::create([
            'ghorfe_online_list_id' => $this->business->id,
            'balance' => 20000.00,
        ]);

        $this->service->setGatewayDispatcher(function () {});

        $response = $this->actingAs($admin)
            ->postJson(route('admin.sms.single.send'), [
                'recipient_phone' => '09123334466',
                'message' => 'کد تایید ورود: 445566',
                'service_type' => 'otp',
                'sender_number_id' => $this->advSender->id, // Request attempts to override
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $created = SmsMessage::where('recipient_phone', '09123334466')->first();
        $this->assertNotNull($created);
        $this->assertEquals($this->otpSender->id, $created->sender_number_id);
        $this->assertEquals($this->otpSender->number, $created->sender_phone);
    }

    /**
     * Test HTTP preview endpoint returns accurate character and page counts.
     */
    public function test_http_endpoint_preview_calculation(): void
    {
        $admin = User::create([
            'name' => 'ادمین',
            'phone' => '09120001144',
            'level' => 'admin',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.sms.single.preview'), [
                'message' => 'سلام خسته نباشید',
                'service_type' => 'advertising',
                'auto_append_cancel' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'is_unicode' => true,
            'encoding' => 'Unicode',
            'appended_cancel' => true,
        ]);
        $this->assertStringContainsString('لغو 11', $response->json('resolved_text'));
    }

    /**
     * Test HTTP inline contact creation endpoint.
     */
    public function test_http_endpoint_inline_contact_store(): void
    {
        $admin = User::create([
            'name' => 'ادمین',
            'phone' => '09120001155',
            'level' => 'admin',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.sms.single.contacts.store'), [
                'name' => 'رضا',
                'family' => 'محمدی',
                'phone' => '09124445566',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'contact' => [
                'name' => 'رضا محمدی',
                'phone' => '09124445566',
            ],
        ]);
        $this->assertDatabaseHas('users', ['phone' => '09124445566', 'name' => 'رضا']);
    }

    /**
     * Test index controller action prepares view and required datasets.
     */
    public function test_index_controller_action_prepares_view(): void
    {
        $admin = User::create([
            'name' => 'مدیر',
            'phone' => '09120001166',
            'level' => 'admin',
            'active' => true,
        ]);

        $this->actingAs($admin);

        $controller = app(\App\Http\Controllers\Admin\SmsSingleSendController::class);
        $view = $controller->index(request());

        $this->assertEquals('Admin.sms.single.index', $view->name());
        $this->assertArrayHasKey('business', $view->getData());
        $this->assertArrayHasKey('otpSender', $view->getData());
        $this->assertArrayHasKey('advertisingNumbers', $view->getData());
        $this->assertArrayHasKey('templates', $view->getData());
        $this->assertArrayHasKey('recentMessages', $view->getData());
    }
}

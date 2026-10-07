<?php

namespace Tests\Feature;

use App\Models\BusinessSenderNumber;
use App\Models\GhorfeOnlineList;
use App\Models\History;
use App\Models\Permission;
use App\Models\Request as UserReqModel;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
use App\Models\User;
use App\Models\UserRequest;
use App\Services\Sms\SmsLogService;
use App\Services\Sms\SmsWebhookService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmsLogAndWebhookTest extends TestCase
{
    protected User $adminUser;
    protected User $superAdminUser;
    protected User $regularCustomer;
    protected GhorfeOnlineList $businessA;
    protected GhorfeOnlineList $businessB;
    protected SenderNumber $sender1;
    protected SenderNumber $sender2;
    protected SmsMessageGroup $normalGroup;
    protected SmsMessageGroup $confidentialGroup;
    protected SmsLogService $logService;
    protected SmsWebhookService $webhookService;

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
            $table->string('access_tier')->default('silver');
            $table->boolean('active')->default(true);
            $table->string('token')->nullable();
            $table->string('api_token')->nullable();
            $table->string('avatar')->nullable();
            $table->unsignedInteger('admin_organization_id')->nullable();
            $table->unsignedInteger('admin_representation_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('ghorfe_online_lists', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('linenumber')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('status')->default('1');
            $table->string('file')->nullable();
            $table->integer('card_id')->nullable();
            $table->integer('store_room_id')->nullable();
            $table->integer('type')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('user_requests', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('user_id')->nullable();
            $table->integer('status')->default(0);
            $table->text('description')->nullable();
            $table->integer('card_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('histories', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('historiable');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('callcenter_id')->nullable();
            $table->integer('status')->default(0);
            $table->text('description')->nullable();
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

        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $table->string('work_title')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('icon')->nullable();
            $table->string('link')->nullable();
            $table->integer('sort')->default(0);
            $table->integer('parent_id')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('ghorfe_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->nullableMorphs('settingable');
            $table->timestamps();
        });

        Schema::create('employee_terms', function (Blueprint $table) {
            $table->id();
            $table->string('role_category')->unique();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('contexts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 200)->nullable();
            $table->text('value')->nullable();
            $table->text('description')->nullable();
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
            $table->unsignedInteger('province_id')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->integer('status')->default(1);
            $table->integer('isShow')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->uuid()->nullable()->unique();
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->text('manipulations')->nullable();
            $table->text('custom_properties')->nullable();
            $table->text('generated_conversions')->nullable();
            $table->text('responsive_images')->nullable();
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->nullableTimestamps();
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
        ];

        foreach ($migrationFiles as $file) {
            $migration = require base_path($file);
            $migration->up();
        }

        // Seed basic fixtures
        $this->businessA = GhorfeOnlineList::create(['title' => 'فروشگاه آلفا', 'slug' => 'alpha', 'linenumber' => '30001111']);
        $this->businessB = GhorfeOnlineList::create(['title' => 'فروشگاه بتا', 'slug' => 'beta', 'linenumber' => '30002222']);

        $this->sender1 = SenderNumber::create(['number' => '10001111', 'type' => 'otp', 'sms_panel' => 'IranSms', 'status' => 'active']);
        $this->sender2 = SenderNumber::create(['number' => '20002222', 'type' => 'advertising', 'sms_panel' => 'NeginSms', 'status' => 'active']);

        BusinessSenderNumber::create(['ghorfe_online_list_id' => $this->businessA->id, 'sender_number_id' => $this->sender1->id]);
        BusinessSenderNumber::create(['ghorfe_online_list_id' => $this->businessA->id, 'sender_number_id' => $this->sender2->id]);
        BusinessSenderNumber::create(['ghorfe_online_list_id' => $this->businessB->id, 'sender_number_id' => $this->sender2->id]);

        $this->normalGroup = SmsMessageGroup::create(['name' => 'پشتیبانی', 'slug' => 'poshtibani']);
        $this->confidentialGroup = SmsMessageGroup::create(['name' => 'حقوقی و اخطار', 'slug' => 'hoghoghi']);

        $this->adminUser = User::create([
            'name' => 'علی',
            'family' => 'مدیر',
            'phone' => '09120000001',
            'level' => 'admin',
            'active' => true,
        ]);

        \App\Models\Employee::create([
            'user_id' => $this->adminUser->id,
            'ghorfe_online_list_id' => $this->businessA->id,
            'work_title' => 'مدیر فروشگاه',
        ]);

        $this->superAdminUser = User::create([
            'name' => 'سعید',
            'family' => 'سوپرادمین',
            'phone' => '09120000002',
            'level' => 'superAdmin',
            'active' => true,
        ]);

        $superRole = \App\Models\Role::create(['name' => 'superadmin', 'display_name' => 'مدیر ارشد']);
        DB::table('role_user')->insert([
            'role_id' => $superRole->id,
            'user_id' => $this->superAdminUser->id,
            'user_type' => User::class,
        ]);

        \App\Models\Employee::create([
            'user_id' => $this->superAdminUser->id,
            'ghorfe_online_list_id' => $this->businessA->id,
            'work_title' => 'مدیر سیستم',
        ]);

        $this->regularCustomer = User::create([
            'name' => 'مریم',
            'family' => 'مشتری',
            'phone' => '09123334455',
            'level' => 'user',
            'active' => true,
        ]);

        \Illuminate\Support\Facades\Cache::flush();
        \App\Models\Setting::create(['key' => 'title', 'value' => 'Laleh']);
        \Illuminate\Support\Facades\RateLimiter::for('api', function () {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(1000);
        });

        $this->logService = app(SmsLogService::class);
        $this->webhookService = app(SmsWebhookService::class);
    }

    public function test_log_service_filters_by_service_type(): void
    {
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'service_type' => 'otp',
            'content' => 'کد تایید ۱۲۳۴',
            'status' => 'delivered',
            'recipient_phone' => '09123334455',
        ]);

        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'service_type' => 'advertising',
            'content' => 'تخفیف ویژه نوروز',
            'status' => 'sent',
            'recipient_phone' => '09129998877',
        ]);

        $otpQuery = $this->logService->buildQuery(['service_type' => 'otp'], $this->businessA, false);
        $advQuery = $this->logService->buildQuery(['service_type' => 'advertising'], $this->businessA, false);

        $this->assertEquals(1, $otpQuery->count());
        $this->assertEquals('otp', $otpQuery->first()->service_type);

        $this->assertEquals(1, $advQuery->count());
        $this->assertEquals('advertising', $advQuery->first()->service_type);
    }

    public function test_log_service_filters_by_direction(): void
    {
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'service_type' => 'normal',
            'content' => 'پیام خروجی',
            'status' => 'sent',
            'recipient_phone' => '09123334455',
        ]);

        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'inbound',
            'service_type' => 'normal',
            'content' => 'پیام ورودی از کاربر',
            'status' => 'delivered',
            'sender_phone' => '09123334455',
        ]);

        $outbound = $this->logService->buildQuery(['direction' => 'outbound'], $this->businessA, false)->get();
        $inbound = $this->logService->buildQuery(['direction' => 'inbound'], $this->businessA, false)->get();

        $this->assertCount(1, $outbound);
        $this->assertEquals('outbound', $outbound->first()->direction);

        $this->assertCount(1, $inbound);
        $this->assertEquals('inbound', $inbound->first()->direction);
    }

    public function test_log_service_filters_by_is_bulk(): void
    {
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'is_bulk' => false,
            'content' => 'تکی',
        ]);

        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'is_bulk' => true,
            'content' => 'گروهی',
        ]);

        $singleLogs = $this->logService->buildQuery(['is_bulk' => '0'], $this->businessA, false)->get();
        $bulkLogs = $this->logService->buildQuery(['is_bulk' => '1'], $this->businessA, false)->get();

        $this->assertCount(1, $singleLogs);
        $this->assertFalse($singleLogs->first()->is_bulk);

        $this->assertCount(1, $bulkLogs);
        $this->assertTrue($bulkLogs->first()->is_bulk);
    }

    public function test_log_service_filters_by_sender_and_recipient_phone(): void
    {
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_phone' => '10001111',
            'recipient_phone' => '09121112233',
            'content' => 'پیام تست ۱',
        ]);

        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_phone' => '20002222',
            'recipient_phone' => '09359998877',
            'content' => 'پیام تست ۲',
        ]);

        $resRec = $this->logService->buildQuery(['recipient_phone' => '09121112233'], $this->businessA, false)->get();
        $this->assertCount(1, $resRec);
        $this->assertEquals('09121112233', $resRec->first()->recipient_phone);

        $resSend = $this->logService->buildQuery(['sender_phone' => '20002222'], $this->businessA, false)->get();
        $this->assertCount(1, $resSend);
        $this->assertEquals('20002222', $resSend->first()->sender_phone);
    }

    public function test_log_service_business_scoping_and_superadmin(): void
    {
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'content' => 'پیام بیزینس آ',
        ]);

        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessB->id,
            'content' => 'پیام بیزینس ب',
        ]);

        // Regular admin scoped to businessA
        $scoped = $this->logService->buildQuery([], $this->businessA, false)->get();
        $this->assertCount(1, $scoped);
        $this->assertEquals($this->businessA->id, $scoped->first()->ghorfe_online_list_id);

        // Superadmin without filter sees all
        $all = $this->logService->buildQuery([], null, true)->get();
        $this->assertCount(2, $all);

        // Superadmin filtered by businessB
        $superB = $this->logService->buildQuery(['business_id' => $this->businessB->id], null, true)->get();
        $this->assertCount(1, $superB);
        $this->assertEquals($this->businessB->id, $superB->first()->ghorfe_online_list_id);
    }

    public function test_webhook_delivery_report_updates_status(): void
    {
        $message = SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'content' => 'کد تایید',
            'status' => 'sent',
            'tracking_id' => 'TRK-WEBHOOK-TEST1',
            'recipient_phone' => '09123334455',
        ]);

        $this->assertNull($message->delivered_at);
        $this->assertNull($message->delivery_status);

        // Webhook delivery callback
        $payload = [
            'tracking_id' => 'TRK-WEBHOOK-TEST1',
            'status' => 'delivered',
        ];

        $res = $this->webhookService->handleDeliveryReport($payload);

        $this->assertTrue($res['success']);
        $message->refresh();

        $this->assertEquals('delivered', $message->status);
        $this->assertEquals('delivered', $message->delivery_status);
        $this->assertNotNull($message->delivered_at);
    }

    public function test_webhook_delivery_failure_updates_error(): void
    {
        $message = SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'content' => 'تست خطا',
            'status' => 'sent',
            'tracking_id' => 'TRK-FAIL-TEST',
            'recipient_phone' => '09120000000',
        ]);

        $payload = [
            'tracking_id' => 'TRK-FAIL-TEST',
            'status' => 'failed',
            'error_reason' => 'شماره در لیست سیاه مخابرات است',
        ];

        $res = $this->webhookService->handleDeliveryReport($payload);

        $this->assertTrue($res['success']);
        $message->refresh();

        $this->assertEquals('failed', $message->status);
        $this->assertEquals('failed', $message->delivery_status);
        $this->assertEquals('شماره در لیست سیاه مخابرات است', $message->error_reason);
    }

    public function test_webhook_inbound_message_creates_row(): void
    {
        $payload = [
            'sender' => '09123334455',
            'recipient' => '30001111',
            'message' => 'درخواست لغو سرویس',
            'tracking_id' => 'INB-PARSA-999',
        ];

        $res = $this->webhookService->handleInboundMessage($payload);

        $this->assertTrue($res['success']);
        $saved = SmsMessage::where('tracking_id', 'INB-PARSA-999')->first();

        $this->assertNotNull($saved);
        $this->assertEquals('inbound', $saved->direction);
        $this->assertEquals('09123334455', $saved->sender_phone);
        $this->assertEquals('30001111', $saved->recipient_phone);
        $this->assertEquals('درخواست لغو سرویس', $saved->content);
        $this->assertEquals($this->regularCustomer->id, $saved->user_id);
        $this->assertEquals($this->businessA->id, $saved->ghorfe_online_list_id);
    }

    public function test_webhook_http_endpoint(): void
    {
        $message = SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'content' => 'تست HTTP وب‌هوک',
            'status' => 'sent',
            'tracking_id' => 'TRK-HTTP-123',
            'recipient_phone' => '09121112233',
        ]);

        // Post delivery report to API route
        $response = $this->postJson('/api/sms/webhook/delivery', [
            'tracking_id' => 'TRK-HTTP-123',
            'status' => 'delivered',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $message->refresh();
        $this->assertEquals('delivered', $message->status);
    }

    public function test_request_tied_sms_writes_to_history(): void
    {
        $req = UserReqModel::create([
            'id' => 101,
            'user_id' => $this->regularCustomer->id,
            'status' => '1',
        ]);

        $initialHistoriesCount = History::where('historiable_type', UserReqModel::class)
            ->where('historiable_id', $req->id)
            ->count();
        $this->assertEquals(0, $initialHistoriesCount);

        // Create outbound SMS message tied to request
        $msg = SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'content' => 'درخواست شما در انبار تایید شد.',
            'status' => 'sent',
            'recipient_phone' => $this->regularCustomer->phone,
            'user_id' => $this->regularCustomer->id,
            'request_id' => $req->id,
        ]);

        // Verify history was created for this request
        $histories = History::where('historiable_type', UserReqModel::class)
            ->where('historiable_id', $req->id)
            ->get();

        $this->assertCount(1, $histories);
        $this->assertStringContainsString('ارسال پیامک', $histories->first()->description);
        $this->assertStringContainsString('درخواست شما در انبار تایید شد', $histories->first()->description);

        // Test request model relations
        $this->assertCount(1, $req->smsMessages);
        $this->assertEquals($msg->id, $req->smsMessages->first()->id);
    }

    public function test_user_request_model_relations(): void
    {
        $req = UserReqModel::create([
            'user_id' => $this->regularCustomer->id,
            'status' => '1',
        ]);

        $userReq = UserRequest::create([
            'id' => $req->id,
            'user_id' => $this->regularCustomer->id,
            'status' => 0,
            'description' => 'درخواست آزمایشی',
        ]);

        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'inbound',
            'content' => 'پاسخ مشتری به درخواست',
            'request_id' => $userReq->id,
        ]);

        $this->assertCount(1, $userReq->smsMessages);
    }

    public function test_customer_profile_messages_normal_vs_confidential_gating(): void
    {
        // 1. Create a normal message for customer
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sms_message_group_id' => $this->normalGroup->id,
            'direction' => 'outbound',
            'content' => 'پیام عادی پشتیبانی',
            'recipient_phone' => $this->regularCustomer->phone,
            'user_id' => $this->regularCustomer->id,
        ]);

        // 2. Create a confidential message for customer
        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sms_message_group_id' => $this->confidentialGroup->id,
            'direction' => 'outbound',
            'content' => 'اخطار حقوقی محرمانه',
            'recipient_phone' => $this->regularCustomer->phone,
            'user_id' => $this->regularCustomer->id,
        ]);

        // Customer without permission accessing profile/messages
        $this->actingAs($this->regularCustomer);

        $response = $this->get(route('profile.messages'));
        $response->assertStatus(200);

        // Check view data
        $normal = $response->viewData('normalMessages');
        $confidential = $response->viewData('confidentialMessages');
        $canView = $response->viewData('canViewConfidential');

        $this->assertFalse($canView);
        $this->assertCount(1, $normal);
        $this->assertEquals('پیام عادی پشتیبانی', $normal->first()->content);
        $this->assertCount(0, $confidential); // Confidential messages hidden from view data!

        // Now grant permission to view confidential messages
        $perm = Permission::create(['name' => 'view-confidential-messages', 'display_name' => 'مشاهده پیام‌های محرمانه']);
        DB::table('permission_user')->insert([
            'permission_id' => $perm->id,
            'user_id' => $this->regularCustomer->id,
            'user_type' => User::class,
        ]);

        $this->regularCustomer->refresh();

        $responseWithPerm = $this->get(route('profile.messages'));
        $responseWithPerm->assertStatus(200);

        $confidentialWithPerm = $responseWithPerm->viewData('confidentialMessages');
        $canViewWithPerm = $responseWithPerm->viewData('canViewConfidential');

        $this->assertTrue($canViewWithPerm);
        $this->assertCount(1, $confidentialWithPerm);
        $this->assertEquals('اخطار حقوقی محرمانه', $confidentialWithPerm->first()->content);
    }

    public function test_admin_sms_logs_http_endpoint(): void
    {
        $this->actingAs($this->superAdminUser);

        SmsMessage::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'content' => 'پیام لاگ ۱',
            'status' => 'delivered',
            'recipient_phone' => '09121112233',
        ]);

        $response = $this->get(route('admin.sms.logs.index'));
        $response->assertStatus(200);
        $response->assertViewIs('Admin.sms.logs.index');
        $this->assertNotEmpty($response->viewData('messages'));

        // Test JSON request
        $jsonResponse = $this->getJson(route('admin.sms.logs.index'));
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJsonStructure(['success', 'data', 'summary']);
    }
}

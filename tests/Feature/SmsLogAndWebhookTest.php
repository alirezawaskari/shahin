<?php

namespace Tests\Feature;

use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\History;
use App\Models\Permission;
use App\Models\Request as UserReqModel;
use App\Models\Setting;
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
    use \Tests\Concerns\SmsSqliteSchema;

    protected User $adminUser;
    protected User $superAdminUser;
    protected User $regularCustomer;
    protected GhorfeOnlineList $businessA;
    protected GhorfeOnlineList $businessB;
    protected Setting $sender1;
    protected Setting $sender2;
    protected Setting $normalGroup;
    protected Setting $confidentialGroup;
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
            $table->boolean('sms_contract_accepted')->default(true); // tests exercise the module, not the contract gate
            $table->string('title');
            $table->string('slug')->nullable();
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
            $table->string('number')->nullable();
            $table->string('type')->nullable();
            $table->string('sms_panel')->nullable();
            $table->string('status')->default('active');
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
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

        Schema::create('groupsms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->text('content')->nullable();
            $table->text('body')->nullable();
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

        $this->applySmsModuleColumns();

        // Seed basic fixtures
        $this->businessA = GhorfeOnlineList::create(['title' => 'فروشگاه آلفا', 'slug' => 'alpha']);
        $this->businessB = GhorfeOnlineList::create(['title' => 'فروشگاه بتا', 'slug' => 'beta']);

        $this->sender1 = Setting::create([
            'key' => 'sms_sender_number',
            'settingable_type' => GhorfeOnlineList::class,
            'settingable_id' => $this->businessA->id,
            'number' => '10001111',
            'type' => 'otp',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);
        $this->sender2 = Setting::create([
            'key' => 'sms_sender_number',
            'settingable_type' => GhorfeOnlineList::class,
            'settingable_id' => $this->businessA->id,
            'number' => '20002222',
            'type' => 'advertising',
            'sms_panel' => 'NeginSms',
            'status' => 'active',
        ]);

        $this->normalGroup = Setting::create(['key' => 'sms_message_group', 'name' => 'پشتیبانی', 'slug' => 'poshtibani']);
        $this->confidentialGroup = Setting::create(['key' => 'sms_message_group', 'name' => 'حقوقی و اخطار', 'slug' => 'hoghoghi']);

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

        $this->logService = app(SmsLogService::class);
        $this->webhookService = app(SmsWebhookService::class);
    }

    public function test_log_service_filters_by_service_type(): void
    {
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'service_type' => 'otp',
            'content' => 'کد تایید ۱۲۳۴',
            'status' => 'delivered',
            'recipient_phone' => '09123334455',
        ]);

        Groupsms::create([
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
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'service_type' => 'normal',
            'content' => 'پیام خروجی',
            'status' => 'sent',
            'recipient_phone' => '09123334455',
        ]);

        Groupsms::create([
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
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'is_bulk' => false,
            'content' => 'تکی',
        ]);

        Groupsms::create([
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
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sender_phone' => '10001111',
            'recipient_phone' => '09121112233',
            'content' => 'پیام تست ۱',
        ]);

        Groupsms::create([
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
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'content' => 'پیام بیزینس آ',
        ]);

        Groupsms::create([
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
        $message = Groupsms::create([
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
        $message = Groupsms::create([
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
            'recipient' => '20002222',
            'message' => 'درخواست لغو سرویس',
            'tracking_id' => 'INB-PARSA-999',
        ];

        $res = $this->webhookService->handleInboundMessage($payload);

        $this->assertTrue($res['success']);
        $saved = Groupsms::where('tracking_id', 'INB-PARSA-999')->first();

        $this->assertNotNull($saved);
        $this->assertEquals('inbound', $saved->direction);
        $this->assertEquals('09123334455', $saved->sender_phone);
        $this->assertEquals('20002222', $saved->recipient_phone);
        $this->assertEquals('درخواست لغو سرویس', $saved->content);
        $this->assertEquals($this->regularCustomer->id, $saved->user_id);
        $this->assertEquals($this->businessA->id, $saved->ghorfe_online_list_id);
    }

    public function test_webhook_http_endpoint(): void
    {
        $message = Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'content' => 'تست HTTP وب‌هوک',
            'status' => 'sent',
            'tracking_id' => 'TRK-HTTP-123',
            'recipient_phone' => '09121112233',
        ]);

        config(['services.sms.webhook_secret' => 'test-secret']);

        // Post delivery report to API route
        $response = $this->withHeader(\App\Http\Middleware\VerifySmsWebhookSecret::HEADER, 'test-secret')
            ->postJson('/api/sms/webhook/delivery', [
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
        $msg = Groupsms::create([
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

    public function test_request_history_is_not_dropped_when_the_request_id_matches_a_groupsms_id(): void
    {
        // A groupsms row already owns the id the request is about to take: the two id sequences are separate.
        $existing = Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'content' => 'پیام قبلی',
            'status' => 'sent',
            'recipient_phone' => $this->regularCustomer->phone,
        ]);

        $req = UserReqModel::create([
            'id' => $existing->id,
            'user_id' => $this->regularCustomer->id,
            'status' => '1',
        ]);

        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'content' => 'پیام مربوط به درخواست',
            'status' => 'sent',
            'recipient_phone' => $this->regularCustomer->phone,
            'user_id' => $this->regularCustomer->id,
            'request_id' => $req->id,
        ]);

        $this->assertCount(1, History::where('historiable_type', UserReqModel::class)
            ->where('historiable_id', $req->id)
            ->get());
    }

    public function test_bulk_recipient_rows_do_not_write_request_history(): void
    {
        $req = UserReqModel::create([
            'id' => 102,
            'user_id' => $this->regularCustomer->id,
            'status' => '1',
        ]);

        // A bulk recipient row points request_id at its campaign, not at a customer request.
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'type' => 'bulk_recipient',
            'content' => 'پیام انبوه',
            'status' => 'pending',
            'recipient_phone' => $this->regularCustomer->phone,
            'request_id' => $req->id,
        ]);

        $this->assertCount(0, History::where('historiable_type', UserReqModel::class)
            ->where('historiable_id', $req->id)
            ->get());
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

        Groupsms::create([
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
        Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'sms_message_group_id' => $this->normalGroup->id,
            'direction' => 'outbound',
            'content' => 'پیام عادی پشتیبانی',
            'recipient_phone' => $this->regularCustomer->phone,
            'user_id' => $this->regularCustomer->id,
        ]);

        // 2. Create a confidential message for customer
        Groupsms::create([
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

        Groupsms::create([
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

    public function test_webhook_goes_through_real_api_throttle_and_secret_middleware(): void
    {
        config(['services.sms.webhook_secret' => 'test-secret']);
        $this->assertNotNull(\Illuminate\Support\Facades\RateLimiter::limiter('api'), 'api rate limiter must be defined');

        $header = \App\Http\Middleware\VerifySmsWebhookSecret::HEADER;

        $this->withHeader($header, 'test-secret')
            ->postJson('/api/sms/webhook/inbound', ['sender' => '09123334455', 'recipient' => '20002222', 'message' => 'سلام'])
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->withHeader($header, 'wrong')
            ->postJson('/api/sms/webhook/inbound', ['sender' => '09123334455', 'message' => 'سلام'])
            ->assertStatus(401);
    }

    public function test_inbound_to_unknown_line_is_stored_without_business_and_does_not_error(): void
    {
        $res = $this->webhookService->handleInboundMessage([
            'sender' => '09123334455', 'recipient' => '99990000', 'message' => 'سلام', 'tracking_id' => 'INB-UNKNOWN-1',
        ]);

        $this->assertTrue($res['success']);
        $this->assertNull(Groupsms::where('tracking_id', 'INB-UNKNOWN-1')->value('ghorfe_online_list_id'));
    }

    public function test_log_detail_page_renders_for_allowed_and_hides_otp_from_lower_tiers(): void
    {
        $normal = Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'service_type' => 'normal',
            'content' => 'متن عادی تست',
            'status' => 'delivered',
            'recipient_phone' => '09121112233',
        ]);
        $otp = Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'outbound',
            'service_type' => 'otp',
            'content' => 'کد تایید 1234',
            'status' => 'delivered',
            'recipient_phone' => '09121112233',
        ]);

        $this->actingAs($this->superAdminUser);
        $this->get(route('admin.sms.logs.show', $normal))
            ->assertStatus(200)
            ->assertViewIs('Admin.sms.logs.show')
            ->assertSee('متن عادی تست');
        $this->get(route('admin.sms.logs.show', $otp))->assertStatus(200)->assertSee('کد تایید 1234');

        // A silver-tier business admin may see normal rows but never OTP rows, even by direct id.
        $this->actingAs($this->adminUser);
        $this->get(route('admin.sms.logs.show', $normal))->assertStatus(200);
        $this->get(route('admin.sms.logs.show', $otp))->assertStatus(404);
    }

    public function test_inbound_webhook_computes_character_and_page_counts_with_zero_cost(): void
    {
        config(['services.sms.webhook_secret' => 'test-secret']);
        $header = \App\Http\Middleware\VerifySmsWebhookSecret::HEADER;
        $text = str_repeat('س', 80);

        $this->withHeader($header, 'test-secret')
            ->postJson('/api/sms/webhook/inbound', [
                'sender' => '09123334455', 'recipient' => '20002222', 'message' => $text,
                'tracking_id' => 'INB-COUNT-1', 'service_type' => 'advertising',
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $saved = Groupsms::where('tracking_id', 'INB-COUNT-1')->first();
        $this->assertSame(80, (int) $saved->character_count);
        $this->assertSame(2, (int) $saved->page_count);
        $this->assertEquals(0, (float) $saved->cost);
        $this->assertStringNotContainsString('لغو 11', $saved->content);
    }

    public function test_log_list_links_to_detail_page_and_shows_persian_labels(): void
    {
        $this->actingAs($this->superAdminUser);

        $msg = Groupsms::create([
            'ghorfe_online_list_id' => $this->businessA->id,
            'direction' => 'inbound',
            'service_type' => 'otp',
            'content' => 'برچسب تست',
            'status' => 'delivered',
            'delivery_status' => 'delivered',
            'recipient_phone' => '09121112233',
        ]);

        $show = route('admin.sms.logs.show', $msg->id);

        $html = $this->get(route('admin.sms.logs.index'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('data-show-url="' . $show . '"', $html);
        $this->assertStringContainsString('id="mShowLink"', $html);
        $this->assertStringContainsString('data-service="اعتبارسنجی (OTP)"', $html);
        $this->assertStringContainsString('data-status="تحویل شد"', $html);
        $this->assertStringContainsString('data-delivery="تحویل شد"', $html);
        $this->assertStringNotContainsString('data-service="otp"', $html);

        $detail = $this->get($show)->assertStatus(200)->getContent();
        $this->assertStringContainsString('اعتبارسنجی (OTP)', $detail);
        $this->assertStringNotContainsString('>delivered<', $detail);
        $this->assertSame('عادی', \App\Support\SmsLabels::serviceType('normal'));
        $this->assertSame('تبلیغاتی', \App\Support\SmsLabels::serviceType('advertising'));
        $this->assertSame('ناموفق', \App\Support\SmsLabels::status('failed'));
    }
}

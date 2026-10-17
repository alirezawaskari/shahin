<?php

namespace Tests\Feature;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Exceptions\Sms\TierAccessDeniedException;
use App\Models\AdvertisingBalance;
use App\Models\GhorfeOnlineList;
use App\Models\Role;
use App\Models\SenderNumber;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\SmsTransaction;
use App\Models\User;
use App\Services\Sms\BulkSmsRecipientFilterService;
use App\Services\Sms\BulkSmsSendService;
use App\Services\Sms\SmsAccessTierService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmsBulkSendTest extends TestCase
{
    use \Tests\Concerns\SeedsSmsBalances;

    protected GhorfeOnlineList $business;
    protected SenderNumber $advSender;
    protected User $superAdmin;
    protected User $silverUser;
    protected User $goldUser;
    protected User $diamondUser;
    protected User $titaniumUser;
    protected BulkSmsSendService $bulkSendService;
    protected BulkSmsRecipientFilterService $filterService;
    protected SmsAccessTierService $tierService;

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

        // Prerequisites tables
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
            $table->integer('birth_year')->nullable();
            $table->integer('birth_month')->nullable();
            $table->integer('birth_day')->nullable();
            $table->tinyInteger('sex')->nullable();
            $table->unsignedInteger('province_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->unsignedInteger('service_id')->nullable();
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

        Schema::create('ghorfe_online_list_user', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('ghorfe_online_list_id');
            $table->primary(['user_id', 'ghorfe_online_list_id']);
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('status')->nullable();
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
            $table->string('user_type')->default('App\\Models\\User');
            $table->primary(['user_id', 'role_id', 'user_type']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('depth')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Run SMS Foundation migrations
        $migrationFiles = [
            'database/migrations/2026_10_07_000001_create_sender_numbers_table.php',
            'database/migrations/2026_10_07_000002_create_sms_message_groups_table.php',
            'database/migrations/2026_10_07_000003_create_sms_message_templates_table.php',
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
        ];

        foreach ($migrationFiles as $file) {
            $migration = require base_path($file);
            $migration->up();
        }

        // Test business & advertising number
        $this->business = GhorfeOnlineList::create(['title' => 'فروشگاه شایان']);

        $this->advSender = SenderNumber::create([
            'number' => '50009999',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);
        $this->business->senderNumbers()->attach($this->advSender->id, ['assigned_at' => now()]);

        // Users with different tiers
        $this->superAdmin = User::create([
            'name' => 'مدیر',
            'family' => 'کل',
            'phone' => '09120000001',
            'level' => 'superAdmin',
            'access_tier' => 'titanium',
            'active' => 1,
        ]);

        $this->silverUser = User::create([
            'name' => 'کاربر',
            'family' => 'سیلور',
            'phone' => '09120000002',
            'level' => 'admin',
            'access_tier' => 'silver',
            'active' => 1,
        ]);

        $this->goldUser = User::create([
            'name' => 'کاربر',
            'family' => 'گلد',
            'phone' => '09120000003',
            'level' => 'admin',
            'access_tier' => 'gold',
            'active' => 1,
        ]);

        $this->diamondUser = User::create([
            'name' => 'کاربر',
            'family' => 'دیاموند',
            'phone' => '09120000004',
            'level' => 'admin',
            'access_tier' => 'diamond',
            'active' => 1,
        ]);

        $this->titaniumUser = User::create([
            'name' => 'کاربر',
            'family' => 'تیتانیوم',
            'phone' => '09120000005',
            'level' => 'admin',
            'access_tier' => 'titanium',
            'active' => 1,
        ]);

        $this->tierService = app(SmsAccessTierService::class);
        $this->filterService = app(BulkSmsRecipientFilterService::class);
        $this->bulkSendService = app(BulkSmsSendService::class);
    }

    /**
     * Test 1: De-duplication logic.
     * Candidate list with multiple duplicates must result in only 1 unique recipient.
     */
    public function test_deduplication_logic(): void
    {
        $candidates = [
            ['phone' => '09123456789', 'name' => 'کاربر ۱'],
            ['phone' => '09123456789', 'name' => 'کاربر ۱ تکرار'],
            ['phone' => '+989123456789', 'name' => 'کاربر ۱ با پیشوند'],
            ['phone' => '۰۹۱۲۳۴۵۶۷۸۹', 'name' => 'کاربر ۱ فارسی'],
            ['phone' => '09128888888', 'name' => 'کاربر ۲'],
            ['phone' => '9128888888', 'name' => 'کاربر ۲ بدون صفر'],
        ];

        $result = $this->filterService->processRecipients($candidates);

        $this->assertEquals(6, $result['total_candidates']);
        $this->assertEquals(2, $result['valid_count']); // Only 09123456789 and 09128888888
        $this->assertEquals(4, $result['duplicate_count']);
        $this->assertEquals(0, $result['invalid_count']);

        $validPhones = array_column($result['valid_recipients'], 'phone');
        $this->assertEquals(['09123456789', '09128888888'], $validPhones);
    }

    /**
     * Test 2: Invalid number filtering and Persian/International normalization.
     */
    public function test_invalid_number_filtering(): void
    {
        $candidates = [
            ['phone' => '09121112233'],           // Valid standard
            ['phone' => '02188776655'],           // Invalid: landline
            ['phone' => '0912'],                  // Invalid: too short
            ['phone' => '091211122334455'],       // Invalid: too long
            ['phone' => 'abcdefghijk'],           // Invalid: text
            ['phone' => ''],                      // Invalid: empty
            ['phone' => null],                    // Invalid: null
            ['phone' => '00989351234567'],       // Valid: normalized from 00989
            ['phone' => '989361234567'],         // Valid: normalized from 989
            ['phone' => '۹۳۷۱۲۳۴۵۶۷'],           // Valid: Persian digits 10 digits
        ];

        $result = $this->filterService->processRecipients($candidates);

        $this->assertEquals(10, $result['total_candidates']);
        $this->assertEquals(4, $result['valid_count']);
        $this->assertEquals(6, $result['invalid_count']);

        $validPhones = array_column($result['valid_recipients'], 'phone');
        $this->assertContains('09121112233', $validPhones);
        $this->assertContains('09351234567', $validPhones);
        $this->assertContains('09361234567', $validPhones);
        $this->assertContains('09371234567', $validPhones);

        $this->assertNotEmpty($result['invalid_recipients']);
        $invalidReasons = array_column($result['invalid_recipients'], 'reason');
        $this->assertStringContainsString('فرمت شماره موبایل نامعتبر است', $invalidReasons[0]);
    }

    /**
     * Test 3: Tier-based filter restriction.
     * Enforces Silver / Gold / Diamond / Titanium pool and filter gates.
     */
    public function test_tier_based_filter_restrictions(): void
    {
        // 1. Silver user: CAN query normal pool
        $this->tierService->assertCanQuery($this->silverUser, [], 'normal');
        $this->assertTrue(true);

        // 2. Silver user: CANNOT query advertising pool
        $this->expectException(TierAccessDeniedException::class);
        $this->tierService->assertCanQuery($this->silverUser, [], 'advertising');
    }

    public function test_silver_user_cannot_use_gold_filters(): void
    {
        // Role filter requires Gold
        $this->expectException(TierAccessDeniedException::class);
        $this->tierService->assertCanQuery($this->silverUser, ['role' => 1], 'normal');
    }

    public function test_silver_user_cannot_use_diamond_category_filters(): void
    {
        // Category filter requires Diamond
        $this->expectException(TierAccessDeniedException::class);
        $this->tierService->assertCanQuery($this->silverUser, ['service_category_id' => 5], 'normal');
    }

    public function test_gold_user_permissions_and_restrictions(): void
    {
        // Gold CAN use role and birth year filters
        $this->tierService->assertCanQuery($this->goldUser, ['role' => 1, 'birth_year' => 1370], 'normal');
        $this->assertTrue(true);

        // Gold CANNOT query advertising pool
        $this->expectException(TierAccessDeniedException::class);
        $this->tierService->assertCanQuery($this->goldUser, [], 'advertising');
    }

    public function test_diamond_user_permissions_and_restrictions(): void
    {
        // Diamond CAN query advertising pool and categories
        $this->tierService->assertCanQuery($this->diamondUser, ['service_category_id' => 2], 'advertising');
        $this->assertTrue(true);

        // Diamond CANNOT query cross-business ('all')
        $this->expectException(TierAccessDeniedException::class);
        $this->tierService->assertCanQuery($this->diamondUser, ['business' => 'all'], 'normal');
    }

    public function test_silver_user_cannot_query_user_database(): void
    {
        // Direct querying on User database (e.g. gender or province) requires Gold
        $this->expectException(TierAccessDeniedException::class);
        $this->tierService->assertCanQuery($this->silverUser, ['gender' => 1], 'normal');
    }

    public function test_diamond_user_cannot_query_organization_database(): void
    {
        // Organization Database Access is exclusive to Titanium
        $this->expectException(TierAccessDeniedException::class);
        $this->tierService->assertCanQuery($this->diamondUser, ['organization_id' => 10], 'normal');
    }

    public function test_gold_user_cannot_query_representation_database(): void
    {
        // Representation Database Access is exclusive to Titanium
        $this->expectException(TierAccessDeniedException::class);
        $this->tierService->assertCanQuery($this->goldUser, ['representation_id' => 3], 'normal');
    }

    public function test_titanium_user_has_full_pool_access(): void
    {
        // Titanium can query cross-business advertising pool and organizations
        $this->tierService->assertCanQuery($this->titaniumUser, [
            'business' => 'all',
            'role' => 2,
            'service_category_id' => 1,
            'organization_id' => 10,
            'representation_id' => 3,
        ], 'advertising');

        $this->assertTrue(true);
    }

    public function test_access_tier_change_restricted_to_superadmin(): void
    {
        // superAdmin CAN change access tier
        $this->actingAs($this->superAdmin);
        $this->silverUser->setAccessTier('gold');
        $this->assertEquals('gold', $this->silverUser->fresh()->access_tier);

        // Regular admin CANNOT change access tier
        $this->actingAs($this->goldUser);
        $this->expectException(AuthorizationException::class);
        $this->silverUser->setAccessTier('diamond');
    }

    /**
     * Test 4: Insufficient balance blocking the whole campaign before any send.
     */
    public function test_insufficient_balance_blocks_campaign_before_any_send(): void
    {
        // Set balance to 1,000 Rials
        $this->seedAdvertisingBalance($this->business->id, 1000);

        // Create 5 valid candidate users for this business
        for ($i = 1; $i <= 5; $i++) {
            $u = User::create([
                'name' => "کاربر $i",
                'phone' => "0912111000$i",
                'level' => 'user',
            ]);
            $u->ghorfeOnlineLists()->attach($this->business->id);
        }

        // Cost required: 5 recipients * 1 page * 1200 = 6,000 Rials > 1,000 Rials
        $campaignData = [
            'recipient_type' => 'normal',
            'message' => 'تست پیامک با موجودی ناکافی',
            'sender_number_id' => $this->advSender->id,
            'filters' => ['business' => $this->business->id],
        ];

        try {
            $this->bulkSendService->createCampaign($campaignData, $this->titaniumUser, $this->business);
            $this->fail('Expected InsufficientBalanceException was not thrown.');
        } catch (InsufficientBalanceException $e) {
            $this->assertEquals(1000, $e->currentBalance);
            $this->assertEquals(6000, $e->requiredAmount);
        }

        // Verify that NO campaign, NO recipients, NO messages, and NO balance changes occurred!
        $this->assertEquals(0, SmsCampaign::count());
        $this->assertEquals(0, SmsCampaignRecipient::count());
        $this->assertEquals(0, SmsMessage::count());
        $this->assertEquals(0, SmsTransaction::count());
        $this->assertEquals(1000, (float) AdvertisingBalance::where('ghorfe_online_list_id', $this->business->id)->value('balance'));
    }

    /**
     * Test 5: Successful campaign creation, balance deduction, and queued job execution.
     */
    public function test_successful_campaign_send_flow_and_job_execution(): void
    {
        // Set sufficient balance: 50,000 Rials
        $this->seedAdvertisingBalance($this->business->id, 50000);

        // Create template
        $group = SmsMessageGroup::create(['name' => 'تخفیفات', 'slug' => 'discounts']);
        $template = SmsMessageTemplate::create([
            'sms_message_group_id' => $group->id,
            'title' => 'تخفیف ویژه نوروز',
            'content' => 'سلام {نام کاربر}، کد تخفیف عید برای شما فعال شد.',
            'status' => 'active',
            'created_by' => $this->superAdmin->id,
        ]);

        // Create 3 users for this business
        $u1 = User::create(['name' => 'رضا', 'family' => 'محمدی', 'phone' => '09121113331', 'level' => 'user']);
        $u2 = User::create(['name' => 'سارا', 'family' => 'احمدی', 'phone' => '09121113332', 'level' => 'user']);
        $u3 = User::create(['name' => 'علی', 'family' => 'کریمی', 'phone' => '09121113333', 'level' => 'user']);

        $u1->ghorfeOnlineLists()->attach($this->business->id);
        $u2->ghorfeOnlineLists()->attach($this->business->id);
        $u3->ghorfeOnlineLists()->attach($this->business->id);

        $dispatchedMessages = [];
        BulkSmsSendService::setGlobalGatewayDispatcher(function ($panel, $phone, $message) use (&$dispatchedMessages) {
            $dispatchedMessages[] = ['phone' => $phone, 'msg' => $message];
        });

        // 3 recipients * 1 page * 1200 = 3600 Rials
        $campaignData = [
            'recipient_type' => 'normal',
            'sms_message_template_id' => $template->id,
            'message' => $template->content,
            'sender_number_id' => $this->advSender->id,
            'filters' => ['business' => $this->business->id],
        ];

        $campaign = $this->bulkSendService->createCampaign($campaignData, $this->titaniumUser, $this->business);

        $this->assertNotNull($campaign);
        $this->assertEquals(3, $campaign->total_recipients);
        $this->assertEquals(3600, (float) $campaign->cost_estimate);
        $this->assertEquals(3, $campaign->recipients()->count());

        $campaign->refresh();
        $this->assertEquals('completed', $campaign->status);
        $this->assertEquals(3, $campaign->sent_count);
        $this->assertEquals(3, $campaign->success_count);
        $this->assertEquals(0, $campaign->failed_count);
        $this->assertEquals(0, $campaign->pending_count);

        // Verify gateway calls
        $this->assertCount(3, $dispatchedMessages);
        $this->assertStringContainsString('سلام رضا', $dispatchedMessages[0]['msg']);

        // Verify balance deduction: 50000 - 3600 = 46400
        $balanceAfter = (float) AdvertisingBalance::where('ghorfe_online_list_id', $this->business->id)->value('balance');
        $this->assertEquals(46400, $balanceAfter);

        // Verify SmsMessage & SmsTransaction records
        $this->assertEquals(3, SmsMessage::where('sms_campaign_id', $campaign->id)->count());
        $this->assertEquals(3, SmsTransaction::where('sms_campaign_id', $campaign->id)->count());
    }

    /**
     * Test 6: HTTP endpoints for preview, send, and status API.
     */
    public function test_http_endpoints_preview_send_and_live_status(): void
    {
        BulkSmsSendService::setGlobalGatewayDispatcher(function () {});

        $this->actingAs($this->titaniumUser);

        \DB::table('employees')->insert([
            'user_id' => $this->titaniumUser->id,
            'ghorfe_online_list_id' => $this->business->id,
        ]);

        $this->seedAdvertisingBalance($this->business->id, 100000);

        User::create(['name' => 'کاربر ۱', 'phone' => '09129990001', 'level' => 'user'])
            ->ghorfeOnlineLists()->attach($this->business->id);
        User::create(['name' => 'کاربر ۲', 'phone' => '09129990002', 'level' => 'user'])
            ->ghorfeOnlineLists()->attach($this->business->id);

        // 1. Preview Endpoint
        $previewResp = $this->postJson(route('admin.sms.bulk.preview'), [
            'recipient_type' => 'normal',
            'message' => 'پیامک تست برای پیش‌نمایش',
            'sender_number_id' => $this->advSender->id,
            'filters' => ['business' => $this->business->id],
        ]);

        $previewResp->assertStatus(200);
        $previewResp->assertJsonPath('status', 'success');
        $previewResp->assertJsonPath('recipient_summary.valid_count', 2);
        $previewResp->assertJsonPath('financial_summary.has_sufficient_balance', true);

        // 2. Send Endpoint
        $sendResp = $this->postJson(route('admin.sms.bulk.send'), [
            'recipient_type' => 'normal',
            'message' => 'پیامک تست ارسال انبوه',
            'sender_number_id' => $this->advSender->id,
            'filters' => ['business' => $this->business->id],
        ]);

        $sendResp->assertStatus(200);
        $sendResp->assertJsonPath('status', 'success');
        $campaignId = $sendResp->json('campaign_id');
        $this->assertNotNull($campaignId);

        // 3. Status JSON Endpoint
        $statusResp = $this->getJson(route('admin.sms.bulk.campaigns.status', $campaignId));
        $statusResp->assertStatus(200);
        $statusResp->assertJsonPath('status', 'success');
        $statusResp->assertJsonPath('campaign.total_recipients', 2);

        // 4. Controller status view and index view preparation
        $controller = app(\App\Http\Controllers\Admin\SmsBulkSendController::class);
        $campaign = SmsCampaign::findOrFail($campaignId);
        $view = $controller->statusView($campaign);
        $this->assertEquals('Admin.sms.bulk.status', $view->name());
        $this->assertArrayHasKey('campaign', $view->getData());
        $this->assertArrayHasKey('recipients', $view->getData());

        $indexView = $controller->index(request());
        $this->assertEquals('Admin.sms.bulk.index', $indexView->name());
        $this->assertArrayHasKey('advertisingNumbers', $indexView->getData());
        $this->assertArrayHasKey('templates', $indexView->getData());
    }

    /**
     * Test 7: Scheduled campaign send.
     */
    public function test_scheduled_campaign_support(): void
    {
        $this->seedAdvertisingBalance($this->business->id, 50000);

        User::create(['name' => 'کاربر ۱', 'phone' => '09127770001', 'level' => 'user'])
            ->ghorfeOnlineLists()->attach($this->business->id);

        $futureDate = now()->addDays(2)->toDateTimeString();

        $campaignData = [
            'recipient_type' => 'normal',
            'message' => 'پیامک زمان‌بندی شده برای آینده',
            'sender_number_id' => $this->advSender->id,
            'scheduled_at' => $futureDate,
            'filters' => ['business' => $this->business->id],
        ];

        $campaign = $this->bulkSendService->createCampaign($campaignData, $this->titaniumUser, $this->business);

        $this->assertEquals('scheduled', $campaign->status);
        $this->assertNotNull($campaign->scheduled_at);
        $this->assertEquals(1, $campaign->total_recipients);
    }

    /**
     * Test 8: Preview endpoint returns 403 when a Silver user attempts to preview advertising pool.
     */
    public function test_preview_endpoint_blocks_tier_unauthorized_filters(): void
    {
        $this->actingAs($this->silverUser);

        $response = $this->postJson(route('admin.sms.bulk.preview'), [
            'recipient_type' => 'advertising', // Silver is not allowed!
            'message' => 'متن پیامک',
            'sender_number_id' => $this->advSender->id,
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('error_type', 'tier_denied');
        $response->assertJsonPath('required_tier', 'diamond');
        $response->assertJsonPath('user_tier', 'silver');
    }

    /**
     * Group send with the requests-page filter as the audience (HTTP level): only the people
     * behind matching requests are messaged, opt-outs never are, and a bare "requests" source
     * with no criteria means all requests — not all business users.
     */
    public function test_http_preview_and_send_with_request_filter_audience(): void
    {
        BulkSmsSendService::setGlobalGatewayDispatcher(function () {});

        $this->prepareRequestTables();
        $this->seedAdvertisingBalance($this->business->id, 100000);

        $makeRequest = function (string $phone, int $status) {
            $registrant = User::create(['name' => 'ثبت‌کننده', 'phone' => $phone, 'level' => 'user']);
            $request = \App\Models\Callcenter::create(['type' => 0, 'status' => $status, 'reg_id' => $registrant->id]);
            \DB::table('callcenter_ghorfe_online_list')->insert(['callcenter_id' => $request->id, 'ghorfe_online_list_id' => $this->business->id]);
        };
        $makeRequest('09121110001', 3);
        $makeRequest('09121110002', 0);
        $makeRequest('09121110003', 11); // opted out of SMS
        User::create(['name' => 'بدون درخواست', 'phone' => '09121110004', 'level' => 'user'])
            ->ghorfeOnlineLists()->attach($this->business->id);

        $payload = fn (array $requestFilter) => [
            'recipient_type' => 'normal',
            'message' => 'پیامک آزمایشی',
            'sender_number_id' => $this->advSender->id,
            'filters' => ['request_filter' => ['__source' => 'requests'] + $requestFilter],
        ];

        // Narrowed by criteria
        $this->postJson(route('admin.sms.bulk.preview'), $payload(['status' => ['3']]))
            ->assertOk()->assertJsonPath('recipient_summary.valid_count', 1);

        // Source only: all requests, still never the opt-out nor the user without a request
        $this->postJson(route('admin.sms.bulk.preview'), $payload([]))
            ->assertOk()->assertJsonPath('recipient_summary.valid_count', 2);

        // Explicitly asking for the opt-out status must not reach them
        $this->postJson(route('admin.sms.bulk.preview'), $payload(['status' => ['11']]))
            ->assertStatus(200)->assertJsonPath('recipient_summary.valid_count', 0);

        // Send: the campaign's recipients are exactly the two live requests' registrants
        $send = $this->postJson(route('admin.sms.bulk.send'), $payload([]));
        $send->assertOk()->assertJsonPath('status', 'success');

        $phones = \App\Models\SmsCampaignRecipient::where('sms_campaign_id', $send->json('campaign_id'))->pluck('phone')->sort()->values()->all();
        $this->assertSame(['09121110001', '09121110002'], $phones);

        // Each message is attributed to the request it was sent for (feeds the request's SMS tab).
        $messages = \App\Models\SmsMessage::where('sms_campaign_id', $send->json('campaign_id'))->orderBy('recipient_phone')->get();
        $this->assertCount(2, $messages);
        $this->assertSame(
            \App\Models\Callcenter::whereIn('status', [3, 0])->orderBy('id')->pluck('id')->all(),
            $messages->pluck('callcenter_id')->all()
        );
    }

    // ------------------------------------------------------------------
    // Enter recipients from Excel
    // ------------------------------------------------------------------

    public function test_excel_extraction_handles_real_world_cells(): void
    {
        $result = $this->filterService->extractPhonesFromSheets([
            [
                ['نام', 'شماره موبایل', 'توضیحات'],                   // header text is ignored
                ['علی', 9121234567, 'مشتری'],                          // Excel dropped the leading zero
                ['رضا', 9.121234568E9, ''],                            // large number arrives as a float
                ['سارا', '۰۹۱۲۱۲۳۴۵۶۹', null],                        // Persian digits
                ['نیلوفر', '+98 912 123 4570', '09121234571, 09121234572'], // prefix, spaces, two numbers in a cell
                ['تکراری', '09121234567', ''],                         // duplicate of row 1
                ['بد', '0912123', '12345678901'],                      // long digit strings that are not mobiles
            ],
            [
                ['09121234573'],                                       // second sheet is read too
            ],
        ]);

        $this->assertSame([
            '09121234567', '09121234568', '09121234569', '09121234570',
            '09121234571', '09121234572', '09121234573',
        ], $result['numbers']);
        $this->assertSame(7, $result['valid_count']);
        $this->assertSame(1, $result['duplicate_count']);
        $this->assertSame(1, $result['invalid_count'], 'only the 11-digit non-mobile counts; "0912123" is too short to look like a phone');
        $this->assertSame(['12345678901'], $result['invalid_sample']);
    }

    public function test_excel_endpoint_reads_xlsx_and_csv_files_and_validates_uploads(): void
    {
        $this->actingAs($this->titaniumUser);
        \DB::table('employees')->insert(['user_id' => $this->titaniumUser->id, 'ghorfe_online_list_id' => $this->business->id]);

        // A genuine .xlsx file, written with the same library the endpoint reads with.
        $sheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet->getActiveSheet()->fromArray([['phone'], [9121234567], ['09121234568'], ['09121234568'], ['oops']]);
        $path = tempnam(sys_get_temp_dir(), 'nums') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sheet))->save($path);

        $xlsx = $this->postJson(route('admin.sms.bulk.excel'), [
            'file' => new \Illuminate\Http\UploadedFile($path, 'numbers.xlsx', null, null, true),
        ]);
        $xlsx->assertOk()->assertJsonPath('status', 'success')->assertJsonPath('valid_count', 2)->assertJsonPath('duplicate_count', 1);
        $this->assertSame(['09121234567', '09121234568'], $xlsx->json('numbers'));

        $csv = $this->postJson(route('admin.sms.bulk.excel'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('numbers.csv', "phone\n09121234569\n09121234570\n"),
        ]);
        $csv->assertOk()->assertJsonPath('valid_count', 2);

        // No usable number -> clear error, nothing returned
        $empty = $this->postJson(route('admin.sms.bulk.excel'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('none.csv', "name\nali\n"),
        ]);
        $empty->assertStatus(422)->assertJsonPath('status', 'error');

        // Wrong type and missing file are rejected by validation
        $this->postJson(route('admin.sms.bulk.excel'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
        ])->assertStatus(422)->assertJsonValidationErrors('file');
        $this->postJson(route('admin.sms.bulk.excel'), [])->assertStatus(422)->assertJsonValidationErrors('file');

        @unlink($path);
    }

    /**
     * Tables + permission the requests (callcenters) access scope needs, and the acting admin
     * pinned to the test business.
     */
    private function prepareRequestTables(): void
    {
        \Illuminate\Support\Facades\Schema::create('sms', function (Blueprint $t) {
            $t->increments('id');
            $t->string('sender_number')->nullable();
            $t->string('body')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        \Illuminate\Support\Facades\Schema::create('callcenters', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('sms_id')->nullable();
            $t->unsignedInteger('reg_id')->nullable();
            $t->unsignedInteger('user_id')->nullable();
            $t->unsignedInteger('agent_id')->nullable();
            $t->unsignedInteger('representation_id')->nullable();
            $t->integer('type')->default(0);
            $t->string('request_type')->nullable();
            $t->integer('status')->default(0);
            $t->text('description')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        \Illuminate\Support\Facades\Schema::create('callcenter_ghorfe_online_list', function (Blueprint $t) {
            $t->unsignedInteger('callcenter_id');
            $t->unsignedBigInteger('ghorfe_online_list_id');
        });

        \Illuminate\Support\Facades\Schema::create('permissions', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('display_name')->nullable();
            $t->timestamps();
        });
        \Illuminate\Support\Facades\Schema::create('permission_user', function (Blueprint $t) {
            $t->unsignedInteger('permission_id');
            $t->unsignedInteger('user_id');
            $t->string('user_type');
        });
        \Illuminate\Support\Facades\Schema::create('permission_role', function (Blueprint $t) {
            $t->unsignedInteger('permission_id');
            $t->unsignedInteger('role_id');
        });

        $this->actingAs($this->titaniumUser);
        \DB::table('employees')->insert(['user_id' => $this->titaniumUser->id, 'ghorfe_online_list_id' => $this->business->id]);
        $permissionId = \DB::table('permissions')->insertGetId(['name' => 'callcenter-detail']);
        \DB::table('permission_user')->insert(['permission_id' => $permissionId, 'user_id' => $this->titaniumUser->id, 'user_type' => User::class]);
    }

    private function makeRequest(GhorfeOnlineList $business, string $phone, int $status = 0, array $attrs = []): \App\Models\Callcenter
    {
        $registrant = User::create(['name' => 'ثبت‌کننده', 'phone' => $phone, 'level' => 'user']);
        $request = \App\Models\Callcenter::create(array_merge(['type' => 0, 'status' => $status, 'reg_id' => $registrant->id], $attrs));
        \DB::table('callcenter_ghorfe_online_list')->insert(['callcenter_id' => $request->id, 'ghorfe_online_list_id' => $business->id]);

        return $request;
    }

    private function sentMessage(array $attrs): \App\Models\SmsMessage
    {
        return \App\Models\SmsMessage::create(array_merge([
            'ghorfe_online_list_id' => $this->business->id, 'direction' => 'outbound', 'service_type' => 'advertising',
            'recipient_phone' => '09121110001', 'content' => 'متن', 'status' => 'sent', 'classification' => 'regular',
        ], $attrs));
    }

    public function test_request_sms_history_shows_only_that_requests_messages(): void
    {
        $this->prepareRequestTables();
        $confidentialId = \DB::table('permissions')->insertGetId(['name' => 'view-confidential-sms']);
        \DB::table('permission_user')->insert(['permission_id' => $confidentialId, 'user_id' => $this->titaniumUser->id, 'user_type' => User::class]);

        $a = $this->makeRequest($this->business, '09121110001', 3);
        $b = $this->makeRequest($this->business, '09121110001', 3); // same person, a second request
        $this->sentMessage(['callcenter_id' => $a->id, 'content' => 'برای درخواست الف']);
        $this->sentMessage(['callcenter_id' => $a->id, 'content' => 'محرمانه الف', 'classification' => 'classified', 'delivery_status' => 'delivered']);
        $this->sentMessage(['callcenter_id' => $b->id, 'content' => 'برای درخواست ب']);
        $this->sentMessage(['callcenter_id' => null, 'content' => 'پیام عمومی همان کاربر']);

        // A request that came in by SMS shows the inbound message first-class
        $smsId = \DB::table('sms')->insertGetId(['sender_number' => '09125550000', 'body' => 'سلام، درخواست دارم', 'created_at' => now()->subDay(), 'updated_at' => now()]);
        $c = $this->makeRequest($this->business, '09125550000', 0, ['type' => 1, 'sms_id' => $smsId]);

        $res = $this->getJson(route('callcenter.smsHistory', $a->id))->assertOk();
        $bodies = collect($res->json('data.messages'));
        $this->assertCount(2, $bodies, 'only the two messages attributed to request A');
        $this->assertEqualsCanonicalizing(['برای درخواست الف', 'محرمانه الف'], $bodies->pluck('content')->all(), 'a viewer with view-confidential-sms sees the text');
        $this->assertSame(['outbound', 'outbound'], $bodies->pluck('direction')->all());
        $this->assertContains('delivered', $bodies->pluck('status')->all());

        $inbound = $this->getJson(route('callcenter.smsHistory', $c->id))->assertOk()->json('data.messages');
        $this->assertCount(1, $inbound);
        $this->assertSame('inbound', $inbound[0]['direction']);
        $this->assertSame('سلام، درخواست دارم', $inbound[0]['content']);
        $this->assertSame('09125550000', $inbound[0]['party']);

        $this->getJson(route('callcenter.smsHistory', $b->id))->assertOk()->assertJsonCount(1, 'data.messages');
    }

    public function test_request_sms_history_hides_confidential_text_and_other_businesses_requests(): void
    {
        $this->prepareRequestTables();

        $own = $this->makeRequest($this->business, '09121110001', 3);
        $this->sentMessage(['callcenter_id' => $own->id, 'content' => 'سری', 'classification' => 'classified']);

        $other = GhorfeOnlineList::create(['title' => 'کسب‌وکار دیگر']);
        $foreign = $this->makeRequest($other, '09127770001', 3);
        $this->sentMessage(['ghorfe_online_list_id' => $other->id, 'callcenter_id' => $foreign->id, 'content' => 'دیگران']);

        // This admin lacks view-confidential-sms -> the body is omitted server-side
        $row = $this->getJson(route('callcenter.smsHistory', $own->id))->assertOk()->json('data.messages.0');
        $this->assertTrue($row['redacted']);
        $this->assertNull($row['content']);

        // Another business' request is not reachable at all
        $this->getJson(route('callcenter.smsHistory', $foreign->id))->assertNotFound();
        $this->getJson(route('callcenter.smsHistory', 999999))->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Silver (and every non-Titanium tier) may only message their own business' users
    // ------------------------------------------------------------------

    private function otherBusinessWithCustomer(): GhorfeOnlineList
    {
        $other = GhorfeOnlineList::create(['title' => 'کسب‌وکار رقیب']);
        User::create(['name' => 'مشتری رقیب', 'phone' => '09127770001', 'level' => 'user'])
            ->ghorfeOnlineLists()->attach($other->id);

        return $other;
    }

    private function previewPayload(array $filters): array
    {
        return [
            'recipient_type' => 'normal',
            'message' => 'متن پیامک',
            'sender_number_id' => $this->advSender->id,
            'filters' => $filters,
        ];
    }

    public function test_non_titanium_tiers_cannot_target_another_businesses_users(): void
    {
        $other = $this->otherBusinessWithCustomer();
        User::create(['name' => 'مشتری من', 'phone' => '09121110001', 'level' => 'user'])
            ->ghorfeOnlineLists()->attach($this->business->id);
        $this->seedAdvertisingBalance($this->business->id, 100000);

        foreach (['silver' => $this->silverUser, 'gold' => $this->goldUser, 'diamond' => $this->diamondUser] as $tier => $actor) {
            \DB::table('employees')->insert(['user_id' => $actor->id, 'ghorfe_online_list_id' => $this->business->id]);
            $this->actingAs($actor);

            // Their own business: fine, and sees only their own customer
            $this->postJson(route('admin.sms.bulk.preview'), $this->previewPayload(['business' => $this->business->id]))
                ->assertOk()->assertJsonPath('recipient_summary.valid_count', 1);

            // Someone else's business, as a scalar or as a list: refused with the tier that would allow it
            foreach ([$other->id, [$other->id], [$this->business->id, $other->id]] as $foreign) {
                $this->postJson(route('admin.sms.bulk.preview'), $this->previewPayload(['business' => $foreign]))
                    ->assertStatus(403)
                    ->assertJsonPath('error_type', 'tier_denied')
                    ->assertJsonPath('required_tier', 'titanium')
                    ->assertJsonPath('user_tier', $tier);
            }

            // The alias key is guarded the same way
            $this->postJson(route('admin.sms.bulk.preview'), $this->previewPayload(['ghorfe_online_list_id' => $other->id]))
                ->assertStatus(403)->assertJsonPath('required_tier', 'titanium');
        }
    }

    public function test_foreign_business_target_is_refused_on_send_and_creates_nothing(): void
    {
        $other = $this->otherBusinessWithCustomer();
        $this->seedAdvertisingBalance($this->business->id, 100000);
        \DB::table('employees')->insert(['user_id' => $this->silverUser->id, 'ghorfe_online_list_id' => $this->business->id]);
        $this->actingAs($this->silverUser);

        $this->postJson(route('admin.sms.bulk.send'), $this->previewPayload(['business' => $other->id]))
            ->assertStatus(403)->assertJsonPath('error_type', 'tier_denied');

        $this->assertSame(0, SmsCampaign::count());
        $this->assertSame(0, SmsCampaignRecipient::count());
        $this->assertSame(100000.0, (float) AdvertisingBalance::where('ghorfe_online_list_id', $this->business->id)->value('balance'));
    }

    public function test_titanium_can_still_target_another_business(): void
    {
        $other = $this->otherBusinessWithCustomer();
        $this->seedAdvertisingBalance($this->business->id, 100000);
        \DB::table('employees')->insert(['user_id' => $this->titaniumUser->id, 'ghorfe_online_list_id' => $this->business->id]);
        $this->actingAs($this->titaniumUser);

        $this->postJson(route('admin.sms.bulk.preview'), $this->previewPayload(['business' => $other->id]))
            ->assertOk()->assertJsonPath('recipient_summary.valid_count', 1);
    }

    public function test_required_tier_for_business_filters(): void
    {
        $tier = $this->tierService;
        $own = $this->business->id;

        // No business filter / own business only: the base tier
        $this->assertSame('silver', $tier->getRequiredTier([], 'normal', $own));
        $this->assertSame('silver', $tier->getRequiredTier(['business' => $own], 'normal', $own));
        $this->assertSame('silver', $tier->getRequiredTier(['business' => (string) $own], 'normal', $own));
        $this->assertSame('silver', $tier->getRequiredTier(['business' => [$own]], 'normal', $own));
        $this->assertSame('silver', $tier->getRequiredTier(['business' => ''], 'normal', $own));

        // Anything beyond the own business needs Titanium
        $this->assertSame('titanium', $tier->getRequiredTier(['business' => $own + 1], 'normal', $own));
        $this->assertSame('titanium', $tier->getRequiredTier(['business' => [$own, $own + 1]], 'normal', $own));
        $this->assertSame('titanium', $tier->getRequiredTier(['ghorfe_online_list_id' => $own + 1], 'normal', $own));
        $this->assertSame('titanium', $tier->getRequiredTier(['business' => 'all'], 'normal', $own));
        $this->assertSame('titanium', $tier->getRequiredTier(['business' => ['all']], 'normal'));

        // The sender's business unknown: behaviour is unchanged (only "all" is cross-business)
        $this->assertSame('silver', $tier->getRequiredTier(['business' => $own + 1], 'normal'));
    }
}

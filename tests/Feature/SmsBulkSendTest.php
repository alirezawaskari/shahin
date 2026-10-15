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

    public function test_titanium_user_has_full_pool_access(): void
    {
        // Titanium can query cross-business advertising pool
        $this->tierService->assertCanQuery($this->titaniumUser, [
            'business' => 'all',
            'role' => 2,
            'service_category_id' => 1,
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
}

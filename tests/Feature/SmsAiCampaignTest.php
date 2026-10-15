<?php

namespace Tests\Feature;

use App\Contracts\Ai\LlmClientInterface;
use App\Exceptions\Sms\InsufficientBalanceException;
use App\Models\AdvertisingBalance;
use App\Models\Category;
use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\Service;
use App\Models\SmsCampaign;
use App\Models\User;
use App\Services\Ai\FakeLlmClient;
use App\Services\Sms\AiCampaignAssistant;
use App\Services\Sms\BulkSmsRecipientFilterService;
use App\Services\Sms\BulkSmsSendService;
use App\Services\Sms\SmsAccessTierService;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

class SmsAiCampaignTest extends TestCase
{
    use \Tests\Concerns\SeedsSmsBalances;

    protected GhorfeOnlineList $business;
    protected SenderNumber $advSender;
    protected User $superAdmin;
    protected User $silverUser;
    protected User $diamondUser;
    protected FakeLlmClient $fakeLlmClient;
    protected AiCampaignAssistant $aiAssistant;
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

        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->longText('role')->nullable();
            $table->string('model');
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

        $this->business = GhorfeOnlineList::create(['title' => 'فروشگاه شایان']);

        $this->advSender = SenderNumber::create([
            'number' => '50008888',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);
        $this->business->senderNumbers()->attach($this->advSender->id, ['assigned_at' => now()]);

        // Users
        $this->superAdmin = User::create([
            'name' => 'مدیر',
            'family' => 'سیستم',
            'phone' => '09121111111',
            'level' => 'superAdmin',
            'access_tier' => 'titanium',
            'active' => 1,
        ]);

        $this->silverUser = User::create([
            'name' => 'کاربر',
            'family' => 'سیلور',
            'phone' => '09122222222',
            'level' => 'admin',
            'access_tier' => 'silver',
            'active' => 1,
        ]);

        $this->diamondUser = User::create([
            'name' => 'کاربر',
            'family' => 'دیاموند',
            'phone' => '09123333333',
            'level' => 'admin',
            'access_tier' => 'diamond',
            'active' => 1,
        ]);

        // Instantiate fake LLM client
        $this->fakeLlmClient = new FakeLlmClient();
        $this->app->instance(LlmClientInterface::class, $this->fakeLlmClient);

        $this->tierService = app(SmsAccessTierService::class);
        $this->filterService = app(BulkSmsRecipientFilterService::class);
        $this->bulkSendService = app(BulkSmsSendService::class);
        $this->aiAssistant = app(AiCampaignAssistant::class);
    }

    /**
     * Test that the AI assistant service has no direct send or dispatch code path.
     */
    public function test_ai_assistant_service_has_no_direct_send_code_path(): void
    {
        $reflector = new ReflectionClass(AiCampaignAssistant::class);
        $methods = array_map(fn($m) => $m->getName(), $reflector->getMethods());

        // Assert that AiCampaignAssistant does NOT contain any sending/dispatch methods
        $this->assertNotContains('send', $methods);
        $this->assertNotContains('dispatch', $methods);
        $this->assertNotContains('executeCampaign', $methods);
        $this->assertNotContains('createCampaign', $methods);
        $this->assertNotContains('dispatchToGateway', $methods);

        // Assert that only analysis, drafting and cost estimation methods exist
        $this->assertContains('proposeAudience', $methods);
        $this->assertContains('draftMessage', $methods);
        $this->assertContains('calculateCost', $methods);
    }

    /**
     * Test that the human confirmation gate is unbypassable at the BulkSmsSendService level.
     */
    public function test_human_confirmation_gate_is_unbypassable_at_service_level(): void
    {
        // Populate customer
        $contact = User::create([
            'name' => 'مریم',
            'family' => 'رضایی',
            'phone' => '09129876543',
            'sex' => 2,
            'level' => 'user',
            'active' => 1,
        ]);
        $this->business->users()->attach($contact->id);

        $this->seedAdvertisingBalance($this->business->id, 500000);

        // Case 1: Missing human_confirmed parameter for an AI campaign
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ارسال کمپین هوش مصنوعی نیازمند تایید نهایی کاربر (Human Confirmation) می‌باشد.');

        $this->bulkSendService->createCampaign([
            'type' => 'ai',
            'ai_goal_prompt' => 'تخفیف ویژه بانوان',
            'message' => 'پیامک تست',
            'filters' => ['gender' => 2],
            'sender_number_id' => $this->advSender->id,
            // 'human_confirmed' => true is intentionally omitted!
        ], $this->superAdmin, $this->business);
    }

    /**
     * Test that false or 0 human confirmation is also rejected.
     */
    public function test_false_human_confirmation_is_rejected(): void
    {
        $this->seedAdvertisingBalance($this->business->id, 500000);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ارسال کمپین هوش مصنوعی نیازمند تایید نهایی کاربر (Human Confirmation) می‌باشد.');

        $this->bulkSendService->createCampaign([
            'type' => 'ai',
            'ai_goal_prompt' => 'تخفیف ویژه بانوان',
            'human_confirmed' => false,
            'message' => 'پیامک تست',
            'filters' => [],
            'sender_number_id' => $this->advSender->id,
        ], $this->superAdmin, $this->business);
    }

    /**
     * Test that the human confirmation gate is unbypassable at the HTTP controller level.
     */
    public function test_human_confirmation_gate_is_unbypassable_at_controller_level(): void
    {
        $contact = User::create([
            'name' => 'سارا',
            'family' => 'محمدی',
            'phone' => '09121234567',
            'sex' => 2,
            'level' => 'user',
            'active' => 1,
        ]);
        $this->business->users()->attach($contact->id);

        $this->seedAdvertisingBalance($this->business->id, 500000);

        // Attempt launch with human_confirmed = false or omitted
        $response = $this->actingAs($this->superAdmin)->postJson(route('admin.sms.ai.confirmAndLaunch'), [
            'human_confirmed' => false,
            'goal' => 'هدف کمپین',
            'message' => 'متن پیامک تستی',
            'filters' => ['gender' => 2],
            'sender_number_id' => $this->advSender->id,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('sms_campaigns', 0);
    }

    /**
     * Test AI audience filter proposal is compatible with BulkSmsRecipientFilterService schema.
     */
    public function test_ai_proposed_audience_filters_are_compatible_with_bulk_recipient_filter_service(): void
    {
        // Seed database users with specific attributes
        $femaleTehran = User::create([
            'name' => 'زهرا',
            'family' => 'کریمی',
            'phone' => '09121112233',
            'sex' => 2,
            'province_id' => 8, // Tehran
            'level' => 'user',
            'active' => 1,
        ]);
        $this->business->users()->attach($femaleTehran->id);

        $maleTehran = User::create([
            'name' => 'علی',
            'family' => 'حسینی',
            'phone' => '09122223344',
            'sex' => 1,
            'province_id' => 8,
            'level' => 'user',
            'active' => 1,
        ]);
        $this->business->users()->attach($maleTehran->id);

        $femaleShiraz = User::create([
            'name' => 'فاطمه',
            'family' => 'شیرازی',
            'phone' => '09123334455',
            'sex' => 2,
            'province_id' => 14, // Fars
            'level' => 'user',
            'active' => 1,
        ]);
        $this->business->users()->attach($femaleShiraz->id);

        // Mock LLM response to propose female + Tehran filters
        $this->fakeLlmClient->setFakeJsonResponse([
            'filters' => [
                'gender' => 2,
                'province_id' => 8,
            ],
            'rationale' => 'بانوان ساکن استان تهران به عنوان جامعه هدف پیشنهاد شدند.',
        ]);

        $proposal = $this->aiAssistant->proposeAudience(
            'تخفیف ویژه بانوان تهرانی',
            $this->superAdmin,
            $this->business
        );

        // Verify structure
        $this->assertEquals('تخفیف ویژه بانوان تهرانی', $proposal['goal']);
        $this->assertEquals(2, $proposal['filters']['gender']);
        $this->assertEquals(8, $proposal['filters']['province_id']);
        $this->assertStringContainsString('بانوان ساکن استان تهران', $proposal['rationale']);
        $this->assertTrue($proposal['is_tier_allowed']);

        // Verify count: exactly 1 contact matches (femaleTehran)
        $this->assertEquals(1, $proposal['recipient_count']);

        // Verify compatibility: BulkSmsRecipientFilterService handles these filters directly
        $queryResult = $this->filterService->filterRecipients($proposal['filters'], $this->superAdmin, $this->business);
        $this->assertEquals(1, $queryResult['valid_count']);
        $this->assertEquals('09121112233', $queryResult['valid_recipients'][0]['phone']);
    }

    /**
     * Test AI drafts message copy sized to page budget.
     */
    public function test_ai_drafts_message_sized_to_page_budget(): void
    {
        $this->fakeLlmClient->setFakeTextResponse('جشنواره پاییزه با ۵۰٪ تخفیف ویژه! اطلاعات بیشتر: lalecard.ir');

        $approvedAudience = [
            'filters' => ['gender' => 2],
            'rationale' => 'مشتریان خانم',
            'recipient_count' => 10,
        ];

        $draft = $this->aiAssistant->draftMessage(
            'تخفیف ویژه پاییزی',
            $approvedAudience,
            1, // 1 page budget
            $this->superAdmin,
            $this->business
        );

        $this->assertNotEmpty($draft['draft_text']);
        $this->assertEquals(1, $draft['page_budget']);
        $this->assertTrue($draft['fits_page_budget']);
        $this->assertEquals(1, $draft['page_count']);
    }

    /**
     * Test the entire end-to-end AI campaign lifecycle with explicit approval and human confirmation.
     */
    public function test_full_ai_campaign_lifecycle_with_human_confirmation(): void
    {
        // 1. Setup recipient
        $customer = User::create([
            'name' => 'الهام',
            'family' => 'احمدی',
            'phone' => '09127778899',
            'sex' => 2,
            'level' => 'user',
            'active' => 1,
        ]);
        $this->business->users()->attach($customer->id);

        $this->seedAdvertisingBalance($this->business->id, 500000);

        // 2. Step 1 & 2: Propose Audience via HTTP
        $this->fakeLlmClient->setFakeJsonResponse([
            'filters' => ['gender' => 2],
            'rationale' => 'بانوان فروشگاه شایان',
        ]);

        $proposeRes = $this->actingAs($this->superAdmin)->postJson(route('admin.sms.ai.proposeAudience'), [
            'goal' => 'تبلیغ تخفیف برای خانم‌ها',
        ]);
        $proposeRes->assertOk();
        $this->assertEquals(1, $proposeRes->json('data.recipient_count'));

        // 3. Step 3: Explicit Human Approval Gate for Audience
        $approveRes = $this->actingAs($this->superAdmin)->postJson(route('admin.sms.ai.approveAudience'), [
            'goal' => 'تبلیغ تخفیف برای خانم‌ها',
            'filters' => ['gender' => 2],
            'approved' => true,
            'rationale' => 'بانوان فروشگاه شایان',
            'recipient_count' => 1,
        ]);
        $approveRes->assertOk();
        $approvedData = $approveRes->json('approved_audience');
        $this->assertNotNull($approvedData);

        // 4. Step 4: AI Drafts Message
        $this->fakeLlmClient->setFakeTextResponse('تخفیف ویژه بانوان گرامی در لاله کارت: lalecard.ir');
        $draftRes = $this->actingAs($this->superAdmin)->postJson(route('admin.sms.ai.draftMessage'), [
            'goal' => 'تبلیغ تخفیف برای خانم‌ها',
            'approved_audience' => $approvedData,
            'page_budget' => 1,
        ]);
        $draftRes->assertOk();
        $draftedText = $draftRes->json('data.draft_text');

        // 5. Step 5 & 6: Final Cost Calculation with user-edited text
        $userEditedText = $draftedText . ' کد: SHAHIN';
        $costRes = $this->actingAs($this->superAdmin)->postJson(route('admin.sms.ai.calculateCost'), [
            'message' => $userEditedText,
            'filters' => ['gender' => 2],
            'sender_number_id' => $this->advSender->id,
            'auto_append_cancel' => true,
        ]);
        $costRes->assertOk();
        $this->assertTrue($costRes->json('data.financial_summary.has_sufficient_balance'));
        $estimatedCost = $costRes->json('data.financial_summary.estimated_total_cost');
        $this->assertGreaterThan(0, $estimatedCost);

        // 6. Step 7 & 8: Final Human Confirmation Gate -> Launch Handoff
        $launchRes = $this->actingAs($this->superAdmin)->postJson(route('admin.sms.ai.confirmAndLaunch'), [
            'human_confirmed' => true,
            'goal' => 'تبلیغ تخفیف برای خانم‌ها',
            'message' => $userEditedText,
            'filters' => ['gender' => 2],
            'sender_number_id' => $this->advSender->id,
            'auto_append_cancel' => true,
        ]);

        $launchRes->assertOk();
        $campaignId = $launchRes->json('campaign_id');
        $this->assertNotNull($campaignId);

        // 7. Verify DB Campaign Record
        $campaign = SmsCampaign::find($campaignId);
        $this->assertNotNull($campaign);
        $this->assertEquals('ai', $campaign->type);
        $this->assertEquals('تبلیغ تخفیف برای خانم‌ها', $campaign->ai_goal_prompt);
        $this->assertEquals(1, $campaign->total_recipients);
        $this->assertEquals($this->business->id, $campaign->ghorfe_online_list_id);

        // 8. Verify recipients and send pipeline
        $this->assertDatabaseHas('sms_campaign_recipients', [
            'sms_campaign_id' => $campaign->id,
            'phone' => '09127778899',
        ]);
    }

    /**
     * Test that insufficient balance blocks AI campaign launch even with human confirmation.
     */
    public function test_insufficient_balance_blocks_ai_campaign_launch(): void
    {
        // Zero balance
        $this->seedAdvertisingBalance($this->business->id, 0);

        $customer = User::create([
            'name' => 'تست',
            'phone' => '09129990011',
            'level' => 'user',
            'active' => 1,
        ]);
        $this->business->users()->attach($customer->id);

        $response = $this->actingAs($this->superAdmin)->postJson(route('admin.sms.ai.confirmAndLaunch'), [
            'human_confirmed' => true,
            'goal' => 'تست موجودی',
            'message' => 'پیام تستی',
            'filters' => [],
            'sender_number_id' => $this->advSender->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'error_type' => 'insufficient_balance',
        ]);
    }

    /**
     * Test tier access gating on AI proposals for Silver users.
     */
    public function test_tier_access_gating_respected_on_ai_proposals(): void
    {
        // Propose Diamond category filter for a silver user
        $this->fakeLlmClient->setFakeJsonResponse([
            'filters' => ['service_category_id' => 99],
            'rationale' => 'دسته‌بندی خدمات تخصصی',
        ]);

        $proposal = $this->aiAssistant->proposeAudience(
            'خدمات تخصصی ویژه',
            $this->silverUser,
            $this->business
        );

        $this->assertEquals('diamond', $proposal['required_tier']);
        $this->assertEquals('silver', $proposal['user_tier']);
        $this->assertFalse($proposal['is_tier_allowed']);
        $this->assertNotNull($proposal['tier_message']);
    }
}

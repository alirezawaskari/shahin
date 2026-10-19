<?php

namespace Tests\Feature;

use App\Models\BusinessSenderNumber;
use App\Models\Contract;
use App\Models\ContractFolder;
use App\Models\ContractUsage;
use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\Setting;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\SmsMessageTemplateHistory;
use App\Models\SmsPanel;
use App\Models\User;
use App\Services\Sms\DynamicParameterResolver;
use App\Services\SmsSettings\SmsContractService;
use App\Services\SmsSettings\SmsMessageGroupService;
use App\Services\SmsSettings\SmsMessageTemplateService;
use App\Services\SmsSettings\SmsPanelService;
use App\Services\SmsSettings\SmsSenderNumberService;
use Database\Seeders\SmsMessageGroupSeeder;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SmsSettingsTest extends TestCase
{
    use \Tests\Concerns\SeedsSmsBalances;

    protected User $adminUser;
    protected GhorfeOnlineList $businessA;
    protected GhorfeOnlineList $businessB;

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
            $table->string('token')->nullable();
            $table->string('api_token')->nullable();
            $table->string('avatar')->nullable();
            $table->string('level')->default('admin');
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('admin_representation_id')->nullable();
            $table->unsignedBigInteger('center_id')->nullable();
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

        Schema::create('ghorfe_settings', function (Blueprint $table) {
            $table->id();
            $table->text('value')->nullable();
            $table->string('key')->nullable();
            $table->nullableMorphs('settingable');
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
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->nullableTimestamps();
        });

        // Run SMS Foundation migrations
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
        ];

        foreach ($migrationFiles as $file) {
            $migration = require base_path($file);
            $migration->up();
        }

        // Contracts tables for contract wiring tests
        Schema::create('contract_folders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type')->nullable();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('file')->nullable();
            $table->integer('status')->default(1);
            $table->unsignedBigInteger('contract_folder_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('contract_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });

        Schema::create('contract_usages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contract_id');
            $table->string('usable_type', 120)->nullable();
            $table->unsignedBigInteger('usable_id')->nullable();
            $table->string('location', 50);
            $table->string('contract_number', 100)->nullable();
            $table->date('contract_date')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        $this->seed(SmsMessageGroupSeeder::class);

        $this->adminUser = User::create([
            'name' => 'ادمین',
            'family' => 'اصلی',
            'phone' => '09120000000',
            'level' => 'superAdmin',
            'active' => true,
        ]);

        $this->businessA = GhorfeOnlineList::create(['title' => 'فروشگاه آلفا', 'slug' => 'alfa']);
        $this->businessB = GhorfeOnlineList::create(['title' => 'فروشگاه بتا', 'slug' => 'beta']);
    }

    // ==========================================
    // 1. SMS PANEL SETTINGS TESTS
    // ==========================================

    public function test_sms_panel_crud_and_status_toggle(): void
    {
        $panelService = app(SmsPanelService::class);

        // 1. Create
        $panel = $panelService->create([
            'name' => 'IranSms',
            'type' => 'otp',
            'status' => 'active',
            'connection_info' => [
                'api_key' => 'secret_key_123',
                'username' => 'iransms_user',
                'base_url' => 'https://api.iransms.com',
            ],
            'send_settings' => [
                'default_sender' => '90001',
                'timeout' => 20,
            ],
            'receive_settings' => [
                'webhook_url' => 'https://lalecard.ir/api/webhook',
            ],
            'delivery_settings' => [
                'auto_check' => true,
            ],
            'description' => 'پنل اصلی پیامک خدماتی',
        ]);

        $this->assertDatabaseHas('sms_panels', [
            'id' => $panel->id,
            'name' => 'IranSms',
            'type' => 'otp',
            'status' => 'active',
        ]);

        $this->assertEquals('secret_key_123', $panel->getConnectionSetting('api_key'));
        $this->assertEquals('90001', $panel->getSendSetting('default_sender'));

        // 2. Setting polymorphic sync
        $this->assertDatabaseHas('ghorfe_settings', [
            'key' => 'sms_panel_iran_sms_status',
            'value' => 'active',
        ]);

        // 3. Update
        $panelService->update($panel, [
            'description' => 'توضیحات به‌روزرسانی شده',
            'send_settings' => ['default_sender' => '90002'],
        ]);
        $this->assertEquals('توضیحات به‌روزرسانی شده', $panel->fresh()->description);
        $this->assertEquals('90002', $panel->fresh()->getSendSetting('default_sender'));

        // 4. Toggle Status
        $panelService->toggleStatus($panel);
        $this->assertEquals('inactive', $panel->fresh()->status);
        $this->assertFalse($panel->fresh()->isActive());

        // 5. Delete
        $panelService->delete($panel);
        $this->assertSoftDeleted('sms_panels', ['id' => $panel->id]);
    }

    // ==========================================
    // 2. SENDER NUMBER MANAGEMENT & OTP GUARD
    // ==========================================

    public function test_sender_number_crud_and_single_otp_guard_clean_validation(): void
    {
        $senderService = app(SmsSenderNumberService::class);

        // 1. Create first OTP sender number - succeeds
        $otpSender = $senderService->create([
            'number' => '90001',
            'type' => 'otp',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('sender_numbers', ['id' => $otpSender->id, 'type' => 'otp']);

        // 2. Attempt to create a second OTP sender number - must throw ValidationException surfacing the guard cleanly
        $this->expectException(ValidationException::class);
        try {
            $senderService->create([
                'number' => '90002',
                'type' => 'otp',
                'sms_panel' => 'NeginSms',
                'status' => 'active',
            ]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('type', $e->errors());
            $this->assertStringContainsString('Only one OTP sender number is allowed', $e->errors()['type'][0]);
            throw $e;
        }
    }

    public function test_business_only_sees_its_own_assigned_numbers(): void
    {
        $senderService = app(SmsSenderNumberService::class);

        // Create advertising number assigned only to businessA
        $senderA = $senderService->create([
            'number' => '30005001',
            'type' => 'advertising',
            'sms_panel' => 'NeginSms',
            'status' => 'active',
        ], [$this->businessA->id]);

        // Create advertising number assigned only to businessB
        $senderB = $senderService->create([
            'number' => '30005002',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ], [$this->businessB->id]);

        // When listing for businessA: must only see senderA
        $listA = $senderService->list($this->businessA);
        $idsA = collect($listA->items())->pluck('id')->toArray();
        $this->assertContains($senderA->id, $idsA);
        $this->assertNotContains($senderB->id, $idsA);

        // When listing for businessB: must only see senderB
        $listB = $senderService->list($this->businessB);
        $idsB = collect($listB->items())->pluck('id')->toArray();
        $this->assertContains($senderB->id, $idsB);
        $this->assertNotContains($senderA->id, $idsB);

        // Check access helper
        $this->assertTrue($senderService->canAccess($senderA, $this->businessA));
        $this->assertFalse($senderService->canAccess($senderA, $this->businessB));
    }

    // ==========================================
    // 3. TEMPLATE MANAGEMENT & HISTORY DIFF
    // ==========================================

    public function test_template_crud_observer_history_writethrough_and_diff(): void
    {
        $templateService = app(SmsMessageTemplateService::class);
        $this->actingAs($this->adminUser);

        // 1. Create Template
        $group = SmsMessageGroup::first();
        $template = $templateService->create([
            'title' => 'اعلام فاکتور خرید',
            'content' => 'سلام {نام کاربر} عزیز، فاکتور شما به شماره {شناسه فاکتور} صادر شد.',
            'service_type' => 'normal',
            'status' => 'active',
            'sms_message_group_id' => $group->id,
        ], $this->adminUser->id);

        $this->assertDatabaseHas('sms_message_templates', ['id' => $template->id, 'title' => 'اعلام فاکتور خرید']);
        $this->assertDatabaseHas('sms_message_template_histories', [
            'sms_message_template_id' => $template->id,
            'content' => 'سلام {نام کاربر} عزیز، فاکتور شما به شماره {شناسه فاکتور} صادر شد.',
        ]);

        // 2. Update Template content -> observer must write a new history snapshot
        $templateService->update($template, [
            'content' => 'سلام {نام کاربر} عزیز، فاکتور {شناسه فاکتور} با مبلغ جدید صادر شد. جهت پرداخت: {لینک پرداخت}',
        ], $this->adminUser->id);

        $this->assertCount(2, $template->histories()->get());

        // 3. Live Preview with DynamicParameterResolver
        $previewResult = $templateService->preview($template->fresh()->content);
        $this->assertStringContainsString('سلام علی عزیز', $previewResult['preview']);
        $this->assertStringContainsString('lalecard.ir/pf/10482', $previewResult['preview']);
        $this->assertContains('{نام کاربر}', $previewResult['placeholders']);
        $this->assertContains('{شناسه فاکتور}', $previewResult['placeholders']);
        $this->assertContains('{لینک پرداخت}', $previewResult['placeholders']);

        // 4. History diff view
        $historyData = $templateService->getHistoryWithDiff($template);
        $this->assertNotNull($historyData['selected_history']);
        $this->assertNotNull($historyData['previous_history']);
        $this->assertNotEmpty($historyData['diff']);
    }

    // ==========================================
    // 4. MESSAGE GROUPS & SUBGROUPS
    // ==========================================

    public function test_message_groups_and_subgroups_crud(): void
    {
        $groupService = app(SmsMessageGroupService::class);

        // 1. Create parent group
        $parent = $groupService->create([
            'name' => 'نظرسنجی مشتریان',
            'slug' => 'customer-survey',
        ]);
        $this->assertDatabaseHas('sms_message_groups', ['name' => 'نظرسنجی مشتریان', 'parent_id' => null]);

        // 2. Create subgroup
        $subgroup = $groupService->create([
            'name' => 'رضایت از خرید',
            'slug' => 'purchase-satisfaction',
            'parent_id' => $parent->id,
        ]);
        $this->assertDatabaseHas('sms_message_groups', [
            'name' => 'رضایت از خرید',
            'parent_id' => $parent->id,
        ]);

        // 3. Hierarchy tree check
        $tree = $groupService->tree();
        $foundParent = $tree->firstWhere('id', $parent->id);
        $this->assertNotNull($foundParent);
        $this->assertTrue($foundParent->children->contains('id', $subgroup->id));

        // 4. Update
        $groupService->update($subgroup, ['name' => 'رضایت از خدمات و خرید']);
        $this->assertEquals('رضایت از خدمات و خرید', $subgroup->fresh()->name);

        // 5. Delete subgroup
        $groupService->delete($subgroup);
        $this->assertDatabaseMissing('sms_message_groups', ['id' => $subgroup->id]);
    }

    // ==========================================
    // 5. CONTRACT & CONSENT DOCUMENTS WIRING
    // ==========================================

    public function test_contract_wiring_otp_global_and_business_advertising(): void
    {
        $contractService = app(SmsContractService::class);
        $this->actingAs($this->adminUser);

        // 1. Create Global OTP Contract
        $otpUsage = $contractService->saveContract(
            'otp',
            [
                'title' => 'تعهدنامه ارسال پیامک خدماتی OTP',
                'contract_number' => 'OTP-CONTRACT-001',
                'contract_date' => '2026-10-07',
                'content' => 'متن رسمی قرارداد سراسری پیامک خدماتی',
            ]
        );

        $this->assertEquals('sms_otp', $otpUsage->location);
        $this->assertNull($otpUsage->usable_type);
        $this->assertNull($otpUsage->usable_id);
        $this->assertEquals('OTP-CONTRACT-001', $otpUsage->contract_number);

        // Read through service
        $activeOtp = $contractService->getOtpContract();
        $this->assertNotNull($activeOtp);
        $this->assertEquals($otpUsage->id, $activeOtp->id);
        $this->assertEquals('تعهدنامه ارسال پیامک خدماتی OTP', $activeOtp->contract->title);

        // 2. Create Per-Business Advertising Contract for businessA
        $advUsageA = $contractService->saveContract(
            'advertising',
            [
                'title' => 'قرارداد تبلیغاتی فروشگاه آلفا',
                'contract_number' => 'ADV-ALFA-100',
                'contract_date' => '2026-10-07',
                'content' => 'تعهدنامه خطوط تبلیغاتی فروشگاه آلفا',
            ],
            $this->businessA
        );

        $this->assertEquals('sms_advertising', $advUsageA->location);
        $this->assertEquals(GhorfeOnlineList::class, $advUsageA->usable_type);
        $this->assertEquals($this->businessA->id, $advUsageA->usable_id);

        // Verify GhorfeOnlineList model HasContractUsages trait wiring
        $this->assertEquals($advUsageA->contract_id, $this->businessA->contractFor('sms_advertising')->id);

        // BusinessB has no contract yet
        $this->assertNull($this->businessB->contractFor('sms_advertising'));

        // 3. Replacing / Updating contract detaches previous
        $advUsageA2 = $contractService->saveContract(
            'advertising',
            [
                'title' => 'قرارداد تبلیغاتی جدید فروشگاه آلفا (نسخه ۲)',
                'contract_number' => 'ADV-ALFA-101',
            ],
            $this->businessA
        );

        $this->assertEquals('ADV-ALFA-101', $advUsageA2->contract_number);
        $this->assertDatabaseMissing('contract_usages', ['id' => $advUsageA->id]); // Old detached
        $this->assertEquals('قرارداد تبلیغاتی جدید فروشگاه آلفا (نسخه ۲)', $this->businessA->contractFor('sms_advertising')->title);

        // 4. Detach Usage
        $contractService->detachUsage($advUsageA2);
        $this->assertNull($this->businessA->contractFor('sms_advertising'));
    }

    // ==========================================
    // 6. ROUTE & CONTROLLER HTTP ENDPOINT INTEGRATION
    // ==========================================

    public function test_http_routes_respond_successfully(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Settings Hub View
        $hubController = app(\App\Http\Controllers\Admin\SmsSettings\SmsSettingsController::class);
        $hubView = $hubController->index(request());
        $this->assertEquals('Admin.sms.settings.index', $hubView->name());
        $this->assertArrayHasKey('stats', $hubView->getData());

        // 2. Panels Views & JSON
        $panelController = app(\App\Http\Controllers\Admin\SmsSettings\SmsPanelController::class);
        $panelView = $panelController->index(request());
        $this->assertEquals('Admin.sms.settings.panels.index', $panelView->name());
        $this->assertArrayHasKey('panels', $panelView->getData());

        $panel = SmsPanel::create([
            'name' => 'RouteTestPanel',
            'type' => 'otp',
            'status' => 'active',
        ]);
        $togglePanelRes = $this->postJson(route('admin.sms.settings.panels.toggle-status', $panel));
        $togglePanelRes->assertOk()->assertJson(['success' => true, 'status' => 'inactive']);

        // 3. Senders Views & JSON
        $senderController = app(\App\Http\Controllers\Admin\SmsSettings\SmsSenderNumberController::class);
        $senderView = $senderController->index(request());
        $this->assertEquals('Admin.sms.settings.senders.index', $senderView->name());
        $this->assertArrayHasKey('senderNumbers', $senderView->getData());

        $sender = SenderNumber::create([
            'number' => '30009988',
            'type' => 'advertising',
            'sms_panel' => 'RouteTestPanel',
            'status' => 'active',
        ]);
        $toggleSenderRes = $this->postJson(route('admin.sms.settings.senders.toggle-status', $sender));
        $toggleSenderRes->assertOk()->assertJson(['success' => true, 'status' => 'inactive']);

        // 4. Templates Views & JSON Preview
        $templateController = app(\App\Http\Controllers\Admin\SmsSettings\SmsMessageTemplateController::class);
        $templateView = $templateController->index(request());
        $this->assertEquals('Admin.sms.settings.templates.index', $templateView->name());
        $this->assertArrayHasKey('templates', $templateView->getData());

        $previewRes = $this->postJson(route('admin.sms.settings.templates.preview'), [
            'content' => 'سلام {نام کاربر}، آزمون موفقیت‌آمیز بود.',
        ]);
        $previewRes->assertOk();
        $this->assertStringContainsString('سلام علی', $previewRes->json('data.preview'));

        // 5. Groups Views
        $groupController = app(\App\Http\Controllers\Admin\SmsSettings\SmsMessageGroupController::class);
        $groupView = $groupController->index();
        $this->assertEquals('Admin.sms.settings.groups.index', $groupView->name());
        $this->assertArrayHasKey('groups', $groupView->getData());

        // 6. Contracts Views
        $contractController = app(\App\Http\Controllers\Admin\SmsSettings\SmsContractController::class);
        $contractView = $contractController->index(request());
        $this->assertEquals('Admin.sms.settings.contracts.index', $contractView->name());
        $this->assertArrayHasKey('folders', $contractView->getData());
    }

    public function test_business_users_cannot_configure_panels_or_raw_senders_and_can_view_unified_contracts_and_legal_tab(): void
    {
        $silverBusinessUser = User::create([
            'name' => 'کاربر',
            'family' => 'نقره‌ای',
            'phone' => '09123334455',
            'level' => 'ghorfeAdmin',
            'active' => true,
        ]);

        DB::table('employees')->insert([
            'user_id' => $silverBusinessUser->id,
            'ghorfe_online_list_id' => $this->businessA->id,
        ]);

        // SuperAdmin creates a shared line in the pool and assigns it to BOTH Business A and Business B (many-to-many)
        $this->actingAs($this->adminUser);
        $sharedSender = SenderNumber::create([
            'number' => '3000777888',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);
        app(SmsSenderNumberService::class)->assignBusinesses($sharedSender, [$this->businessA->id, $this->businessB->id]);

        $this->assertCount(2, $sharedSender->fresh()->businesses);

        // Now act as the Silver-tier business user
        $this->actingAs($silverBusinessUser);

        // 1. Business user CANNOT access SMS Panels (upstream gateways are completely hidden & forbidden)
        $panelController = app(\App\Http\Controllers\Admin\SmsSettings\SmsPanelController::class);
        try {
            $panelController->index(request());
            $this->fail('Business user should not be able to access SmsPanelController::index');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // 2. Business user CANNOT access sender create/store/edit/update/destroy
        $senderController = app(\App\Http\Controllers\Admin\SmsSettings\SmsSenderNumberController::class);
        try {
            $senderController->create();
            $this->fail('Business user should not be able to access SmsSenderNumberController::create');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // 3. Business user CAN view the unified Contracts & Legal tab (read-only / sign-ready)
        $contractController = app(\App\Http\Controllers\Admin\SmsSettings\SmsContractController::class);
        $contractView = $contractController->index(request());
        $this->assertEquals('Admin.sms.settings.contracts.index', $contractView->name());
        $viewData = $contractView->getData();
        $this->assertArrayHasKey('regulatoryRulesText', $viewData);
        $this->assertArrayHasKey('contractText', $viewData);
        $this->assertArrayHasKey('otpPhoneMasked', $viewData);
    }
}

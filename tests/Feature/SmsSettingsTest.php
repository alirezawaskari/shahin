<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractFolder;
use App\Models\ContractUsage;
use App\Models\GhorfeOnlineList;
use App\Models\Setting;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\Sms\DynamicParameterResolver;
use App\Services\SmsSettings\SmsContractService;
use App\Services\SmsSettings\SmsMessageGroupService;
use App\Services\SmsSettings\SmsMessageTemplateService;
use App\Services\SmsSettings\SmsPanelAccess;
use App\Services\SmsSettings\SmsPanelService;
use App\Services\SmsSettings\SmsSenderNumberService;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SmsSettingsTest extends TestCase
{
    use \Tests\Concerns\SmsSqliteSchema;

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
        $this->registerSqliteJsonFunctions();

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
            $table->decimal('otp_balance', 15, 2)->default(0);
            $table->decimal('advertising_balance', 15, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('ghorfe_settings', function (Blueprint $table) {
            $table->id();
            $table->text('value')->nullable();
            $table->string('key')->nullable();
            $table->nullableMorphs('settingable');
            $table->string('number')->nullable();
            $table->string('type')->nullable();
            $table->string('sms_panel')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $table->string('name')->nullable();
            $table->text('content')->nullable();
            $table->string('type')->default('normal');
            $table->string('service_type')->default('normal');
            $table->string('group')->nullable();
            $table->string('otp_category')->nullable();
            $table->integer('status')->default(1);
            $table->softDeletes();
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

        app(SmsMessageGroupService::class)->ensureDefaultGroupsForBusiness(1);

        $this->adminUser = User::create([
            'name' => 'ادمین',
            'family' => 'اصلی',
            'phone' => '09120000000',
            'level' => 'superAdmin',
            'active' => true,
        ]);

        $this->businessA = GhorfeOnlineList::create(['title' => 'فروشگاه آلفا', 'slug' => 'alfa']);
        $this->businessB = GhorfeOnlineList::create(['title' => 'فروشگاه بتا', 'slug' => 'beta']);
        $this->useStoredSettingsShape();
        $this->useStoredTemplateShape();
        $this->useHistoriesTable();
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

        // type and status are stored in the JSON value of the row, not in columns.
        $this->assertDatabaseHas('ghorfe_settings', [
            'id' => $panel->id,
            'key' => 'sms_panel',
            'value->type' => 'otp',
            'value->status' => 'active',
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
        $this->assertDatabaseMissing('ghorfe_settings', ['id' => $panel->id]);
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
        $this->assertDatabaseHas('ghorfe_settings', ['id' => $otpSender->id, 'value->type' => 'otp']);

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
    // 3. TEMPLATE MANAGEMENT & PREVIEW
    // ==========================================

    public function test_template_crud_and_preview(): void
    {
        $templateService = app(SmsMessageTemplateService::class);
        $this->actingAs($this->adminUser);

        // 1. Create Template
        $group = Setting::where('key', 'sms_message_group')->first();
        $template = $templateService->create([
            'name' => 'اعلام فاکتور خرید',
            'title' => 'اعلام فاکتور خرید',
            'content' => 'سلام {نام کاربر} عزیز، فاکتور شما به شماره {شناسه فاکتور} صادر شد.',
            'service_type' => 'normal',
            'status' => 'active',
            'sms_message_group_id' => $group?->id,
        ], $this->adminUser->id);

        $this->assertDatabaseHas('sms_templates', ['id' => $template->id, 'name' => 'اعلام فاکتور خرید']);

        // 2. Update Template content
        $templateService->update($template, [
            'content' => 'سلام {نام کاربر} عزیز، فاکتور {شناسه فاکتور} با مبلغ جدید صادر شد. جهت پرداخت: {لینک پرداخت}',
        ], $this->adminUser->id);

        $this->assertEquals('سلام {نام کاربر} عزیز، فاکتور {شناسه فاکتور} با مبلغ جدید صادر شد. جهت پرداخت: {لینک پرداخت}', $template->fresh()->content);

        // 3. Live Preview with DynamicParameterResolver
        $previewResult = $templateService->preview($template->fresh()->content);
        $this->assertStringContainsString('سلام علی عزیز', $previewResult['preview']);
        $this->assertStringContainsString('lalecard.ir/pf/10482', $previewResult['preview']);
        $this->assertContains('{نام کاربر}', $previewResult['placeholders']);
        $this->assertContains('{شناسه فاکتور}', $previewResult['placeholders']);
        $this->assertContains('{لینک پرداخت}', $previewResult['placeholders']);

        // 4. Compute Diff
        $diff = $templateService->computeDiff('متن قدیم', 'متن جدید');
        $this->assertNotEmpty($diff);
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
        $this->assertDatabaseHas('ghorfe_settings', ['key' => 'sms_message_group']);

        // 2. Create subgroup
        $subgroup = $groupService->create([
            'name' => 'رضایت از خرید',
            'slug' => 'purchase-satisfaction',
            'parent_id' => $parent->id,
        ]);
        $this->assertDatabaseHas('ghorfe_settings', ['key' => 'sms_message_group']);

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
        $this->assertDatabaseMissing('ghorfe_settings', ['id' => $subgroup->id]);
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

        $panel = app(SmsPanelService::class)->create([
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

        $sender = app(SmsSenderNumberService::class)->create([
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
        $sharedSender = app(SmsSenderNumberService::class)->create([
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

    /**
     * The picker library must come after </main> (the Vue-compiled content drops scripts and re-renders
     * the nodes), and the file inputs must be inside it.
     */
    private function assertPickerOutsideContent(string $html): void
    {
        $mainEnd = strpos($html, '</main>');
        $this->assertNotFalse($mainEnd, 'The layout has no <main> content area.');

        $marker = 'window.ContractFilePicker = { ensure';
        $this->assertSame(1, substr_count($html, $marker), 'The picker library must be printed exactly once.');
        $this->assertGreaterThan($mainEnd, strpos($html, $marker), 'The picker library is inside the Vue content.');

        $this->assertLessThan($mainEnd, strpos($html, 'data-file-picker'), 'The picker input is outside the content area.');
    }

    /**
     * Renders the SMS contracts page through the test-only layout (see tests/Fixtures/views/Admin/master.blade.php).
     * Super admin: the staff list with the assign popup. Business user: the business view with the client-files input.
     */
    private function renderContractsPage(bool $asBusinessUser = false): string
    {
        // The page body, its @include and its sections render unchanged; only the admin layout is swapped and
        // its composers are skipped for this test.
        app('view')->getFinder()->prependLocation(base_path('tests/Fixtures/views'));
        app('events')->forget('composing: Admin.master');
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());

        if ($asBusinessUser) {
            // Not signed yet: the flag, a signing line and the legacy status = 1 are all cleared, so the sign-ready box shows.
            DB::table('ghorfe_online_lists')->where('id', $this->businessA->id)->update(['sms_contract_accepted' => false, 'status' => false]);
            $user = User::create(['name' => 'کاربر', 'family' => 'کسب‌وکار', 'phone' => '09123330001', 'level' => 'ghorfeAdmin', 'active' => true]);
            DB::table('employees')->insert(['user_id' => $user->id, 'ghorfe_online_list_id' => $this->businessA->id]);
            $this->actingAs($user);
            // The sidebar menu reads category_permissions, which this fixture does not build: an empty cached menu
            // (MenuService's key for this user at menu version 0) is what the layout would get anyway.
            \Illuminate\Support\Facades\Cache::put('menu_user_' . $user->id . '_0', [], 60);
        } else {
            $this->actingAs($this->adminUser);
            \Illuminate\Support\Facades\Cache::put('menu_super_admin_0', [], 60);
        }

        return app(\App\Http\Controllers\Admin\SmsSettings\SmsContractController::class)
            ->index(request())
            ->render();
    }

    public function test_contract_page_says_contract_template_not_bare_template(): void
    {
        $html = $this->renderContractsPage();

        // «الگو» on the contract page means a contract template, so it must not collide with SMS templates.
        $this->assertStringNotContainsString('الگوهای پیامک', $html);
        $this->assertStringNotContainsString('الگوی پیش‌فرض پنل OTP', $html);
        $this->assertStringContainsString('الگوهای قرارداد', $html);
        $this->assertStringContainsString('بانک قراردادها و الگوهای قرارداد', $html);
        $this->assertStringContainsString('تنظیم الگوی قرارداد', $html);
    }

    public function test_contract_page_wires_the_shared_file_picker_on_both_upload_inputs(): void
    {
        // Business view: the client-files input (business user, not yet signed).
        $business = $this->renderContractsPage(true);
        $this->assertStringContainsString('name="client_attachments[]" id="clientIndexAttachmentsInput" class="form-control form-control-sm" multiple data-file-picker data-notice-id="clientIndexFileNotice"', $business);
        $this->assertStringContainsString('data-upload-max-bytes="', $business);
        $this->assertStringContainsString('data-post-max-bytes="', $business);
        $this->assertStringContainsString('data-max-files="5"', $business);
        $this->assertStringNotContainsString('clientIndexFileInput.files.length > 5', $business);
        $this->assertPickerOutsideContent($business);

        // Super admin: the staff files input of the assign popup.
        $admin = $this->renderContractsPage();
        $this->assertStringContainsString('name="staff_attachments[]" id="staffAttachmentsInput"', $admin);
        $this->assertStringContainsString('data-file-picker data-notice-id="staffFileCountNotice"', $admin);
        $this->assertStringNotContainsString('staffFileInput.files.length > 5', $admin);
        $this->assertPickerOutsideContent($admin);
    }

    public function test_business_contract_page_says_contract_template_not_bare_template(): void
    {
        $html = $this->renderContractsPage(true);

        $this->assertStringNotContainsString('الگوهای پیامک', $html);
        $this->assertStringNotContainsString('الگوی پیش‌فرض پنل OTP', $html);
    }

    // ==========================================
    // BUSINESS PANEL SWITCH (super admin only)
    // ==========================================

    private function panelSwitchUrl(string $action, string $type, ?GhorfeOnlineList $business = null): string
    {
        return route('admin.sms.contract.panels.' . $action, ['business' => ($business ?? $this->businessA)->id, 'type' => $type]);
    }

    public function test_only_the_super_admin_can_disable_or_enable_a_business_panel(): void
    {
        $businessUser = User::create([
            'name' => 'کاربر', 'family' => 'کسب‌وکار', 'phone' => '09123330002', 'level' => 'ghorfeAdmin', 'active' => true,
        ]);
        DB::table('employees')->insert(['user_id' => $businessUser->id, 'ghorfe_online_list_id' => $this->businessA->id]);
        $this->actingAs($businessUser);

        $this->post($this->panelSwitchUrl('disable', 'advertising'), ['reason' => 'تست'])->assertForbidden();
        $this->post($this->panelSwitchUrl('enable', 'advertising'))->assertForbidden();

        $this->assertFalse(app(SmsPanelAccess::class)->isDisabled($this->businessA, 'advertising'));
    }

    public function test_disabling_needs_a_reason_and_enabling_restores_the_panel(): void
    {
        $this->actingAs($this->adminUser);
        $service = app(SmsPanelAccess::class);

        $this->post($this->panelSwitchUrl('disable', 'advertising'), ['reason' => ''])->assertSessionHasErrors('reason');
        $this->assertFalse($service->isDisabled($this->businessA, 'advertising'));

        $this->post($this->panelSwitchUrl('disable', 'advertising'), ['reason' => 'مغایرت با قرارداد'])->assertRedirect();
        $state = $service->state($this->businessA, 'advertising');
        $this->assertTrue($state['disabled']);
        $this->assertSame('مغایرت با قرارداد', $state['reason']);
        $this->assertSame($this->adminUser->id, $state['by_id']);
        $this->assertNotNull($state['at']);
        $this->assertFalse($service->isDisabled($this->businessA, 'otp'));

        $this->post($this->panelSwitchUrl('enable', 'advertising'))->assertRedirect();
        $this->assertFalse($service->isDisabled($this->businessA, 'advertising'));
        $this->assertNull($service->state($this->businessA, 'advertising')['reason']);
    }

    public function test_unknown_panel_type_is_not_found(): void
    {
        $this->actingAs($this->adminUser);

        $this->post('/dashboard/sms/contract/panels/' . $this->businessA->id . '/sms/disable', ['reason' => 'تست'])->assertNotFound();
    }

    public function test_staff_see_the_disabled_badge_and_the_business_sees_the_notice(): void
    {
        app(SmsPanelAccess::class)->disable($this->businessA, 'advertising', 'تست', $this->adminUser);

        $staff = $this->renderContractsPage();
        $this->assertStringContainsString('پنل غیرفعال', $staff);
        $this->assertStringContainsString('فعال‌سازی پنل', $staff);

        $business = $this->renderContractsPage(true);
        $this->assertStringContainsString('پنل شما توسط مدیریت غیرفعال شده است.', $business);
        $this->assertStringContainsString('(تبلیغاتی)', $business);
    }

    public function test_settings_overview_says_contract_template_not_bare_template(): void
    {
        $this->actingAs($this->adminUser);
        \Illuminate\Support\Facades\Cache::put('menu_super_admin_0', [], 60);
        app('view')->getFinder()->prependLocation(base_path('tests/Fixtures/views'));
        app('events')->forget('composing: Admin.master');
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());

        $html = app(\App\Http\Controllers\Admin\SmsSettings\SmsSettingsController::class)
            ->index(request())
            ->render();

        // The contracts card says «الگوی قرارداد», never the bare «الگو» that collides with SMS templates.
        $this->assertStringContainsString('مدیریت الگوی قرارداد و انتساب قراردادها', $html);
        $this->assertStringNotContainsString('مدیریت الگوها و انتساب قراردادها', $html);
    }
}

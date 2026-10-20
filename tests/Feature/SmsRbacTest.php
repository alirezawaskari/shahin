<?php

namespace Tests\Feature;

use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\User;
use App\Services\Sms\BulkSmsSendService;
use App\Services\Sms\SmsAccessTierService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Role/tier lock-downs of the SMS module: the view-phone-numbers permission (Direct SMS button,
 * preview samples) and the Titanium-only OTP area.
 */
class SmsRbacTest extends TestCase
{
    use \Tests\Concerns\SeedsSmsBalances;

    protected GhorfeOnlineList $business;
    protected Setting $advSender;
    protected User $superAdmin;
    protected User $silverUser;
    protected User $goldUser;
    protected User $diamondUser;
    protected User $titaniumUser;
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
            $table->string('work_title')->nullable();
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

        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->unsignedInteger('cat_id')->nullable();
            $table->boolean('isMenu')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // Tables read by the admin layout (sidebar menu, terms banner, navbar city picker).
        Schema::create('category_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
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
            $table->string('key', 200)->unique();
            $table->text('value');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->unsignedInteger('province_id')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('permission_user', function (Blueprint $table) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('user_id');
            $table->string('user_type');
            $table->primary(['user_id', 'permission_id', 'user_type']);
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

        $migration = require base_path('database/migrations/2026_10_07_000001_add_sms_module_columns_to_existing_tables.php');
        $migration->up();

        // Test business & advertising number
        $this->business = GhorfeOnlineList::create(['title' => 'فروشگاه شایان']);

        $this->advSender = Setting::create([
            'key' => 'sms_sender_number',
            'settingable_type' => GhorfeOnlineList::class,
            'settingable_id' => $this->business->id,
            'number' => '50009999',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

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
    }

    protected function grantPhoneNumbers(User $user): void
    {
        $permission = Permission::firstOrCreate(['name' => SmsAccessTierService::PERMISSION_VIEW_PHONE_NUMBERS]);
        $user->permissions()->attach($permission->id);
        $user->flushCache();
    }

    protected function seedBusinessRecipients(User $actor): void
    {
        DB::table('employees')->insert([
            'user_id' => $actor->id,
            'ghorfe_online_list_id' => $this->business->id,
        ]);

        $this->seedAdvertisingBalance($this->business->id, 100000);

        User::create(['name' => 'کاربر ۱', 'phone' => '09129990001', 'level' => 'user'])
            ->ghorfeOnlineLists()->attach($this->business->id);
        User::create(['name' => 'کاربر ۲', 'phone' => '09129990002', 'level' => 'user'])
            ->ghorfeOnlineLists()->attach($this->business->id);
    }

    protected function previewPayload(): array
    {
        return [
            'recipient_type' => 'normal',
            'message' => 'پیامک تست برای پیش‌نمایش',
            'sender_number_id' => $this->advSender->id,
            'filters' => ['business' => $this->business->id],
        ];
    }

    public function test_phone_number_gate_is_permission_or_super_admin(): void
    {
        $this->assertFalse($this->tierService->canViewPhoneNumbers($this->titaniumUser));
        $this->assertTrue($this->tierService->canViewPhoneNumbers($this->superAdmin));

        $this->grantPhoneNumbers($this->silverUser);
        $this->assertTrue($this->tierService->canViewPhoneNumbers($this->silverUser));
        $this->assertFalse($this->tierService->canViewPhoneNumbers($this->goldUser));
    }

    public function test_direct_sms_button_is_hidden_without_permission(): void
    {
        $this->actingAs($this->goldUser);
        $this->assertStringNotContainsString(
            'open_filter_sms',
            view('Admin.callcenter.partials.sms-to-filter-button')->render()
        );

        $this->actingAs($this->superAdmin);
        $this->assertStringContainsString(
            'open_filter_sms',
            view('Admin.callcenter.partials.sms-to-filter-button')->render()
        );

        $this->grantPhoneNumbers($this->goldUser);
        $this->actingAs($this->goldUser);
        $this->assertStringContainsString(
            'open_filter_sms',
            view('Admin.callcenter.partials.sms-to-filter-button')->render()
        );
    }

    public function test_direct_sms_endpoints_reject_users_without_permission(): void
    {
        $this->actingAs($this->goldUser);
        $payload = $this->previewPayload();
        $payload['filters'] = ['request_filter' => ['__source' => 'requests', '__direct' => '1']];

        $this->postJson(route('admin.sms.bulk.preview'), $payload)->assertStatus(403);
        $this->postJson(route('admin.sms.bulk.send'), $payload)->assertStatus(403);
    }

    public function test_preview_returns_counts_only_without_permission(): void
    {
        BulkSmsSendService::setGlobalGatewayDispatcher(function () {});
        $this->actingAs($this->titaniumUser);
        $this->seedBusinessRecipients($this->titaniumUser);

        $response = $this->postJson(route('admin.sms.bulk.preview'), $this->previewPayload());

        $response->assertStatus(200);
        $response->assertJsonPath('recipient_summary.valid_count', 2);
        $response->assertJsonPath('recipient_summary.invalid_count', 0);
        $this->assertArrayNotHasKey('valid_sample', $response->json('recipient_summary'));
        $this->assertArrayNotHasKey('invalid_sample', $response->json('recipient_summary'));
        $this->assertStringNotContainsString('0912999', $response->getContent());
    }

    public function test_preview_includes_samples_with_permission(): void
    {
        BulkSmsSendService::setGlobalGatewayDispatcher(function () {});
        $this->grantPhoneNumbers($this->titaniumUser);
        $this->actingAs($this->titaniumUser);
        $this->seedBusinessRecipients($this->titaniumUser);

        $response = $this->postJson(route('admin.sms.bulk.preview'), $this->previewPayload());

        $response->assertStatus(200);
        $response->assertJsonPath('recipient_summary.valid_count', 2);
        $this->assertCount(2, $response->json('recipient_summary.valid_sample'));
        $this->assertContains('09129990001', array_column($response->json('recipient_summary.valid_sample'), 'phone'));
    }

    public function test_lower_tiers_cannot_use_business_filters(): void
    {
        $this->actingAs($this->goldUser);

        $otherBusiness = GhorfeOnlineList::create(['title' => 'کسب‌وکار دیگر']);
        DB::table('employees')->insert([
            'user_id' => $this->goldUser->id,
            'ghorfe_online_list_id' => $this->business->id,
        ]);

        foreach ([
            ['business' => $otherBusiness->id],
            ['business' => 'all'],
        ] as $filters) {
            $payload = $this->previewPayload();
            $payload['filters'] = $filters;

            $this->postJson(route('admin.sms.bulk.preview'), $payload)
                ->assertStatus(403)
                ->assertJsonPath('error_type', 'tier_denied')
                ->assertJsonPath('required_tier', 'titanium');
        }
    }

    public function test_otp_routes_are_denied_for_lower_tiers(): void
    {
        foreach ([$this->silverUser, $this->goldUser, $this->diamondUser] as $user) {
            $this->actingAs($user);

            $this->get(route('admin.sms.financial.otp.index', ['business_id' => $this->business->id]))->assertForbidden();
            $this->post(route('admin.sms.financial.otp.topup'), ['amount' => 50000, 'business_id' => $this->business->id])->assertForbidden();
            $this->get(route('admin.sms.reports.otp.index', ['business_id' => $this->business->id]))->assertForbidden();
            $this->get(route('admin.sms.reports.otp.export', ['business_id' => $this->business->id]))->assertForbidden();
            $this->postJson(route('admin.sms.financial.check'), ['type' => 'otp', 'business_id' => $this->business->id])->assertForbidden();
        }
    }

    public function test_otp_single_send_and_preview_are_denied_for_lower_tiers(): void
    {
        Setting::create(['key' => 'sms_sender_number', 'number' => '90001111', 'type' => 'otp', 'sms_panel' => 'IranSms', 'status' => 'active']);
        $this->seedOtpBalance($this->business->id, 20000.00);

        $this->actingAs($this->diamondUser);

        $this->postJson(route('admin.sms.single.send'), [
            'recipient_phone' => '09123334466',
            'message' => 'کد تایید ورود: 445566',
            'service_type' => 'otp',
        ])->assertForbidden();

        $this->postJson(route('admin.sms.single.preview'), [
            'message' => 'کد تایید',
            'service_type' => 'otp',
        ])->assertForbidden();

        $this->assertDatabaseCount('groupsms', 0);
    }

    public function test_otp_sender_resolution_refuses_lower_tier_actor(): void
    {
        Setting::create(['key' => 'sms_sender_number', 'number' => '90001111', 'type' => 'otp', 'sms_panel' => 'IranSms', 'status' => 'active']);
        $service = app(\App\Services\Sms\SingleSmsSendService::class);

        $this->expectException(\DomainException::class);
        $service->resolveSenderNumber('otp', $this->business, null, $this->goldUser);
    }

    public function test_otp_routes_stay_open_for_titanium_and_super_admin(): void
    {
        // A non-super admin needs a business membership, otherwise the request has no active business (403).
        DB::table('employees')->insert([
            'user_id' => $this->titaniumUser->id,
            'ghorfe_online_list_id' => $this->business->id,
            'work_title' => 'test',
        ]);
        // The page layout prints auth()->user()->employee->work_title.
        DB::table('employees')->insert(['user_id' => $this->superAdmin->id, 'work_title' => 'test']);

        foreach ([$this->titaniumUser, $this->superAdmin] as $user) {
            $this->actingAs($user);
            $this->assertTrue($this->tierService->canUseOtp($user));
            $this->get(route('admin.sms.financial.otp.index', ['business_id' => $this->business->id]))->assertOk();
        }
    }

    protected function seedLogMessage(string $serviceType): Groupsms
    {
        return Groupsms::create([
            'ghorfe_online_list_id' => $this->business->id,
            'direction' => 'outbound',
            'service_type' => $serviceType,
            'recipient_phone' => '09121234567',
            'content' => 'test ' . $serviceType,
            'status' => 'delivered',
        ]);
    }

    public function test_lower_tiers_never_see_otp_rows_in_logs_even_when_filtering_for_them(): void
    {
        $this->seedLogMessage('otp');
        $this->seedLogMessage('normal');
        $service = app(\App\Services\Sms\SmsLogService::class);

        $this->assertCount(0, $service->buildQuery(['service_type' => 'otp'], $this->business, false, false)->get());
        $visible = $service->buildQuery([], $this->business, false, false)->get();
        $this->assertCount(1, $visible);
        $this->assertSame('normal', $visible->first()->service_type);
        $this->assertSame(1, $service->getSummaryMetrics([], $this->business, false, false)['total']);
    }

    public function test_titanium_and_super_admin_still_see_otp_rows_in_logs(): void
    {
        $this->seedLogMessage('otp');
        $this->seedLogMessage('normal');
        $service = app(\App\Services\Sms\SmsLogService::class);

        $this->assertCount(2, $service->buildQuery([], $this->business, false, true)->get());
        $this->assertCount(1, $service->buildQuery(['service_type' => 'otp'], $this->business, false, true)->get());
        $this->assertSame(2, $service->getSummaryMetrics([], $this->business, false, true)['total']);
    }

    public function test_log_detail_hides_otp_rows_from_lower_tiers(): void
    {
        $otp = $this->seedLogMessage('otp');

        $this->actingAs($this->goldUser);
        $this->get(route('admin.sms.logs.show', $otp))->assertNotFound();
    }
}

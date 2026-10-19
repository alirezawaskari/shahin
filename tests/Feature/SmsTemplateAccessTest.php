<?php

namespace Tests\Feature;

use App\Models\GhorfeOnlineList;
use App\Models\Permission;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\SmsMessageTemplate;
use App\Models\User;
use App\Services\Sms\SmsAccessTierService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * OTP message templates are staff-only (Titanium/superadmin); advertising templates stay open to business users.
 */
class SmsTemplateAccessTest extends TestCase
{
    use \Tests\Concerns\SeedsSmsBalances;

    protected GhorfeOnlineList $business;
    protected SenderNumber $advSender;
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

        // The admin layout renders the sidebar menu, which joins permissions to category_permissions.
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
            $table->id();
            $table->string('key')->nullable();
            $table->text('value')->nullable();
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
            'database/migrations/2026_10_18_000001_create_sms_audit_logs_and_sender_role.php',
            'database/migrations/2026_10_20_000001_add_otp_category_to_sms_templates_and_messages.php',
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

        // The admin layout navbar prints auth()->user()->employee->work_title.
        foreach (User::all() as $u) {
            \Illuminate\Support\Facades\DB::table('employees')->insert(['user_id' => $u->id, 'work_title' => 'test']);
        }

        $this->tierService = app(SmsAccessTierService::class);
    }

    protected function payload(string $serviceType, array $extra = []): array
    {
        return array_merge([
            'title' => 'الگو ' . $serviceType,
            'content' => 'متن الگو',
            'service_type' => $serviceType,
            'status' => 'active',
            'business_id' => $this->business->id,
        ], $extra);
    }

    protected function makeTemplate(string $serviceType, string $title = 'الگو'): SmsMessageTemplate
    {
        return SmsMessageTemplate::create([
            'title' => $title,
            'content' => 'متن ' . $title,
            'service_type' => $serviceType,
            'status' => 'active',
            'ghorfe_online_list_id' => $this->business->id,
            'created_by' => $this->titaniumUser->id,
            'updated_by' => $this->titaniumUser->id,
        ]);
    }

    public function test_non_staff_cannot_create_otp_template_but_can_create_advertising(): void
    {
        foreach ([$this->silverUser, $this->goldUser, $this->diamondUser] as $user) {
            $this->actingAs($user);

            $this->post(route('admin.sms.settings.templates.store'), $this->payload('otp'))->assertSessionHasErrors('service_type');
            $this->assertSame(0, SmsMessageTemplate::where('service_type', 'otp')->count());
        }

        $this->actingAs($this->silverUser);
        $this->post(route('admin.sms.settings.templates.store'), $this->payload('advertising'))->assertSessionHasNoErrors();
        $this->assertSame(1, SmsMessageTemplate::where('service_type', 'advertising')->count());
    }

    public function test_staff_can_create_edit_and_delete_otp_template(): void
    {
        foreach ([$this->superAdmin, $this->titaniumUser] as $staff) {
            $this->actingAs($staff);

            $this->post(route('admin.sms.settings.templates.store'), $this->payload('otp', ['title' => 'کد ' . $staff->id, 'otp_category' => 'sales']))
                ->assertSessionHasNoErrors();
            $template = SmsMessageTemplate::where('title', 'کد ' . $staff->id)->firstOrFail();
            $this->assertSame('otp', $template->service_type);

            $this->put(route('admin.sms.settings.templates.update', $template), $this->payload('otp', ['title' => 'ویرایش ' . $staff->id, 'otp_category' => 'finance']))
                ->assertSessionHasNoErrors();
            $this->assertSame('ویرایش ' . $staff->id, $template->fresh()->title);

            $this->delete(route('admin.sms.settings.templates.destroy', $template), ['business_id' => $this->business->id]);
            $this->assertNull(SmsMessageTemplate::find($template->id));
        }
    }

    public function test_non_staff_cannot_edit_delete_or_convert_an_otp_template(): void
    {
        $otp = $this->makeTemplate('otp', 'قالب کد');

        foreach ([$this->silverUser, $this->goldUser, $this->diamondUser] as $user) {
            $this->actingAs($user);

            $this->get(route('admin.sms.settings.templates.edit', [$otp, 'business_id' => $this->business->id]))->assertForbidden();
            $this->put(route('admin.sms.settings.templates.update', $otp), $this->payload('advertising'))->assertForbidden();
            $this->delete(route('admin.sms.settings.templates.destroy', $otp), ['business_id' => $this->business->id])->assertForbidden();
            $this->post(route('admin.sms.settings.templates.toggle-status', $otp), ['business_id' => $this->business->id])->assertForbidden();
        }

        $fresh = $otp->fresh();
        $this->assertNotNull($fresh);
        $this->assertSame('otp', $fresh->service_type);
        $this->assertSame('قالب کد', $fresh->title);
    }

    public function test_non_staff_cannot_turn_an_advertising_template_into_otp(): void
    {
        $adv = $this->makeTemplate('advertising', 'تبلیغ');
        $this->actingAs($this->silverUser);

        $this->put(route('admin.sms.settings.templates.update', $adv), $this->payload('otp'))->assertSessionHasErrors('service_type');
        $this->assertSame('advertising', $adv->fresh()->service_type);
    }

    public function test_non_staff_can_edit_and_delete_advertising_templates(): void
    {
        $adv = $this->makeTemplate('advertising', 'تبلیغ');
        $this->actingAs($this->goldUser);

        $this->put(route('admin.sms.settings.templates.update', $adv), $this->payload('advertising', ['title' => 'تبلیغ ویرایش']))
            ->assertSessionHasNoErrors();
        $this->assertSame('تبلیغ ویرایش', $adv->fresh()->title);

        $this->delete(route('admin.sms.settings.templates.destroy', $adv), ['business_id' => $this->business->id]);
        $this->assertNull(SmsMessageTemplate::find($adv->id));
    }

    public function test_otp_templates_are_hidden_from_non_staff_lists_and_visible_to_staff(): void
    {
        $this->makeTemplate('otp', 'قالب مخفی کد');
        $this->makeTemplate('advertising', 'قالب تبلیغ');

        $this->actingAs($this->diamondUser);
        $response = $this->get(route('admin.sms.settings.templates.index', ['business_id' => $this->business->id]));
        $titles = $response->viewData('templates')->pluck('title')->all();
        $this->assertContains('قالب تبلیغ', $titles);
        $this->assertNotContains('قالب مخفی کد', $titles);

        // Filtering by the OTP type must not leak them either.
        $filtered = $this->get(route('admin.sms.settings.templates.index', ['business_id' => $this->business->id, 'service_type' => 'otp']));
        $this->assertCount(0, $filtered->viewData('templates'));

        $this->actingAs($this->titaniumUser);
        $staffTitles = $this->get(route('admin.sms.settings.templates.index', ['business_id' => $this->business->id]))
            ->viewData('templates')->pluck('title')->all();
        $this->assertContains('قالب مخفی کد', $staffTitles);
    }

    public function test_template_form_offers_otp_type_only_to_staff(): void
    {
        $this->actingAs($this->silverUser);
        $response = $this->get(route('admin.sms.settings.templates.create', ['business_id' => $this->business->id]));
        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringNotContainsString('value="otp"', $html);
        $this->assertStringContainsString('value="advertising"', $html);

        $this->actingAs($this->titaniumUser);
        $html = $this->get(route('admin.sms.settings.templates.create', ['business_id' => $this->business->id]))->getContent();
        $this->assertStringContainsString('value="otp"', $html);
    }

    public function test_single_send_template_picker_offers_otp_templates_only_to_otp_users(): void
    {
        $this->makeTemplate('otp', 'قالب کد یکبار مصرف');
        $this->makeTemplate('advertising', 'قالب تبلیغ');

        $this->actingAs($this->goldUser);
        $titles = $this->get(route('admin.sms.single.index', ['business_id' => $this->business->id]))->viewData('templates')->pluck('title')->all();
        $this->assertNotContains('قالب کد یکبار مصرف', $titles);
        $this->assertContains('قالب تبلیغ', $titles);

        $this->actingAs($this->titaniumUser);
        $titles = $this->get(route('admin.sms.single.index', ['business_id' => $this->business->id]))->viewData('templates')->pluck('title')->all();
        $this->assertContains('قالب کد یکبار مصرف', $titles);
    }
}

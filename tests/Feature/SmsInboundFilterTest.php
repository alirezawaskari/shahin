<?php

namespace Tests\Feature;

use App\Models\GhorfeOnlineList;
use App\Models\Permission;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\SmsMessageTemplate;
use App\Models\User;
use App\Services\Sms\SmsAccessTierService;
use App\Services\Sms\SmsLogService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Direction (incoming/outgoing) filter on the SMS logs; inbound OTP stays hidden from lower tiers.
 */
class SmsInboundFilterTest extends TestCase
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

        $this->tierService = app(SmsAccessTierService::class);
    }

    protected function seedMessages(): void
    {
        $base = ['ghorfe_online_list_id' => $this->business->id, 'status' => 'delivered'];

        SmsMessage::create($base + ['direction' => 'outbound', 'service_type' => 'advertising', 'content' => 'خروجی تبلیغاتی', 'recipient_phone' => '09121110001']);
        SmsMessage::create($base + ['direction' => 'outbound', 'service_type' => 'otp', 'content' => 'خروجی کد', 'recipient_phone' => '09121110002']);
        SmsMessage::create($base + ['direction' => 'inbound', 'service_type' => 'normal', 'content' => 'ورودی عادی', 'sender_phone' => '09121110003']);
        SmsMessage::create($base + ['direction' => 'inbound', 'service_type' => 'otp', 'content' => 'ورودی کد', 'sender_phone' => '09121110004']);
    }

    protected function logs(User $viewer, array $filters = [])
    {
        return app(SmsLogService::class)->buildQuery(
            $filters,
            $this->business,
            $viewer->isSuperAdmin(),
            $this->tierService->canUseOtp($viewer)
        )->get();
    }

    public function test_direction_filter_returns_only_incoming_or_only_outgoing(): void
    {
        $this->seedMessages();

        $incoming = $this->logs($this->titaniumUser, ['direction' => 'inbound']);
        $this->assertCount(2, $incoming);
        $this->assertSame(['inbound'], $incoming->pluck('direction')->unique()->values()->all());

        $outgoing = $this->logs($this->titaniumUser, ['direction' => 'outbound']);
        $this->assertCount(2, $outgoing);
        $this->assertSame(['outbound'], $outgoing->pluck('direction')->unique()->values()->all());

        $this->assertCount(4, $this->logs($this->titaniumUser, ['direction' => 'all']));
        $this->assertCount(4, $this->logs($this->titaniumUser));
    }

    public function test_lower_tiers_never_see_inbound_or_outbound_otp(): void
    {
        $this->seedMessages();

        foreach ([$this->silverUser, $this->goldUser, $this->diamondUser] as $viewer) {
            $incoming = $this->logs($viewer, ['direction' => 'inbound']);
            $this->assertCount(1, $incoming);
            $this->assertSame('ورودی عادی', $incoming->first()->content);

            // Explicitly asking for OTP must not leak anything either.
            $this->assertCount(0, $this->logs($viewer, ['direction' => 'inbound', 'service_type' => 'otp']));
            $this->assertCount(2, $this->logs($viewer, ['direction' => 'all']));
        }

        $this->assertCount(1, $this->logs($this->titaniumUser, ['direction' => 'inbound', 'service_type' => 'otp']));
    }

    public function test_summary_counts_incoming_and_outgoing_and_hides_otp_from_lower_tiers(): void
    {
        $this->seedMessages();
        $service = app(SmsLogService::class);

        $full = $service->getSummaryMetrics([], $this->business, false, true);
        $this->assertSame(2, $full['inbound']);
        $this->assertSame(2, $full['outbound']);

        $limited = $service->getSummaryMetrics([], $this->business, false, false);
        $this->assertSame(1, $limited['inbound']);
        $this->assertSame(1, $limited['outbound']);
    }

    public function test_logs_endpoint_applies_direction_filter(): void
    {
        $this->seedMessages();
        $this->actingAs($this->superAdmin);

        $response = $this->getJson(route('admin.sms.logs.index', ['direction' => 'inbound', 'business_id' => $this->business->id]));
        $response->assertStatus(200);
        $this->assertSame(2, $response->json('summary.total'));
        $this->assertSame(2, $response->json('summary.inbound'));
        $this->assertSame(0, $response->json('summary.outbound'));
    }
}

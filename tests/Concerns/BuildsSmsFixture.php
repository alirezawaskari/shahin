<?php

namespace Tests\Concerns;

use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * In-memory SQLite schema + fixtures shared by the OTP-category and report-breadcrumb tests
 * (same world as SmsFinancialAndReportTest: titanium admin in business A, a superadmin, one OTP
 * line and two advertising lines). Call buildSmsFixture() from setUp() after parent::setUp().
 */
trait BuildsSmsFixture
{
    use SeedsSmsBalances;

    protected User $adminUser;
    protected User $superAdminUser;
    protected GhorfeOnlineList $businessA;
    protected GhorfeOnlineList $businessB;
    protected SenderNumber $otpSender;
    protected SenderNumber $advSender1;
    protected SenderNumber $advSender2;

    protected function buildSmsFixture(): void
    {

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

        // Create prerequisites needed by foreign keys
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
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $table->string('work_title')->nullable();
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

        Schema::create('employee_terms', function (Blueprint $table) {
            $table->id();
            $table->string('role_category')->unique();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('contexts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique();
            $table->text('value')->nullable();
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
            $table->string('name')->nullable();
            $table->integer('province_id')->unsigned()->nullable();
            $table->timestamps();
        });

        // Run SMS migrations
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
            'database/migrations/2026_10_21_000001_create_sms_top_up_payments_table.php',
        ];

        foreach ($migrationFiles as $file) {
            $migration = require base_path($file);
            $migration->up();
        }

        // Setup test data
        $this->adminUser = User::create([
            'name' => 'ادمین',
            'family' => 'کسب‌وکار',
            'phone' => '09121111111',
            'level' => 'admin',
            'access_tier' => 'titanium', // OTP credit/reports are Titanium-only
            'active' => true,
        ]);

        $this->superAdminUser = User::create([
            'name' => 'مدیر',
            'family' => 'ارشد کل',
            'phone' => '09120000000',
            'level' => 'superAdmin',
            'active' => true,
        ]);

        $this->businessA = GhorfeOnlineList::create(['title' => 'فروشگاه آلفا', 'slug' => 'alfa']);
        $this->businessB = GhorfeOnlineList::create(['title' => 'فروشگاه بتا', 'slug' => 'beta']);

        \App\Models\Employee::create([
            'user_id' => $this->adminUser->id,
            'ghorfe_online_list_id' => $this->businessA->id,
            'work_title' => 'مدیر فروشگاه',
        ]);

        \App\Models\Employee::create([
            'user_id' => $this->superAdminUser->id,
            'ghorfe_online_list_id' => null,
            'work_title' => 'مدیر ارشد سامانه',
        ]);

        // OTP Sender Number (singleton)
        $this->otpSender = SenderNumber::create([
            'number' => '90001',
            'type' => 'otp',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

        // Advertising Sender Numbers
        $this->advSender1 = SenderNumber::create([
            'number' => '30001111',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

        $this->advSender2 = SenderNumber::create([
            'number' => '30002222',
            'type' => 'advertising',
            'sms_panel' => 'NeginSms',
            'status' => 'active',
        ]);

        // Assign advertising numbers to Business A
        $this->businessA->senderNumbers()->attach([
            $this->advSender1->id => ['assigned_at' => now()],
            $this->advSender2->id => ['assigned_at' => now()],
        ]);
    }
}

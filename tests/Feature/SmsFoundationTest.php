<?php

namespace Tests\Feature;

use App\Models\AdvertisingBalance;
use App\Models\BusinessSenderNumber;
use App\Models\GhorfeOnlineList;
use App\Models\OtpBalance;
use App\Models\SenderNumber;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\SmsMessageTemplateHistory;
use App\Models\SmsTransaction;
use App\Models\User;
use Database\Seeders\SmsMessageGroupSeeder;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmsFoundationTest extends TestCase
{
    use \Tests\Concerns\SeedsSmsBalances;

    protected function setUp(): void
    {
        parent::setUp();

        if (!file_exists(base_path('database/migrations/2026_10_07_000001_create_sender_numbers_table.php'))) {
            $this->markTestSkipped('SmsFoundationTest tests parallel table migrations that were removed when reverting db to authentic schema.');
        }

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

        // Run SMS migrations
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
    }

    public function test_sender_number_otp_singleton_guard_enforced(): void
    {
        // First OTP row succeeds
        $otpNumber = SenderNumber::create([
            'number' => '90001',
            'type' => 'otp',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('sender_numbers', ['id' => $otpNumber->id, 'type' => 'otp']);

        // Second OTP row MUST throw DomainException
        $this->expectException(DomainException::class);
        SenderNumber::create([
            'number' => '90002',
            'type' => 'otp',
            'sms_panel' => 'NeginSms',
            'status' => 'active',
        ]);
    }

    public function test_multiple_advertising_numbers_are_allowed(): void
    {
        $adv1 = SenderNumber::create([
            'number' => '10001',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);
        $adv2 = SenderNumber::create([
            'number' => '10002',
            'type' => 'advertising',
            'sms_panel' => 'NeginSms',
            'status' => 'active',
        ]);

        $this->assertEquals(2, SenderNumber::advertising()->count());
    }

    public function test_business_and_sender_number_many_to_many_relation(): void
    {
        $business = GhorfeOnlineList::create(['title' => 'فروشگاه تست']);
        $sender = SenderNumber::create([
            'number' => '30001',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

        $business->senderNumbers()->attach($sender->id, ['assigned_at' => now()]);

        $this->assertCount(1, $business->fresh()->senderNumbers);
        $this->assertEquals('30001', $business->fresh()->senderNumbers->first()->number);
        $this->assertCount(1, $sender->fresh()->businesses);
    }

    public function test_otp_balances_and_thresholds(): void
    {
        $business = GhorfeOnlineList::create(['title' => 'کسب و کار ۲']);

        $otpBalance = $this->seedOtpBalance($business->id, 50000, 100000);

        $this->assertTrue($otpBalance->isLowBalance());
        $this->assertTrue($otpBalance->hasSufficientBalance(40000));
        $this->assertFalse($otpBalance->hasSufficientBalance(60000));
        $this->assertEquals($business->id, $business->otpBalance->ghorfe_online_list_id);
    }

    public function test_advertising_balances_per_sender_number(): void
    {
        $business = GhorfeOnlineList::create(['title' => 'کسب و کار تبلیغاتی']);
        $sender = SenderNumber::create([
            'number' => '50001',
            'type' => 'advertising',
            'sms_panel' => 'NeginSms',
        ]);

        $advBalance = $this->seedAdvertisingBalance($business->id, 250000);

        $this->assertEquals(250000, (float) $advBalance->balance);
        $this->assertTrue($advBalance->hasSufficientBalance(200000));
        $this->assertEquals($advBalance->id, $business->fresh()->advertisingBalance->id);
    }

    public function test_sms_message_groups_seeder_and_hierarchy(): void
    {
        $seeder = new SmsMessageGroupSeeder();
        $seeder->run();

        // 6 base root groups (incl. monasebati: event/greeting)
        $this->assertEquals(6, SmsMessageGroup::root()->count());
        $this->assertNotNull(SmsMessageGroup::where('slug', 'monasebati')->first());

        $financial = SmsMessageGroup::where('slug', 'mali')->first();
        $this->assertNotNull($financial);
        $this->assertEquals('مالی', $financial->name);

        $subgroups = $financial->subgroups;
        $this->assertCount(2, $subgroups);
        $this->assertTrue($subgroups->pluck('name')->contains('واریز پیش‌پرداخت'));
        $this->assertTrue($subgroups->pluck('name')->contains('پرداخت فاکتور'));

        $sales = SmsMessageGroup::where('slug', 'foroush')->first();
        $this->assertNotNull($sales);
        $this->assertTrue($sales->subgroups->pluck('name')->contains('پیگیری مشتری'));
    }

    public function test_template_creation_and_history_observer(): void
    {
        $user = User::create(['name' => 'ادمین']);

        $template = SmsMessageTemplate::create([
            'title' => 'پیامک خوش‌آمدگویی',
            'content' => 'سلام {نام کاربر} به لاله خوش آمدید',
            'service_type' => 'normal',
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->assertDatabaseHas('sms_message_templates', ['id' => $template->id]);

        // Update content to verify observer snapshot
        $template->update([
            'content' => 'سلام {نام کاربر} {نام خانوادگی} به باشگاه لاله خوش آمدید',
            'updated_by' => $user->id,
        ]);

        $histories = $template->fresh()->histories;
        $this->assertGreaterThanOrEqual(1, $histories->count());
        $this->assertStringContainsString('باشگاه لاله', $histories->last()->content);
    }

    public function test_sms_message_and_transaction_recording(): void
    {
        $user = User::create(['name' => 'مشتری']);
        $business = GhorfeOnlineList::create(['title' => 'فروشگاه ۳']);
        $sender = SenderNumber::create([
            'number' => '100099',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
        ]);

        $message = SmsMessage::create([
            'ghorfe_online_list_id' => $business->id,
            'direction' => 'outbound',
            'service_type' => 'advertising',
            'sender_number_id' => $sender->id,
            'recipient_phone' => '09120000000',
            'sender_phone' => '100099',
            'content' => 'پیامک تبلیغاتی تستی',
            'character_count' => 18,
            'page_count' => 1,
            'status' => 'sent',
            'cost' => 1200.00,
            'tracking_id' => 'TRK-1001',
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('sms_messages', ['id' => $message->id]);

        $transaction = SmsTransaction::create([
            'ghorfe_online_list_id' => $business->id,
            'credit_type' => 'advertising',
            'sender_number_id' => $sender->id,
            'charge_amount' => 0,
            'consume_amount' => 1200.00,
            'balance_before' => 50000.00,
            'balance_after' => 48800.00,
            'sms_message_id' => $message->id,
            'tracking_id' => 'TXN-5001',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('sms_transactions', ['id' => $transaction->id]);
        $this->assertEquals($message->id, $transaction->message->id);
    }

    public function test_sms_campaign_with_recipients(): void
    {
        $user = User::create(['name' => 'بازاریاب']);
        $business = GhorfeOnlineList::create(['title' => 'شرکت کمپین']);

        $campaign = SmsCampaign::create([
            'ghorfe_online_list_id' => $business->id,
            'type' => 'bulk',
            'filters' => ['city' => 'تهران', 'min_purchases' => 3],
            'total_recipients' => 2,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $recipient1 = SmsCampaignRecipient::create([
            'sms_campaign_id' => $campaign->id,
            'phone' => '09121111111',
            'status' => 'pending',
        ]);
        $recipient2 = SmsCampaignRecipient::create([
            'sms_campaign_id' => $campaign->id,
            'phone' => '09122222222',
            'status' => 'pending',
        ]);

        $this->assertCount(2, $campaign->fresh()->recipients);
        $this->assertEquals('تهران', $campaign->fresh()->filters['city']);
    }
}

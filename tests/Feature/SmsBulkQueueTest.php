<?php

namespace Tests\Feature;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\Sms\BulkSmsSendService;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Outgoing queue (pending_queue), due-campaign scheduling, OTP-template rejection and the
 * «کاهش تعداد مخاطبین» cap of the bulk promotional flow.
 */
class SmsBulkQueueTest extends TestCase
{
    use \Tests\Concerns\SmsSqliteSchema;

    use \Tests\Concerns\SeedsSmsBalances;

    protected GhorfeOnlineList $business;
    protected GhorfeOnlineList $otherBusiness;
    protected Setting $advSender;
    protected User $titaniumUser;
    protected BulkSmsSendService $bulkSendService;

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

        $this->applySmsModuleColumns();

        $this->business = GhorfeOnlineList::create(['title' => 'فروشگاه شایان']);
        $this->otherBusiness = GhorfeOnlineList::create(['title' => 'فروشگاه دیگر']);

        $this->advSender = Setting::create([
            'key' => 'sms_sender_number',
            'settingable_type' => GhorfeOnlineList::class,
            'settingable_id' => $this->business->id,
            'number' => '50009999',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

        $this->titaniumUser = User::create([
            'name' => 'کاربر',
            'family' => 'تیتانیوم',
            'phone' => '09120000005',
            'level' => 'admin',
            'access_tier' => 'titanium',
            'active' => 1,
        ]);

        config()->set('services.sms.cost_per_page', 1200);
        BulkSmsSendService::setGlobalGatewayDispatcher(function () {});
        $this->bulkSendService = app(BulkSmsSendService::class);
    }

    protected function tearDown(): void
    {
        BulkSmsSendService::setGlobalGatewayDispatcher(null);
        parent::tearDown();
    }

    protected function makeRecipients(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            User::create(['name' => "کاربر $i", 'phone' => '0912555000' . $i, 'level' => 'user'])
                ->ghorfeOnlineLists()->attach($this->business->id);
        }
    }

    private function actAsBusinessAdmin(): void
    {
        $this->actingAs($this->titaniumUser);
        DB::table('employees')->insert([
            'user_id' => $this->titaniumUser->id,
            'ghorfe_online_list_id' => $this->business->id,
        ]);
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'recipient_type' => 'normal',
            'message' => 'پیامک آزمایشی صف ارسال',
            'sender_number_id' => $this->advSender->id,
            'filters' => ['business' => $this->business->id],
        ], $overrides);
    }

    // ------------------------------------------------------------------
    // Outgoing queue
    // ------------------------------------------------------------------

    public function test_saving_to_queue_has_no_debit_and_no_send(): void
    {
        $this->seedAdvertisingBalance($this->business->id, 50000);
        $this->makeRecipients(3);

        $dispatched = 0;
        BulkSmsSendService::setGlobalGatewayDispatcher(function () use (&$dispatched) {
            $dispatched++;
        });

        $queued = $this->bulkSendService->saveToQueue($this->payload(), $this->titaniumUser, $this->business);

        $this->assertSame(Groupsms::STATUS_PENDING_QUEUE, $queued->status);
        $this->assertSame(3, $queued->total_recipients);
        $this->assertEquals(50000, (float) $this->business->fresh()->advertising_balance);
        $this->assertSame(0, Groupsms::whereNotNull('request_id')->count());
        $this->assertSame(0, Groupsms::whereNotNull('request_id')->count());
        $this->assertSame(0, SmsLog::whereNotNull('consume_amount')->where('consume_amount', '>', 0)->count());
        $this->assertSame(0, $dispatched);

        // Leaving the page twice with the same content must not create a second entry.
        $this->bulkSendService->saveToQueue($this->payload(), $this->titaniumUser, $this->business);
        $this->assertSame(1, Groupsms::pendingQueue()->count());
    }

    public function test_queue_save_endpoint_persists_without_debit(): void
    {
        $this->actAsBusinessAdmin();
        $this->seedAdvertisingBalance($this->business->id, 50000);
        $this->makeRecipients(2);

        $this->postJson(route('admin.sms.bulk.queue.save'), $this->payload())
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertSame(1, Groupsms::pendingQueue()->where('ghorfe_online_list_id', $this->business->id)->count());
        $this->assertEquals(50000, (float) $this->business->fresh()->advertising_balance);
    }

    public function test_queue_page_and_actions_are_scoped_to_the_business(): void
    {
        $this->actAsBusinessAdmin();
        $this->seedAdvertisingBalance($this->business->id, 50000);
        $this->makeRecipients(2);

        $mine = $this->bulkSendService->saveToQueue($this->payload(), $this->titaniumUser, $this->business);

        $foreign = Groupsms::create([
            'ghorfe_online_list_id' => $this->otherBusiness->id,
            'type' => 'bulk',
            'filters' => ['business' => $this->otherBusiness->id, 'message' => 'x', 'message_preview' => 'x', 'recipient_type' => 'normal'],
            'status' => Groupsms::STATUS_PENDING_QUEUE,
            'created_by' => $this->titaniumUser->id,
        ]);

        // The queue is a tab of the bulk page: queue() only redirects to it.
        $redirect = app(\App\Http\Controllers\Admin\SmsBulkSendController::class)->queue(request());
        $this->assertStringContainsString('tab=queue', $redirect->getTargetUrl());

        $view = app(\App\Http\Controllers\Admin\SmsBulkSendController::class)->index(request()->merge(['tab' => 'queue']));
        $this->assertSame('Admin.sms.bulk.index', $view->name());
        $ids = $view->getData()['queuedList']->pluck('id')->all();
        $this->assertSame([$mine->id], $ids);

        // Another business' entry can be neither sent, resumed nor deleted from here.
        $this->post(route('admin.sms.bulk.queue.send', $foreign))->assertNotFound();
        $this->delete(route('admin.sms.bulk.queue.destroy', $foreign))->assertNotFound();
        $this->get(route('admin.sms.bulk.index', ['queue' => $foreign->id]))->assertNotFound();
        $this->assertNotNull(Groupsms::find($foreign->id));

        // Own entry: send now goes through the normal path (debit + campaign), then leaves the queue.
        $this->post(route('admin.sms.bulk.queue.send', $mine))->assertRedirect();
        $this->assertNull(Groupsms::find($mine->id));
        $this->assertSame(1, Groupsms::campaigns()->where('status', 'completed')->count());
        $this->assertEquals(50000 - 2 * 1200, (float) $this->business->fresh()->advertising_balance);

        // Delete removes only the queue entry.
        $again = $this->bulkSendService->saveToQueue($this->payload(['message' => 'متن دیگر']), $this->titaniumUser, $this->business);
        $this->delete(route('admin.sms.bulk.queue.destroy', $again))->assertRedirect();
        $this->assertNull(Groupsms::find($again->id));
    }

    public function test_send_now_from_queue_still_enforces_balance_guard(): void
    {
        $this->seedAdvertisingBalance($this->business->id, 1000);
        $this->makeRecipients(3);

        $queued = $this->bulkSendService->saveToQueue($this->payload(), $this->titaniumUser, $this->business);

        try {
            $this->bulkSendService->sendQueuedCampaign($queued, $this->titaniumUser, $this->business);
            $this->fail('Expected InsufficientBalanceException was not thrown.');
        } catch (InsufficientBalanceException) {
            // expected
        }

        // The entry stays queued and nothing was debited.
        $this->assertSame(Groupsms::STATUS_PENDING_QUEUE, $queued->fresh()->status);
        $this->assertEquals(1000, (float) $this->business->fresh()->advertising_balance);
    }

    // ------------------------------------------------------------------
    // Scheduled campaigns under the sync queue
    // ------------------------------------------------------------------

    public function test_due_campaign_command_sends_once_and_is_idempotent(): void
    {
        $this->seedAdvertisingBalance($this->business->id, 50000);
        $this->makeRecipients(2);

        $dispatched = 0;
        BulkSmsSendService::setGlobalGatewayDispatcher(function () use (&$dispatched) {
            $dispatched++;
        });

        $campaign = $this->bulkSendService->createCampaign(
            $this->payload(['scheduled_at' => now()->addDay()->toDateTimeString()]),
            $this->titaniumUser,
            $this->business
        );
        $this->assertSame('scheduled', $campaign->fresh()->status);

        // Not due yet: the command leaves it alone.
        Artisan::call('sms:dispatch-due-campaigns');
        $this->assertSame('scheduled', $campaign->fresh()->status);
        $this->assertSame(0, $dispatched);

        $campaign->update(['scheduled_at' => now()->subMinute()]);

        Artisan::call('sms:dispatch-due-campaigns');
        Artisan::call('sms:dispatch-due-campaigns');

        $campaign->refresh();
        $this->assertSame('completed', $campaign->status);
        $this->assertSame(2, $dispatched);
        $this->assertSame(2, Groupsms::where('request_id', $campaign->id)->count());
        $this->assertEquals(50000 - 2 * 1200, (float) $this->business->fresh()->advertising_balance);
    }

    public function test_queued_campaign_is_never_executed_by_the_runner(): void
    {
        $this->seedAdvertisingBalance($this->business->id, 50000);
        $this->makeRecipients(2);

        $queued = $this->bulkSendService->saveToQueue($this->payload(), $this->titaniumUser, $this->business);
        $this->bulkSendService->executeCampaign($queued);

        $this->assertSame(Groupsms::STATUS_PENDING_QUEUE, $queued->fresh()->status);
        $this->assertSame(0, Groupsms::whereNotNull('request_id')->count());
    }

    // ------------------------------------------------------------------
    // Templates: promotional only
    // ------------------------------------------------------------------

    public function test_otp_template_is_rejected_on_bulk_send(): void
    {
        $this->seedAdvertisingBalance($this->business->id, 50000);
        $this->makeRecipients(2);

        $otp = SmsTemplate::create([
            'ghorfe_online_list_id' => $this->business->id,
            'name' => 'کد تایید',
            'content' => 'کد شما {کد}',
            'service_type' => 'otp',
            'status' => 1,
            'user_id' => $this->titaniumUser->id,
        ]);

        // Service level
        try {
            $this->bulkSendService->createCampaign(
                $this->payload(['sms_message_template_id' => $otp->id]),
                $this->titaniumUser,
                $this->business
            );
            $this->fail('Expected DomainException was not thrown.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('OTP', $e->getMessage());
        }
        $this->assertSame(0, Groupsms::campaigns()->count());

        // Request level
        $this->actAsBusinessAdmin();
        $this->postJson(route('admin.sms.bulk.send'), $this->payload(['sms_message_template_id' => $otp->id]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('sms_message_template_id');
        $this->postJson(route('admin.sms.bulk.preview'), $this->payload(['sms_message_template_id' => $otp->id]))
            ->assertStatus(422);
        $this->assertSame(0, Groupsms::campaigns()->count());
    }

    public function test_bulk_template_selector_lists_only_advertising_templates(): void
    {
        $make = fn (string $title, string $type) => SmsTemplate::create([
            'ghorfe_online_list_id' => $this->business->id,
            'name' => $title,
            'content' => 'متن',
            'service_type' => $type,
            'status' => 1,
            'user_id' => $this->titaniumUser->id,
        ]);
        $make('کد', 'otp');
        $make('پشتیبانی', 'normal');
        $make('جشنواره', 'advertising');

        $this->actingAs($this->titaniumUser);
        $view = app(\App\Http\Controllers\Admin\SmsBulkSendController::class)->index(request());

        $this->assertSame(['جشنواره'], $view->getData()['templates']->pluck('title')->all());
        $this->assertSame(1200.0, (float) $view->getData()['costPerPage']);
    }

    // ------------------------------------------------------------------
    // Insufficient balance: «کاهش تعداد مخاطبین»
    // ------------------------------------------------------------------

    public function test_reduce_recipients_cap_never_exceeds_what_the_balance_affords(): void
    {
        $this->seedAdvertisingBalance($this->business->id, 3600); // affords exactly 3 one-page messages
        $this->makeRecipients(5);

        // Without a cap the send is blocked and the exception reports the affordable count.
        try {
            $this->bulkSendService->createCampaign($this->payload(), $this->titaniumUser, $this->business);
            $this->fail('Expected InsufficientBalanceException was not thrown.');
        } catch (InsufficientBalanceException $e) {
            $this->assertSame(3, $e->maxAffordableRecipients);
            $this->assertEquals(6000, $e->requiredAmount);
        }

        // A smaller cap is honoured.
        $small = $this->bulkSendService->createCampaign(
            $this->payload(['max_recipients' => 2]),
            $this->titaniumUser,
            $this->business
        );
        $this->assertSame(2, $small->total_recipients);

        // An inflated client number is clamped to the affordable remainder (3600 - 2400 = 1200 -> 1).
        $clamped = $this->bulkSendService->createCampaign(
            $this->payload(['max_recipients' => 999, 'message' => 'پیام دوم']),
            $this->titaniumUser,
            $this->business
        );
        $this->assertSame(1, $clamped->total_recipients);
        $this->assertEquals(0, (float) $this->business->fresh()->advertising_balance);
    }

    public function test_insufficient_balance_json_carries_deficit_and_max_affordable(): void
    {
        $this->actAsBusinessAdmin();
        $this->seedAdvertisingBalance($this->business->id, 3600);
        $this->makeRecipients(5);

        $this->postJson(route('admin.sms.bulk.send'), $this->payload())
            ->assertStatus(422)
            ->assertJsonPath('error_type', 'insufficient_balance')
            ->assertJsonPath('deficit', 2400)
            ->assertJsonPath('max_affordable_recipients', 3);

        $this->postJson(route('admin.sms.bulk.send'), $this->payload(['max_recipients' => 3]))
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('campaign.total_recipients', 3);
    }
}

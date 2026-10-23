<?php

namespace Tests\Feature;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\User;
use App\Services\Sms\SingleSmsSendService;
use App\Services\Sms\SmsKeywordNormalizer;
use App\Services\Sms\SmsReservedCodes;
use App\Services\Sms\SmsWebhookService;
use App\Services\SmsSettings\SmsAutoResponderService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Per-business SMS auto-responder: rule CRUD, reserved/duplicate trigger validation, keyword normalisation
 * and the inbound engine (reply through the real single-send path with a fake gateway).
 */
class SmsAutoResponderTest extends TestCase
{
    use \Tests\Concerns\SmsSqliteSchema;

    use \Tests\Concerns\SeedsSmsBalances;

    protected GhorfeOnlineList $business;
    protected GhorfeOnlineList $otherBusiness;
    protected Setting $advSender;
    protected Setting $otpSender;
    protected User $admin;
    protected User $otherAdmin;
    protected User $customer;
    protected SmsAutoResponderService $rules;
    /** @var array<int, array{0:string,1:string,2:string}> */
    protected array $gatewayCalls = [];
    protected bool $gatewayFails = false;

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

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('family')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('token')->nullable();
            $table->string('api_token')->nullable();
            $table->string('avatar')->nullable();
            $table->string('level')->default('user');
            $table->string('access_tier')->default('silver');
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('ghorfe_online_lists', function (Blueprint $table) {
            $table->id();
            $table->boolean('sms_contract_accepted')->default(true);
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('otp_balance', 15, 2)->default(0);
            $table->decimal('advertising_balance', 15, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->softDeletes();
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
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();
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
            $table->json('manipulations')->nullable();
            $table->json('custom_properties')->nullable();
            $table->json('generated_conversions')->nullable();
            $table->json('responsive_images')->nullable();
            $table->unsignedInteger('order_column')->nullable();
            $table->nullableTimestamps();
        });

        Schema::create('groupsms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $table->unsignedBigInteger('auto_responder_id')->nullable();
            $table->unsignedBigInteger('callcenter_id')->nullable();
            $table->unsignedBigInteger('phone_list_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('request_id')->nullable();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->text('content')->nullable();
            $table->text('body')->nullable();
            $table->string('direction', 20)->default('outbound');
            $table->string('service_type', 30)->default('normal');
            $table->string('classification', 30)->default('regular');
            $table->string('type', 30)->default('single');
            $table->boolean('is_bulk')->default(false);
            $table->string('recipient_phone', 20)->nullable();
            $table->string('sender_phone', 50)->nullable();
            $table->string('sender_number', 50)->nullable();
            $table->string('status', 30)->default('pending');
            $table->string('delivery_status', 30)->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->integer('character_count')->default(0);
            $table->integer('page_count')->default(1);
            $table->string('tracking_id', 100)->nullable();
            $table->string('gateway_message_id', 100)->nullable();
            $table->integer('sms_count')->default(1);
            $table->decimal('cost', 15, 2)->default(0);
            $table->text('phones')->nullable();
            $table->string('send_at')->nullable();
            $table->unsignedBigInteger('sender_number_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('group')->nullable();
            $table->string('otp_category')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('sender_role')->nullable();
            $table->string('error_reason')->nullable();
            $table->integer('flag')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contexts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee_terms', function (Blueprint $table) {
            $table->id();
            $table->string('role_category')->unique();
            $table->string('file_path')->nullable();
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

        Schema::create('sms_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('sms_message_id')->nullable();
            $table->unsignedBigInteger('sms_campaign_id')->nullable();
            $table->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('sender_number', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('tracking_id', 100)->nullable();
            $table->decimal('charge_amount', 15, 2)->default(0);
            $table->decimal('consume_amount', 15, 2)->default(0);
            $table->string('credit_type', 50)->nullable();
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->unsignedBigInteger('sender_number_id')->nullable();
            $table->text('message')->nullable();
            $table->text('response')->nullable();
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

        $this->business = GhorfeOnlineList::create(['title' => 'فروشگاه شایان']);
        $this->otherBusiness = GhorfeOnlineList::create(['title' => 'فروشگاه دیگر']);

        $this->advSender = Setting::create([
            'key' => 'sms_sender_number',
            'number' => '50009999',
            'type' => 'advertising',
            'sms_panel' => 'IranSms',
            'status' => 'active',
            'settingable_type' => GhorfeOnlineList::class,
            'settingable_id' => $this->business->id,
        ]);
        $this->otpSender = Setting::create([
            'key' => 'sms_sender_number',
            'number' => '10001111',
            'type' => 'otp',
            'sms_panel' => 'IranSms',
            'status' => 'active',
        ]);

        $this->admin = User::create(['name' => 'مدیر', 'family' => 'یک', 'phone' => '09120000001', 'level' => 'admin', 'access_tier' => 'silver', 'active' => 1]);
        $this->otherAdmin = User::create(['name' => 'مدیر', 'family' => 'دو', 'phone' => '09120000002', 'level' => 'admin', 'access_tier' => 'silver', 'active' => 1]);
        $this->customer = User::create(['name' => 'مریم', 'family' => 'مشتری', 'phone' => '09123334455', 'level' => 'user', 'active' => 1]);

        DB::table('employees')->insert(['user_id' => $this->admin->id, 'ghorfe_online_list_id' => $this->business->id, 'work_title' => 'test']);
        DB::table('employees')->insert(['user_id' => $this->otherAdmin->id, 'ghorfe_online_list_id' => $this->otherBusiness->id, 'work_title' => 'test']);

        $this->seedAdvertisingBalance($this->business, 1000000);

        $this->rules = app(SmsAutoResponderService::class);

        // Fake gateway: record every dispatch instead of calling a real panel.
        $this->gatewayCalls = [];
        $this->gatewayFails = false;
        $send = app(SingleSmsSendService::class)->setGatewayDispatcher(function ($panel, $phone, $message) {
            if ($this->gatewayFails) {
                throw new \RuntimeException('gateway down');
            }
            $this->gatewayCalls[] = [$panel, $phone, $message];
        });
        $this->app->instance(SingleSmsSendService::class, $send);
        $this->useStoredSettingsShape();
    }

    protected function makeRule(string $keyword, string $type = 'exact', array $extra = [], ?GhorfeOnlineList $business = null): Setting
    {
        $business ??= $this->business;

        return app(SmsAutoResponderService::class)->create($business->id, array_merge([
            'name' => 'قانون ' . $keyword,
            'trigger_keyword' => $keyword,
            'match_type' => $type,
            'reply_text' => 'پاسخ ' . $keyword,
            'is_active' => true,
        ], $extra));
    }

    protected function inbound(string $text, string $from = '09123334455', string $to = '50009999', array $extra = []): array
    {
        return app(SmsWebhookService::class)->handleInboundMessage(array_merge([
            'sender' => $from,
            'recipient' => $to,
            'message' => $text,
        ], $extra));
    }

    protected function payload(array $extra = []): array
    {
        return array_merge([
            'name' => 'قیمت',
            'trigger_keyword' => 'قیمت',
            'match_type' => 'exact',
            'reply_text' => 'لیست قیمت‌ها: ...',
            'is_active' => 1,
            'business_id' => $this->business->id,
        ], $extra);
    }

    // ---------------------------------------------------------------- normalisation

    public function test_normaliser_trims_casefolds_converts_digits_and_collapses_whitespace(): void
    {
        $this->assertSame('stop now 12', SmsKeywordNormalizer::normalize("  STOP \t Now  ۱۲ "));
        $this->assertSame('لغو 11', SmsKeywordNormalizer::normalize('لغو ١١'));
        $this->assertSame('لغو 11', SmsKeywordNormalizer::normalize("لغو\u{200C}۱۱"));
        $this->assertSame('کتاب', SmsKeywordNormalizer::normalize('كتاب'));
    }

    // ---------------------------------------------------------------- reserved codes

    public function test_reserved_codes_are_rejected_including_persian_digit_and_case_variants(): void
    {
        foreach (['11', '۱۱', 'لغو 11', 'لغو۱۱', 'لغو11', '12', 'STOP', ' Stop ', 'help', 'لغو', 'توقف'] as $word) {
            $errors = $this->rules->validateTrigger($this->business->id, $word, 'exact');
            $this->assertNotEmpty($errors, "«{$word}» should be reserved");
            $this->assertStringContainsString('رزروشده', $errors[0]);
        }
        $this->assertSame([], $this->rules->validateTrigger($this->business->id, 'قیمت', 'exact'));
    }

    public function test_service_ids_and_sms_codes_are_reserved(): void
    {
        DB::table('services')->insert(['id' => 7, 'title' => 'خدمت']);
        Schema::table('users', fn (Blueprint $t) => $t->text('sms_code')->nullable());
        DB::table('users')->where('id', $this->admin->id)->update(['sms_code' => json_encode(['AB12', '۴۴۰'])]);

        $this->assertNotEmpty($this->rules->validateTrigger($this->business->id, '۷', 'exact'));
        $this->assertNotEmpty($this->rules->validateTrigger($this->business->id, 'ab12', 'exact'));
        $this->assertNotEmpty($this->rules->validateTrigger($this->business->id, '440', 'exact'));
        $this->assertSame([], $this->rules->validateTrigger($this->business->id, '8', 'exact'));
    }

    public function test_starts_with_prefix_that_would_shadow_a_reserved_code_is_rejected(): void
    {
        $errors = $this->rules->validateTrigger($this->business->id, 'لغ', 'starts_with');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('پوشش', $errors[0]);
        $this->assertSame([], $this->rules->validateTrigger($this->business->id, 'قیمت', 'starts_with'));
    }

    // ---------------------------------------------------------------- duplicates

    public function test_duplicate_active_trigger_is_rejected_after_normalisation(): void
    {
        $this->makeRule('Price 1');

        $errors = $this->rules->validateTrigger($this->business->id, '  price ۱ ', 'exact');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('تداخل', $errors[0]);
        $this->assertStringContainsString('قانون Price 1', $errors[0]);
    }

    public function test_editing_a_rule_ignores_itself(): void
    {
        $rule = $this->makeRule('قیمت');
        $this->assertSame([], $this->rules->validateTrigger($this->business->id, 'قیمت', 'exact', $rule->id));
    }

    public function test_inactive_and_soft_deleted_rules_do_not_block(): void
    {
        $this->makeRule('قیمت', 'exact', ['is_active' => false]);
        $deleted = $this->makeRule('تخفیف');
        $deleted->delete();

        $this->assertSame([], $this->rules->validateTrigger($this->business->id, 'قیمت', 'exact'));
        $this->assertSame([], $this->rules->validateTrigger($this->business->id, 'تخفیف', 'exact'));
    }

    public function test_same_trigger_in_another_business_is_allowed(): void
    {
        $this->makeRule('قیمت', 'exact', [], $this->otherBusiness);
        $this->assertSame([], $this->rules->validateTrigger($this->business->id, 'قیمت', 'exact'));
    }

    public function test_starts_with_and_exact_overlap_is_rejected(): void
    {
        $this->makeRule('قیمت', 'starts_with');
        $this->assertNotEmpty($this->rules->validateTrigger($this->business->id, 'قیمت محصول', 'exact'));
        $this->assertNotEmpty($this->rules->validateTrigger($this->business->id, 'قیم', 'starts_with'));
        $this->assertNotEmpty($this->rules->validateTrigger($this->business->id, 'قیمت ویژه', 'starts_with'));
        $this->assertSame([], $this->rules->validateTrigger($this->business->id, 'تخفیف', 'starts_with'));
    }

    public function test_reactivating_a_conflicting_rule_is_blocked(): void
    {
        $this->makeRule('قیمت');
        $off = $this->makeRule('قیمت', 'exact', ['is_active' => false]);

        $errors = $this->rules->toggle($off);
        $this->assertNotEmpty($errors);
        $this->assertFalse($off->fresh()->is_active);
    }

    // ---------------------------------------------------------------- HTTP CRUD

    public function test_crud_is_scoped_per_business(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.sms.settings.auto-responders.store'), $this->payload())->assertSessionHasNoErrors();
        $rule = Setting::where('key', 'sms_auto_responder')->first();
        $this->assertSame((int) $this->business->id, (int) $rule->settingable_id);
        $this->assertSame('قیمت', $rule->normalized_keyword);

        $this->get(route('admin.sms.settings.auto-responders.index'))->assertOk()->assertSee('قیمت');
        $this->get(route('admin.sms.settings.auto-responders.create'))->assertOk();
        $this->get(route('admin.sms.settings.auto-responders.edit', $rule))->assertOk();

        $this->put(route('admin.sms.settings.auto-responders.update', $rule), $this->payload(['reply_text' => 'جدید']))->assertSessionHasNoErrors();
        $this->assertSame('جدید', $rule->fresh()->reply_text);

        $this->post(route('admin.sms.settings.auto-responders.toggle-status', $rule));
        $this->assertFalse($rule->fresh()->is_active);

        // another business' admin sees none and cannot touch it
        $this->actingAs($this->otherAdmin);
        $this->get(route('admin.sms.settings.auto-responders.index'))->assertOk()->assertDontSee('لیست قیمت');
        $this->get(route('admin.sms.settings.auto-responders.edit', $rule))->assertForbidden();
        $this->put(route('admin.sms.settings.auto-responders.update', $rule), $this->payload())->assertForbidden();
        $this->post(route('admin.sms.settings.auto-responders.toggle-status', $rule))->assertForbidden();
        $this->delete(route('admin.sms.settings.auto-responders.destroy', $rule))->assertForbidden();
        $this->assertNotNull(Setting::find($rule->id));

        $this->actingAs($this->admin);
        $this->delete(route('admin.sms.settings.auto-responders.destroy', $rule))->assertRedirect();
        $this->assertNull(Setting::find($rule->id));
    }

    public function test_create_and_store_without_a_business_redirect_to_list_with_friendly_error(): void
    {
        $super = User::create(['name' => 'super', 'family' => 'admin', 'phone' => '09120000099', 'password' => 'x', 'level' => 'superAdmin', 'active' => true]);
        $this->actingAs($super);

        $this->get(route('admin.sms.settings.auto-responders.create'))
            ->assertRedirect(route('admin.sms.settings.auto-responders.index'))
            ->assertSessionHas('error', 'ابتدا یک کسب‌وکار را انتخاب کنید.');

        $this->post(route('admin.sms.settings.auto-responders.store'), $this->payload(['business_id' => null]))
            ->assertRedirect(route('admin.sms.settings.auto-responders.index'))
            ->assertSessionHas('error', 'ابتدا یک کسب‌وکار را انتخاب کنید.');
        $this->assertSame(0, Setting::where('key', 'sms_auto_responder')->count());
    }

    public function test_store_shows_reserved_and_duplicate_conflicts_inline(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.sms.settings.auto-responders.store'), $this->payload(['trigger_keyword' => 'لغو 11', 'name' => 'x']))
            ->assertSessionHasErrors('trigger_keyword');
        $this->assertSame(0, Setting::where('key', 'sms_auto_responder')->count());

        $this->post(route('admin.sms.settings.auto-responders.store'), $this->payload())->assertSessionHasNoErrors();
        $response = $this->from(route('admin.sms.settings.auto-responders.create'))
            ->post(route('admin.sms.settings.auto-responders.store'), $this->payload(['name' => 'دوم', 'trigger_keyword' => ' قیمت ']));
        $response->assertSessionHasErrors('trigger_keyword');
        $this->assertSame(1, Setting::where('key', 'sms_auto_responder')->count());

        $this->get(route('admin.sms.settings.auto-responders.create'))->assertOk()->assertSee('کدهای رزروشده');
    }

    // ---------------------------------------------------------------- engine

    public function test_engine_replies_on_exact_match_through_single_send_with_billing_and_audit(): void
    {
        $rule = $this->makeRule('قیمت', 'exact', ['reply_text' => 'سلام {نام کاربر}، قیمت‌ها در سایت است']);

        $res = $this->inbound(' قیمت ');

        $this->assertTrue($res['success']);
        $this->assertCount(1, $this->gatewayCalls);
        $this->assertSame('09123334455', $this->gatewayCalls[0][1]);
        $this->assertStringContainsString('سلام مریم', $this->gatewayCalls[0][2]);

        $reply = Groupsms::where('direction', 'outbound')->first();
        $this->assertNotNull($reply);
        $this->assertSame($rule->id, (int) $reply->auto_responder_id);
        $this->assertSame($res['auto_reply_sms_message_id'], $reply->id);
        $this->assertSame('normal', $reply->service_type);
        $this->assertNotSame('otp', $reply->service_type);
        $this->assertSame('regular', $reply->classification);
        $this->assertSame('50009999', $reply->sender_phone);
        $this->assertSame($this->business->id, $reply->ghorfe_online_list_id);

        $this->assertLessThan(1000000, (float) $this->business->fresh()->advertising_balance);
        $this->assertSame(1, SmsLog::where('type', 'audit_single')->count());
    }

    public function test_engine_replies_on_longest_starts_with_and_prefers_exact(): void
    {
        $this->makeRule('قیمت', 'starts_with', ['reply_text' => 'کوتاه']);
        $this->makeRule('خرید', 'exact', ['reply_text' => 'دقیق']);
        // an exact rule inside the prefix range is blocked by the validator, so exercise the engine ordering directly
        app(SmsAutoResponderService::class)->create($this->business->id, [
            'name' => 'بلند', 'trigger_keyword' => 'قیمت ویژه',
            'match_type' => 'starts_with', 'reply_text' => 'بلندتر', 'is_active' => true,
        ]);

        $this->inbound('قیمت ویژه برای من', '09120001111');
        $this->assertSame('بلندتر', $this->gatewayCalls[0][2]);

        $this->inbound('قیمت محصول', '09120002222');
        $this->assertSame('کوتاه', $this->gatewayCalls[1][2]);

        $this->inbound('خرید', '09120003333');
        $this->assertSame('دقیق', $this->gatewayCalls[2][2]);
    }

    public function test_real_engine_creates_reply_row_for_inbound_to_known_sender_line(): void
    {
        // No mocking of line lookup: business resolved via sender_numbers/business pivot only.
        $rule = $this->makeRule('قیمت');

        $res = $this->inbound('قیمت', '09123334455', '50009999');

        $this->assertTrue($res['success']);
        $this->assertNotNull($res['auto_reply_sms_message_id']);
        $reply = Groupsms::findOrFail($res['auto_reply_sms_message_id']);
        $this->assertSame($rule->id, (int) $reply->auto_responder_id);
        $this->assertSame('outbound', $reply->direction);
        $this->assertSame('09123334455', $reply->recipient_phone);
        $this->assertSame($this->business->id, (int) $reply->ghorfe_online_list_id);
    }

    public function test_engine_matches_persian_digits(): void
    {
        $this->makeRule('کد 55');
        $this->inbound('کد ۵۵');
        $this->assertCount(1, $this->gatewayCalls);
    }

    public function test_no_reply_without_a_match_or_for_other_business(): void
    {
        $this->makeRule('قیمت');
        $this->makeRule('تخفیف', 'exact', [], $this->otherBusiness);

        $this->inbound('سلام');
        $this->inbound('تخفیف');
        $this->inbound('قیمت', '09120005555', '30002222'); // other business' line

        $this->assertCount(0, $this->gatewayCalls);
        $this->assertSame(0, Groupsms::where('direction', 'outbound')->count());
    }

    public function test_inactive_and_deleted_rules_do_not_answer(): void
    {
        $this->makeRule('قیمت', 'exact', ['is_active' => false]);
        $gone = $this->makeRule('تخفیف');
        $gone->delete();

        $this->inbound('قیمت');
        $this->inbound('تخفیف');

        $this->assertCount(0, $this->gatewayCalls);
    }

    public function test_reserved_and_opt_out_words_are_never_auto_answered(): void
    {
        // Legacy/foreign rules that slipped past validation still must not answer reserved words.
        foreach (['11', 'stop', 'لغو 11'] as $i => $word) {
            app(SmsAutoResponderService::class)->create($this->business->id, [
                'name' => 'bad' . $i, 'trigger_keyword' => $word,
                'match_type' => 'exact',
                'reply_text' => 'x', 'is_active' => true,
            ]);
        }
        $this->makeRule('ل', 'starts_with');

        foreach (['11', '۱۱', 'STOP', 'stop please', 'لغو 11', 'لغو۱۱ ممنون'] as $i => $word) {
            $this->inbound($word, '0912111000' . $i);
        }

        $this->assertCount(0, $this->gatewayCalls);
        $this->assertTrue(SmsReservedCodes::isProtectedInbound('Stop'));
    }

    public function test_never_replies_to_own_lines_or_inbound_otp(): void
    {
        $this->makeRule('قیمت');

        $this->inbound('قیمت', '50009999', '50009999'); // from our own advertising line
        $this->inbound('قیمت', '10001111');             // from another of our own sender lines
        $this->inbound('قیمت', '09124445566', '10001111'); // sent to the OTP line
        $this->inbound('قیمت', '09124445577', '50009999', ['service_type' => 'otp']);

        $this->assertCount(0, $this->gatewayCalls);
    }

    public function test_rate_limit_one_reply_per_sender_per_rule_per_window(): void
    {
        $this->makeRule('قیمت');
        $this->makeRule('تخفیف');

        $this->inbound('قیمت');
        $this->inbound('قیمت');
        $this->assertCount(1, $this->gatewayCalls);

        // another sender and another rule are independent
        $this->inbound('قیمت', '09127776655');
        $this->inbound('تخفیف');
        $this->assertCount(3, $this->gatewayCalls);

        // after the window the same sender is answered again
        Groupsms::where('direction', 'outbound')->update(['created_at' => now()->subMinutes(11)]);
        $this->inbound('قیمت');
        $this->assertCount(4, $this->gatewayCalls);

        // window is configurable
        config(['services.sms.auto_reply_window_minutes' => 60]);
        Groupsms::where('direction', 'outbound')->update(['created_at' => now()->subMinutes(11)]);
        $this->inbound('قیمت');
        $this->assertCount(4, $this->gatewayCalls);
    }

    public function test_webhook_still_succeeds_when_reply_fails(): void
    {
        $this->makeRule('قیمت');

        // 1) gateway down: reply row is marked failed, webhook ok
        $this->gatewayFails = true;
        $res = $this->inbound('قیمت');
        $this->assertTrue($res['success']);
        $this->assertSame('failed', Groupsms::where('direction', 'outbound')->value('status'));

        // 2) no credit: send throws, nothing is stored for the reply, webhook ok
        $this->gatewayFails = false;
        $this->seedAdvertisingBalance($this->business, 0);
        $res = $this->inbound('قیمت', '09128889900');
        $this->assertTrue($res['success']);
        $this->assertNull($res['auto_reply_sms_message_id']);
        $this->assertSame(1, Groupsms::where('direction', 'inbound')->where('sender_phone', '09128889900')->count());
        $this->assertSame(1, Groupsms::where('direction', 'outbound')->count());

        // 3) engine blows up unexpectedly: still ok
        $this->app->instance(SingleSmsSendService::class, new class {
            public function send(): never
            {
                throw new \RuntimeException('boom');
            }
        });
        $this->seedAdvertisingBalance($this->business, 1000000);
        $res = $this->inbound('قیمت', '09128889911');
        $this->assertTrue($res['success']);
    }
}

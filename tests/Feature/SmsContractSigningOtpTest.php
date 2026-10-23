<?php

namespace Tests\Feature;

use App\Contracts\Auth\OtpNotifierInterface;
use App\Exceptions\Auth\OtpNotificationException;
use App\Models\ContractView;
use App\Models\GhorfeOnlineList;
use App\Models\User;
use App\Services\Sms\BulkSmsRecipientFilterService;
use App\Services\Sms\SmsContractOtpService;
use App\Services\SmsSettings\SmsSignatureRecord;
use App\Services\SmsSettings\SmsCmsContentService;
use App\Services\V1\Auth\NumericOtpGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The SMS usage contract + telecom rules are signed with a 6-digit OTP sent to the signer's OWN phone.
 *
 * Runs on in-memory SQLite only (the default connection is switched in setUp before any query).
 * The business starts unsigned: status off and no signing history line.
 */
class SmsContractSigningOtpTest extends TestCase
{
    private GhorfeOnlineList $business;
    private User $signer;
    private User $colleague;
    private object $sms;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');
        Cache::flush();

        Schema::create('users', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('family')->nullable();
            $t->string('phone')->nullable();
            $t->string('password')->nullable();
            $t->string('level')->default('user');
            $t->boolean('active')->default(true);
            $t->string('token')->nullable();
            $t->string('api_token')->nullable();
            $t->string('avatar')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        // Only the columns the signing flow reads or writes. The signing state is NOT on this table:
        // it lives in the central history line (see SmsSignatureRecord).
        Schema::create('ghorfe_online_lists', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('en_title')->nullable();
            $t->string('slug')->nullable();
            $t->boolean('status')->default(false);
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('employees', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('ghorfe_settings', function (Blueprint $t) {
            $t->id();
            $t->text('value')->nullable();
            $t->string('key')->nullable();
            $t->nullableMorphs('settingable');
            $t->timestamps();
        });
        foreach (['roles', 'permissions'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->increments('id');
                $t->string('name')->nullable();
                $t->timestamps();
            });
        }
        Schema::create('role_user', function (Blueprint $t) {
            $t->unsignedInteger('role_id');
            $t->unsignedInteger('user_id');
            $t->string('user_type');
        });
        // The SMS log: the OTP rows (type contract_otp) and the audit rows of the sends.
        Schema::create('sms_logs', function (Blueprint $t) {
            $t->id();
            $t->string('phone', 20)->nullable();
            $t->string('type', 30)->nullable();
            $t->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->string('sender_number', 50)->nullable();
            $t->unsignedBigInteger('sms_message_id')->nullable();
            $t->unsignedBigInteger('sms_campaign_id')->nullable();
            $t->string('status', 50)->nullable();
            $t->string('tracking_id', 100)->nullable();
            $t->text('message')->nullable();
            $t->text('response')->nullable();
            $t->timestamps();
        });
        // The central signing record: one history line on the business, and the snapshot of the signed text.
        Schema::create('histories', function (Blueprint $t) {
            $t->increments('id');
            $t->string('historiable_type');
            $t->unsignedBigInteger('historiable_id');
            $t->unsignedInteger('user_id')->nullable();
            $t->unsignedInteger('callcenter_id')->nullable();
            $t->integer('status');
            $t->unsignedInteger('reason_id')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });
        Schema::create('contract_views', function (Blueprint $t) {
            $t->increments('id');
            $t->longText('contract');
            $t->string('contract_type');
            $t->unsignedBigInteger('contract_id');
            $t->timestamps();
        });
        // Read by the sign page and by the signed-text builder (no usage rows in these tests).
        Schema::create('contracts', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('contract_folder_id')->nullable();
            $t->unsignedTinyInteger('type')->nullable();
            $t->string('title')->nullable();
            $t->longText('content')->nullable();
            $t->string('file')->nullable();
            $t->integer('status')->default(1);
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('contract_usages', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('contract_id');
            $t->string('usable_type')->nullable();
            $t->unsignedBigInteger('usable_id')->nullable();
            $t->string('location');
            $t->string('contract_number')->nullable();
            $t->date('contract_date')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('position')->default(0);
            $t->softDeletes();
            $t->timestamps();
        });
        // Spatie media (the sign page lists the client files of the business).
        Schema::create('media', function (Blueprint $t) {
            $t->char('id', 36)->primary();
            $t->morphs('model');
            $t->char('uuid', 36)->nullable()->unique();
            $t->string('collection_name');
            $t->string('name');
            $t->string('file_name');
            $t->string('mime_type')->nullable();
            $t->string('disk');
            $t->string('conversions_disk')->nullable();
            $t->unsignedBigInteger('size');
            $t->json('manipulations');
            $t->json('custom_properties');
            $t->json('generated_conversions');
            $t->json('responsive_images');
            $t->unsignedInteger('order_column')->nullable()->index();
            $t->nullableTimestamps();
        });

        $this->business = GhorfeOnlineList::create(['title' => 'کسب‌وکار تست', 'status' => false]);
        $this->signer = User::create(['name' => 'امضا', 'family' => 'کننده', 'phone' => '09121234567', 'level' => 'admin', 'active' => 1]);
        $this->colleague = User::create(['name' => 'همکار', 'phone' => '09351234567', 'level' => 'admin', 'active' => 1]);

        app(SmsCmsContentService::class)->set(SmsCmsContentService::KEY_CONTRACT_TEXT, 'متن تعهدنامه');
        app(SmsCmsContentService::class)->set(SmsCmsContentService::KEY_REGULATORY_RULES, 'متن ضوابط');

        // A notifier that records instead of sending a real SMS.
        $this->sms = new class implements OtpNotifierInterface {
            public array $sent = [];
            public bool $fail = false;

            public function send(string $phone, string $code): void
            {
                if ($this->fail) {
                    throw new OtpNotificationException('boom');
                }
                $this->sent[] = ['phone' => $phone, 'code' => $code];
            }

            public function lastCode(): string
            {
                return end($this->sent)['code'];
            }
        };
        $this->app->singleton(SmsContractOtpService::class, fn ($app) => new SmsContractOtpService(
            new NumericOtpGenerator(6), $this->sms, $app->make(BulkSmsRecipientFilterService::class), 300, 5, 60, 5
        ));
    }

    private function sendOtp(?User $as = null, string $name = 'علی رضایی', array $extra = [])
    {
        return $this->actingAs($as ?? $this->signer)->postJson(route('admin.sms.contract.otp'), array_merge([
            'agree' => 1, 'signature_name' => $name, 'business_id' => $this->business->id,
        ], $extra));
    }

    private function accept(string $otp, ?User $as = null, string $name = 'علی رضایی')
    {
        return $this->actingAs($as ?? $this->signer)->post(route('admin.sms.contract.accept'), [
            'agree' => 1, 'signature_name' => $name, 'otp' => $otp, 'business_id' => $this->business->id,
        ]);
    }

    public function test_full_signing_flow_records_the_verified_signature(): void
    {
        $this->sendOtp()->assertOk()->assertJsonPath('status', 'success')->assertJsonPath('masked_phone', '0912***4567');

        $this->assertCount(1, $this->sms->sent);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $this->sms->lastCode());

        $this->accept($this->sms->lastCode())->assertSessionHasNoErrors();

        $business = $this->business->fresh();
        $this->assertTrue((bool) $business->sms_contract_accepted);
        $this->assertSame('علی رضایی', $business->sms_contract_signature_name);

        // The signing line in the central history: reason 1, status 1, the SMS marker, the signer,
        // the name in «», the verified phone, the OTP time and the text hash.
        $line = SmsSignatureRecord::lineFor($business);
        $this->assertNotNull($line);
        $this->assertSame(SmsSignatureRecord::REASON_ID, (int) $line->reason_id);
        $this->assertSame(SmsSignatureRecord::STATUS, (int) $line->status);
        $this->assertSame($this->signer->id, (int) $line->user_id);
        $this->assertStringContainsString(SmsSignatureRecord::MARKER, $line->description);
        $this->assertStringContainsString('امضاکننده: «علی رضایی»', $line->description);

        $parsed = SmsSignatureRecord::parse($line->description);
        $this->assertSame('علی رضایی', $parsed['name']);
        $this->assertSame('09121234567', $parsed['phone']);
        $this->assertSame(app(SmsContractOtpService::class)->contractHash('متن تعهدنامه', 'متن ضوابط'), $parsed['hash']);
        $this->assertNotNull($parsed['verified_at']);

        // And the exact signed text is kept as a snapshot on the business, the central way.
        $snapshot = ContractView::query()
            ->where('contract_type', GhorfeOnlineList::class)
            ->where('contract_id', $business->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($snapshot, 'the signed text must be kept as a contract_views snapshot');
        $this->assertStringContainsString('متن تعهدنامه', $snapshot->contract);
        $this->assertStringContainsString('متن ضوابط', $snapshot->contract);

        // Single use: the same code cannot sign again, and a signed contract is final.
        $this->assertSame('used', DB::table('sms_logs')->where('type', 'contract_otp')->value('status'));
        $this->sendOtp()->assertStatus(409);
    }

    public function test_code_goes_only_to_the_signers_own_account_phone(): void
    {
        $this->sendOtp(null, 'علی رضایی', ['phone' => '09999999999', 'receptor' => '09888888888'])->assertOk();

        $this->assertSame('09121234567', $this->sms->sent[0]['phone'], 'a number supplied in the request must be ignored');
    }

    public function test_signer_without_a_valid_phone_cannot_get_a_code(): void
    {
        $this->signer->forceFill(['phone' => null])->save();

        $this->sendOtp()->assertStatus(422)->assertJsonPath('reason', 'missing_phone');
        $this->assertSame([], $this->sms->sent);
    }

    public function test_sending_requires_agreement_and_a_name(): void
    {
        $this->actingAs($this->signer)->postJson(route('admin.sms.contract.otp'), ['business_id' => $this->business->id])
            ->assertStatus(422)->assertJsonValidationErrors(['agree', 'signature_name']);
        $this->assertSame([], $this->sms->sent);
    }

    public function test_signing_without_a_code_or_with_a_malformed_one_is_rejected(): void
    {
        $this->sendOtp();

        $this->accept('')->assertSessionHasErrors('otp');
        $this->accept('12345')->assertSessionHasErrors('otp');
        $this->accept('12ab56')->assertSessionHasErrors('otp');
        $this->assertFalse((bool) $this->business->fresh()->sms_contract_accepted);
    }

    public function test_wrong_codes_are_counted_and_the_code_burns_after_the_limit(): void
    {
        $this->sendOtp();
        $good = $this->sms->lastCode();
        $wrong = $good === '000000' ? '111111' : '000000';

        $this->accept($wrong)->assertSessionHasErrors('otp');
        $meta = json_decode(DB::table('sms_logs')->where('type', 'contract_otp')->value('response'), true);
        $this->assertSame(1, (int) ($meta['attempts'] ?? 0), 'the failed attempt must be committed');

        foreach (range(1, 4) as $i) {
            $this->accept($wrong);
        }

        // Burned: even the right code no longer works.
        $this->accept($good)->assertSessionHasErrors('otp');
        $this->assertFalse((bool) $this->business->fresh()->sms_contract_accepted);

        // ...and a fresh code works again (after the resend cooldown).
        $this->travel(61)->seconds();
        $this->sendOtp()->assertOk();
        $this->accept($this->sms->lastCode())->assertSessionHasNoErrors();
        $this->assertTrue((bool) $this->business->fresh()->sms_contract_accepted);
    }

    public function test_expired_code_is_rejected(): void
    {
        $this->sendOtp();
        $code = $this->sms->lastCode();

        $this->travel(301)->seconds();

        $this->accept($code)->assertSessionHasErrors('otp');
        $this->assertFalse((bool) $this->business->fresh()->sms_contract_accepted);
    }

    public function test_code_is_bound_to_the_signature_name_it_was_requested_for(): void
    {
        $this->sendOtp(null, 'علی رضایی');

        $this->accept($this->sms->lastCode(), null, 'شخص دیگری')->assertSessionHasErrors('otp');
        $meta = json_decode(DB::table('sms_logs')->where('type', 'contract_otp')->value('response'), true);
        $this->assertSame(0, (int) ($meta['attempts'] ?? 0), 'a name mismatch is not a guess');

        // The original name still works with the same code.
        $this->accept($this->sms->lastCode(), null, 'علی رضایی')->assertSessionHasNoErrors();
        $this->assertTrue((bool) $this->business->fresh()->sms_contract_accepted);
    }

    public function test_code_is_bound_to_the_exact_contract_and_rules_text(): void
    {
        $this->sendOtp();
        $code = $this->sms->lastCode();

        app(SmsCmsContentService::class)->set(SmsCmsContentService::KEY_CONTRACT_TEXT, 'متن تغییر یافته توسط مدیر');

        $this->accept($code)->assertSessionHasErrors('otp');
        $this->assertFalse((bool) $this->business->fresh()->sms_contract_accepted);
    }

    public function test_one_users_code_cannot_sign_for_another_user(): void
    {
        $this->sendOtp($this->signer);

        $this->accept($this->sms->lastCode(), $this->colleague)->assertSessionHasErrors('otp');
        $this->assertFalse((bool) $this->business->fresh()->sms_contract_accepted);
    }

    public function test_resend_cooldown_and_hourly_cap(): void
    {
        $this->sendOtp()->assertOk();
        $this->sendOtp()->assertStatus(429)->assertJsonPath('reason', 'cooldown');
        $this->assertCount(1, $this->sms->sent);

        // 5 sends per hour in total
        foreach (range(2, 5) as $i) {
            $this->travel(61)->seconds();
            $this->sendOtp()->assertOk();
        }
        $this->travel(61)->seconds();
        $this->sendOtp()->assertStatus(429)->assertJsonPath('reason', 'hourly_limit');
        $this->assertCount(5, $this->sms->sent);

        // Only the newest code is valid
        $this->travel(1)->hours();
        $this->sendOtp()->assertOk();
        $this->assertSame(1, DB::table('sms_logs')->where('type', 'contract_otp')->where('status', 'pending')->count());
    }

    public function test_a_failed_sms_does_not_cost_the_signer_a_cooldown(): void
    {
        $this->sms->fail = true;
        $this->sendOtp()->assertStatus(503)->assertJsonPath('reason', 'send_failed');
        $this->assertSame(0, DB::table('sms_logs')->where('type', 'contract_otp')->count());

        $this->sms->fail = false;
        $this->sendOtp()->assertOk(); // immediately, no cooldown
    }

    public function test_the_plain_code_is_never_stored_and_persian_digits_are_accepted(): void
    {
        $this->sendOtp();
        $code = $this->sms->lastCode();

        $stored = DB::table('sms_logs')->where('type', 'contract_otp')->first();
        $this->assertSame(64, strlen($stored->tracking_id));
        $this->assertStringNotContainsString($code, json_encode($stored));

        $persian = strtr($code, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
        $this->accept($persian)->assertSessionHasNoErrors();
        $this->assertTrue((bool) $this->business->fresh()->sms_contract_accepted);
    }

    public function test_the_sign_page_gets_the_masked_phone_and_never_the_full_number(): void
    {
        $this->actingAs($this->signer);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['business_id' => $this->business->id]);

        $data = app(\App\Http\Controllers\Admin\SmsContractSignController::class)->index($request)->getData();
        $this->assertSame('0912***4567', $data['otpPhoneMasked']);
        $this->assertSame(60, $data['otpResendSeconds']);
        $this->assertStringNotContainsString('09121234567', json_encode($data['otpPhoneMasked']));

        // No usable phone on the account -> no code field; the page explains why
        $this->signer->forceFill(['phone' => null])->save();
        $this->assertNull(app(\App\Http\Controllers\Admin\SmsContractSignController::class)->index($request)->getData()['otpPhoneMasked']);
    }

    public function test_the_sign_page_markup_has_the_code_field_and_compiles(): void
    {
        $source = file_get_contents(resource_path('views/Admin/sms/contract/sign.blade.php'));

        $this->assertStringContainsString('name="otp"', $source);
        $this->assertStringContainsString("route('admin.sms.contract.otp')", $source);
        $this->assertStringContainsString('autocomplete="one-time-code"', $source);
        $this->assertStringContainsString('maxlength="6"', $source);

        // Throws if the Blade (incl. the new @section('scripts')) does not compile.
        $compiled = app('blade.compiler')->compileString($source);
        $this->assertStringContainsString('otpBox', $compiled);
    }
}

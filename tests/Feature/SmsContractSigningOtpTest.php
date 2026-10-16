<?php

namespace Tests\Feature;

use App\Contracts\Auth\OtpNotifierInterface;
use App\Exceptions\Auth\OtpNotificationException;
use App\Models\GhorfeOnlineList;
use App\Models\User;
use App\Services\Sms\BulkSmsRecipientFilterService;
use App\Services\Sms\SmsContractOtpService;
use App\Services\SmsSettings\SmsCmsContentService;
use App\Services\V1\Auth\NumericOtpGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The SMS usage contract + telecom rules are signed with a 6-digit OTP sent to the signer's OWN phone.
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
            $t->string('access_tier')->default('silver');
            $t->boolean('active')->default(true);
            $t->string('token')->nullable();
            $t->string('api_token')->nullable();
            $t->string('avatar')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('ghorfe_online_lists', function (Blueprint $t) {
            $t->id();
            $t->boolean('sms_contract_accepted')->default(false);
            $t->timestamp('sms_contract_accepted_at')->nullable();
            $t->string('sms_contract_signature_name')->nullable();
            $t->unsignedInteger('sms_contract_accepted_by')->nullable();
            $t->string('title');
            $t->boolean('status')->default(true);
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
        Schema::create('sms_cms_contents', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->longText('value')->nullable();
            $t->unsignedInteger('updated_by')->nullable();
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

        // The real migration: creates sms_contract_otps and adds the signature-evidence columns.
        (require base_path('database/migrations/2026_10_16_000002_create_sms_contract_otps_table.php'))->up();

        $this->business = GhorfeOnlineList::create(['title' => 'کسب‌وکار تست']);
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
        $this->assertSame($this->signer->id, (int) $business->sms_contract_accepted_by);
        $this->assertSame('09121234567', $business->sms_contract_signed_phone);
        $this->assertSame(app(SmsContractOtpService::class)->contractHash('متن تعهدنامه', 'متن ضوابط'), $business->sms_contract_text_hash);
        $this->assertNotNull($business->sms_contract_otp_verified_at);

        // Single use: the same code cannot sign again, and a signed contract is final.
        $this->assertNotNull(DB::table('sms_contract_otps')->value('used_at'));
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
        $this->assertSame(1, (int) DB::table('sms_contract_otps')->value('attempts'), 'the failed attempt must be committed');

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
        $this->assertSame(0, (int) DB::table('sms_contract_otps')->value('attempts'), 'a name mismatch is not a guess');

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
        $this->assertSame(1, DB::table('sms_contract_otps')->whereNull('used_at')->count());
    }

    public function test_a_failed_sms_does_not_cost_the_signer_a_cooldown(): void
    {
        $this->sms->fail = true;
        $this->sendOtp()->assertStatus(503)->assertJsonPath('reason', 'send_failed');
        $this->assertSame(0, DB::table('sms_contract_otps')->count());

        $this->sms->fail = false;
        $this->sendOtp()->assertOk(); // immediately, no cooldown
    }

    public function test_the_plain_code_is_never_stored_and_persian_digits_are_accepted(): void
    {
        $this->sendOtp();
        $code = $this->sms->lastCode();

        $stored = DB::table('sms_contract_otps')->first();
        $this->assertSame(64, strlen($stored->code_hash));
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

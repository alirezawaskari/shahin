<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\Sms\SendBulkSmsRequest;
use App\Http\Requests\Admin\Sms\SendSingleSmsRequest;
use App\Models\SenderNumber;
use App\Models\SmsMessageGroup;
use App\Models\User;
use Database\Seeders\SmsMessageGroupSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Promotional sends only support the event/greeting, sales and support subject groups
 * (and their subgroups); mali / hoghoghi / ejraei are not offered or accepted. OTP is unaffected.
 */
class SmsPromotionalGroupsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

        Schema::create('ghorfe_online_lists', function (Blueprint $t) {
            $t->id();
            $t->string('title')->nullable();
            $t->timestamps();
        });
        foreach ([
            'database/migrations/2026_10_07_000001_create_sender_numbers_table.php',
            'database/migrations/2026_10_07_000002_create_sms_message_groups_table.php',
            'database/migrations/2026_10_12_000002_separate_categories_and_templates_per_business.php',
        ] as $file) {
            $m = require base_path($file);
            try {
                $m->up();
            } catch (\Throwable $e) {
                // later migrations may touch tables this slim test does not create; the groups table is what matters
            }
        }
        Schema::dropIfExists('sms_message_templates');
        Schema::create('sms_message_templates', function (Blueprint $t) {
            $t->id();
            $t->string('service_type')->nullable();
            $t->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $t->string('otp_category')->nullable();
            $t->timestamps();
        });
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $t) {
            $t->increments('id');
            $t->timestamps();
        });

        (new SmsMessageGroupSeeder())->run();

        $user = new class extends User {
            public function isSuperAdmin(): bool { return false; }
        };
        $user->forceFill(['id' => 1, 'access_tier' => 'titanium', 'level' => 'admin']);
        Auth::setUser($user);
    }

    private function gid(string $slug): int
    {
        return SmsMessageGroup::where('slug', $slug)->firstOrFail()->id;
    }

    private function sender(): int
    {
        return SenderNumber::create(['number' => '5000', 'type' => 'advertising', 'sms_panel' => 'IranSms', 'status' => 'active'])->id;
    }

    private function validateRequest(string $class, array $data): ?ValidationException
    {
        $request = $class::create('/x', 'POST', $data);
        $request->setContainer(app())->setRedirector(app('redirect'));
        try {
            $request->validateResolved();
        } catch (ValidationException $e) {
            return $e;
        }

        return null;
    }

    public function test_promotional_set_is_event_sales_support_with_subgroups(): void
    {
        $ids = SmsMessageGroup::promotional()->pluck('slug')->all();

        foreach (['monasebati', 'tabrik-monasebat', 'tatilat-va-jashn', 'foroush', 'peygiri-moshtari', 'poshtibani', 'adam-pasokhgooei-be-tamas'] as $slug) {
            $this->assertContains($slug, $ids);
        }
        foreach (['mali', 'variz-pish-pardakht', 'hoghoghi', 'ekhtar', 'ejraei', 'nobat-dehi'] as $slug) {
            $this->assertNotContains($slug, $ids);
        }
        $this->assertEqualsCanonicalizing(
            SmsMessageGroup::PROMOTIONAL_ROOT_SLUGS,
            SmsMessageGroup::promotionalRoots()->pluck('slug')->all()
        );
    }

    public function test_is_promotional_helpers(): void
    {
        $this->assertTrue(SmsMessageGroup::isPromotionalId(null));
        $this->assertTrue(SmsMessageGroup::isPromotionalId($this->gid('foroush')));
        $this->assertTrue(SmsMessageGroup::isPromotionalId($this->gid('tabrik-monasebat')));
        $this->assertFalse(SmsMessageGroup::isPromotionalId($this->gid('mali')));
        $this->assertFalse(SmsMessageGroup::isPromotionalId($this->gid('nobat-dehi')));
        $this->assertFalse(SmsMessageGroup::isPromotionalId(999999));
    }

    public function test_bulk_accepts_promotional_group_and_rejects_others(): void
    {
        $sender = $this->sender();
        $base = ['recipient_type' => 'advertising', 'message' => 'سلام', 'sender_number_id' => $sender];

        $this->assertNull($this->validateRequest(SendBulkSmsRequest::class, $base + ['sms_message_group_id' => $this->gid('monasebati')]));
        $this->assertNull($this->validateRequest(SendBulkSmsRequest::class, $base + ['sms_message_group_id' => $this->gid('peygiri-moshtari')]));
        $this->assertNull($this->validateRequest(SendBulkSmsRequest::class, $base));

        foreach (['mali', 'hoghoghi', 'ejraei', 'ekhtar'] as $slug) {
            $e = $this->validateRequest(SendBulkSmsRequest::class, $base + ['sms_message_group_id' => $this->gid($slug)]);
            $this->assertNotNull($e, "$slug must be rejected on bulk");
            $this->assertArrayHasKey('sms_message_group_id', $e->errors());
        }
    }

    public function test_single_advertising_accepts_promotional_and_rejects_others(): void
    {
        $base = ['recipient_phone' => '09123456789', 'message' => 'سلام', 'service_type' => 'advertising'];

        $this->assertNull($this->validateRequest(SendSingleSmsRequest::class, $base + ['sms_message_group_id' => $this->gid('poshtibani')]));

        foreach (['mali', 'hoghoghi', 'ejraei'] as $slug) {
            $e = $this->validateRequest(SendSingleSmsRequest::class, $base + ['sms_message_group_id' => $this->gid($slug)]);
            $this->assertNotNull($e, "$slug must be rejected on single advertising");
            $this->assertArrayHasKey('sms_message_group_id', $e->errors());
        }
        $normal = $this->validateRequest(SendSingleSmsRequest::class, ['service_type' => 'normal', 'sms_message_group_id' => $this->gid('mali')] + $base);
        $this->assertNotNull($normal);
    }

    public function test_single_otp_is_unaffected_by_the_promotional_restriction(): void
    {
        $e = $this->validateRequest(SendSingleSmsRequest::class, [
            'recipient_phone' => '09123456789',
            'message' => 'کد',
            'service_type' => 'otp',
            'otp_category' => 'authentication',
            'sms_message_group_id' => $this->gid('mali'),
        ]);

        $this->assertTrue($e === null || !array_key_exists('sms_message_group_id', $e->errors()));
    }

    public function test_legacy_group_references_do_not_break_models(): void
    {
        $legacy = SmsMessageGroup::where('slug', 'mali')->first();

        $this->assertFalse($legacy->isPromotional());
        $this->assertTrue($legacy->isConfidential());
    }
}

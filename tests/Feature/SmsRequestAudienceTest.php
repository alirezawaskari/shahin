<?php

namespace Tests\Feature;

use App\Models\Callcenter;
use App\Models\GhorfeOnlineList;
use App\Models\User;
use App\Services\Sms\BulkSmsRecipientFilterService;
use App\Services\Sms\SmsAccessTierService;
use App\Exceptions\Sms\TierAccessDeniedException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The group-send audience can be "the people behind the requests matching the requests-page
 * filter". These tests pin the business boundary, the opt-out exclusion and the phone fallback.
 */
class SmsRequestAudienceTest extends TestCase
{
    protected GhorfeOnlineList $bizA;
    protected GhorfeOnlineList $bizB;
    protected User $actor;
    protected BulkSmsRecipientFilterService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

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
            $t->unsignedInteger('admin_representation_id')->nullable();
            $t->unsignedInteger('center_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('ghorfe_online_lists', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->boolean('status')->default(true);
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('sms', function (Blueprint $t) {
            $t->increments('id');
            $t->string('sender_number')->nullable();
            $t->string('body')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('callcenters', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('sms_id')->nullable();
            $t->unsignedInteger('reg_id')->nullable();
            $t->unsignedInteger('user_id')->nullable();
            $t->unsignedInteger('agent_id')->nullable();
            $t->unsignedInteger('representation_id')->nullable();
            $t->integer('type')->default(0);
            $t->string('request_type')->nullable();
            $t->integer('status')->default(0);
            $t->text('description')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('callcenter_ghorfe_online_list', function (Blueprint $t) {
            $t->unsignedInteger('callcenter_id');
            $t->unsignedBigInteger('ghorfe_online_list_id');
        });
        foreach (['roles', 'permissions'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->increments('id');
                $t->string('name')->nullable();
                $t->string('display_name')->nullable();
                $t->timestamps();
            });
        }
        Schema::create('role_user', function (Blueprint $t) {
            $t->unsignedInteger('role_id');
            $t->unsignedInteger('user_id');
            $t->string('user_type');
        });
        Schema::create('permission_user', function (Blueprint $t) {
            $t->unsignedInteger('permission_id');
            $t->unsignedInteger('user_id');
            $t->string('user_type');
        });
        Schema::create('permission_role', function (Blueprint $t) {
            $t->unsignedInteger('permission_id');
            $t->unsignedInteger('role_id');
        });

        $this->bizA = GhorfeOnlineList::create(['title' => 'A']);
        $this->bizB = GhorfeOnlineList::create(['title' => 'B']);

        // A super admin sees every request the access scope allows, so the explicit business
        // pin is what these tests exercise.
        $this->actor = User::create(['name' => 'Admin', 'phone' => '09120000000', 'level' => 'superAdmin']);
        $perm = DB::table('permissions')->insertGetId(['name' => 'callcenter-detail']);
        DB::table('permission_user')->insert(['permission_id' => $perm, 'user_id' => $this->actor->id, 'user_type' => User::class]);
        $this->actingAs($this->actor);

        $this->service = app(BulkSmsRecipientFilterService::class);
    }

    private function request(GhorfeOnlineList $biz, array $attrs = [], ?User $registrant = null, ?string $smsFrom = null): Callcenter
    {
        $smsId = $smsFrom ? DB::table('sms')->insertGetId(['sender_number' => $smsFrom, 'body' => 'hi', 'created_at' => now(), 'updated_at' => now()]) : null;

        $request = Callcenter::create(array_merge(['type' => 0, 'status' => 0, 'reg_id' => $registrant?->id, 'sms_id' => $smsId], $attrs));
        DB::table('callcenter_ghorfe_online_list')->insert(['callcenter_id' => $request->id, 'ghorfe_online_list_id' => $biz->id]);

        return $request;
    }

    private function phones(array $criteria, ?GhorfeOnlineList $biz = null): array
    {
        return collect($this->service->requestCandidates($criteria, $this->actor, $biz ?? $this->bizA))->pluck('phone')->sort()->values()->all();
    }

    public function test_audience_is_pinned_to_the_sending_business(): void
    {
        $this->request($this->bizA, [], User::create(['name' => 'a', 'phone' => '09121111111']));
        $this->request($this->bizB, [], User::create(['name' => 'b', 'phone' => '09122222222']));

        $this->assertSame(['09121111111'], $this->phones([]));
        $this->assertSame(['09122222222'], $this->phones([], $this->bizB));
    }

    public function test_requests_that_opted_out_of_sms_are_never_messaged_even_if_asked_for(): void
    {
        $this->request($this->bizA, ['status' => 3], User::create(['name' => 'ok', 'phone' => '09121111111']));
        $this->request($this->bizA, ['status' => 11], User::create(['name' => 'optout', 'phone' => '09123333333']));

        $this->assertSame(['09121111111'], $this->phones([]));
        $this->assertSame([], $this->phones(['status' => ['11']]), 'explicitly selecting status 11 must still exclude opt-outs');
    }

    public function test_criteria_narrow_the_audience_and_phone_falls_back_to_the_incoming_sms_number(): void
    {
        $this->request($this->bizA, ['status' => 3], User::create(['name' => 'paid', 'phone' => '09121111111']));
        $this->request($this->bizA, ['status' => 0], User::create(['name' => 'new', 'phone' => '09124444444']));
        $this->request($this->bizA, ['status' => 3, 'type' => 1], null, '09125555555'); // anonymous SMS request

        $this->assertSame(['09121111111', '09125555555'], $this->phones(['status' => ['3']]));
        $this->assertSame(['09125555555'], $this->phones(['status' => ['3'], 'type' => ['1']]));
    }

    public function test_filter_recipients_uses_the_request_source_and_dedupes(): void
    {
        $user = User::create(['name' => 'dup', 'phone' => '09121111111']);
        $this->request($this->bizA, [], $user);
        $this->request($this->bizA, [], $user);
        // A business user who has no request must not be pulled in by the request source.
        User::create(['name' => 'bystander', 'phone' => '09126666666']);

        $result = $this->service->filterRecipients(['request_filter' => ['status' => ['0']]], $this->actor, $this->bizA);

        $this->assertSame(2, $result['total_candidates']);
        $this->assertSame(1, $result['valid_count']);
        $this->assertSame(1, $result['duplicate_count']);
        $this->assertSame('09121111111', $result['valid_recipients'][0]['phone']);
    }

    public function test_category_based_request_filters_need_diamond_like_user_filters(): void
    {
        $tier = app(SmsAccessTierService::class);

        $this->assertSame('silver', $tier->getRequiredTier(['request_filter' => ['status' => ['3'], 'startDate' => '2026-01-01']]));
        $this->assertSame('diamond', $tier->getRequiredTier(['request_filter' => ['product_category' => ['3']]]));
        $this->assertSame('silver', $tier->getRequiredTier(['request_filter' => ['product_category' => ['all'], 'service_items' => ['']]]));

        $silver = new User(['level' => 'user']);
        $silver->access_tier = 'silver';
        $this->expectException(TierAccessDeniedException::class);
        $tier->assertCanQuery($silver, ['request_filter' => ['service_category' => ['2']]]);
    }
}

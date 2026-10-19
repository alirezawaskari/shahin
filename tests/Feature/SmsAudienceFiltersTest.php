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
 * The audience-filter modal of the group-send page: product filters (category -> product ->
 * variant) narrow the people behind the requests, the representation and business filters are
 * gone, and the audience stays pinned to the business the campaign is sent from.
 */
class SmsAudienceFiltersTest extends TestCase
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
        Schema::create('employees', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->increments('id');
            $t->string('title')->nullable();
            $t->integer('type')->default(1);
            $t->integer('parent_id')->default(0);
            $t->integer('status')->default(1);
            $t->integer('price')->default(0);
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('services', function (Blueprint $t) {
            $t->increments('id');
            $t->string('title')->nullable();
            $t->integer('category_id')->nullable();
            $t->integer('status')->default(1);
            $t->integer('price')->default(0);
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('reasons', function (Blueprint $t) {
            $t->increments('id');
            $t->string('title')->nullable();
            $t->timestamps();
        });
        Schema::create('representations', function (Blueprint $t) {
            $t->increments('id');
            $t->string('fa_name')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('provinces', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('organizations', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('recruitment_notices', function (Blueprint $t) {
            $t->increments('id');
            $t->string('title')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('tasks', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->timestamps();
        });
        Schema::create('areas', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->timestamps();
        });
        Schema::create('centers', function (Blueprint $t) {
            $t->increments('id');
            $t->string('title')->nullable();
            $t->integer('status')->default(1);
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('factors', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('task_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('factor_items', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('factor_id')->nullable();
            $t->string('factoritemable_type')->nullable();
            $t->unsignedInteger('factoritemable_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('factor_item_products', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('factor_item_id')->nullable();
            $t->unsignedInteger('product_id')->nullable();
            $t->unsignedInteger('product_attribute_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
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

    private function request(GhorfeOnlineList $biz, string $phone, array $attrs = []): Callcenter
    {
        $request = Callcenter::create(array_merge(['type' => 0, 'status' => 0, 'reg_id' => User::create(['name' => 'u', 'phone' => $phone])->id], $attrs));
        DB::table('callcenter_ghorfe_online_list')->insert(['callcenter_id' => $request->id, 'ghorfe_online_list_id' => $biz->id]);

        return $request;
    }

    /** Invoice a product (and optionally one of its variants) on a request: factor -> factor item -> factor_item_products. */
    private function buy(Callcenter $request, int $productId, ?int $attributeId = null): void
    {
        $factorId = DB::table('factors')->insertGetId(['task_id' => $request->id, 'created_at' => now(), 'updated_at' => now()]);
        $itemId = DB::table('factor_items')->insertGetId(['factor_id' => $factorId, 'factoritemable_type' => \App\Models\Category::class, 'factoritemable_id' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('factor_item_products')->insert(['factor_item_id' => $itemId, 'product_id' => $productId, 'product_attribute_id' => $attributeId, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function phones(array $criteria, ?GhorfeOnlineList $biz = null): array
    {
        return collect($this->service->requestCandidates($criteria, $this->actor, $biz ?? $this->bizA))->pluck('phone')->sort()->values()->all();
    }

    public function test_product_filter_narrows_the_audience_to_buyers_of_those_products(): void
    {
        $this->buy($this->request($this->bizA, '09121111111'), 10);
        $this->buy($this->request($this->bizA, '09122222222'), 11);
        $this->request($this->bizA, '09123333333'); // no invoice at all

        $this->assertSame(['09121111111'], $this->phones(['product' => ['10']]));
        $this->assertSame(['09121111111', '09122222222'], $this->phones(['product' => ['10', '11']]));
        $this->assertSame([], $this->phones(['product' => ['99']]));
        $this->assertCount(3, $this->phones(['product' => ['all'], 'product_attribute' => ['']]), 'placeholders mean no filter');
    }

    public function test_product_variant_filter_narrows_further(): void
    {
        $this->buy($this->request($this->bizA, '09121111111'), 10, 100);
        $this->buy($this->request($this->bizA, '09122222222'), 10, 101);

        $this->assertSame(['09121111111'], $this->phones(['product' => ['10'], 'product_attribute' => ['100']]));
        $this->assertSame(['09122222222'], $this->phones(['product_attribute' => ['101']]));
        $this->assertSame([], $this->phones(['product' => ['11'], 'product_attribute' => ['100']]));
    }

    public function test_product_filter_never_leaves_the_sending_business(): void
    {
        $this->buy($this->request($this->bizA, '09121111111'), 10);
        $this->buy($this->request($this->bizB, '09129999999'), 10);

        $this->assertSame(['09121111111'], $this->phones(['product' => ['10']]));
        $this->assertSame(['09129999999'], $this->phones(['product' => ['10']], $this->bizB));
    }

    public function test_product_filters_need_diamond(): void
    {
        $tier = app(SmsAccessTierService::class);

        $this->assertSame('diamond', $tier->getRequiredTier(['request_filter' => ['product' => ['10']]]));
        $this->assertSame('diamond', $tier->getRequiredTier(['request_filter' => ['product_attribute' => ['100']]]));

        $gold = new User(['level' => 'user']);
        $gold->access_tier = 'gold';
        $this->expectException(TierAccessDeniedException::class);
        $tier->assertCanQuery($gold, ['request_filter' => ['product' => ['10']]]);
    }

    public function test_representation_param_is_ignored_by_the_audience_picker(): void
    {
        $this->request($this->bizA, '09121111111', ['representation_id' => 5]);
        $this->request($this->bizA, '09122222222', ['representation_id' => 6]);

        $this->assertSame(['09121111111', '09122222222'], $this->phones(['representation' => '5']));

        // No tier is tied to it for the picker any more
        $this->assertSame('silver', app(SmsAccessTierService::class)->getRequiredTier(['request_filter' => ['representation' => '5']]));
    }

    public function test_business_params_cannot_move_the_audience_to_another_business(): void
    {
        $this->request($this->bizA, '09121111111');
        $this->request($this->bizB, '09122222222');

        $this->assertSame(['09121111111'], $this->phones(['ghorfe_online_list' => (string) $this->bizB->id]));
        $this->assertSame(['09121111111'], $this->phones(['business' => (string) $this->bizB->id, 'ghorfe_online_list_id' => (string) $this->bizB->id]));

        $result = $this->service->filterRecipients(['request_filter' => ['ghorfe_online_list' => (string) $this->bizB->id]], $this->actor, $this->bizA);
        $this->assertSame(['09121111111'], array_column($result['valid_recipients'], 'phone'));
    }

    public function test_filter_modal_offers_products_but_not_representation_or_business(): void
    {
        $modal = file_get_contents(resource_path('views/Admin/callcenter/partials/filter-modal.blade.php'));
        $fields = file_get_contents(resource_path('views/Admin/callcenter/partials/filter-fields.blade.php'));

        $this->assertStringContainsString("'withProducts' => true", $modal);
        $this->assertStringContainsString("'withRepresentation' => false", $modal);
        $this->assertStringContainsString("'withBusinessPicker' => false", $modal);
        $this->assertStringContainsString('name="product[]"', $fields);
        $this->assertStringContainsString('name="product_attribute[]"', $fields);
        // The requests page keeps its own representation filter and business picker by default.
        $this->assertStringContainsString('@if($withRepresentation ?? true)', $fields);
        $this->assertStringContainsString('@if($withBusinessPicker ?? true)', $fields);
        $this->assertStringContainsString('@if($withProducts ?? false)', $fields);

        $picker = file_get_contents(public_path('assets/dashboard/js/requestFilterPicker.js'));
        $this->assertStringNotContainsString("'representation'", $picker);
        $this->assertStringNotContainsString('ghorfe_online_id', $picker);
        $this->assertStringContainsString('/dashboard/callcenter/productsByCategory', $picker);
    }
}

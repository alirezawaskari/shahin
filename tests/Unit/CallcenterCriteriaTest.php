<?php

namespace Tests\Unit;

use App\Models\Callcenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

/**
 * Locks in the requests-page filter fixes. Builds SQL only (no rows needed),
 * which is enough to prove how criteria combine.
 */
class CallcenterCriteriaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

        Schema::create('services', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('category_id')->nullable();
            $t->softDeletes();
        });
        DB::table('services')->insert([['id' => 10, 'category_id' => 1], ['id' => 11, 'category_id' => 1], ['id' => 12, 'category_id' => 2]]);
    }

    private function sql(array $criteria): array
    {
        $q = Callcenter::query()->applyCriteria($criteria);

        return [$q->toSql(), $q->getBindings()];
    }

    public function test_array_category_inputs_no_longer_crash_and_filter_by_all_values(): void
    {
        [$sql, $b] = $this->sql([
            'product_category' => ['3', '4'],
            'service_category' => ['1'],
            'sub_service_category' => ['all'],
            'service_items' => ['7'],
        ]);

        $this->assertStringContainsString('"factoritemable_id" in (?, ?)', $sql);
        $this->assertContains('3', $b);
        $this->assertContains('4', $b);
        // service_category -> its services (10, 11), since no sub service is picked
        $this->assertContains(10, $b);
        $this->assertContains(11, $b);
        $this->assertNotContains(12, $b);
    }

    public function test_category_or_is_grouped_so_it_cannot_escape_other_filters(): void
    {
        [$sql] = $this->sql(['status' => ['3'], 'product_category' => ['3']]);

        // status AND (factors... OR categories...) — the OR must sit inside one group.
        $this->assertMatchesRegularExpression('/"status" in \(\?\) and \(exists .* or exists .*\) and "callcenters"\."deleted_at" is null$/s', trim($sql));
    }

    public function test_description_search_keeps_other_criteria_ungrouped_from_its_or(): void
    {
        [$sql] = $this->sql(['description' => 'x', 'status' => ['3']]);

        $this->assertMatchesRegularExpression('/^select \* from "callcenters" where \("description" LIKE \? or exists/s', $sql);
        $this->assertStringContainsString('"status" in (?)', $sql);
    }

    public function test_phone_with_multiple_types_is_or_across_register_and_sms(): void
    {
        [$sql, $b] = $this->sql(['user_phone' => '0912', 'type' => ['0', '1']]);

        $this->assertStringContainsString('"type" in (?, ?)', $sql);
        $this->assertMatchesRegularExpression('/\(exists \(.*"phone" = \?.*\) or exists \(.*"sender_number" = \?.*\)\)/s', $sql);
        $this->assertSame(['0', '1', '0912', '0912'], array_values(array_filter($b, fn ($v) => !is_null($v))));
    }

    public function test_single_date_bound_is_applied_on_its_own(): void
    {
        [$sqlFrom] = $this->sql(['startDate' => '2026-01-01']);
        [$sqlTo] = $this->sql(['endDate' => '2026-02-01']);

        $this->assertStringContainsString('>= cast(? as text)', $sqlFrom);
        $this->assertStringNotContainsString('<=', $sqlFrom);
        $this->assertStringContainsString('<= cast(? as text)', $sqlTo);
    }

    public function test_placeholder_and_empty_values_mean_no_filter(): void
    {
        [$sql, $b] = $this->sql([
            'province' => 'all', 'city' => 'all', 'sex' => 'all', 'representation' => 'all',
            'request_type' => 'all', 'status' => [''], 'name' => '  ', 'product_category' => [''],
            'sub_service_category' => ['all'], 'service_category' => ['all'],
        ]);

        $this->assertSame('select * from "callcenters" where "callcenters"."deleted_at" is null', $sql);
        $this->assertSame([], $b);
    }

    public function test_city_accepts_json_and_scalar_forms(): void
    {
        [, $b1] = $this->sql(['city' => '5']);
        [, $b2] = $this->sql(['city' => '[5,6]']);

        $this->assertSame([5], array_values($b1));
        $this->assertSame([5, 6], array_values($b2));
    }

    public function test_center_representation_means_no_representation(): void
    {
        [$sql] = $this->sql(['representation' => 'center']);

        $this->assertStringContainsString('"representation_id" is null', $sql);
    }
}

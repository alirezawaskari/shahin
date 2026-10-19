<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Service;
use App\Services\Sms\CatalogBreadcrumbResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmsCatalogBreadcrumbResolverTest extends TestCase
{
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

        Schema::create('categories', function (Blueprint $t) {
            $t->increments('id');
            $t->string('title');
            $t->unsignedInteger('parent_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('services', function (Blueprint $t) {
            $t->increments('id');
            $t->string('title');
            $t->unsignedInteger('category_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('callcenterables', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('callcenter_id');
            $t->unsignedBigInteger('callcenterable_id');
            $t->string('callcenterable_type');
            $t->timestamps();
        });

        DB::table('categories')->insert([
            ['id' => 2, 'title' => 'Services', 'parent_id' => null],
            ['id' => 10, 'title' => 'Repairs', 'parent_id' => 2],
        ]);
        DB::table('services')->insert([['id' => 100, 'title' => 'Boiler Repair', 'category_id' => 10]]);
        DB::table('callcenterables')->insert([
            ['callcenter_id' => 1, 'callcenterable_id' => 100, 'callcenterable_type' => Service::class],
            ['callcenter_id' => 2, 'callcenterable_id' => 10, 'callcenterable_type' => Category::class],
        ]);
    }

    public function test_service_breadcrumb_walks_root_to_leaf(): void
    {
        $this->assertSame([100 => 'Services > Repairs > Boiler Repair'], (new CatalogBreadcrumbResolver())->forServices([100]));
    }

    public function test_category_breadcrumb_and_missing_ids_fall_back_to_hash_id(): void
    {
        $out = (new CatalogBreadcrumbResolver())->forCategories([10, 999]);

        $this->assertSame('Services > Repairs', $out[10]);
        $this->assertSame('#999', $out[999]);
    }

    public function test_request_resolves_via_its_service_then_category_then_fallback(): void
    {
        $out = (new CatalogBreadcrumbResolver())->forRequests([1, 2, 3]);

        $this->assertSame('Services > Repairs > Boiler Repair', $out[1]);
        $this->assertSame('Services > Repairs', $out[2]);
        $this->assertSame('#3', $out[3]);
        $this->assertNull((new CatalogBreadcrumbResolver())->forRequest(null));
    }

    public function test_batch_lookup_does_not_query_per_id(): void
    {
        DB::enableQueryLog();
        (new CatalogBreadcrumbResolver())->forRequests([1, 2, 3, 4, 5]);
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        // links + services + categories (one query per tree level): bounded, independent of id count
        $this->assertLessThanOrEqual(6, $count);
    }

    public function test_annotate_messages_sets_breadcrumb_without_persisting(): void
    {
        $messages = collect([
            (object) ['callcenter_id' => 1, 'request_id' => null],
            (object) ['callcenter_id' => null, 'request_id' => null],
        ])->map(fn ($o) => new class($o) extends \Illuminate\Database\Eloquent\Model {
            public function __construct($o = null)
            {
                parent::__construct();
                if ($o) {
                    $this->setRawAttributes((array) $o);
                }
            }
        });

        (new CatalogBreadcrumbResolver())->annotateMessages($messages);

        $this->assertSame('Services > Repairs > Boiler Repair', $messages[0]->request_breadcrumb);
        $this->assertNull($messages[1]->request_breadcrumb);
    }
}

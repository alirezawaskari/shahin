<?php

namespace Tests\Feature;

use Database\Seeders\SmsMessageGroupSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * 2026_10_22_000002_merge_duplicate_sms_groups_and_templates: exact duplicates inside ONE business are merged
 * (references repointed first); other businesses and rows that differ in content are never touched.
 */
class SmsDuplicateCleanupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

        Schema::create('sms_message_groups', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('parent_id')->nullable();
            $t->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $t->string('name');
            $t->string('slug');
            $t->timestamps();
        });
        Schema::create('sms_message_templates', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('content');
            $t->unsignedBigInteger('sms_message_group_id')->nullable();
            $t->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $t->string('type')->default('normal');
            $t->string('service_type')->default('normal');
            $t->string('status')->default('active');
            $t->string('otp_category')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('sms_messages', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('sms_message_group_id')->nullable();
            $t->unsignedBigInteger('sms_message_template_id')->nullable();
        });
    }

    private function migrate(): void
    {
        (require base_path('database/migrations/2026_10_22_000002_merge_duplicate_sms_groups_and_templates.php'))->up();
    }

    private function group(string $name, string $slug, ?int $biz, ?int $parent = null, string $at = '2026-01-01 00:00:00'): int
    {
        return DB::table('sms_message_groups')->insertGetId([
            'name' => $name, 'slug' => $slug, 'ghorfe_online_list_id' => $biz, 'parent_id' => $parent,
            'created_at' => $at, 'updated_at' => $at,
        ]);
    }

    private function template(string $title, string $content, ?int $biz, ?int $group = null, string $at = '2026-01-01 00:00:00'): int
    {
        return DB::table('sms_message_templates')->insertGetId([
            'title' => $title, 'content' => $content, 'ghorfe_online_list_id' => $biz, 'sms_message_group_id' => $group,
            'created_at' => $at, 'updated_at' => $at,
        ]);
    }

    public function test_merges_same_business_duplicate_groups_and_repoints_references(): void
    {
        $keep = $this->group('اجرایی', 'ejraei', 1, null, '2026-01-01 00:00:00');
        $keepSub = $this->group('نوبت‌دهی', 'nobat-dehi', 1, $keep);
        $dup = $this->group('اجرایی', 'ejraei-1', 1, null, '2026-02-01 00:00:00');
        $dupSub = $this->group('نوبتدهی', 'nobat-dehi', 1, $dup, '2026-02-01 00:00:00');
        $other = $this->group('اجرایی', 'ejraei', 2); // other business: untouched
        $tpl = $this->template('x', 'y', 1, $dupSub);
        DB::table('sms_messages')->insert(['sms_message_group_id' => $dupSub, 'sms_message_template_id' => null]);

        $this->migrate();

        $this->assertNull(DB::table('sms_message_groups')->find($dup));
        $this->assertNull(DB::table('sms_message_groups')->find($dupSub));
        $this->assertNotNull(DB::table('sms_message_groups')->find($keep));
        $this->assertNotNull(DB::table('sms_message_groups')->find($keepSub));
        $this->assertNotNull(DB::table('sms_message_groups')->find($other));
        $this->assertSame($keepSub, (int) DB::table('sms_message_templates')->find($tpl)->sms_message_group_id);
        $this->assertSame($keepSub, (int) DB::table('sms_messages')->value('sms_message_group_id'));
    }

    public function test_merges_exact_duplicate_templates_but_keeps_different_ones(): void
    {
        $a = $this->template('تبریک نوروز', 'متن', 1, null, '2026-01-01 00:00:00');
        $b = $this->template('تبریک نوروز', 'متن', 1, null, '2026-02-01 00:00:00');   // exact duplicate
        $c = $this->template('تبریک نوروز', 'متن دیگر', 1);                           // different content
        $d = $this->template('تبریک نوروز', 'متن', 2);                                 // other business
        DB::table('sms_messages')->insert(['sms_message_group_id' => null, 'sms_message_template_id' => $b]);

        $this->migrate();

        $this->assertNull(DB::table('sms_message_templates')->find($a)->deleted_at);
        $this->assertNotNull(DB::table('sms_message_templates')->find($b)->deleted_at);
        $this->assertNull(DB::table('sms_message_templates')->find($c)->deleted_at);
        $this->assertNull(DB::table('sms_message_templates')->find($d)->deleted_at);
        $this->assertSame($a, (int) DB::table('sms_messages')->value('sms_message_template_id'));
    }

    public function test_is_idempotent_and_canonicalizes_old_spelling(): void
    {
        $this->group('اجرایی', 'ejraei', 1);
        $sub = $this->group('نوبتدهی', 'nobat-dehi', 1, 1);
        $this->template('t', 'c', 1);
        $this->template('t', 'c', 1);

        $this->migrate();
        $groups = DB::table('sms_message_groups')->count();
        $live = DB::table('sms_message_templates')->whereNull('deleted_at')->count();
        $this->migrate();

        $this->assertSame($groups, DB::table('sms_message_groups')->count());
        $this->assertSame($live, DB::table('sms_message_templates')->whereNull('deleted_at')->count());
        $this->assertSame(1, $live);
        $this->assertSame('نوبت‌دهی', DB::table('sms_message_groups')->find($sub)->name);
    }

    public function test_seeder_uses_canonical_spelling_and_is_idempotent(): void
    {
        (new SmsMessageGroupSeeder())->run();
        $count = DB::table('sms_message_groups')->count();
        (new SmsMessageGroupSeeder())->run();

        $this->assertSame($count, DB::table('sms_message_groups')->count());
        $this->assertSame('نوبت‌دهی', DB::table('sms_message_groups')->where('slug', 'nobat-dehi')->value('name'));
        $this->assertSame('واریز پیش‌پرداخت', DB::table('sms_message_groups')->where('slug', 'variz-pish-pardakht')->value('name'));
    }
}

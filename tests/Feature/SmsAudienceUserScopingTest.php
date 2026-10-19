<?php

namespace Tests\Feature;

use App\Models\GhorfeOnlineList;
use App\Models\User;
use App\Providers\FilterServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The SMS audience pop-up passes the sending business to the requests-filter partial: its
 * کارشناس / بازاریاب lists then only contain that business's own users (pivot or employee).
 */
class SmsAudienceUserScopingTest extends TestCase
{
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
            $t->string('level')->default('admin');
            $t->string('token')->nullable();
            $t->string('api_token')->nullable();
            $t->string('avatar')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('ghorfe_online_lists', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('ghorfe_online_list_user', function (Blueprint $t) {
            $t->unsignedInteger('user_id');
            $t->unsignedBigInteger('ghorfe_online_list_id');
        });
        Schema::create('employees', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
    }

    public function test_only_users_of_the_sending_business_are_offered(): void
    {
        $a = GhorfeOnlineList::create(['title' => 'الف']);
        $b = GhorfeOnlineList::create(['title' => 'ب']);
        $ownerA = User::create(['name' => 'مالک', 'family' => 'الف']);
        $ownerB = User::create(['name' => 'مالک', 'family' => 'ب']);
        $employeeA = User::create(['name' => 'کارمند', 'family' => 'الف']);
        $nobody = User::create(['name' => 'بدون', 'family' => 'کسب‌وکار']);
        $a->users()->attach($ownerA->id);
        $b->users()->attach($ownerB->id);
        DB::table('employees')->insert(['user_id' => $employeeA->id, 'ghorfe_online_list_id' => $a->id]);

        $all = User::all();
        $forA = FilterServiceProvider::onlyBusinessMembers($all, $a)->pluck('id')->all();
        $forB = FilterServiceProvider::onlyBusinessMembers($all, $b)->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$ownerA->id, $employeeA->id], $forA);
        $this->assertSame([$ownerB->id], $forB);
        $this->assertNotContains($ownerB->id, $forA);
        $this->assertNotContains($nobody->id, $forA);
    }
}

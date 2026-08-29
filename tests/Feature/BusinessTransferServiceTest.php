<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\EmployeeController;
use App\Models\Employee;
use App\Models\User;
use App\Services\Admin\BusinessTransferService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BusinessTransferServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // This suite must never inherit the project's development MySQL connection.
        // Build only the ownership tables needed by the service in an isolated process-local DB.
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

        Schema::create('ghorfe_online_lists', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('factors', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('factor_ghorfe_online_list', function (Blueprint $table) {
            $table->unsignedBigInteger('factor_id');
            $table->unsignedBigInteger('ghorfe_online_list_id');
            $table->primary(['factor_id', 'ghorfe_online_list_id']);
        });
        Schema::create('factor_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factor_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->nullable();
            $table->string('level')->nullable();
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('ghorfe_online_list_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('category_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_name');
            $table->string('description');
            $table->string('link')->nullable();
            $table->boolean('isMenu')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('cat_id');
            $table->timestamps();
        });
        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('permission_user', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('user_id');
        });

        DB::table('ghorfe_online_lists')->insert([
            ['id' => 1, 'title' => 'Source', 'status' => 1],
            ['id' => 2, 'title' => 'Destination', 'status' => 1],
            ['id' => 3, 'title' => 'Inactive', 'status' => 0],
            ['id' => 4, 'title' => 'Other Destination', 'status' => 1],
        ]);
        DB::table('category_permissions')->insert([
            'id' => 25,
            'name' => 'مغازه دیجیتال',
            'is_active' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_it_replaces_every_factor_business_in_one_bulk_operation(): void
    {
        DB::table('factors')->insert([['id' => 10], ['id' => 11]]);
        DB::table('factor_ghorfe_online_list')->insert([
            ['factor_id' => 10, 'ghorfe_online_list_id' => 1],
            ['factor_id' => 11, 'ghorfe_online_list_id' => 1],
        ]);

        $result = app(BusinessTransferService::class)->transfer('factor', [10, 11], 2, 1);

        $this->assertSame(2, $result['count']);
        $this->assertSame(2, $result['destination']->id);
        $this->assertSame(
            [[10, 2], [11, 2]],
            DB::table('factor_ghorfe_online_list')
                ->orderBy('factor_id')
                ->get(['factor_id', 'ghorfe_online_list_id'])
                ->map(fn ($row) => [(int) $row->factor_id, (int) $row->ghorfe_online_list_id])
                ->all()
        );
    }

    public function test_invalid_selected_id_leaves_the_entire_batch_unchanged(): void
    {
        DB::table('factors')->insert(['id' => 10]);
        DB::table('factor_ghorfe_online_list')->insert([
            'factor_id' => 10,
            'ghorfe_online_list_id' => 1,
        ]);

        try {
            app(BusinessTransferService::class)->transfer('factor', [10, 999], 2);
            $this->fail('A missing source record should fail the whole transfer.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('ids', $exception->errors());
        }

        $this->assertDatabaseHas('factor_ghorfe_online_list', [
            'factor_id' => 10,
            'ghorfe_online_list_id' => 1,
        ]);
        $this->assertDatabaseMissing('factor_ghorfe_online_list', [
            'factor_id' => 10,
            'ghorfe_online_list_id' => 2,
        ]);
    }

    public function test_critical_write_failure_rolls_back_every_record(): void
    {
        DB::table('factors')->insert([['id' => 10], ['id' => 11]]);
        DB::table('factor_ghorfe_online_list')->insert([
            ['factor_id' => 10, 'ghorfe_online_list_id' => 1],
            ['factor_id' => 11, 'ghorfe_online_list_id' => 1],
        ]);
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER reject_second_factor_transfer
            BEFORE INSERT ON factor_ghorfe_online_list
            WHEN NEW.factor_id = 11 AND NEW.ghorfe_online_list_id = 2
            BEGIN
                SELECT RAISE(ABORT, 'forced write failure');
            END
        SQL);

        try {
            app(BusinessTransferService::class)->transfer('factor', [10, 11], 2, 1);
            $this->fail('The forced write failure should abort the transfer.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }

        $this->assertSame(
            [[10, 1], [11, 1]],
            DB::table('factor_ghorfe_online_list')
                ->orderBy('factor_id')
                ->get(['factor_id', 'ghorfe_online_list_id'])
                ->map(fn ($row) => [(int) $row->factor_id, (int) $row->ghorfe_online_list_id])
                ->all()
        );
    }

    public function test_inactive_and_current_businesses_are_rejected_as_destinations(): void
    {
        DB::table('factors')->insert(['id' => 10]);
        DB::table('factor_ghorfe_online_list')->insert([
            'factor_id' => 10,
            'ghorfe_online_list_id' => 1,
        ]);

        foreach ([3, 1] as $destinationId) {
            try {
                app(BusinessTransferService::class)->transfer('factor', [10], $destinationId, 1);
                $this->fail('The destination should have been rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('destination_business_id', $exception->errors());
            }
        }

        $this->assertSame(
            [1],
            DB::table('factor_ghorfe_online_list')->where('factor_id', 10)
                ->pluck('ghorfe_online_list_id')->map(fn ($id) => (int) $id)->all()
        );
    }

    public function test_business_actor_cannot_move_another_business_record(): void
    {
        DB::table('factors')->insert(['id' => 10]);
        DB::table('factor_ghorfe_online_list')->insert([
            'factor_id' => 10,
            'ghorfe_online_list_id' => 2,
        ]);

        $this->expectException(AuthorizationException::class);

        app(BusinessTransferService::class)->transfer('factor', [10], 1, 1);
    }

    public function test_business_actor_cannot_strip_a_shared_record_from_other_owners(): void
    {
        DB::table('factors')->insert(['id' => 10]);
        DB::table('factor_ghorfe_online_list')->insert([
            ['factor_id' => 10, 'ghorfe_online_list_id' => 1],
            ['factor_id' => 10, 'ghorfe_online_list_id' => 2],
        ]);

        try {
            app(BusinessTransferService::class)->transfer('factor', [10], 4, 1);
            $this->fail('A tenant actor must not replace another business owner.');
        } catch (AuthorizationException) {
            $this->assertTrue(true);
        }

        $this->assertSame(
            [1, 2],
            DB::table('factor_ghorfe_online_list')->where('factor_id', 10)
                ->orderBy('ghorfe_online_list_id')
                ->pluck('ghorfe_online_list_id')->map(fn ($id) => (int) $id)->all()
        );
    }

    public function test_factor_item_transfer_changes_its_inherited_factor_association(): void
    {
        DB::table('factors')->insert(['id' => 10]);
        DB::table('factor_items')->insert([['id' => 20, 'factor_id' => 10]]);
        DB::table('factor_ghorfe_online_list')->insert([
            'factor_id' => 10,
            'ghorfe_online_list_id' => 1,
        ]);

        $result = app(BusinessTransferService::class)->transfer('factor_item', [20], 2, 1);

        $this->assertSame(1, $result['count']);
        $this->assertDatabaseHas('factor_ghorfe_online_list', [
            'factor_id' => 10,
            'ghorfe_online_list_id' => 2,
        ]);
        $this->assertDatabaseMissing('factor_ghorfe_online_list', [
            'factor_id' => 10,
            'ghorfe_online_list_id' => 1,
        ]);
    }

    public function test_factor_item_transfer_rejects_an_unselected_sibling_case(): void
    {
        DB::table('factors')->insert(['id' => 10]);
        DB::table('factor_items')->insert([
            ['id' => 20, 'factor_id' => 10],
            ['id' => 21, 'factor_id' => 10],
        ]);
        DB::table('factor_ghorfe_online_list')->insert([
            'factor_id' => 10,
            'ghorfe_online_list_id' => 1,
        ]);

        try {
            app(BusinessTransferService::class)->transfer('factor_item', [20], 2, 1);
            $this->fail('A partial factor-item selection must not move unselected siblings.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('ids', $exception->errors());
        }

        $this->assertDatabaseHas('factor_ghorfe_online_list', [
            'factor_id' => 10,
            'ghorfe_online_list_id' => 1,
        ]);

        $result = app(BusinessTransferService::class)->transfer('factor_item', [20, 21], 2, 1);
        $this->assertSame(2, $result['count']);
        $this->assertDatabaseHas('factor_ghorfe_online_list', [
            'factor_id' => 10,
            'ghorfe_online_list_id' => 2,
        ]);
    }

    public function test_employee_transfer_updates_only_the_business_foreign_key(): void
    {
        DB::table('employees')->insert([
            'id' => 30,
            'ghorfe_online_list_id' => 1,
        ]);

        $result = app(BusinessTransferService::class)->transfer('employee', [30], 2, 1);

        $this->assertSame(1, $result['count']);
        $this->assertDatabaseHas('employees', [
            'id' => 30,
            'ghorfe_online_list_id' => 2,
        ]);
    }

    public function test_employee_update_endpoint_cannot_bypass_transfer_permission(): void
    {
        DB::table('employees')->insert([
            'id' => 30,
            'ghorfe_online_list_id' => 1,
        ]);

        $actor = $this->getMockBuilder(User::class)
            ->onlyMethods(['hasPermission'])
            ->getMock();
        $actor->expects($this->once())
            ->method('hasPermission')
            ->with('transfer_to_business')
            ->willReturn(false);
        $request = Request::create('/dashboard/employee/30', 'PATCH', [
            'ghorfe_online_list_id' => 2,
        ]);
        $request->setUserResolver(fn () => $actor);

        try {
            app(EmployeeController::class)->update($request, Employee::findOrFail(30));
            $this->fail('The general update endpoint must not move an employee.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $this->assertDatabaseHas('employees', [
            'id' => 30,
            'ghorfe_online_list_id' => 1,
        ]);
    }

    public function test_permission_migration_is_idempotent_and_cleans_up_assignments(): void
    {
        $migration = require database_path(
            'migrations/2026_08_24_120000_add_transfer_to_business_permission.php'
        );

        $migration->up();
        $migration->up();

        $permissionId = DB::table('permissions')
            ->where('name', 'transfer_to_business')
            ->value('id');
        $this->assertNotNull($permissionId);
        $this->assertSame(
            1,
            DB::table('permissions')->where('name', 'transfer_to_business')->count()
        );
        $this->assertDatabaseHas('permissions', [
            'id' => $permissionId,
            'cat_id' => 25,
            'isMenu' => 0,
            'is_active' => 1,
        ]);

        DB::table('permission_role')->insert(['permission_id' => $permissionId, 'role_id' => 7]);
        DB::table('permission_user')->insert(['permission_id' => $permissionId, 'user_id' => 8]);

        $migration->down();

        $this->assertDatabaseMissing('permissions', ['id' => $permissionId]);
        $this->assertDatabaseMissing('permission_role', ['permission_id' => $permissionId]);
        $this->assertDatabaseMissing('permission_user', ['permission_id' => $permissionId]);
    }
}

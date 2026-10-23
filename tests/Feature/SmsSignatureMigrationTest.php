<?php

namespace Tests\Feature;

use App\Services\SmsSettings\SmsSignatureRecord;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The legacy-signature migration, run against a throwaway in-memory SQLite copy of the four tables
 * it reads. The real database is never touched by this test.
 */
class SmsSignatureMigrationTest extends TestCase
{
    private string $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = DB::getDefaultConnection();

        config()->set('database.connections.migration_sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('migration_sqlite');
        DB::setDefaultConnection('migration_sqlite');

        Schema::create('users', fn (Blueprint $t) => $t->increments('id'));
        Schema::create('ghorfe_online_lists', fn (Blueprint $t) => $t->increments('id'));
        Schema::create('ghorfe_settings', function (Blueprint $t) {
            $t->increments('id');
            $t->text('value')->nullable();
            $t->string('key');
            $t->string('settingable_type')->nullable();
            $t->unsignedBigInteger('settingable_id')->nullable();
            $t->timestamps();
        });
        Schema::create('histories', function (Blueprint $t) {
            $t->increments('id');
            $t->string('historiable_type');
            $t->unsignedBigInteger('historiable_id');
            $t->unsignedInteger('user_id')->nullable();
            $t->unsignedInteger('callcenter_id')->nullable();
            $t->integer('status');
            $t->integer('reason_id')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });

        $type = 'App\\Models\\GhorfeOnlineList';

        DB::table('users')->insert(['id' => 1]);
        DB::table('ghorfe_online_lists')->insert([['id' => 1], ['id' => 2], ['id' => 3]]);

        // Business 1: a real legacy signing. Business 2: no settings at all (status-only in the real world).
        // Business 3: the flag is off, so it is not a signing.
        DB::table('ghorfe_settings')->insert([
            ['key' => 'sms_contract_accepted', 'value' => '1', 'settingable_type' => $type, 'settingable_id' => 1],
            ['key' => 'sms_contract_signature_name', 'value' => 'علی', 'settingable_type' => $type, 'settingable_id' => 1],
            ['key' => 'sms_contract_signed_phone', 'value' => '09120000000', 'settingable_type' => $type, 'settingable_id' => 1],
            ['key' => 'sms_contract_text_hash', 'value' => 'h1', 'settingable_type' => $type, 'settingable_id' => 1],
            ['key' => 'sms_contract_otp_verified_at', 'value' => '2026-10-01 08:00:00', 'settingable_type' => $type, 'settingable_id' => 1],
            ['key' => 'sms_contract_accepted_by', 'value' => '1', 'settingable_type' => $type, 'settingable_id' => 1],
            ['key' => 'sms_contract_accepted', 'value' => '0', 'settingable_type' => $type, 'settingable_id' => 3],
            ['key' => 'sms_contract_signature_name', 'value' => 'نباید منتقل شود', 'settingable_type' => $type, 'settingable_id' => 3],
        ]);
    }

    protected function tearDown(): void
    {
        DB::setDefaultConnection($this->originalConnection);

        parent::tearDown();
    }

    private function migration(): object
    {
        return require base_path('database/migrations/2026_10_23_100000_move_sms_signatures_to_central_history.php');
    }

    public function test_a_real_signing_is_copied_once_and_nothing_else_is_invented(): void
    {
        $migration = $this->migration();
        $migration->up();
        $migration->up();

        $lines = DB::table('histories')->get();
        $this->assertCount(1, $lines, 'one line, however many times the migration runs');

        $line = $lines->first();
        $this->assertSame('App\\Models\\GhorfeOnlineList', $line->historiable_type);
        $this->assertSame(1, (int) $line->historiable_id);
        $this->assertSame(1, (int) $line->user_id);
        $this->assertSame(1, (int) $line->reason_id);
        $this->assertSame(1, (int) $line->status);
        $this->assertStringContainsString(SmsSignatureRecord::MIGRATED, $line->description);
        $this->assertSame(
            ['name' => 'علی', 'phone' => '09120000000', 'verified_at' => '2026-10-01 08:00:00', 'hash' => 'h1'],
            SmsSignatureRecord::parse($line->description)
        );

        // Source settings are read, never deleted.
        $this->assertSame(8, DB::table('ghorfe_settings')->count());
    }

    public function test_down_removes_only_the_lines_the_migration_wrote(): void
    {
        $migration = $this->migration();
        $migration->up();

        DB::table('histories')->insert([
            'historiable_type' => 'App\\Models\\GhorfeOnlineList',
            'historiable_id' => 2,
            'user_id' => 1,
            'status' => 1,
            'reason_id' => 1,
            'description' => SmsSignatureRecord::describe('امضای زنده', '', '', ''),
        ]);

        $migration->down();

        $remaining = DB::table('histories')->get();
        $this->assertCount(1, $remaining);
        $this->assertSame(2, (int) $remaining->first()->historiable_id, 'a line written live is kept');
        $this->assertSame(8, DB::table('ghorfe_settings')->count());
    }
}

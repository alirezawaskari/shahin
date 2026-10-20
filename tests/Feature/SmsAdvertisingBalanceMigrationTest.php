<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class SmsAdvertisingBalanceMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!file_exists(base_path('database/migrations/2026_10_15_000001_make_advertising_balance_per_business.php'))) {
            $this->markTestSkipped('advertising_balances table was removed in favor of authentic ghorfe_online_lists.advertising_balance.');
        }

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
        });

        foreach ([
            'database/migrations/2026_10_07_000001_create_sender_numbers_table.php',
            'database/migrations/2026_10_07_000007_create_advertising_balances_table.php',
        ] as $file) {
            (require base_path($file))->up();
        }
    }

    private function migration()
    {
        return require base_path('database/migrations/2026_10_15_000001_make_advertising_balance_per_business.php');
    }

    private function seedLine(int $id): void
    {
        DB::table('sender_numbers')->insert([
            'id' => $id, 'number' => (string) (5000 + $id), 'type' => 'advertising', 'sms_panel' => 'IranSms',
            'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function seedBalance(int $business, int $line, float $balance): void
    {
        DB::table('advertising_balances')->insert([
            'ghorfe_online_list_id' => $business, 'sender_number_id' => $line, 'balance' => $balance,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_per_line_balances_are_summed_per_business_without_losing_credit(): void
    {
        DB::table('ghorfe_online_lists')->insert([['id' => 1, 'title' => 'A'], ['id' => 2, 'title' => 'B'], ['id' => 3, 'title' => 'C']]);
        foreach ([1, 2, 3, 4] as $line) {
            $this->seedLine($line);
        }

        // Business 1 has three lines, business 2 one line, business 3 none.
        $this->seedBalance(1, 1, 1000);
        $this->seedBalance(1, 2, 2500.50);
        $this->seedBalance(1, 3, 0);
        $this->seedBalance(2, 4, 7000);

        $before = (float) DB::table('advertising_balances')->sum('balance');

        $this->migration()->up();

        $this->assertFalse(Schema::hasColumn('advertising_balances', 'sender_number_id'));
        $this->assertEquals(2, DB::table('advertising_balances')->count());
        $this->assertEquals(3500.50, (float) DB::table('advertising_balances')->where('ghorfe_online_list_id', 1)->value('balance'));
        $this->assertEquals(7000, (float) DB::table('advertising_balances')->where('ghorfe_online_list_id', 2)->value('balance'));
        $this->assertEquals($before, (float) DB::table('advertising_balances')->sum('balance'), 'Total credit must be preserved');
    }

    public function test_one_balance_row_per_business_is_enforced_after_migration(): void
    {
        DB::table('ghorfe_online_lists')->insert(['id' => 1, 'title' => 'A']);
        $this->migration()->up();

        DB::table('advertising_balances')->insert(['ghorfe_online_list_id' => 1, 'balance' => 10, 'created_at' => now(), 'updated_at' => now()]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('advertising_balances')->insert(['ghorfe_online_list_id' => 1, 'balance' => 20, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_migration_can_be_rolled_back(): void
    {
        DB::table('ghorfe_online_lists')->insert(['id' => 1, 'title' => 'A']);
        $this->migration()->up();
        $this->migration()->down();

        $this->assertTrue(Schema::hasColumn('advertising_balances', 'sender_number_id'));
    }
}

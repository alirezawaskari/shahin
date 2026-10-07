<?php

namespace Database\Seeders;

use App\Models\AiSetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.1
     * ?"L,XZ\
     *
     * @return void
     */
    public function run()
    {
        $this->call(SmsMessageGroupSeeder::class);
    }
}

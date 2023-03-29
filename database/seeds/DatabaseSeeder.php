<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SettingTableSeeder::class);
        $this->call(MenuTableSeeder::class);
        $this->call(ContactsettingTableSeeder::class);
        $this->call(AboutTableSeeder::class);
        $this->call(ServiceTableSeeder::class);
    }
}

<?php

use Illuminate\Database\Seeder;

class XTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Artisan::call('import:xtable');//calling the command to import data from .sql file to database table
    }
}

<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'name' => 'حدیثه',
        'family' => 'صفری',
        'phone' => '09386682995',
        'melicode' => '0371267961',
        'avatar' => '/img/user.jpg',
        'password' => \Illuminate\Support\Facades\Hash::make('123456'),
        'province_id' => '9',
        'city_id' => '145',
        'api_token' => '123456',
        'activation_code' => '123456',
        'token' => '104890458739',
        'active' => 1,
        'level' => 'admin',
        'birth' => '123',
    ];
});

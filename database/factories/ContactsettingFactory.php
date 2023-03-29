<?php

use Faker\Generator as Faker;

$factory->define(\App\Contactsetting::class, function (Faker $faker) {
    return [
        'title' => 'تماس با ما',
        'description' => 'متن توضیحات',
    ];
});

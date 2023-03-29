<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Service::class, function (Faker $faker) {
    return [
        'title' => 'خدمات دندانپزشکی',
        'en_title' => 'dentistry',
        'demo' => 'متن دمو',
        'content' => 'متن تستی',
        'left_content' => 'متن تستی',
        'icon' => '/img/icontheth.png',
        'thumb' => '/img/thethpic.png',
        'status' => 0,
        'price' => '59000',
    ];



});

<?php

use Faker\Generator as Faker;

$factory->define(\App\About::class, function (Faker $faker) {
    return [
        'title' => 'درباره ما',
        'content' => 'متن توضیحات',
        'content2' => 'متن توضیحات 2'
    ];
});

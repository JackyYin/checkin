<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Module::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'description' => $faker->text 
    ];
});

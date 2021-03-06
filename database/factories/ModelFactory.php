<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->email,
        'password' => app('hash')->make('pass')
    ];
});
$factory->define(App\Group::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->company,
        'is_public' => $faker->boolean(50),
        'created_by' => 2 // test admin
    ];
});

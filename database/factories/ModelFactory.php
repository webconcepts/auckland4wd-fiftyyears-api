<?php

use Carbon\Carbon;

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
        'email' => $faker->email,
    ];
});

$factory->define(App\PhotoAlbum::class, function (Faker\Generator $faker) {
    return  [
        'user_id' => factory(App\User::class)->create()->id,
        'title' => 'Example photo album',
        'date' => Carbon::parse('October 7, 1975'),
        'location' => 'Example location',
        'photographer' => 'Example photographer',
        'description' => '<p>This is an example description.</p><p>It could contain html and multiple paragraphs</p>',
    ];
});

$factory->state(App\PhotoAlbum::class, 'published', function ($faker) {
    return [
        'published_at' => Carbon::parse('-1 week'),
        'removed_at' => null,
    ];
});

$factory->state(App\PhotoAlbum::class, 'draft', function ($faker) {
    return [
        'published_at' => null,
        'removed_at' => null,
    ];
});

$factory->state(App\PhotoAlbum::class, 'removed', function ($faker) {
    return [
        'published_at' => null,
        'removed_at' => Carbon::parse('-1 day'),
    ];
});

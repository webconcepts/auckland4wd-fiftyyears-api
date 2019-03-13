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

$factory->state(App\User::class, 'editor', function ($faker) {
    return [
        'editor' => true,
    ];
});

$factory->define(App\Item::class, function (Faker\Generator $faker) {
    return  [
        'user_id' => factory(App\User::class)->create()->id,
        'type' => App\Item::PHOTO_ALBUM,
        'title' => 'Example content item',
        'date' => Carbon::parse('October 7, 1975'),
        'approx_day' => 7,
        'approx_month' => 10,
        'approx_year' => 1975,
        'location' => 'Example location',
        'authorship' => 'Example author',
        'description' => '<p>This is an example description.</p><p>It could contain html and multiple paragraphs</p>',
    ];
});

$factory->state(App\Item::class, 'album', function (Faker\Generator $faker) {
    return [
        'type' => App\Item::PHOTO_ALBUM,
        'title' => 'Example photo album',
        'authorship' => 'Example photographer',
    ];
});

$factory->state(App\Item::class, 'video', function (Faker\Generator $faker) {
    return [
        'type' => App\Item::VIDEO,
        'title' => 'Example video',
        'authorship' => 'Example videographer',
        'video_url' => 'https://www.vimeo.com/123456789',
        'video_type' => 'vimeo',
        'video_id' => '123456789',
    ];
});

$factory->state(App\Item::class, 'milestone', function (Faker\Generator $faker) {
    return [
        'type' => App\Item::MILESTONE,
        'title' => 'Example milestone',
        'authorship' => null,
        'location' => null,
    ];
});

$factory->state(App\Item::class, 'published', function ($faker) {
    return [
        'published_at' => Carbon::parse('-1 week'),
        'removed_at' => null,
    ];
});

$factory->state(App\Item::class, 'draft', function ($faker) {
    return [
        'published_at' => null,
        'removed_at' => null,
    ];
});

$factory->state(App\Item::class, 'removed', function ($faker) {
    return [
        'published_at' => null,
        'removed_at' => Carbon::parse('-1 day'),
    ];
});

$factory->define(App\Photo::class, function (Faker\Generator $faker) {
    return  [
        'item_id' => factory(App\Item::class)->states('album', 'draft')->create()->id,
        'uploaded_by_id' => factory(App\User::class)->create()->id,
        'type' => 'image/jpeg',
        'number' => 24,
        'uploaded' => false,
        'original_filename' => 'photo123.jpg',
        // 'description' => '<p>This is an example description.</p><p>It could contain html and multiple paragraphs</p>',
    ];
});

$factory->state(App\Photo::class, 'uploaded', function ($faker) {
    return [
        'uploaded' => true,
    ];
});

$factory->state(App\Photo::class, 'not-uploaded', function ($faker) {
    return [
        'uploaded' => false,
    ];
});

$factory->state(App\Photo::class, 'removed', function ($faker) {
    return [
        'removed_at' => Carbon::parse('-1 day'),
    ];
});

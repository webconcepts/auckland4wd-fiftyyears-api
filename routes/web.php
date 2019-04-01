<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('timeline', ['uses' => 'TimelineController@index']);
$router->get('timeline/{year}', ['uses' => 'TimelineController@show']);

$router->get('photo-albums', ['uses' => 'PhotoAlbumController@index']);
$router->get('photo-albums/{obfuscatedId}', ['uses' => 'PhotoAlbumController@show', 'as' => 'photoalbums.show']);

$router->group(['prefix' => 'photo-albums/{obfuscatedAlbumId}'], function () use ($router) {
    $router->get('photos', ['uses' => 'PhotoAlbumPhotoController@index']);
    $router->get('photos/{obfuscatedId}', ['uses' => 'PhotoAlbumPhotoController@show']);
});

$router->get('videos', ['uses' => 'VideoController@index']);
$router->get('videos/{obfuscatedId}', ['uses' => 'VideoController@show', 'as' => 'videos.show']);

$router->get('milestones', ['uses' => 'MilestoneController@index']);
$router->get('milestones/{obfuscatedId}', ['uses' => 'MilestoneController@show', 'as' => 'milestones.show']);

$router->group(['middleware' => 'auth'], function () use ($router) {
    // publish items
    $router->post('photo-albums', ['uses' => 'PhotoAlbumController@store']);
    $router->post('videos', ['uses' => 'VideoController@store']);
    $router->post('milestones', ['uses' => 'MilestoneController@store']);
    // unpublish items
    $router->delete('photo-albums/{obfuscatedId}', ['uses' => 'PhotoAlbumController@destroy']);
    $router->delete('videos/{obfuscatedId}', ['uses' => 'VideoController@destroy']);
    $router->delete('milestones/{obfuscatedId}', ['uses' => 'MilestoneController@destroy']);
});

$router->group(['prefix' => 'drafts', 'namespace' => 'Drafts', 'middleware' => 'auth'], function () use ($router) {
    $router->get('timeline', ['uses' => 'TimelineController@index']);

    $router->group(['prefix' => 'photo-albums'], function () use ($router) {
        $router->get('', ['uses' => 'PhotoAlbumController@index']);
        $router->post('', ['uses' => 'PhotoAlbumController@store']);
        $router->get('/{obfuscatedId}', ['uses' => 'PhotoAlbumController@show', 'as' => 'drafts.photoalbums.show']);
        $router->patch('/{obfuscatedId}', ['uses' => 'PhotoAlbumController@update']);
        $router->delete('/{obfuscatedId}', ['uses' => 'PhotoAlbumController@destroy']);

        $router->group(['prefix' => '{obfuscatedAlbumId}'], function () use ($router) {
        $router->get('photos', ['uses' => 'PhotoAlbumPhotoController@index']);
        $router->post('photos', ['uses' => 'PhotoAlbumPhotoController@store']);
        $router->get('photos/{obfuscatedId}', ['uses' => 'PhotoAlbumPhotoController@show', 'as' => 'drafts.photoalbums.photo.show']);
        $router->patch('photos/{obfuscatedId}', ['uses' => 'PhotoAlbumPhotoController@update']);
        $router->delete('photos/{obfuscatedId}', ['uses' => 'PhotoAlbumPhotoController@destroy']);

            $router->post('cover-photo', ['uses' => 'PhotoAlbumCoverPhotoController@store']);
            $router->delete('cover-photo', ['uses' => 'PhotoAlbumCoverPhotoController@destroy']);
    });
    });

    $router->group(['prefix' => 'videos'], function () use ($router) {
        $router->get('', ['uses' => 'VideoController@index']);
        $router->post('', ['uses' => 'VideoController@store']);
        $router->get('/{obfuscatedId}', ['uses' => 'VideoController@show', 'as' => 'drafts.videos.show']);
        $router->patch('/{obfuscatedId}', ['uses' => 'VideoController@update']);
        $router->delete('/{obfuscatedId}', ['uses' => 'VideoController@destroy']);
    });

    $router->group(['prefix' => 'milestones'], function () use ($router) {
        $router->get('', ['uses' => 'MilestoneController@index']);
        $router->post('', ['uses' => 'MilestoneController@store']);
        $router->get('/{obfuscatedId}', ['uses' => 'MilestoneController@show', 'as' => 'drafts.milestones.show']);
        $router->patch('/{obfuscatedId}', ['uses' => 'MilestoneController@update']);
        $router->delete('/{obfuscatedId}', ['uses' => 'MilestoneController@destroy']);
    });
});

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('user', ['uses' => 'AuthController@store']);
    $router->post('verification', ['uses' => 'AuthController@verify']);
    $router->post('token', ['uses' => 'AuthController@generateToken']);
    $router->patch('token', ['uses' => 'AuthController@refreshToken', 'middleware' => 'auth']);
});

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

$router->get('photo-albums', ['uses' => 'PhotoAlbumController@index']);
$router->get('photo-albums/{obfuscatedId}', ['uses' => 'PhotoAlbumController@show', 'as' => 'photoalbums.show']);

$router->get('videos', ['uses' => 'VideoController@index']);
$router->get('videos/{obfuscatedId}', ['uses' => 'VideoController@show', 'as' => 'videos.show']);

$router->group(['prefix' => 'drafts', 'namespace' => 'Drafts', 'middleware' => 'auth'], function () use ($router) {
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
    });
    });

    $router->group(['prefix' => 'videos'], function () use ($router) {
        $router->get('', ['uses' => 'VideoController@index']);
        $router->post('', ['uses' => 'VideoController@store']);
        $router->get('/{obfuscatedId}', ['uses' => 'VideoController@show', 'as' => 'drafts.videos.show']);
        $router->patch('/{obfuscatedId}', ['uses' => 'VideoController@update']);
        $router->delete('/{obfuscatedId}', ['uses' => 'VideoController@destroy']);
    });
});

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('user', ['uses' => 'AuthController@store']);
    $router->post('verification', ['uses' => 'AuthController@verify']);
    $router->post('token', ['uses' => 'AuthController@generateToken']);
    $router->patch('token', ['uses' => 'AuthController@refreshToken', 'middleware' => 'auth']);
});

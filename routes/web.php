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

$router->get('photoalbums', ['uses' => 'PhotoAlbumController@index']);
$router->post('photoalbums', ['uses' => 'PhotoAlbumController@store']);
$router->get('photoalbums/{obfuscatedId}', ['uses' => 'PhotoAlbumController@show', 'as' => 'photoalbums.show']);
$router->patch('photoalbums/{obfuscatedId}', ['uses' => 'PhotoAlbumController@update']);
$router->delete('photoalbums/{obfuscatedId}', ['uses' => 'PhotoAlbumController@destroy']);

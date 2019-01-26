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

$router->group(['prefix' => 'drafts', 'namespace' => 'Drafts'], function () use ($router) {
    $router->post('photo-albums', ['uses' => 'PhotoAlbumController@store']);
    $router->get('photo-albums/{obfuscatedId}', ['uses' => 'PhotoAlbumController@show', 'as' => 'drafts.photoalbums.show']);
    $router->patch('photo-albums/{obfuscatedId}', ['uses' => 'PhotoAlbumController@update']);
    $router->delete('photo-albums/{obfuscatedId}', ['uses' => 'PhotoAlbumController@destroy']);
});

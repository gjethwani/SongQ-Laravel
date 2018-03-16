<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::get('/host', 'SpotifyController@userAuth');
Route::get('/spotify-redirect', 'SpotifyController@getCode');
Route::get('/playlists', 'PlaylistController@showAll');
Route::get('/create-playlist', 'PlaylistController@create');
Route::post('/create-playlist', 'PlaylistController@addPlaylist');

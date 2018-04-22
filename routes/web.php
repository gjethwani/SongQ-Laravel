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
//https://accounts.spotify.com/en/status
Route::get('/host', 'SpotifyController@userAuth');
Route::get('/guest', 'RequestController@findPlaylist');
Route::post('/guest', 'RequestController@authenticatePlaylist');
Route::get('/spotify-redirect', 'SpotifyController@getCode');


Route::middleware(['guestAuthentication'])->group(function() {
  Route::get('/search', 'RequestController@returnResults');
  Route::get('/add-request', 'RequestController@addRequest');
  Route::get('/get-nearby-parties', 'PlaylistController@getNearbyParties');
});

Route::middleware(['authentication'])->group(function() {
  Route::get('/playlists', 'PlaylistController@showAll');
  Route::get('/playlists/{roomCode}', 'PlaylistController@showPlaylist');
  Route::get('/create-playlist', 'PlaylistController@create');
  Route::post('/create-playlist', 'PlaylistController@addPlaylist');
  Route::get('/accept', 'RequestController@accept');
  Route::get('/decline', 'RequestController@decline');
  Route::get('/playlists/{roomCode}/edit', 'PlaylistController@edit');
  Route::post('/playlists/{roomCode}/edit', 'PlaylistController@executeEdit');
  Route::get('/playlists/{roomCode}/delete', 'PlaylistController@deletePlaylist');
});

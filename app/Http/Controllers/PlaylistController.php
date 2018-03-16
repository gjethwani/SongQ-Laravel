<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Playlist;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;

class PlaylistController extends Controller
{
    public function showAll() {
      $playlists = Playlist::all();
      return view('playlists', [
        'playlists' => $playlists
       ]);
    }

    public function create() {
      $client = new Client();
      $accessToken = Config::get('accessToken');
      $bearerToken = 'Bearer ' . $accessToken;
      $response = $client->request('GET','https://api.spotify.com/v1/me/playlists?limit=50', ['headers' => ['Authorization' => $bearerToken]]);
      $jsonResponse = json_decode($response->getBody()->getContents());
      $playlistArray = $jsonResponse->items;
      $playlistData = [];
      for ($i = 0; $i < sizeof($playlistArray); $i++) {
        array_push($playlistData, [$playlistArray[$i]->id, $playlistArray[$i]->name]);
      }
      return view('create-playlist', [
        'playlistData' => $playlistData,
      ]);
    }

    public function addPlaylist(Request $request) {
        $name = $request->input('playlistName');
        $id = $request->input('playlistId');

        //get user id
        $client = new Client();
        $accessToken = Config::get('accessToken');
        $bearerToken = 'Bearer ' . $accessToken;
        $response = $client->request('GET','https://api.spotify.com/v1/me', ['headers' => ['Authorization' => $bearerToken]]);
        $jsonResponse = json_decode($response->getBody()->getContents());
        $userId = $jsonResponse->id;

        if ($name != NULL) {
          $playlistResponse = $client->request('POST','https://api.spotify.com/v1/users/' . $userId . '/playlists', [
            'headers' => ['Authorization' => $bearerToken, 'Content-Type' => 'application/json'],
            'json' => ['name' => $name],
          ]);

          $jsonPlaylistResponse = json_decode($playlistResponse->getBody()->getContents());


        } else if ($id != NULL) {
          $playlistResponse = $client 
        }
        $playlist = new Playlist;
        $playlist->playlistId = $jsonResponse->id;
        $playlist->owner =
    }
}

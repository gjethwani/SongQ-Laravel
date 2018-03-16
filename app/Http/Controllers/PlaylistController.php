<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Playlist;
use App\Request as SongRequest;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;

class PlaylistController extends Controller
{
    public function showAll() {
      $playlists = Playlist::all();
      //dd($playlists);
      return view('playlists', [
        'playlists' => $playlists
       ]);
    }

    public function create() {

      //get user id
      $client = new Client();
      $accessToken = Config::get('accessToken');
      $bearerToken = 'Bearer ' . $accessToken;
      $response = $client->request('GET','https://api.spotify.com/v1/me', ['headers' => ['Authorization' => $bearerToken]]);
      $jsonResponse = json_decode($response->getBody()->getContents());
      $userId = $jsonResponse->id;

      $client = new Client();
      $accessToken = Config::get('accessToken');
      $bearerToken = 'Bearer ' . $accessToken;
      $response = $client->request('GET','https://api.spotify.com/v1/me/playlists?limit=50', ['headers' => ['Authorization' => $bearerToken]]);
      $jsonResponse = json_decode($response->getBody()->getContents());
      $playlistArray = $jsonResponse->items;
      $playlistData = [];
      for ($i = 0; $i < sizeof($playlistArray); $i++) {
        $owner = $playlistArray[$i]->owner;
        if ($owner->id == $userId) {
            array_push($playlistData, [$playlistArray[$i]->id, $playlistArray[$i]->name]);
        }
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
        $playlistId = '';
        if ($name != NULL) {
          $playlistResponse = $client->request('POST','https://api.spotify.com/v1/users/' . $userId . '/playlists', [
            'headers' => ['Authorization' => $bearerToken, 'Content-Type' => 'application/json'],
            'json' => ['name' => $name],
          ]);

          $jsonPlaylistResponse = json_decode($playlistResponse->getBody()->getContents());
          $playlistId = $jsonPlaylistResponse->id;

        } else if ($id != NULL) {
          $playlistResponse = $client->request('GET', 'https://api.spotify.com/v1/users/' . $userId . '/playlists/' . $id, ['headers' => ['Authorization' => $bearerToken]]);
          $jsonPlaylistResponse = json_decode($playlistResponse->getBody()->getContents());
          $name = $jsonPlaylistResponse->name;
          $playlistId = $id;
        }
        $roomCodeCharacters = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","0","1","2","3","4","5","6","7","8","9"];
        $roomCode = '';
        for ($i = 0; $i < 4; $i++) {
          $index = rand(0,35);
          $roomCode = $roomCode . $roomCodeCharacters[$index];
        }
        $playlist = new Playlist;
        $playlist->roomCode = $roomCode;
        $playlist->playlistId = $playlistId;
        $playlist->owner = $userId;
        $playlist->playlistName = $name;
        $playlist->save();
        return redirect('/playlists');
    }

    public function showPlaylist($roomCode) {
        $requests = Playlist::find($roomCode)->Request;
        return view('requests', [
          'requests' => $requests
        ]);
    }
}

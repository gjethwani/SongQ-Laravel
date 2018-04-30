<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Playlist;
use App\User;
use App\Request as SongRequest;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;
use Validator;
use Illuminate\Support\Facades\Auth;
use DB;

class PlaylistController extends Controller
{
    public function showAll() {
      //get user id
    /*  $client = new Client();
      $accessToken = Config::get('accessToken');
      $bearerToken = 'Bearer ' . $accessToken;
      $response = $client->request('GET','https://api.spotify.com/v1/me', ['headers' => ['Authorization' => $bearerToken]]);
      $jsonResponse = json_decode($response->getBody()->getContents());
      $userId = $jsonResponse->id;*/
      $userId = Auth::id();
      $playlists = Playlist::where('owner',$userId)->get();
      return view('playlists', [
        'playlists' => $playlists
       ]);
    }

    public function create() {

      //get user id
      /*$client = new Client();
      $accessToken = Config::get('accessToken');
      $bearerToken = 'Bearer ' . $accessToken;
      $response = $client->request('GET','https://api.spotify.com/v1/me', ['headers' => ['Authorization' => $bearerToken]]);
      $jsonResponse = json_decode($response->getBody()->getContents());
      $userId = $jsonResponse->id;*/
      $userId = Auth::id();
      $client = new Client();
      $accessToken = User::find($userId)->accessToken;
      $bearerToken = 'Bearer ' . $accessToken;
      $response = '';
      try {
          $response = $client->request('GET','https://api.spotify.com/v1/me/playlists?limit=50', ['headers' => ['Authorization' => $bearerToken]]);
      } catch(\GuzzleHttp\Exception\RequestException $e) {
          if ($e->getResponse()->getStatusCode() == 401) {
            Auth::logout();
            return redirect('/');
          } else {
            dd($e);
          }
      }
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
        $formSelect = $request->input('formSelect');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        //get user id
        $client = new Client();
        /*$accessToken = Config::get('accessToken');
        $bearerToken = 'Bearer ' . $accessToken;
        $response = $client->request('GET','https://api.spotify.com/v1/me', ['headers' => ['Authorization' => $bearerToken]]);
        $jsonResponse = json_decode($response->getBody()->getContents());
        $userId = $jsonResponse->id;*/
        $userId = Auth::id();
        $bearerToken = 'Bearer ' . User::find($userId)->accessToken;
        $playlistId = '';
        $passes = false;
        if ($formSelect == 'create') {
        //  dd('here1');
          $validation = Validator::make([
            'playlistName' => $request->input('playlistName')
          ], [
            'playlistName' => 'required|min:3'
          ]);
          if ($validation->passes()) {
            $playlistResponse = '';
            try {
              $playlistResponse = $client->request('POST','https://api.spotify.com/v1/users/' . $userId . '/playlists', [
                'headers' => ['Authorization' => $bearerToken, 'Content-Type' => 'application/json'],
                'json' => ['name' => $name],
              ]);
            } catch(\GuzzleHttp\Exception\RequestException $e) {
                if ($e->getResponse()->getStatusCode() == 401) {
                  Auth::logout();
                  return redirect('/');
                } else {
                  dd($e);
                }
            }
            $jsonPlaylistResponse = json_decode($playlistResponse->getBody()->getContents());
            $playlistId = $jsonPlaylistResponse->id;
            $passes = true;
          } else {
            /*return redirect('/create-playlist')
              ->withInput()
              ->withErrors($validation);*/
            return json_encode($validation->errors());
          }
        } else if ($formSelect == 'existing') {
          $passes = true;
          $playlistResponse = '';
          try {
              $playlistResponse = $client->request('GET', 'https://api.spotify.com/v1/users/' . $userId . '/playlists/' . $id, ['headers' => ['Authorization' => $bearerToken]]);
          } catch(\GuzzleHttp\Exception\RequestException $e) {
              if ($e->getResponse()->getStatusCode() == 401) {
                Auth::logout();
                return redirect('/');
              } else {
                dd($e);
              }
          }
          $jsonPlaylistResponse = json_decode($playlistResponse->getBody()->getContents());
          $name = $jsonPlaylistResponse->name;
          $playlistId = $id;
        } else {
          return response('error', 500)
                  ->header('Content-Type', 'text/plain');
        }
        if ($passes) {
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
          if ($latitude != null) {
            $playlist->latitude = $latitude;
          }
          if ($longitude != null) {
            $playlist->longitude = $longitude;
          }
          $playlist->save();
          return 'success';
        }
    }

    public function showPlaylist($roomCode) {
        $requests = SongRequest::where('serviced','0')
          ->where('roomCode',$roomCode)
          ->get();
        $userId = Auth::id();
        $playlistId = Playlist::find($roomCode)->playlistId;
        return view('requests', [
          'requests' => $requests,
          'userId' => $userId,
          'playlistId' => $playlistId
        ]);
    }

    public function deletePlaylist($roomCode) {
        $toDelete = Playlist::find($roomCode);
        $toDelete->delete();
    }

    public function edit($roomCode) {
        $playlistName = Playlist::find($roomCode)->playlistName;
        return view('edit-playlist', [
            'roomCode' => $roomCode,
            'playlistName' => $playlistName
        ]);
    }

    public function executeEdit(Request $request, $roomCode) {
      $newName = $request->input('playlistName');
      $validation = Validator::make([
        'playlistName' => $newName
      ], [
        'playlistName' => 'required|min:3'
      ]);
      if ($validation->passes()) {
        $client = new Client();
        $userId = Auth::id();
        $bearerToken = 'Bearer ' . User::find($userId)->accessToken;
        $playlistId = Playlist::find($roomCode)->playlistId;
        try {
          $client->request('PUT','https://api.spotify.com/v1/users/' . $userId . '/playlists/' . $playlistId, [
            'headers' => ['Authorization' => $bearerToken, 'Content-Type' => 'application/json'],
            'json' => ['name' => $newName],
          ]);
        } catch(\GuzzleHttp\Exception\RequestException $e) {
            if ($e->getResponse()->getStatusCode() == 401) {
              Auth::logout();
              return redirect('/');
            } else {
              dd($e);
            }
        }
        $toEdit = Playlist::find($roomCode);
        $toEdit->playlistName = $newName;
        $toEdit->save();
        return redirect('/playlists');
      } else {
        return redirect('/playlists/' . $roomCode . '/edit')
          ->withInput()
          ->withErrors($validation);
      }
    }

    public function googleApiCall($withinThirty, $currLongitude, $currLatitude) {
      $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=";
      $url = $url . $currLatitude . ',' . $currLongitude . '&destinations=';
      for ($i = 0; $i < sizeof($withinThirty)-1; $i++) {
        $url = $url . $withinThirty[$i]->latitude . ',' . $withinThirty[$i]->longitude . '|';
      }
      $url = $url . $withinThirty[sizeof($withinThirty) - 1]->latitude . ',' . $withinThirty[sizeof($withinThirty) - 1]->longitude;
      $url = $url . '&key=' . env('GOOGLE_API_KEY');

      $client = new Client();
      $response = $client->request('GET', $url);
      if ($response->getStatusCode() == 200) {
        $googleResponse = json_decode($response->getBody()->getContents());
        $valueArray = $googleResponse->rows[0]->elements;

        //$valueArray = $rowsArray->elements;
        //return $valueArray;
        /*$toReturn = '{"data":';
        $jsonArray = '[';
        for ($i = 0; $i < sizeof($valueArray); $i++) {
          $value = $valueArray[$i]->elements[0]->distance->value;
          if ($value <= 200) {
            $jsonArray = $jsonArray . '{"roomCode": "' . $withinThirty[$i]->roomCode . '","playlistName":"' . $withinThirty[$i]->playlistName . '"},';
          //  $toReturn->push($withinThirty[$i]->roomCode);
          }
        }
        $jsonArray = substr($jsonArray, 0, -1);
        $toReturn = $toReturn . $jsonArray . ']' . '}';
        return json_encode(array($toReturn));*/
        $toReturn = array();
        for ($i = 0; $i < sizeof($valueArray); $i++) {
          $value = $valueArray[$i]->distance->value;
          if ($value <= 200) {
            array_push($toReturn, array('roomCode' => $withinThirty[$i]->roomCode, 'playlistName' => $withinThirty[$i]->playlistName));
          //  $toReturn->push($withinThirty[$i]->roomCode);
          }
        }
        return json_encode($toReturn);
      } else {
        return 'error';
      }
    }

    public function getNearbyParties(Request $request) {
      $currLongitude = $request->input('longitude');
      $currLatitude = $request->input('latitude');
      $withinThirty = DB::select(
        'select roomCode, abs(longitude - '. $currLongitude .') as longitudeDifference, abs(latitude - '. $currLatitude .') as latitudeDifference, latitude, longitude, playlistName
        from playlists
        where latitudeDifference <= 30 AND longitudeDifference <= 30'
      );
      $nearbyParties = $this->googleApiCall($withinThirty, $currLongitude, $currLatitude);
      return $nearbyParties;
      //return json_encode($nearbyParties);
    }
}

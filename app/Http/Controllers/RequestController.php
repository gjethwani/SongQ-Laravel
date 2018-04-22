<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Request as RequestObject;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use App\Playlist;
use App\User;
use Validator;
use File;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function findPlaylist(Request $request) {
      /* Spotify Authentication */
      $client = new Client();
      $formParams = [
        'grant_type' => 'client_credentials',
        'client_id' => '57a94d67afaa4a55802fdb9c6ca3d28f',
        'client_secret' => '47c18e0c81f242acbf372d6ddfc263df',
      ];
      $responseJson = postRequest('https://accounts.spotify.com/api/token', $formParams);
      $clientCredentialsToken = $responseJson->access_token;
  //    File::put(base_path() . '/config/clientCredentialsToken.php', "<?php\n return '$clientCredentialsToken' ;");
      $request->session()->put('clientCredentialsToken', $clientCredentialsToken);
      /* Get nearby parties */
      return view('find-playlist', [
        'exists' => true
      ]);
    }

    public function returnResults(Request $request) {
      $query = $request->input('query');
      if ($query == null) {
        dd('No query');
      }
    //  $accessToken = Config::get('clientCredentialsToken');
      $accessToken = $request->session()->get('clientCredentialsToken');
      $endpoint = 'https://api.spotify.com/v1/search?q=' . $query . '&type=track';
      $jsonResponse = getRequest($endpoint, $accessToken);
      return json_encode($jsonResponse);
    }

    public function authenticatePlaylist(Request $request) {
      $code = $request->input('code');
      $location = $request->input('location');
      $formSelect = $request->input('formSelect');
      if ($formSelect == 'enterCode') {
        $codeExists = Playlist::where('roomCode',$code)->get();
        if (sizeof($codeExists) > 0) {
          $request->session()->put('playlistAuthenticated', true);
        //  $request->session()->put('roomCode', $codeExists[0]->roomCode);
        //  $request->session()->put('owner', $codeExists[0]->owner);
          return view('search', [
            'roomCode' => $codeExists[0]->roomCode,
            'owner' => $codeExists[0]->owner
          ]);
        } else {
          $request->session()->put('playlistAuthenticated', false);
          return view('find-playlist', [
             'exists' => false
          ]);
        }
      } else if ($formSelect == 'findByLocation') {
        $playlists = Playlist::where('roomCode',$location)->get();
        $request->session()->put('playlistAuthenticated', true);
      //  $request->session()->put('roomCode', $code);
      //  $request->session()->put('owner', $playlists[0]->owner);
        return view('search', [
          'roomCode' => $code,
          'owner' => $playlists[0]->owner
        ]);
      }
    }

    public function addRequest(Request $request) {
      $roomCode = $request->input('roomCode');
  /*    if ($roomCode == null) {
        $roomCode = $request->session()->get('roomCode');
      }*/
      $owner = $request->input('owner');
  /*    if ($owner == null) {
        $owner = $request->session()->get('owner');
      }*/
      $songId = $request->input('songId');
      $songName = $request->input('songName');
      $artists = $request->input('artists');
      $album = $request->input('album');
      $newRequest = new RequestObject;
      $newRequest->roomCode = $roomCode;
      $newRequest->owner = $owner;
      $newRequest->songId = $songId;
      $newRequest->songName = $songName;
      $newRequest->artists = $artists;
      $newRequest->album = $album;
      $newRequest->serviced = false;
      $newRequest->save();
      return 'success';
    }

    public function accept(Request $request) {
      $requestId = $request->input('id');
      $toUpdate = RequestObject::find($requestId);
      $userId = $toUpdate->owner;
      $playlistId = $toUpdate->Playlist->playlistId;
      $songId = $toUpdate->songId;

      $client = new Client();
      $accessToken = User::find($userId)->accessToken;
      $bearerToken = 'Bearer ' . $accessToken;
      $url = 'https://api.spotify.com/v1/users/' . $userId . '/playlists/' . $playlistId . '/tracks?uris=spotify:track:' . $songId;
      $response = '';
      try {
          $response = $client->request('POST', $url, ['headers' => ['Authorization' => $bearerToken, 'Content-Type' => 'application/json']]);
      } catch(\GuzzleHttp\Exception\RequestException $e) {
          if ($e->getResponse()->getStatusCode() == 401) {
            Auth::logout();
            return redirect('/');
          } else {
            dd($e);
          }
      }
      $jsonResponse = json_decode($response->getBody()->getContents());

      $toUpdate->serviced = true;
      $toUpdate->accepted = true;
      $toUpdate->rejected = false;
      $toUpdate->save();
      return 'success,' . $requestId;
    }

    public function decline(Request $request) {
      $requestId = $request->input('id');
      $toUpdate = RequestObject::find($requestId);
      $toUpdate->serviced = true;
      $toUpdate->accepted = false;
      $toUpdate->rejected = true;
      $toUpdate->save();
      return 'success,' . $requestId;
    }
}

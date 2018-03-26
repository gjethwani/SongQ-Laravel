<?php
//// TODO: replace all API calls and implement middleware and auth
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
    public function findPlaylist() {
      $client = new Client();
      $formParams = [
        'grant_type' => 'client_credentials',
        'client_id' => '57a94d67afaa4a55802fdb9c6ca3d28f',
        'client_secret' => '47c18e0c81f242acbf372d6ddfc263df',
      ];
      /*$response = $client->request('POST', 'https://accounts.spotify.com/api/token', [
        'form_params' => [
          'grant_type' => 'client_credentials',
          'client_id' => '57a94d67afaa4a55802fdb9c6ca3d28f',
          'client_secret' => '47c18e0c81f242acbf372d6ddfc263df',
        ]
      ]);
      $responseJson = json_decode($response->getBody()->getContents()); */
      $responseJson = postRequest('https://accounts.spotify.com/api/token', $formParams);
      $clientCredentialsToken = $responseJson->access_token;
      File::put(base_path() . '/config/clientCredentialsToken.php', "<?php\n return '$clientCredentialsToken' ;");
      return view('find-playlist', [
        'exists' => true
      ]);
    }

    public function returnResults(Request $request) {
      $query = $request->input('query');
      $accessToken = Config::get('clientCredentialsToken');
      $endpoint = 'https://api.spotify.com/v1/search?q=' . $query . '&type=track';
      $jsonResponse = getRequest($endpoint, $accessToken);
      return json_encode($jsonResponse);
    }

    public function authenticatePlaylist(Request $request) {
      $code = $request->input('code');
      $location = $request->input('location');
      if ($code != NULL) {
        $codeExists = Playlist::where('roomCode',$code)->get();

        if (sizeof($codeExists) > 0) {
          return view('search', [
            'roomCode' => $codeExists[0]->roomCode,
            'owner' => $codeExists[0]->owner
          ]);
        } else {
          return view('find-playlist', [
             'exists' => false
          ]);
        }
      }
    }

    public function addRequest(Request $request) {
      $roomCode = $request->input('roomCode');
      $owner = $request->input('owner');
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

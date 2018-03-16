<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;
use DB;
use File;

class SpotifyController extends Controller
{
    private $URI = 'http://localhost:8000';
    public function userAuth(Request $request) {
      if ($request->input('access_token') != NULL) {
        var_dump('hello');
      } else {
        $clientId = Config::get('constants.clientId');
        $redirectURI = $this->URI . '/spotify-redirect';
        return redirect('https://accounts.spotify.com/authorize?client_id=' . $clientId . '&response_type=code&redirect_uri=' . $redirectURI . '&scope=playlist-modify-public%20playlist-modify-private%20streaming%20user-read-email%20user-read-private%20user-read-birthdate&show_dialog=true' );
      }
    }

    public function getCode(Request $request) {
      $code = $request->input('code');
      $client = new Client();
      $response = $client->request('POST', 'https://accounts.spotify.com/api/token', [
        'form_params' => [
          'grant_type' => 'authorization_code',
          'code' => $code,
          'redirect_uri' => $this->URI . '/spotify-redirect',
          'client_id' => '57a94d67afaa4a55802fdb9c6ca3d28f',
          'client_secret' => '47c18e0c81f242acbf372d6ddfc263df',
        ]
      ]);
      $responseJson = json_decode($response->getBody()->getContents());
      $accessToken = $responseJson->access_token;
      //$config = new Config();
      //Config::set('constants.accessToken', $accessToken);
      File::put(base_path() . '/config/accessToken.php', "<?php\n return '$accessToken' ;");
      $bearerToken = 'Bearer ' . $accessToken;
      $response = $client->request('GET','https://api.spotify.com/v1/me', ['headers' => ['Authorization' => $bearerToken]]);
      $jsonResponse = json_decode($response->getBody()->getContents());
      try {
        DB::table('users')->insert(
          ['id' => $jsonResponse->id,
           'username' => $jsonResponse->display_name]
        );
      } catch(\Illuminate\Database\QueryException $e) {

      }
      return redirect('/playlists');
    }
}

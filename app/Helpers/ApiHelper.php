<?php
  use GuzzleHttp\Client;
  function getRequest($endpoint, $accessToken) {
    $client = new Client();
    //$accessToken = Config::get('clientCredentialsToken');
    $bearerToken = 'Bearer ' . $accessToken;
    $response = $client->request('GET', $endpoint, ['headers' => ['Authorization' => $bearerToken]]);
    return json_decode($response->getBody()->getContents());
  }

  function postRequest($endpoint, $formParams) {
    $client = new Client();
    $response = $client->request('POST', $endpoint, ['form_params' => $formParams]);
    $responseJson = json_decode($response->getBody()->getContents());
    return $responseJson;
  }

  function getUserId() {

  }

@extends('main-layout')
@section('title', 'Playlists')
@section('content')
  <a href='/create-playlist' class='nav-link'>Create Playlist</a>
  <table class='table'>
    <tr>
      <th>Playlists</th>
    </tr>
    @foreach ($playlists as $playlist)
      <tr>
        <td><a href='/playlists/{{$playlist->roomCode}}'>{{$playlist->playlistName}} (Code: {{$playlist->roomCode}})</a></td>
      </tr>
    @endforeach
  </table>
@endsection

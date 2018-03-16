@extends('main-layout')
@section('title', 'Playlists')
@section('content')
  <a href='/create-playlist' class='nav-link'>Create Playlist</a>
  <table class='table'>
    <tr>
      <th>Code</th>
    </tr>
    @foreach ($playlists as $playlist)
      <tr>
        <td>{{$playlist->roomCode}}</td>
      </tr>
    @endforeach
  </table>
@endsection

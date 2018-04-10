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
        <td id='{{$playlist->roomCode}}'>
          <a href='/playlists/{{$playlist->roomCode}}'>{{$playlist->playlistName}} (Code: {{$playlist->roomCode}}) </a>
          <button onclick='return editPlaylist("{{$playlist->roomCode}}");'>Edit</button>
          <button onclick='return deletePlaylist("{{$playlist->roomCode}}");'>Delete</button>
        </td>
      </tr>
    @endforeach
  </table>
  <script>
    function editPlaylist(roomCode) {
      console.log(window.location.origin);
      var url = window.location.origin;
      window.location.href = url + '/playlists/' + roomCode + '/edit';
    }
    function deletePlaylist(roomCode) {
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          document.getElementById(roomCode).style.display = 'none';
        }
      };
      xhttp.open('GET', '/playlists/' + roomCode + '/delete', false);
      xhttp.send();
    }
  </script>
@endsection

@extends('main-layout')
@section('title','Create Playlist')
@section('content')
  <h1>Create a Playlist</h1>
  <input type='radio' name='formSelect' onclick='return toggleForms("newPlaylist","existingPlaylist");' checked> Create a Playlist <br>
  <input type='radio' name='formSelect' onclick='return toggleForms("existingPlaylist","newPlaylist");'> Choose an Existing Playlist <br>
  <form action='/create-playlist' method='post' id='newPlaylist'>
    {{csrf_field()}}
    <div class='form-group'>
      <input
        type='text'
        placeholder='Playlist Name'
        name='playlistName'
        class='form-control'>
    </div>
    <button type='submit' class='btn btn-primary'>Create</button>
  </form>

  <form action='/create-playlist' method='post' id='existingPlaylist' style='display: none'>
    {{csrf_field()}}
    <div class='form-group'>
      <select
        name='playlistId'
        class='form-control'>
        @foreach ($playlistData as $playlist)
          <option value={{$playlist[0]}}>{{$playlist[1]}}</option>
        @endforeach
      </select>
    </div>
    <button type='submit' class='btn btn-primary'>Create</button>
  </form>
@endsection
<script>
  function toggleForms(id1, id2) {
    var newPlaylist = document.getElementById(id1);
    var existingPlaylist = document.getElementById(id2);
    if (newPlaylist.style.display == 'none') {
      newPlaylist.style.display = 'block';
      existingPlaylist.style.display = 'none';
    } else {
      newPlaylist.style.display = 'none';
      existingPlaylist.style.display = 'block';
    }
  }
</script>

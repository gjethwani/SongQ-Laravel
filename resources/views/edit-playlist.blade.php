@extends('main-layout')
@section('title','Edit Playlist')
@section('content')
  <h1>Edit a Playlist</h1>

  @if ($errors->isNotEmpty())
      <div class="alert alert-danger" role="alert">
        @foreach($errors->all() as $message)
          {{$message}}
        @endforeach
      </div>
  @endif

  <form action='/playlists/{{$roomCode}}/edit' method='post'>
    {{csrf_field()}}
    <div>
      <div class='form-group'>
        @if (old('playlistName', null) != null)
          <input
            type='text'
            placeholder='New Playlist Name'
            name='playlistName'
            class='form-control'
            value='{{old("playlistName")}}'>
        @else
          <input
            type='text'
            placeholder='New Playlist Name'
            name='playlistName'
            class='form-control'
            value='{{$playlistName}}'>
        @endif
      </div>
      <button type='submit' class='btn btn-primary'>Create</button>
    </div>
  </form>
@endsection

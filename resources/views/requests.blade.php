@extends('main-layout')
@section('title','Requests')
@section('content')
  <table class='table'>
    <tr>
      <th>Song</th>
      <th>Artist</th>
      <th>Album</th>
      <th>Accept or Deny</th>
    </tr>
    <tr>
      @foreach ($requests as $request)
        <td>{{$request->songName}}</td>
        <td>{{$request->artist}}</td>
        <td>{{$request->album}}</td>
        <td>
          <button>Accept</button>
          <button>Deny</button>
        </td>
      @endforeach
    </tr>
  </table>
@endsection

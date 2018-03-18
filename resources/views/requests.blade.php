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
    @foreach ($requests as $request)
    <tr id='{{$request->id}}'>
      <td>{{$request->songName}}</td>
      <td>{{$request->artists}}</td>
      <td>{{$request->album}}</td>
      <td>
        <button onclick = 'return acceptOrDecline("accept","{{$request->id}}")'>Accept</button>
        <button onclick = 'return acceptOrDecline("decline","{{$request->id}}")'>Decline</button>
      </td>
    </tr>
    @endforeach
  </table>
@endsection
<script>
  var url = window.location.href;
  var roomCode = url.substr(url.length - 4);
  function acceptOrDecline(action, requestId) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
           // Typical action to be performed when the document is ready:
           var responseRaw = xhttp.responseText;
           var response = responseRaw.split(',');
           if (response[0] === "success") {
             var id = response[1];
             document.getElementById(id).style.display = 'none';
           }
        }
    };
    if (action === "accept") {
      xhttp.open("GET", "/accept?id=" + requestId, true);
    } else if (action === "decline") {
      xhttp.open("GET", "/decline?id=" + requestId, true);
    }
    xhttp.send();
  }
</script>

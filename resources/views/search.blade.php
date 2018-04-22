@extends('main-layout')
@section('title', 'Search for a Song')
@section('content')
  <div class='form-group'>
    {{csrf_field()}}
    <input
      type='text'
      placeholder='Search'
      name='search'
      class='form-control'
      id='searchField'>
  </div>
  <button id='submitButton' class='btn btn-primary' onclick='return searchForTrack(document.getElementById("searchField").value)'>Search</button>
  <table class='table' id='resultsTable'>
  </table>
  <script>
    function searchForTrack(query) {
      if (query == null || query == '') {
        return;
      }
      var ids = [];
      var names = [];
      var albums = [];
      var artistsArray = [];
      var table = document.getElementById('resultsTable');
      table.innerHTML = '';
      var headerTr = document.createElement('tr');
      var songTh = document.createElement('th');
      var artistTh = document.createElement('th');
      var albumTh = document.createElement('th');
      var requestTh = document.createElement('th');
      songTh.innerHTML = 'Song';
      artistTh.innerHTML = 'Artist';
      albumTh.innerHTML = 'Album';
      requestTh.innerHTML = 'Request';
      headerTr.appendChild(songTh);
      headerTr.appendChild(artistTh);
      headerTr.appendChild(albumTh);
      headerTr.appendChild(requestTh);
      table.appendChild(headerTr);
      var xhttp = new XMLHttpRequest();
      var metas = document.getElementsByTagName('meta');
      var token;
      for (var i=0; i<metas.length; i++) {
        if (metas[i].getAttribute("name") == "csrf-token") {
           token = metas[i].getAttribute("content");
        }
      }
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
           var resultsJson = JSON.parse(xhttp.responseText);
           console.log(resultsJson);
           var items = resultsJson.tracks.items;
           for (i = 0; i < items.length; i++) {
             var track = items[i];
             var overallTr = document.createElement('tr');
             var songTd = document.createElement('td');
             var artistTd = document.createElement('td');
             var albumTd = document.createElement('td');
             songTd.innerHTML = track.name;
             var artists = track.artists;
             var artistText = '';
             for (j = 0; j < artists.length - 1; j++) {
               var artist = artists[j];
               artistText = artistText + artist.name + ',';
             }
             artistText += artists[artists.length-1].name;
             artistTd.innerHTML = artistText;
             albumTd.innerHTML = track.album.name;
             ids.push(track.id);
             names.push(track.name);
             albums.push(track.album.name);
             artistsArray.push(artistText);
             var requestButton = document.createElement('button');
             requestButton.classList.add(i);
             requestButton.innerHTML = 'Request';
             requestButton.id = track.id;
             requestButton.onclick = function() {
               var addRequest = new XMLHttpRequest();
               var index = this.classList.item(0);
               addRequest.onreadystatechange = function() {
                  if (this.readyState == 4 && this.status == 200) {
                     // Typical action to be performed when the document is ready:
                     if (addRequest.responseText === 'success') {
                       document.getElementById(ids[index]).style.display = 'none';
                       document.getElementById('requested' + ids[index]).style.display = 'block';
                     } else if (addRequest.responseText === 'failure') {
                       console.log('error');
                     }
                  }
               };
               addRequest.open('GET', '/add-request?roomCode=' + '{{$roomCode}}' + '&owner=' + '{{$owner}}' + '&songId=' + ids[index] + '&songName=' + names[index]+ '&artists=' + artistsArray[index] + '&album=' + albums[index], true);
               addRequest.send();
             }
             var buttonTd = document.createElement('td');
             var requestedP = document.createElement('p');
             requestedP.innerHTML = 'Requested!';
             requestedP.style.display = 'none';
             requestedP.id = 'requested' + track.id;
             buttonTd.appendChild(requestedP);
             buttonTd.appendChild(requestButton);
             overallTr.appendChild(songTd);
             overallTr.appendChild(artistTd);
             overallTr.appendChild(albumTd);
             overallTr.appendChild(buttonTd);
             var resultsTable = document.getElementById('resultsTable');
             resultsTable.appendChild(overallTr);
           }
           console.log(resultsJson);
           // Typical action to be performed when the document is ready:
          /*  var overallTr = document.createElement('tr');
            var songTd = '';
            var artistTd = '';
            var albumTd = '';
            overallTr.appendChild(songTd);
            overallTr.appendChild(artistTd);
            overallTr.appendChild(albumTd);
            var resultsTable = document.getElementById('resultsTable');
            resultsTable.appendChild(overallTr);*/
        }
      };
      xhttp.open("GET", "/search?query=" + query, true);
      xhttp.setRequestHeader('X-CSRF-TOKEN', token);
      xhttp.send();
    }
  </script>
@endsection

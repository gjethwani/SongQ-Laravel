@extends('main-layout')
@section('title', 'Find a playlist')
@section('content')
  <h1>Find a Playlist</h1>
  <input type='radio' name='formSelect' onclick='return toggleForms("code","location");' checked> Enter code <br>
  <input type='radio' name='formSelect' onclick='return toggleForms("location","code");'> Find by Location <br>
  <form action='/guest' method='post' id='code'>
    {{csrf_field()}}
    <div class='form-group'>
      <input
        type='text'
        placeholder='Code'
        name='code'
        class='form-control'>
    </div>
    <button type='submit' class='btn btn-primary'>Submit</button>
  </form>

  <form action='/guest' method='post' id='location' style='display: none'>
    {{csrf_field()}}
    <div class='form-group' id='locationDiv'>
      <select
        name='location'
        class='form-control'
        id='locationSelect'>
      </select>
    </div>
    <button type='submit' class='btn btn-primary'>Submit</button>
  </form>

  @if ($exists == false)
    <div class="alert alert-danger">
      <ul>
          <p>Playlist doesn't exist</p>
      </ul>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
          @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
      </ul>
    </div>
  @endif
@endsection
  <script>
    var latitude = "";
    var longitude = "";

    function getNearbyParties() {
      console.log(latitude + " " + longitude);
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
             // Typical action to be performed when the document is ready:
             var response = JSON.parse(xhttp.responseText);
             console.log(response);
             for (var i = 0; i < response.length; i++) {
               var optionTag = document.createElement('option');
               optionTag.value = response[i].roomCode;
               optionTag.innerHTML = response[i].playlistName;
               document.getElementById('locationSelect').appendChild(optionTag);
             }
          }
      };
      xhttp.open("GET", "/get-nearby-parties?latitude=" + latitude + "&longitude=" + longitude, true);
      xhttp.send();
    }

    function initLocation() {
      var determiningLocation = document.getElementById('determiningLocation');
      if ("geolocation" in navigator) {
        console.log('here1');
        navigator.geolocation.getCurrentPosition(function(position) {
          console.log(position);
          latitude = position.coords.latitude;
          longitude = position.coords.longitude;
          getNearbyParties();
        }, function() {
          console.log('error');
          document.getElementById('locationSelect').display = 'none';
          document.getElementById('locationDiv').innerHTML = 'Location Unavailable';
        }, {timeout: 5000});
      } else {
        console.log('here2');
         console.log("Location unavailable");
         document.getElementById('locationSelect').display = 'none';
         document.getElementById('locationDiv').innerHTML = 'Location Unavailable';
      }
    }
    initLocation();
    function toggleForms(id1, id2) {
      var id1 = document.getElementById(id1);
      var id2 = document.getElementById(id2);
      if (id1.style.display == 'none') {
        id1.style.display = 'block';
        id2.style.display = 'none';
      } else {
        id1.style.display = 'none';
        id2.style.display = 'block';
      }
    }
  </script>

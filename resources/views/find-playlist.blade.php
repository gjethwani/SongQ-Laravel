@extends('main-layout')
@section('title', 'Find a playlist')
@section('content')
  <h1>Find a Playlist</h1>
  <form action='/guest' method='post'>
    <input type='radio' value='enterCode' id='enterCode' name='formSelect' onclick='return toggleForms("code","location");' checked> Enter code <br>
    <input type='radio' value='findByLocation' id='findByLocation' name='formSelect' onclick='return toggleForms("location","code");'> Find by Location <br>
    {{csrf_field()}}
    <div class='form-group'  id='code'>
      <input
        type='text'
        placeholder='Code'
        name='code'
        class='form-control'>
    </div>

    <div id='location' style='display: none'>
      <div class='form-group' id='locationDiv'>
        <select
          name='location'
          class='form-control'
          id='locationSelect'>
        </select>
      </div>
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
             if (response.length == 0) {
               document.getElementById('locationDiv').innerHTML = 'No parties nearby';
             }
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
      var enterCode = document.getElementById('enterCode');
      var findByLocation = document.getElementById('findByLocation');
      var codeForm = document.getElementById('code');
      var locationForm = document.getElementById('location');
      if (enterCode.checked == true) {
        codeForm.style.display = 'block';
        locationForm.style.display = 'none';
      } else if (findByLocation.checked == true) {
        codeForm.style.display = 'none';
        locationForm.style.display = 'block';
      }
    }
  </script>

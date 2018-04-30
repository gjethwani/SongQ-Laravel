@extends('main-layout')
@section('title','Create Playlist')
@section('content')
  <h1>Create a Playlist</h1>

  <div class="alert alert-danger" id = 'errors' role="alert" style='display:none'>
  </div>

  <form id='overallForm'>
    {{csrf_field()}}
    <input type='radio' value='create' name='formSelect' onclick='return toggleForms("newPlaylist","existingPlaylist");' checked> Create a Playlist <br>
    <input type='radio' value='existing' name='formSelect' onclick='return toggleForms("existingPlaylist","newPlaylist");'> Choose an Existing Playlist <br>
    <div id='newPlaylist'>
      <div class='form-group'>
        @if (old('playlistName', null) != null)
          <input
            type='text'
            placeholder='Playlist Name'
            name='playlistName'
            class='form-control'
            value='{{old("playlistName")}}'>
        @else
          <input
            type='text'
            placeholder='Playlist Name'
            name='playlistName'
            class='form-control'>
        @endif
      </div>
    </div>

    <div id='existingPlaylist' style='display: none'>
      <div class='form-group'>
        <select
          name='playlistId'
          class='form-control'>
          @foreach ($playlistData as $playlist)
            <option value={{$playlist[0]}}>{{$playlist[1]}}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class='form-group'>
        <input id="locationCheckBox" type="checkbox" class="checkbox" name="location" value="enableLocation" onclick="return initLocation();"><label>Use location services?</label><br>
    </div>
  </form>
  <p id="determiningLocation"></p>
  <button class='btn btn-primary' onclick='return submitForm();' id="submit">Create</button>
@endsection
<script>
  var latitude = "";
  var longitude = "";
  function initLocation() {
    var determiningLocation = document.getElementById('determiningLocation');
    if ("geolocation" in navigator) {
      determiningLocation.innerHTML = "Determining Location";
      document.getElementById("submit").disabled = true;
      navigator.geolocation.getCurrentPosition(function(position) {
        console.log(position);
        latitude = position.coords.latitude;
        longitude = position.coords.longitude;
        determiningLocation.innerHTML = "";
        document.getElementById("submit").disabled = false;
      }, function() {
        console.log('error');
        determiningLocation.innerHTML = "Unable to determine location";
        document.getElementById("submit").disabled = false;
      }, {timeout: 10000});
    } else {
       console.log("Location unavailable");
    }
  }
  function submitForm() {
    var form = document.getElementById('overallForm');
    var token = form.elements.namedItem('_token').value;
    var params = {
      'playlistName': form.elements.namedItem('playlistName').value,
      'playlistId': form.elements.namedItem('playlistId').value,
      'formSelect': form.elements.namedItem('formSelect').value,
      '_token': token
    };
    console.log(form.elements.namedItem('formSelect').value);
    if (document.getElementById('locationCheckBox').checked == true) {
      console.log('here1');
      if (latitude != "") {
        params.latitude = latitude;
      }
      if (longitude != "") {
        params.longitude = longitude;
      }
    }
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
           // Typical action to be performed when the document is ready:
           var response = xhttp.responseText;
           var url = window.location.origin;
           if (response === 'success') {
              document.getElementById('errors').style.display = 'none';
              window.location.href = url + '/playlists/';
           } else {
              var responseJSON = JSON.parse(response);
              document.getElementById('errors').style.display = 'block';
              var pError = document.createElement('p');
              pError.innerHTML = responseJSON.playlistName[0];
              document.getElementById('errors').appendChild(pError);
           }
        }
    };
    console.log(params);
    xhttp.open('POST', '/create-playlist');
    xhttp.setRequestHeader('X-CSRF-TOKEN', token);
    xhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhttp.send(JSON.stringify(params));

  }
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

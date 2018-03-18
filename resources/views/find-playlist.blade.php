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
    <div class='form-group'>
      <select
        name='location'
        class='form-control'>
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

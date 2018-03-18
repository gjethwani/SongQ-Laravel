<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    public $timestamps = false;
    public $incrementing = true;

    public function Playlist() {
      return $this->belongsTo('App\Playlist', 'roomCode');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'roomCode';
    public $incrementing = false;

    public function Request() {
      return $this->hasMany('App\Request', 'roomCode');
    }
}

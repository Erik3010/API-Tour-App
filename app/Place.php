<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $guarded = [];

    public function history_from_place() {
        return $this->hasMany(History::class, 'from_place_id');
    }

    public function history_to_place() {
        return $this->hasMany(History::class, 'to_place_id');
    }
}

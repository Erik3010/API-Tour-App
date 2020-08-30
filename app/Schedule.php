<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $guarded = [];

    public function from_place() {
        return $this->belongsTo(Place::class, 'from_place_id');
    }

    public function to_place() {
        return $this->belongsTo(Place::class, 'to_place_id');
    }
}

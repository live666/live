<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Index extends Model
{
    protected $table = 'indexes';

    protected $dates = [
        'start_play',
    ];
    
    public function sport()
    {
        return $this->belongsTo('App\Sport');
    }

    public function competition()
    {
        return $this->belongsTo('App\Competition');
    }

    public function event()
    {
        return $this->hasOne('App\Event', 'id' ,'id');
    }
}

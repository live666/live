<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $dates = [
        'start_play',
    ];

    public function competition()
    {
        return $this->belongsTo('App\Competition');
    }

    public function homeTeam()
    {
        return $this->belongsTo('App\Team', 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo('App\Team', 'away_team_id');
    }

    public function index()
    {
        return $this->hasOne('App\Index', 'id', 'id');
    }

    public function channels()
    {
        return $this->hasMany('App\Channel');
    }

    public function getStatusStringAttribute()
    {
        if ($this->status) {
            return $this->status;
        }
        if ($this->start_play) {
            $m = $this->start_play->timestamp - time();
            if ($m > 0) {
                return 'Fixture';
            } else if ($m <= 0 && $m > -(60*60*2)) {
                return 'Playing';
            } else if ($m <= 0 && $m < -(60*60*2)) {
                return 'Played';
            }
        }
        return null;
    }
}

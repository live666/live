<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $casts = [
        'live' => 'array'
    ];
}

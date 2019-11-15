<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $casts = [
        'live' => 'array'
    ];

    public function getNameAttribute(){
        if (!$this->title && $this->key) {
            switch ($this->key) {
                case 'stream':
                    return __('home.live') . ' 1';
                case 'streamNa':
                    return __('home.live') . ' 2';
                case 'streamAmAli':
                    return __('home.live') . ' 3';
                default:
                    return __('home.live');
            }
        }
        return $this->title;
    }
}

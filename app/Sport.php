<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Sport extends Model
{
    public $timestamps = false;

    protected $casts = [
        'name_i18n' => 'array'
    ];

    public function getNameAttribute(){
        $locale = App::getLocale();
        if (!in_array($locale, ['en', '']) && $this->name_i18n && array_key_exists($locale, $this->name_i18n)) {
            return $this->name_i18n[$locale];
        }
        return $this->attributes['name'];
    }
}

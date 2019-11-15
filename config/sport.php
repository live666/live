<?php

return [

    'api' => [
        'base_url' => env('API_BASE_URL'),
        'project' => env('API_PROJECT'),
        'secret' => env('API_SECRET'),
        'timeout' => env('API_TIMEOUT', 2),
    ],

    'home_sports_id' => env('HOME_SPORTS_ID'),

    'home_competitions_id' => env('HOME_COMPETITIONS_ID'),
    
    'home_only_live' => env('HOME_ONLY_LIVE', true),

    'basketball_sport_id' => env('BASKETBALL_SPORT_ID'),
    
    'basketball_competitions_id' => env('BASKETBALL_FILTER_COMPETITIONS_ID'),

];
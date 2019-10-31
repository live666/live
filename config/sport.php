<?php

return [

    'api' => [
        'base_url' => env('API_BASE_URL'),
        'project' => env('API_PROJECT'),
        'secret' => env('API_SECRET'),
        'timeout' => env('API_TIMEOUT', 2),
    ],

    'home_sports_id' => env('HOME_SPORTS_ID'),

    'home_competitions_id' => env('HOME_COMPETITION_ID')

];
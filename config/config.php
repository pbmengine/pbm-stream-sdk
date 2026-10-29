<?php

declare(strict_types=1);

return [
    /*
     | -------------------------------------
     | PBM Stream API
     | -------------------------------------
     |
     | Base URL of the PBM Stream API (always use https), the project id
     | and the access key that is sent as Authorization header.
     |
     */
    'url' => env('PBM_STREAM_URL', ''),

    'project' => env('PBM_STREAM_PROJECT', ''),

    'access_key' => env('PBM_STREAM_ACCESS_KEY', '')
];

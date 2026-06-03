<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Route prefix
    |--------------------------------------------------------------------------
    | Where the admin-api JSON endpoints are mounted.
    */
    'prefix' => env('REBEL_ADMIN_API_PREFIX', 'rebel/admin/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    | guard:   the auth guard the request must be authenticated on ('' = default).
    | ability: the Gate ability the user must pass. Defaults to 'rebel-admin' so the API
    |          is FAIL-CLOSED out of the box — define that Gate (or change this) to grant
    |          access; set it to '' only if your guard already implies admin rights.
    */
    'guard' => env('REBEL_ADMIN_API_GUARD', ''),
    'ability' => env('REBEL_ADMIN_API_ABILITY', 'rebel-admin'),

    /*
    |--------------------------------------------------------------------------
    | Base middleware
    |--------------------------------------------------------------------------
    | Applied before the EnsureAdmin gate (which is always appended).
    |
    | The web admin panel (laravel-rebel-admin) authenticates with the default
    | web (session) guard, so the API must run inside the 'web' middleware group
    | for the session cookie to be read. Use ['auth:sanctum'] instead if you call
    | the API from a token client.
    */
    'middleware' => ['web'],

];

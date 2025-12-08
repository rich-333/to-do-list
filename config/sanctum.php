<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request. If none of these guards
    | are able to authenticate the request, a 401 response is returned.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens do
    | not expire. This won't tweak the lifetime of first-party sessions.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA that resides on the same
    | repository as your API, you may need to customize some of the
    | middleware Sanctum uses while handling requests. You may change
    | these middleware classes as needed for your application.
    |
    */

    'middleware' => [
        'verify_csrf_token' => \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => \Illuminate\Cookie\Middleware\EncryptCookies::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies, allowing first-party SPAs to rely on cookies
    | for authentication instead of relying on bearer tokens. You should
    | include your local development domain here as well.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,localhost:8000,127.0.0.1,127.0.0.1:8000,127.0.0.1:3000',
        env('APP_URL') ? ',' . parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Routes Prefix / Subdomain
    |--------------------------------------------------------------------------
    |
    | This value controls the route prefix which Sanctum's routes will be
    | registered under. You're free to change this value to fit your own
    | needs and the needs of your application or API.
    |
    */

    'prefix' => 'sanctum',

    /*
    |--------------------------------------------------------------------------
    | CORS: Allowed Origins
    |--------------------------------------------------------------------------
    |
    | The origins from which your application's frontend may access your API.
    | These origins may be local development servers or production domains.
    |
    */

    'cors' => [
        'paths' => ['api/*', 'sanctum/csrf-cookie'],
        'allowed_methods' => ['*'],
        'allowed_origins' => ['*'],
        'allowed_origins_patterns' => [],
        'allowed_headers' => ['*'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => true,
    ],

];

<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}


/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->instance('path.config', $app->basePath() . DIRECTORY_SEPARATOR . 'config');


$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

class_alias('Tymon\JWTAuth\Facades\JWTAuth', 'JWTAuth');
class_alias('Tymon\JWTAuth\Facades\JWTFactory', 'JWTFactory');
class_alias('Illuminate\Support\Facades\Storage', 'Storage');
class_alias('Intervention\Image\Facades\Image', 'Image');


$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Filesystem\Factory::class,
    function ($app) {
        return new Illuminate\Filesystem\FilesystemManager($app);
    }
);
$app->singleton('filesystem', function ($app) {
    return $app->loadComponent('filesystems', 'Illuminate\Filesystem\FilesystemServiceProvider', 'filesystem');
});

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    \App\Http\Middleware\CorsMiddleware::class,
    \App\Http\Middleware\CreateBookmarkSetFirstTime::class,
    \App\Http\Middleware\CheckLanguageHeaderMiddleware::class
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'user.active' => \App\Http\Middleware\UserIsActive::class
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/


$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Intervention\Image\ImageServiceProviderLumen::class);
$app->register(Jacobcyl\AliOSS\AliOssServiceProvider::class);
$app->register(\Laravel\Scout\ScoutServiceProvider::class);
$app->register(\TeamTNT\Scout\TNTSearchScoutServiceProvider::class);
$app->register(App\Providers\AppServiceProvider::class);
$app->register(\Sentry\SentryLaravel\SentryLumenServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

$app->configure('filesystems');
$app->configure('scout');
$app->configure('smsir');

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/
$app->router->get('/', function () {
    return response()->json('Chaar Paye Protected Api, © 2018 CHAARPAYE.IR ALL RIGHTS RESERVED', 403);
});

$app->router->group([
    'namespace' => 'App\Http\Controllers\v1',
    'prefix' => '/v1'
], function ($router) {
    require __DIR__ . '/../routes/v1.php';
});

$app->router->group([
    'namespace' => 'App\Http\Controllers\v2',
    'prefix' => '/v2'
], function ($router) {
    require __DIR__ . '/../routes/v2.php';
});

$app->router->group([
    'namespace' => 'App\Http\Controllers\v3',
    'prefix' => '/v3'
], function ($router) {
    require __DIR__ . '/../routes/v3.php';
});

return $app;

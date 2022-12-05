<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Place;
use App\Models\Post;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        date_default_timezone_set('Asia/Tehran');
        Relation::morphMap([
            'tag' => 'App\Models\Tag',
            'event' => 'App\Models\Event',
            'place' => 'App\Models\Place',
            'post' => 'App\Models\Post',
            'author' => 'App\Models\Author',
            'photographer' => 'App\Models\Photographer',
            'comment' => 'App\Models\Comment',
            'rating' => 'App\Models\Rating',
            'category' => 'App\Models\Category',
            'celebrity' => 'App\Models\Celebrity',
            'characterproperty' => 'App\Models\CharacterProperty',
        ]);


        Resource::withoutWrapping();
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $tnt_dir = storage_path('tnt');
        if (!file_exists($tnt_dir) || !is_dir($tnt_dir)) {
            mkdir($tnt_dir);
        }

        $this->app['auth']->viaRequest('api', function ($request) {
            return app('auth')->setRequest($request)->user();
        });
    }
}

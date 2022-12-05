<?php

namespace App\Console;

use App\Console\Commands\ActivateCities;
use App\Console\Commands\ActivateCitiesCommand;
use App\Console\Commands\AddDefaultCityForPlaceEventPost;
use App\Console\Commands\DeleteDailyVisits;
use App\Console\Commands\DeleteIncompleteUsers;
use App\Console\Commands\FillGeoLocationForPlaces;
use App\Console\Commands\FixPostsLanguageId;
use App\Console\Commands\MakeSearchbleCommand;
use App\Console\Commands\RefreshCategoriesCommand;
use App\Console\Commands\ReindexAllPositionedCateogries;
use App\Console\Commands\RemoveAllUploadedFiles;
use App\Console\Commands\RemoveHttpFromImagesPath;
use App\Console\Commands\ReverseLocationOnPlaces;
use App\Console\Commands\StoreVisitsToDB;
use App\Console\Commands\TntIndexEvents;
use App\Console\Commands\TntIndexPlaces;
use App\Console\Commands\TntIndexPosts;
use App\Console\Commands\TntIndexTags;
use App\Console\Commands\UpdatePlaceTell;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        RemoveAllUploadedFiles::class,
        ReindexAllPositionedCateogries::class,
        StoreVisitsToDB::class,
        DeleteDailyVisits::class,
        DeleteIncompleteUsers::class,
        FillGeoLocationForPlaces::class,
        ReverseLocationOnPlaces::class,
        TntIndexTags::class,
        TntIndexPosts::class,
        TntIndexPlaces::class,
        TntIndexEvents::class,
        RemoveHttpFromImagesPath::class,
        MakeSearchbleCommand::class,
        AddDefaultCityForPlaceEventPost::class,
        RefreshCategoriesCommand::class,
        ActivateCitiesCommand::class,
        FixPostsLanguageId::class,
        UpdatePlaceTell::class
    ];

    /**`
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(StoreVisitsToDB::class)->everyFiveMinutes();
        $schedule->command(DeleteDailyVisits::class)
            ->timezone('Asia/Tehran')
            ->dailyAt('04:00:00');

//        $schedule->command(DeleteIncompleteUsers::class)->hourly();
    }
}

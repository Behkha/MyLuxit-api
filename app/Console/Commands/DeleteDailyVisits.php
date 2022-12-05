<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Event;
use App\Models\Place;
use Illuminate\Console\Command;

class DeleteDailyVisits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visits:deletecache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete daily views cache';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Event::deleteDailyVisitsCache();
        Place::deleteDailyVisitsCache();
        Category::deleteDailyVisitsCache();
    }
}

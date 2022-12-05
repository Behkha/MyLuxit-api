<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Event;
use App\Models\Place;
use App\Models\Visit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class StoreVisitsToDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visits:store';

    /**
     * The console command description.
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move visits stored in redis to database';

    /**
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
        Event::storeVisits();
        Place::storeVisits();
        Category::storeVisits();
    }
}

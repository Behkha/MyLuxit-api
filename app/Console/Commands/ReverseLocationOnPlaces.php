<?php

namespace App\Console\Commands;

use App\Models\Place;
use Illuminate\Console\Command;

class ReverseLocationOnPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reverse:locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reverse lat and long in places';

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
        $places = Place::get();
        foreach ($places as $place) {

            $long_lat = explode(',', $place->location);
            $lat = (float)$long_lat[0];
            $long = (float)$long_lat[1];
            if ($lat > 36.5 || $lat < 36 || $long > 60 || $long < 59.2) {
                $location = $long . ',' . $lat;
                $place->update([
                    'location' => $location
                ]);
            }

        }
    }
}

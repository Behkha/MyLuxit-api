<?php

namespace App\Console\Commands;

use App\Models\Place;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillGeoLocationForPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geo:places';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill geo_location field of places';

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
        foreach (Place::get() as $place) {
            $long_lat = explode(',', $place->location);
            $location = $long_lat[1] . ' ' . $long_lat[0];


            $place->update([
                'geo_location' => DB::raw("ST_GeogFromText('SRID=4326;POINT(" . $location . ")')")
            ]);

        }
    }
}

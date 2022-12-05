<?php

namespace App\Console\Commands;

use App\Models\Citiable;
use App\Models\City;
use App\Models\Event;
use App\Models\Place;
use App\Models\Post;
use Illuminate\Console\Command;

class AddDefaultCityForPlaceEventPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'city:default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add Default City For Post/Place/Event (Mashhad)';

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
        $mashhad = City::where('name', 'مشهد')->firstOrFail();
        foreach (Place::get() as $place) {
            $place->cities()->updateOrCreate([
                'city_id' => $mashhad->id
            ]);
        }

        foreach (Post::get() as $post) {
            $post->cities()->updateOrCreate([
                'city_id' => $mashhad->id
            ]);
        }

        foreach (Event::get() as $event) {
            $event->cities()->updateOrCreate([
                'city_id' => $mashhad->id
            ]);
        }
    }
}

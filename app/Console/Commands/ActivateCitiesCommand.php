<?php

namespace App\Console\Commands;

use App\Models\City;
use Illuminate\Console\Command;

class ActivateCitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activate:cities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate Cities';

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

	$cities = [
        'مشهد',
        'اهواز',
        'سیاهکل',
        'زیباکنار',
        'بندرکیاشهر',
        'بندرانزلی',
        'نور',
        'چمستان',
        'رامسر',
        'سنگر',
        'چمخاله',
        'فریدون کنار',
        'نوشهر',
        'آمل',
        'محمود آباد',
        'ماسال',
        'لاهيجان',
	'سرخرود'
    ];
	foreach($cities as $city) {

		$selectedCity = City::where('name', $city)->first();
		if ($selectedCity) {
		if (env('APP_ENV') == "local") {
         	   $host = "http://142.44.150.155:8008/";
        	} else {
            	   $host = "file.myluxit.ir/";
        	}

		$selectedCity->update([
            		'is_active' => true,
            		'image' => $host . 'cities/' . $city . '.jpg'
        	]);
		}
	}

/**
        $mashhad = City::where('name', 'مشهد')->firstOrFail();
        $ahvaz = City::where('name', 'اهواز')->firstOrFail();

        if (env('APP_ENV') == "local") {
            $host = "http://142.44.150.155:8008/";
        } else {
            $host = "file.myluxit.ir/";
        }

        $mashhad->update([
            'is_active' => true,
            'image' => $host . 'cities/mashhad.jpg'
        ]);

        $ahvaz->update([
            'is_active' => true,
            'image' => $host . 'cities/ahvaz.jpg'
        ]);
**/
     $this->info('Cities successfully activated!');
    }
}

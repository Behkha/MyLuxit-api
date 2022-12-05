<?php

namespace App\Console\Commands;

use App\Models\Place;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TntIndexPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:places';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index places in tnt';

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
        $tnt_dir = storage_path('tnt');
        if (!file_exists($tnt_dir) || !is_dir($tnt_dir)) {
            mkdir($tnt_dir);
        }

        DB::beginTransaction();
        foreach (Place::all() as $place) {
            $place->update([
                'updated_at' => Carbon::now()
            ]);
        }
        $this->info('places indexed in tnt successfully!');
        DB::commit();
    }
}

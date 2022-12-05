<?php

namespace App\Console\Commands;

use App\Models\Place;
use Illuminate\Console\Command;

class UpdatePlaceTell extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updated:tell';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //
        Place::get()->each(function (Place $place) {
            if (is_array($place->details)) {
                $details = $place->details;

                if ($details['tell'])
                {
                    if ($details['tell']['content'])
                    {
                        $details['tell']['content'] = '09154129365';
                    }
                }
                $place->update([
                    'details' => $details
                ]);
            }
        });
    }
}

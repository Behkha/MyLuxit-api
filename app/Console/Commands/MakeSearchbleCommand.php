<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Place;
use App\Models\Searchable;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeSearchbleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'searchable:all';

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

        Searchable::query()->delete();
        DB::beginTransaction();
        foreach (Place::get() as $place) {
            Searchable::create([
                'searchable_type' => 'place',
                'searchable_id' => $place->id
            ]);
        }

        foreach (Event::get() as $event) {
            Searchable::create([
                'searchable_type' => 'event',
                'searchable_id' => $event->id
            ]);
        }
        DB::commit();
    }
}

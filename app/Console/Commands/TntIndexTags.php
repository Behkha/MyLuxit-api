<?php

namespace App\Console\Commands;

use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TntIndexTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index tags in tnt';

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
        foreach (Tag::all() as $tag) {
            $tag->update([
                'updated_at' => Carbon::now()
            ]);
        }
        DB::commit();
        $this->info('Tags indexed in tnt successfully!');
    }
}

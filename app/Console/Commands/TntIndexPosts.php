<?php

namespace App\Console\Commands;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TntIndexPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate tnt index files for posts';

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

        foreach (Post::all() as $post) {
            $post->update([
                'updated_at' => Carbon::now()
            ]);
        }
    }
}

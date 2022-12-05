<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\City;
use Illuminate\Console\Command;

class ReindexAllPositionedCateogries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'redindex all positioned categories ';

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
        $categories = Category::whereNotNull('position')->orderBy('id')->get();

        foreach ($categories as $category) {
            $category->reindexAll();
        }

        $this->info('Categories indexed successfully!');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RemoveAllUploadedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove All Uploaded Files';

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
        Storage::disk('oss')->deleteDirectory('events');
        Storage::disk('oss')->deleteDirectory('places');
    }
}

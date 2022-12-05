<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteIncompleteUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:incompleteusers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete users that are not actiavted since registration';

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
        User::where([
            ['status_id', '=', User::STATUSES['incomplete']],
            ['updated_at', '<', Carbon::now()->subMinutes(50)]
        ])->delete();

    }
}

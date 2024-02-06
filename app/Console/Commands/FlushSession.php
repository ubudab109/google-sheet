<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class FlushSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Sesssion';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('CLEARING SESSIONS');
        Session::flush();
        $this->info('ALL SESSION CLEARED');
    }
}

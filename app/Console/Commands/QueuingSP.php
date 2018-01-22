<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QueuingSP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sp:queuing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fungsi untuk mengantrikan aging invoice ke dalam table SP queue';

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
        // kelompokin aging invoice per tenant

    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExecuteMailSP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sp:mailing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fungsi utk execute email SP ke customer';

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
    }
}

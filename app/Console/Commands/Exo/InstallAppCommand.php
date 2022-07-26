<?php

namespace App\Console\Commands\Exo;

use Illuminate\Console\Command;

class InstallAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exo:app:fresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fresh installation';

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
     * @return int
     */
    public function handle()
    {
        if ($this->confirm('Extraordinary CBT akan dilakukan fresh installation, with to continue?')) {

            $this->call('migrate:fresh');

            $this->call('db:seed');

            $this->call('storage:link');

            $this->call('cache:clear');

            $this->call('route:clear');

            $this->call('config:clear');
        }
        return 0;
    }
}

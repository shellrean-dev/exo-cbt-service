<?php

namespace App\Listeners;

use \Illuminate\Console\Events\CommandFinished;

class AddFinishedToOputput
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CommandFinished $event): void
    {
        $message = 'Tanks for using Extraordinary CBT';
        $event->output->writeln('');
        $event->output->writeln($message);
    }
}

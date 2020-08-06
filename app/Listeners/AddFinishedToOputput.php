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
        $message = 'Thanks for using Extraordinary CBT Version 1.1.0';
        $event->output->writeln('');
        $event->output->writeln($message);
    }
}

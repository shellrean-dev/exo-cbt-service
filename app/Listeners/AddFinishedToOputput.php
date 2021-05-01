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
        $message = sprintf('Thanks for using Extraordinary CBT %s version %s', config('exo.version.name'), config('exo.version.code'));
        $event->output->writeln('');
        $event->output->writeln($message);
    }
}

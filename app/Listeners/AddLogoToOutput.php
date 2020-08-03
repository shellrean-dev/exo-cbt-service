<?php

namespace App\Listeners;

use Illuminate\Console\Events\CommandStarting;

class AddLogoToOutput
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CommandStarting $event): void
    {
        $event->output->write("
   ______     __                            ___                                __    __ 
   / ____/  __/ /__________ _____  _________/ (_)___  ____ ________  __   _____/ /_  / /_
  / __/ | |/_/ __/ ___/ __ `/ __ \/ ___/ __  / / __ \/ __ `/ ___/ / / /  / ___/ __ \/ __/
 / /____>  </ /_/ /  / /_/ / /_/ / /  / /_/ / / / / / /_/ / /  / /_/ /  / /__/ /_/ / /_  
/_____/_/|_|\__/_/   \__,_/\____/_/   \__,_/_/_/ /_/\__,_/_/   \__, /   \___/_.___/\__/  
                                                                   /____/                                                                                                                      
");
    }
}

<?php

namespace App\Console\Commands\Exo;

use Illuminate\Console\GeneratorCommand;

class MakeServiceCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'exo:make:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create exo service';

    /**
     * The type of class being generated
     * 
     * @var string
     */
    protected $type = 'service';

    /**
     * Get the stub file for the generator.
     * 
     * @return string
     */
    protected function getStub()
    {
        return resource_path('stubs/ServiceStub.php');
    }

    /**
     * Get the default namespace for the class.
     * 
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Services';
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return 'ShellreanDev';
    }
}

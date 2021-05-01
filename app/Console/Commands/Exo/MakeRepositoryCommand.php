<?php

namespace App\Console\Commands\Exo;

use Illuminate\Console\GeneratorCommand;

class MakeRepositoryCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'exo:make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create exo repository';

    /**
     * The type of class being generated
     * 
     * @var string
     */
    protected $type = 'repository';

    /**
     * Get the stub file for the generator.
     * 
     * @return string
     */
    protected function getStub()
    {
        return resource_path('stubs/RepositoryStub.php');
    }

    /**
     * Get the default namespace for the class.
     * 
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Repositories';
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

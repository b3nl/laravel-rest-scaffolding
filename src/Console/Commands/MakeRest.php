<?php

namespace b3nl\RESTScaffolding\Console\Commands;

use b3nl\RESTScaffolding\Code\Generator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;

/**
 * Creates a template based rest api.
 * @author b3nl
 * @category Console
 * @package b3nl\RESTScaffolding
 * @subpackage Console\Commands
 * @version $id$
 */
class MakeRest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:rest-api ' .
    '{namespace : The basic namespace for the API.} ' .
    '{prefix : Which URL prefix should be used.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a basic REST API for the given namespace und route.';

    /**
     * Execute the console command.
     * @param Container The service container.
     * @return mixed
     */
    public function handle(Container $services)
    {
        $config = config('rest-scaffolding');
        $namespace = $this->argument('namespace');
        $prefix = $this->argument('prefix');

        $services->make(Generator::class)
            ->setProgressBar($this->output->createProgressBar())
            ->processFiles($config, $namespace, $prefix);
    } // function
}

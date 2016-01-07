<?php

namespace b3nl\RESTScaffolding\Jobs;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Str;

/**
 * Writes/changes the controller file for the project.
 * @author b3nl
 * @category Jobs
 * @package b3nl\RESTScaffolding
 * @subpackage Jobs
 * @version $id$
 */
class ControllerWriter extends Job implements SelfHandling
{
    /**
     * Execute the job.
     * @return bool
     */
    public function handle()
    {
        $config = $this->getConfig();
        $template = storage_path("rest-scaffolding/controllers/{$config['tableName']}.stub");

        if (!file_exists($template)) {
            $template = storage_path('rest-scaffolding/controllers/default.stub');
        } // if

        if (!file_exists($template)) {
            // TODO Error Message!
        } // if

        $config['tableNamespace'] = ucfirst(Str::camel($config['tableNamespace']));
        $target = app_path("Http/Controllers/{$config['customNamespace']}/{$config['tableNamespace']}Controller.php");

        if (!is_dir($targetFolder = dirname($target))) {
            mkdir($targetFolder, 0644, true);
        } // if

        return (bool)file_put_contents(
            $target,
            str_replace(
                array_map(function ($key) {
                    return '{' . $key . '}';
                }, array_keys($config)),
                array_values($config),
                file_get_contents($template)
            )
        );
    } // function
}

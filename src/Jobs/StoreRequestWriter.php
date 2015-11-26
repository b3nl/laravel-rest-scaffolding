<?php
namespace b3nl\RESTScaffolding\Jobs;

use b3nl\RESTScaffolding\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use PHP_CodeSniffer_CLI;

/**
 * Writes/changes the store request file for the project.
 * @author b3nl
 * @category Jobs
 * @package b3nl\RESTScaffolding
 * @subpackage Jobs
 * @version $id$
 */
class StoreRequestWriter extends Job implements SelfHandling
{
    /**
     * Returns the target path.
     * @return string
     */
    protected function getTargetPath()
    {
        $config = $this->getConfig();

        return app_path("Http/Requests/{$config['customNamespace']}/{$config['entityClass']}/StoreRequest.php");
    } // function

    /**
     * Returns path to the template.
     * @return string
     */
    protected function getTemplatePath()
    {
        $return = storage_path('rest-scaffolding/requests/store.php');

        if (!file_exists($return)) {
            $return = storage_path('rest-scaffolding/requests/store.example.php');
        } // if

        return $return;
    } // function

    /**
     * Execute the job.
     * @return bool
     */
    public function handle()
    {
        $config = $this->getConfig();
        $template = $this->getTemplatePath();

        if (!file_exists($template)) {
            // TODO Error Message!
        } // if

        $target = $this->getTargetPath();

        if (!is_dir($targetFolder = dirname($target))) {
            mkdir($targetFolder, 0644, true);
        } // if

        $config['validationRules'] = var_export($config['validationRules'], true);

        $saved = (bool)file_put_contents(
            $target,
            str_replace(
                array_map(function ($key) {
                    return '{' . $key . '}';
                }, array_keys($config)),
                array_values($config),
                file_get_contents($template)
            )
        );

        if ($saved) {
            ob_start();
            $cli = new PHP_CodeSniffer_CLI();
            $cli->process([
                'files' => [$target],
                'reports' => ['cbf' => null],
                'phpcbf-suffix' => '',
                'standard' => 'PSR2',
                'verbosity' => 0
            ]);
            ob_end_clean();
        } // if

        return $saved;
    } // function
}

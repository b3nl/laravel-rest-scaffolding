<?php
namespace b3nl\RESTScaffolding\Code;

use b3nl\RESTScaffolding\Jobs\ControllerWriter;
use b3nl\RESTScaffolding\Jobs\RouteWriter;
use b3nl\RESTScaffolding\Jobs\StoreRequestWriter;
use b3nl\RESTScaffolding\Jobs\UpdateRequestWriter;
use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Writes the generated api.
 * @author b3nl
 * @category Model
 * @package b3nl\RESTScaffolding
 * @subpackage Code
 * @version $id$
 */
class Generator
{
    use AppNamespaceDetectorTrait, DispatchesJobs;

    /**
     * The progress bar.
     * @var ProgressBar|void
     */
    protected $progressBar = null;

    /**
     * Returns the progressbar.
     * @return ProgressBar|void
     */
    public function getProgressBar()
    {
        return $this->progressBar;
    } // function

    public function processFiles(array $config, $namespace, $prefix)
    {
        if ($bar = $this->getProgressBar()) {
            $bar->start((count($config) * 3) + 1);
        } // if

        $this->dispatch(new RouteWriter($config, $namespace, $prefix));

        if ($bar) {
            $bar->advance();
        } // if

        $basicReplace = [
            'appNamespace' => rtrim($this->getAppNamespace(), '\\'),
            'customNamespace' => $namespace
        ];

        foreach ($config as $table => $tableConfig) {
            $replace = $basicReplace + [
                'customUsages' => "use {$tableConfig['model']};",
                'entityClass' => preg_replace('/(\w+\\\)+/', '', $tableConfig['model']),
                'tableName' => $table,
                'tableNamespace' => ucfirst($table)
            ];

            $this->dispatch(new ControllerWriter($replace));

            if ($bar) {
                $bar->advance();
            } // if

            $replace += [
                'validationRules' => $tableConfig['validators']['store'] ?: []
            ];

            $this->dispatch(new StoreRequestWriter($replace));

            if ($bar) {
                $bar->advance();
            } // if

            $replace['validationRules'] = $tableConfig['validators']['update'] ?: [];

            $this->dispatch(new UpdateRequestWriter($replace));

            if ($bar) {
                $bar->advance();
            } // if
        } // foreach
    } // function

    /**
     * Sets the progress bar.
     * @param ProgressBar $progressBar
     * @return Writer
     */
    public function setProgressBar($progressBar)
    {
        $this->progressBar = $progressBar;

        return $this;
    } // function
}

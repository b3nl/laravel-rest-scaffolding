<?php

namespace b3nl\RESTScaffolding\Jobs;

use b3nl\RESTScaffolding\Code\Line;
use b3nl\RESTScaffolding\File;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Str;

/**
 * Writes/changes the route file for the project.
 * @author b3nl
 * @category Jobs
 * @package b3nl\RESTScaffolding
 * @subpackage Jobs
 * @version $id$
 */
class RouteWriter extends Job implements SelfHandling
{
    /**
     * The used namespace.
     * @var string
     */
    protected $namespace = '';

    /**
     * The used prefix.
     * @var string
     */
    protected $prefix = '';

    /**
     * Create a new job instance.
     * @param array $config The config.
     * @param string $namespace The chosen namespace.
     * @param string $prefix the URL prefix.
     */
    public function __construct($config, $namespace, $prefix)
    {
        parent::__construct($config);

        $this->setNamespace($namespace)->setPrefix($prefix);
    } // function

    /**
     * Returns the namespace.
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    } // function

    /**
     * Returns the prefix.
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    } // function

    /**
     * Execute the job.
     * @param Container $services The service container.
     * @return bool
     * @throws Exception If there is an error.
     */
    public function handle(Container $services)
    {
        $routesFile = app_path('Http/routes.php');

        if (file_exists($routesFile)) {
            touch($routesFile);
        } // if

        /** @var File $file */
        $config = $this->getConfig();
        $file = $services->make(File::class, [$routesFile]);
        $codeTemplate = 'Route::group([\'namespace\' => \'%s\', \'prefix\' => \'%s\'], function (Router $router)';

        $groups = $file->findLine(
            [$codeTemplate => $values = [$this->getNamespace(), $this->getPrefix()]],
            1
        );

        if (!$groups) {
            $group = $file
                ->appendLine(
                    new Line(call_user_func_array('sprintf', array_merge([$codeTemplate], $values)), T_VARIABLE)
                )
                ->setNestingLevel(1);
        } else {
            $group = reset($groups);
        } // else

        foreach (@$config['tables'] ?: [] as $table => $tableConfig) {
            $ucTable = ucfirst($table);
            $className = preg_replace('/(\w+\\\)+/', '', $tableConfig['model']);

            if (!$file->findLine(['use %s' => [$tableConfig['model']]], 1)) {
                $file->prependLine(new Line("use {$tableConfig['model']}"));
            } // if

            $modelLines = $file->findLine(
                ['$router->model(\'%s\', %s::class)' => [$table, $className]], 1, $group->getNestingLevel() + 1
            );

            if (!$modelLines) {
                $group->appendLine(new Line(
                    "\$router->model('{$table}', {$className}::class)"
                ));
            } // if

            $ctrlrPrefix = Str::ucfirst(Str::camel($ucTable));
            $ctrlrLines = $file->findLine(
                ['$router->resource(\'%s\', \'%sController\', [\'except\' => [\'create\', \'edit\']])' =>
                    [$table, $ctrlrPrefix]],
                $group->getNestingLevel() + 1
            );

            if (!$ctrlrLines) {
                $group->appendLine(new Line(
                    "\$router->resource('{$table}', '{$ctrlrPrefix}Controller', ['except' => ['create', 'edit']])"
                ));
            } // if
        } // foreach

        if (!$file->save()) {
            throw new Exception('Routes files can not be cached.');
        } // if

        return true;
    } // function

    /**
     * Sets the namespace.
     * @param string $namespace
     * @return Job
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    } // function

    /**
     * Sets the prefix.
     * @param string $prefix
     * @return Job
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    } // function
}

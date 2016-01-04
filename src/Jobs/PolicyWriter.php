<?php

namespace b3nl\RESTScaffolding\Jobs;

use b3nl\RESTScaffolding\Code\Line;
use b3nl\RESTScaffolding\File;
use Exception;
use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Container\Container;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/**
 * Writes the default policies for the rest api.
 * @author b3nl
 * @category Jobs
 * @package b3nl\RESTScaffolding
 * @subpackage Jobs
 * @version $id$
 */
class PolicyWriter extends Job implements SelfHandling
{
    use AppNamespaceDetectorTrait;

    /**
     * The authserviceprovider file.
     * @var File
     */
    protected $authProviderFile = null;

    /**
     * The service container.
     * @var Container
     */
    protected $services = null;

    /**
     * Adds new policies to the provider.
     * @param array $mappedPolicies The mapped policies
     * @param Line $policiesCodeLine The found code line.
     * @param string $rawMatch
     * @return PolicyWriter
     */
    protected function addNewPoliciesToProvider(array $mappedPolicies, Line $policiesCodeLine, $rawMatch)
    {
        $file = $this->getAuthProviderFile();
        $newPolicyLines = '';

        foreach ($mappedPolicies as $model => $policy) {
            $newPolicyLines .= $model . ' => ' . $policy . ",\n";
        } // foreach

        $policiesCodeLine->setContent(
            str_replace($rawMatch, "\n" . trim($newPolicyLines, ",\n") . "\n", $policiesCodeLine)
        );

        $file->save();

        return $this;
    } // function

    /**
     * Adds the new class usages to the provider file.
     * @param array $newUsages
     * @return PolicyWriter
     */
    protected function addNewUsagesToProvider(array $newUsages)
    {
        $file = $this->getAuthProviderFile();
        $lastUsage = ($oldUsages = $file->findLine('/use .*/', 0, 1)) ? end($oldUsages) : null;

        if (!$lastUsage) {
            // TODO Find Namespace to add lines after that.
        } else {
            foreach ($newUsages as $newUsageCall) {
                $file->addAfter(new Line("use {$newUsageCall};", T_USE), $lastUsage);
            } // foreach
        } // else

        $file->save();

        return $this;
    } // function

    /**
     * Creates the new policies for the rest config.
     * @param array $mappedPolicies
     * @return array The first element are the new class usages and the second element is the policy mapping.
     * @throws Exception
     */
    public function createNewPolicies(array $mappedPolicies)
    {
        $appNamespace = $this->getAppNamespace();
        $config = $this->getConfig();
        $newUsages = [];
        $services = $this->getServices();

        foreach (@$config['tables'] ?: [] as $table => $tableConfig) {
            $policyName = Str::ucfirst(Str::camel($table)) . 'Policy';
            $className = preg_replace('/(\w+\\\)+/', '', $tableConfig['model']);
            $classCall = $className . '::class';

            if (!class_exists($fullPolicyName = $appNamespace . '\\Policies\\' . $policyName)) {
                if (Artisan::call('make:policy', ['name' => $policyName])) {
                    throw new Exception('Could not write ' . $policyName);
                } // if


                $services->make(File::class, [$policyFile = app_path("Policies/{$policyName}.php")])->setContent(
                    str_replace(
                        [
                            '    }',
                            "\nclass "
                        ],
                        [
                            "    }\n{$this->getPolicyMethods($table)}",
                            "use {$tableConfig['model']};\n\nclass "
                        ],
                        file_get_contents($policyFile)
                    )
                )->save();
            } // if


            if (!array_key_exists($classCall, $mappedPolicies)) {
                $newUsages = array_merge(
                    $newUsages,
                    [$tableConfig['model'], str_replace('\\\\', '\\', $fullPolicyName)]
                );

                $mappedPolicies[$className . '::class'] = $policyName . '::class';
            } // if
        }
        return array($newUsages, $mappedPolicies);
    } // function

    /**
     * Returns the parsed authserviceprovider file.
     * @return File|void
     */
    protected function getAuthProviderFile()
    {
        if (!$this->authProviderFile) {
            if (file_exists($providerFile = app_path('Providers/AuthServiceProvider.php'))) {
                /** @var File $file */
                $this->authProviderFile = $this->getServices()->make(File::class, [$providerFile]);
            } // if
        } // if

        return $this->authProviderFile;
    } // function

    /**
     * Returns the templated php content for the policy file.
     * @param string $table
     * @return string
     */
    protected function getPolicyMethods($table)
    {
        $config = $this->getConfig();
        $tableConfig = @$config['tables'][$table] ?: [];
        $policyConfig = [
            'defaultBeforeCallback' => @$config['before'] ?: 'return null;',
            'defaultReturn' => @$config['policy'] ? 'true' : 'false',
            'entityClass' => preg_replace('/(\w+\\\)+/', '', $tableConfig['model'])
        ];
        $template = storage_path("rest-scaffolding/policies/{$table}.stub");

        if (!file_exists($template)) {
            $template = storage_path('rest-scaffolding/policies/default.stub');
        } // if

        if (array_key_exists('before', $tableConfig)) {
            $policyConfig['defaultBeforeCallback'] = $tableConfig['before'];
        } // if

        if (array_key_exists('policy', $tableConfig)) {
            $policyConfig['defaultReturn'] = $tableConfig['policy'] ? 'true' : 'false';
        } // if

        return str_replace(
            array_map(function ($key) {
                return "{{$key}}";
            }, array_keys($policyConfig)),
            array_values($policyConfig),
            file_get_contents($template)
        );
    } // function

    /**
     * Returns the array of the used policies from the auth provider.
     * @param Line $line The matched code line.
     * @return array
     */
    protected function getUsedPoliciesFromLine(Line $line)
    {
        $return = [];

        preg_match('/^protected\s*\$policies\s=\s\[(.*)\];$/msU', $line, $matches);

        if ($policyLinesMatch = $matches[1]) {
            $return[] = $policyLinesMatch;

            foreach (array_filter(explode(',', $policyLinesMatch), 'trim') as $policyLine) {
                list($model, $policyClass) = array_map(
                    'trim',
                    explode('=>', $policyLine)
                );

                $return[1][$model] = $policyClass;
            } // foreach
        } // if

        return $return;
    } // function

    /**
     * Returns the array of the used policies from the auth provider.
     * @return Line|void
     */
    protected function getUsedPoliciesLineFromProvider()
    {
        $return = null;

        if ($file = $this->getAuthProviderFile()) {
            $return = $file->findLine('/' . preg_quote('protected $policies = ', '/') . '/', 1);
        } // if

        return $return;
    } // function

    /**
     * Returns the service container.
     * @return Container
     */
    public function getServices()
    {
        return $this->services;
    } // function

    /**
     * Execute the job.
     * @return bool
     * @throws Exception If there is something wrong.
     */
    public function handle()
    {
        if ($policiesCodeLine = $this->getUsedPoliciesLineFromProvider()) {
            list($rawMatch, $mappedPolicies) = $this->getUsedPoliciesFromLine($policiesCodeLine);
            list($newUsages, $mappedPolicies) = $this->createNewPolicies($mappedPolicies);

            if ($mappedPolicies) {
                $this->addNewPoliciesToProvider($mappedPolicies, $policiesCodeLine, $rawMatch);
            } // if

            if ($newUsages) {
                $this->addNewUsagesToProvider($newUsages);
            } // if
        } // if
    } // function

    /**
     * Sets the service container.
     * @param Container $services
     * @return PolicyWriter
     */
    public function setServices($services)
    {
        $this->services = $services;

        return $this;
    } // function
}

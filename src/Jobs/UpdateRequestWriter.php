<?php

namespace b3nl\RESTScaffolding\Jobs;

/**
 * Writes/changes the update request file for the project.
 * @author b3nl
 * @category Jobs
 * @package b3nl\RESTScaffolding
 * @subpackage Jobs
 * @version $id$
 */
class UpdateRequestWriter extends StoreRequestWriter
{
    /**
     * Returns the target path.
     * @return string
     */
    protected function getTargetPath()
    {
        $config = $this->getConfig();

        return app_path("Http/Requests/{$config['customNamespace']}/{$config['entityClass']}/UpdateRequest.php");
    } // function

    /**
     * Returns path to the template.
     * @return string
     */
    protected function getTemplatePath()
    {

        $config = $this->getConfig();
        $return = storage_path("rest-scaffolding/requests/update/{$config['tableName']}.stub");

        if (!file_exists($return)) {
            $return = storage_path('rest-scaffolding/requests/update/default.stub');
        } // if

        return $return;
    } // function
}

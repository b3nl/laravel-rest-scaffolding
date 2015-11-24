<?php

namespace b3nl\RESTScaffolding\Jobs;

use Illuminate\Bus\Queueable;

/**
 * The basic class.
 * @author b3nl
 * @category Jobs
 * @package b3nl\RESTScaffolding
 * @subpackage Jobs
 * @version $id$
 */
abstract class Job
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "onQueue" and "delay" queue helper methods.
    |
    */
    use Queueable;

    /**
     * The used config.
     * @var array|null
     */
    protected $config = null;

    /**
     * Create a new job instance.
     * @param array $config The config.
     */
    public function __construct($config)
    {
        $this->setConfig($config);
    } // function

    /**
     * Returns the config.
     * @return array|null
     */
    public function getConfig()
    {
        return $this->config;
    } // function

    /**
     * Sets the config.
     * @param array $config
     * @return Job
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    } // function
}

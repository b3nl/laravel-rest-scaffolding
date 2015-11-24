<?php

namespace b3nl\RESTScaffolding\Providers;

use b3nl\RESTScaffolding\Console\Commands\MakeRest;
use Illuminate\Support\ServiceProvider;

/**
 * Service-Provider.
 * @author b3nl
 * @category Providers
 * @package b3nl\RESTScaffolding
 * @subpackage Providers
 * @version $id$
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(
            [__DIR__.'/../../config/rest-scaffolding.php' => config_path('rest-scaffolding.php')],
            'config'
        );

        $this->publishes(
            [__DIR__.'/../../storage/rest-scaffolding' => storage_path('rest-scaffolding')],
            'templates'
        );
    } // function

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([MakeRest::class]);
    } // function
}

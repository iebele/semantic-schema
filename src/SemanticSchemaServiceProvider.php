<?php

namespace Iebele\SemanticSchema;

use Illuminate\Support\ServiceProvider;


/**
 * Class SemanticSchemaServiceProvider
 * @package Esb\Esb
 */
class SemanticSchemaServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred .
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register() {

        $this->app->singleton('command.schema.update', function()
        {
            return new  Commands\SchemaUpdate;
        });


        $this->commands(
            'command.schema.update'
        );
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot() {
        
        require __DIR__ . '/Http/routes.php';
        $this->loadViewsFrom( __DIR__ . '/resources/views', 'semantic-schema');

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        
    }

}

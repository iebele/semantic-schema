<?php

namespace Iebele\SemanticSchema;

use Illuminate\Support\ServiceProvider;


/**
 * Class SemanticSchemaServiceProvider
 * @package Artifact2\Collectional
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


        $this->app->singleton('command.schema.migrate', function()
        {
            return new  Commands\SchemaMigrate;
        });


        $this->commands(
            ['command.schema.update', 'command.schema.migrate']
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


        // You can use the command schema:migrate to create the tables.
        // Uncomment this line if you want to add the migration to the standard artisan migrate procedure.
        // 
        //$this->loadMigrationsFrom(__DIR__ . '/../migrations');
        
    }

}

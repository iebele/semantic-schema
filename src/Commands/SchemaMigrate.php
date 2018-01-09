<?php namespace Iebele\SemanticSchema\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// raw queries
use Illuminate\Support\Facades\DB as DB;

use Illuminate\Console\Command;


/**
 *
 *
 * @copyright  Copyright (C) 2017 Iebele Abel
 * @license    Licensed under the MIT License; see LICENSE
 *
 *
 */




class SchemaMigrate extends Command {


    /*
     * Print detailed information
     */
    protected  $verbose;



    /**
     * The console command name.
     *
     * @var string
     */
    //protected $name = 'schema:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create table structure for semantic-schema.';


    protected $name = 'schema:migrate';


    /**
     *
     */

    public function handle()
    {

        $this->fire();
    }


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {

        if ($this->confirm('This action will drop all semantic-schema tables and build new, empty ones. Do you wish to continue?')) {
            $this->info("Running migrations");
            $this->drop();
            $this->migrate();
            $this->info("Done");
            $this->info(PHP_EOL);
            $this->comment("Run 'php artisan schema:update' to seed the tables.");
        }
        else {
            $this->info("Cancelled");
        }

    }

    private function drop() {

        $this->info("Drop tables");
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('schema_types');
        Schema::dropIfExists('schema_parent_type');
        Schema::dropIfExists('schema_properties');
        Schema::dropIfExists('schema_expected_types');
        Schema::dropIfExists('schema_property_type');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


    }

    private function migrate() {

        $this->info("Create tables");

        Schema::create('schema_types', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('position')->nullable();
            $table->boolean('favorite')->default(false);
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('url')->nullable();
            // 'extends' is a comma seperated lists of parents (used to generate the 'schema_parent_type' table in the second pass of parsing schema.og)
            $table->text('extends')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // A type hasMany types as parent
        Schema::create('schema_parent_type', function(Blueprint $table)
        {
            $table->increments('id');

            $table->integer('parent_id')->unsigned()->index();
            $table->foreign('parent_id')->references('id')->on('schema_types');

            $table->integer('type_id')->unsigned()->index();
            $table->foreign('type_id')->references('id')->on('schema_types');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('schema_properties', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('position')->nullable();
            $table->boolean('favorite')->default(false);
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('url')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // A property hasMany expected values (which, many cases, can be types)
        Schema::create('schema_expected_types', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('property_id')->unsigned()->index();
            $table->foreign('property_id')->references('id')->on('schema_properties');
            $table->string('type_name')->nullable();
            $table->string('property_name')->nullable();
            //$table->integer('type_id')->unsigned()->index();
            //$table->foreign('type_id')->references('id')->on('schema_types');

            $table->timestamps();
            $table->softDeletes();
        });

        // A type hasMany properties
        Schema::create('schema_property_type', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('position')->nullable();
            $table->boolean('favorite')->default(false);
            $table->integer('property_id')->unsigned()->index();
            $table->foreign('property_id')->references('id')->on('schema_properties');
            $table->integer('type_id')->unsigned()->index();
            $table->foreign('type_id')->references('id')->on('schema_types');

            $table->timestamps();
            $table->softDeletes();
        });
    }



}
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateSchemaOrgTypesProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * The 'position' and 'favorite' columns are used for indexing.
     *
     * @return void
     */
    public function up()
    {

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


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::drop('schema_types');
        Schema::drop('schema_parent_type');
        Schema::drop('schema_properties');
        Schema::drop('schema_expected_types');
        Schema::drop('schema_property_type');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

}

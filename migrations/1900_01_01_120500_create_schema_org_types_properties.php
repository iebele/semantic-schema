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
        });


        Schema::create('schema_types_pivot', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->boolean('favorite')->default(false);
            $table->integer('child_id')->unsigned()->index();
            $table->foreign('child_id')->references('id')->on('schema_types');
            $table->integer('parent_id')->unsigned()->index();
            $table->foreign('parent_id')->references('id')->on('schema_types');
        });

        Schema::create('schema_properties', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('position')->nullable();
            $table->boolean('favorite')->default(false);
            $table->string('name');
            $table->string('expected_type')->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('schema_property_types', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('position')->nullable();
            $table->integer('type_id')->unsigned()->index();
            $table->foreign('type_id')->references('id')->on('schema_types');
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
        Schema::drop('schema_types_pivot');
        Schema::drop('schema_properties');
        Schema::drop('schema_property_types');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

}

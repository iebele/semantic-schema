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
            $table->text('extends')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('schema_properties', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('position')->nullable();
            $table->boolean('favorite')->default(false);
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('from_type')->nullable();
            $table->text('url')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // A property hasMany expected values (which are types)
        Schema::create('schema_expected_types', function(Blueprint $table)
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
        Schema::drop('schema_properties');
        Schema::drop('schema_expected_types');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

}

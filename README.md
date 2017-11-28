# semantic-schema

Laravel/Lumen package for schema.org types and properties.

*Please not this package is under construction. It doesn't work yet*


## Purpose

[Schema.org](http://schema.org) is a collaborative, community activity with a mission to create, maintain, and promote schemas for *structured data* on the Internet, on web pages, in email messages, and beyond. [Schema.org](http://schema.org) provides an expanding list of `types`. Each `type` has a list of expected `properties`, which can be strings or values, or, in many cases, one or more `types`. 

An example of a type is a `CreativeWork`, which has - among many others - a property `author`, which can be an `Organization` or a `Person`. An `Organization` or a `Person` is nothing but another type, which both have a property `name`. `name` can be any string describing the person or organziation. But, the property `author` of the type `CreativeWork` can also be any string describing the person or organziation. It is up to the designer of any piece of structured data which 'schema' applies best in a given situation. 

Software dealing with structured data design can use this package to retrieve lists of `types` and their expected `properties` as provided by [Schema.org](http://schema.org).

The main types provide by [Schema.org](http://schema.org) are:

   - Action
   - CreativeWork
   - Event
   - Intangible
   - Organization
   - Person
   - Place
   - Product 
   - MedicalEntity

### Example

```php
$schema = new Schema();
// return all sub-types and properties of 'CreativeWork'
$creativeWork = $schema->type('CreativeWork');
```



## Installation

### PSR-4


In the root of your Laravel/Lumen installation create a directory 'packages' (if it does not already exists) and change to this directory.

Clone this repository:

```bash
git clone https://github.com/iebele/semantic-schema
```

Add the following line to the 'autoload/psr-4' section of the composer.json file:

```json
{
        "psr-4": {
            "App\\": "app/",
            "Iebele\\SemanticSchema\\": "packages/iebele/semantic-schema/src"

        }
}
```

Add the following line to your `bootstrap/app.php (Lumen)` file:

```php
/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(Iebele\SemanticSchema\SemanticSchemaServiceProvider::class);
```

In your terminal run:

```bash
composer dump-autoload
```

Now, if the installation is succesfull, you can run in your terminal:

```
php artisan list
```

You will see the available commands for **semantic-schema** listed under `schema`.

<hr>

The migrations in this packages are disabled by default to prevent overwriting tables.
You can change the file `SemanticSchemaServiceProvider.php` or add the following to your migrations:


```php
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
```


## Todo

- [x] Readme
- [ ]
- [ ] Artisan Command
- [ ] Package Installation
- [x] Migrations

## References

  - [Schema.org](http://schema.org)
  - [A Web Bot that crawls the Schema.org web site to retrieve all available Types and Properties](https://github.com/alexprut/Spider4Schema)
  - [A nice visual example of Schema.org](https://technicalseo.com/seo-tools/schema-markup-generator/visual/)



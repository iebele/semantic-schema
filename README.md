# semantic-schema

Laravel/Lumen package for schema.org types and properties.

*Please not this package is under construction. It doesn't work yet*


## Purpose

[Schema.org](http://schema.org) is a collaborative, community activity with a mission to create, maintain, and promote schemas for *structured data* on the Internet, on web pages, in email messages, and beyond.

[Schema.org](http://schema.org)  is a markup vocabulary for structured data developed by Google, Microsoft, Yahoo and Yandex, with the goal of creating a structured data markup that all search engines can understand.

[Schema.org](http://schema.org) provides an ever expanding list of `types`. Each `type` has a list of expected `properties`, which can be strings or values, or, in many cases, one or more `types`.

An example of a type is a `CreativeWork`, which has - among many others - a property `author`, which can be an `Organization` or a `Person`. An `Organization` or a `Person` is nothing but another type, which both have a property `name`. `name` can be any string describing the person or organziation. But, the property `author` of the type `CreativeWork` can also be any string describing the person or organziation. It is up to the designer of any piece of structured data which 'schema' applies best in a given situation.

Software dealing with structured data design can use this package to retrieve lists of `types` and their expected `properties` as provided by [Schema.org](http://schema.org).

The main types provided (following [Schema.org](http://schema.org))are:

   - Action
   - CreativeWork
   - Event
   - Intangible
   - Organization
   - Person
   - Place
   - Product 
   - MedicalEntity

### Quick Example

```php
$schema = new SemanticSchema();

// return all main types
$mainTypes = $schema->mainTypes();

// return all data types ('Boolean', 'Date', 'DateTime', 'Number', etc. )
$mainDataTypes = $schema->dataTypes();

// return all sub-types and properties of 'CreativeWork'
$creativeWork = $schema->type('CreativeWork');
```



## Installation

### PSR-4


In the root of your Laravel/Lumen installation create a directory 'packages' (if it does not already exists) and change to this directory.

Clone this repository:

```bash{r, engine='bash', clone_repo}
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

```bash{r, engine='bash', dump_autoload}
composer dump-autoload
```

Now, if the installation is succesfull, you can run in your terminal:

```bash{r, engine='bash', artisan_list}
php artisan list
```

You will see the available commands for **semantic-schema** listed under `schema`.

## Usage



The artisan command `php artisan schema:update` fetches all types and its properties from schema.org.
All types and properties are stored in database tables.  `php artisan schema:update` will check if the database tables exists.
If they don't, this command will run the migrations.
 Entries in the tables will never be overwritten (in order to keep the integrity of relationships);
 new types and properties will be added to the tables, but existing ones only will be updated.

The tables used by `Iebele/SemanticSchema` are:

 -  schema_properties
 -  schema_property_types
 -  schema_types
 -  schema_types_pivot

You can access all schema.org `types` and `properties` (read-only) with the class `Iebele/SemanticSchema/SemanticSchema`.
The code below shows how to generate a list of all types (types of `Thing`) and properties of schema.org.
This is an example of all public methods available in the class.

```php
$schema = new SemanticSchema();
$tree = [];
index = 0;
foreach ($schema->types() as $type ){
    $tree[$index]['types'] = $type->types();
    $tree[$index]['properties'] = $type->properties();

    index++;
}

```


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

## Limitations

**Semantic Schema** uses the [Full Hierarchy](https://schema.org/docs/full.html) as provided by [Schema.org](http://schema.org).
The collection of the  [Full Hierarchy](https://schema.org/docs/full.html) by **Semantic Schema** using about 700+ HTTP requests for each `type` takes considerable time.
Might the markup of the [Full Hierarchy](https://schema.org/docs/full.html) change, **Semantic Schema** might produce errors while fetching the data.
If this might happen, please report this to me.

I could have decided to use [schema.rdfa](https://github.com/schemaorg/schemaorg/blob/master/data/schema.rdfa).
But, at the moment of writing this package I could not decide how to obtain all properties of a given `type`, so "I did it my way".

## References

  - [Schema.org](http://schema.org)
  - [A Web Bot that crawls the Schema.org web site to retrieve all available Types and Properties](https://github.com/alexprut/Spider4Schema)
  - [A nice visual example of Schema.org](https://technicalseo.com/seo-tools/schema-markup-generator/visual/)



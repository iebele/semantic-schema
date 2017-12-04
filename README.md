# semantic-schema


[![Build Status](https://travis-ci.org/iebele/semantic-schema.svg?branch=master)](https://travis-ci.org/iebele/semantic-schema)


**[Semantic Schema](https://github.com/iebele/semantic-schema)** is a package for using
the full schema.org vocabulary in your PHP Laravel/Lumen (version 5.5) project.
It comes with Artisan commands to make local updates of your schema.org vocabulaire,
some straightforwarded PHP helper classes and a simple, yet powerful API.

Are you not familiar with semantic web practice, yet?
Probably you should :wink:

Please read the following documents before you start:

 - [Schema.org vocab](http://schema.org)
 - [W3C Semantic Web](https://www.w3.org/standards/semanticweb)
 - [W3C Structured/Micro Data](https://www.w3.org/TR/microdata/)


## Purpose

[Schema.org](http://schema.org)  is a markup vocabulary for structured data developed by Google, Microsoft, Yahoo and Yandex.
[Schema.org](http://schema.org) is a collaborative activity with a mission to create, maintain, and promote schemas
for *structured data* on the Internet, on web pages, in email messages, and beyond.


[Schema.org](http://schema.org) provides an ever expanding list of `types` and `properties`.
This schema vocabulary is maintained by a large community of specialists; it is a great starting point to semantically organize and distribute collections and content of any type.

**[Semantic Schema](https://github.com/iebele/semantic-schema)** provides quick and reliable
(server-side) access to all `types` and `properties` - **including their many-to-many relations**.


The main types provided (following the structure of [Schema.org](http://schema.org)) are:

   - Action
   - CreativeWork
   - Event
   - Intangible
   - Organization
   - Person
   - Place
   - Product 
   - MedicalEntity


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

To seed the database tables with data from schema.org, you need to run
```bash
php artisan schema:update
```

The updating proces may take ±30 minutes.

The artisan command `php artisan schema:update` fetches all types and its properties from schema.org.
All types and properties are stored in database tables.  `php artisan schema:update` will check if the database tables exists.
If they don't, this command will run the migrations.
 Entries in the tables will never be overwritten (in order to keep the integrity of relationships);
 new types and properties will be added to the tables, but existing ones only will be updated.

The tables used by `Iebele/SemanticSchema` are:

- schema_expected_types
- schema_parent_type
- schema_properties
- schema_property_type
- schema_types


## Usage of the package


#### API

Four API endpoints can be used:

 - GET semantic-schema/api/main (return all main types)
 - GET semantic-schema/api/type/ (return all types)
 - GET semantic-schema/api/type/{name} (return type with name 'name')
 - GET semantic-schema/api/type/{name}/properties (return all properties type with name 'name)

These endpoints return JSON on success, ```null``` on error.




#### The SemanticSchema Class

You can access all schema.org `types` and `properties` (read-only) with the class `Iebele/SemanticSchema/SemanticSchema`.
The code below shows how to generate a list of all main types and their properties.


```php

$types = SemanticSchema::mainTypes();
foreach($types as $type){
    echo "<h1>" . $type->name . "</h1>";
    $typeProperties = SemanticSchema::getTypeProperties($type->name);
    echo "<ul>";
    foreach($typeProperties as $typeProperty){
        echo "<li>" . $typeProperty->name . "</li>";
    }
    echo "</ul>
}

```


The method ```SemanticSchema::allTypes()``` returns all types;
the method ```SemanticSchema::getType($name)``` returns the type with name `name`.

**That's all what it takes!**


#### HTML view


At last, the route `semantic-schema/` return a plain HTML file which lists all current main types, types and properties found in
  the local copy of the [Full Hierarchy](https://schema.org/docs/full.html).
  Each `type` has a button, which retrieves all properties of that given type.
  The screendump below shows a fragment (the 'Place' type)of the shown hierarchy:

!["HTML fragment of view index.php"](docs/images/html.jpg?raw=true "HTML fragment of view index.php")




## Limitations



- *Semantic Schema* uses the [Full Hierarchy](https://schema.org/docs/full.html) as provided by [Schema.org](http://schema.org).
The collection of the  [Full Hierarchy](https://schema.org/docs/full.html) takes about an average of ±25 HTTP requests for each `type`.
Collecting all types and their properties consumes considerable time (about 30 minutes).

- The collection of types is done by the Artisan command `schema::update`.
This command crawls through the  [Full Hierarchy](https://schema.org/docs/full.html) of schema.org
If the markup of this document and its links might change in the future, *Semantic Schema* might produce errors while fetching the data.
If this happens, please report this to me.

- The Artisan command `schema::update` relies on many HTTP requests to [schema.org](https://schema.org/). In case of a connection time-out the command will fail and report an error.
In such a case, one might run `schema::update` again, although the integrity of the tables is not guaranteed.

- The `id's` used in the tables generated by the `schema::update` command might change when tables are reset by a migration.
Therefore one should not use *Sematic Schema* `id's` in applications but rather use `type` and `property` names instead .
The functions `SemanticSchema::getType( (string) $typeName)` and and `SemanticSchema::getProperty( (string) $propertyName)`
return the respective models.

- Tests are not yet available.




## FAQ

 - *The [Semantic Schema](https://github.com/iebele/semantic-schema) `update` command makes many requests to [schema.org](https://schema.org/). Doesn't this look like a DDOS attack?*

The **Semantic Schema** `update` command does not DDOS [schema.org](https://schema.org/).
The `update` makes HTTPS requests with random time intervals.
[schema.org](https://schema.org/) is prepared for intensive traffic.

 -  *Why don't you use [schema.rdfa](https://github.com/schemaorg/schemaorg/blob/master/data/schema.rdfa) instead of the [Full Hierarchy](https://schema.org/docs/full.html)?*

I could have decided to use [schema.rdfa](https://github.com/schemaorg/schemaorg/blob/master/data/schema.rdfa).
But, at the moment of writing this package I could not decide how to obtain all properties of a given `type`, so "I did it my way".

## References

  - [Schema.org](http://schema.org)
  - [A Web Bot that crawls the Schema.org web site to retrieve all available Types and Properties](https://github.com/alexprut/Spider4Schema)
  - [A nice visual example of Schema.org](https://technicalseo.com/seo-tools/schema-markup-generator/visual/)



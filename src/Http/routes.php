<?php



/*
|--------------------------------------------------------------------------
| Route for viewing a simple html page with all types and its properties
|--------------------------------------------------------------------------
|
*/

$this->app->router->get('/semantic-schema', Iebele\SemanticSchema\Http\Controllers\SimpleController::class . '@index');


/*
|--------------------------------------------------------------------------
| Simple API
|
| An example of using the API is given in the view
| iebele/semantic-schema/src/resources/views/index.blade.php
|--------------------------------------------------------------------------
|
*/

// Return all schema.org main types
$this->app->router->get('/semantic-schema/api/main/', Iebele\SemanticSchema\Http\Controllers\SimpleController::class . '@mainTypes');
// Return all schema.org types
$this->app->router->get('/semantic-schema/api/type/', Iebele\SemanticSchema\Http\Controllers\SimpleController::class . '@type');
// Return schema.org type with name 'name'
$this->app->router->get('/semantic-schema/api/type/{name}', Iebele\SemanticSchema\Http\Controllers\SimpleController::class . '@type');
// Return schema.org parents of type with name 'name'
$this->app->router->get('/semantic-schema/api/type/{name}/parents', Iebele\SemanticSchema\Http\Controllers\SimpleController::class . '@typeParents');
// Return schema.org children of type with name 'name'
$this->app->router->get('/semantic-schema/api/type/{name}/childs', Iebele\SemanticSchema\Http\Controllers\SimpleController::class . '@typeChilds');
// Return all properties of schema.org type with name 'name'
$this->app->router->get('/semantic-schema/api/type/{name}/properties', Iebele\SemanticSchema\Http\Controllers\SimpleController::class . '@typeProperties');
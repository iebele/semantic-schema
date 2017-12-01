<?php



/*
|--------------------------------------------------------------------------
| Route for viewing a simple html page with all types and its properties
|--------------------------------------------------------------------------
|
*/


$this->app->router->get('/semantic-schema', Iebele\SemanticSchema\Http\Controllers\SimpleController::class . '@index');

$this->app->router->get('/semantic-schema/type/{name}', Iebele\SemanticSchema\Http\Controllers\SimpleController::class . '@type');
$this->app->router->get('/semantic-schema/type/{name}/properties', Iebele\SemanticSchema\Http\Controllers\SimpleController::class . '@typeProperties');
<?php



/*
|--------------------------------------------------------------------------
| Route for viewing a simple html page with all types and its properties
|--------------------------------------------------------------------------
|
*/


$this->app->router->get('/semantic-schema', Iebele\SemanticSchema\Http\Controllers\SimpleController::class . '@index');
    

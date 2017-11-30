<?php

namespace Iebele\SemanticSchema\Http\Controllers;

use Iebele\SemanticSchema\Models\SchemaTypes as SchemaTypes;
use Iebele\SemanticSchema\Models\SchemaProperties as SchemaProperties;

use Laravel\Lumen\Routing\Controller as BaseController;



/**
 * Class SimpleController
 * @package Iebele\SemanticSchema\Http\Controllers
 */
class SimpleController extends BaseController
{


    public function index(){

        $data= null;
        $types= SchemaTypes::all();
        foreach ($types as $type){
            $data['types'][$type->name] = $type;
        }

        $properties= SchemaProperties::all();
        foreach ($properties as $property){
            $data['properties'][$property->name] = $property;
        }


       return view('semantic-schema::index')->with('data', $data);

    }

}

<?php

namespace Iebele\SemanticSchema\Http\Controllers;

use Iebele\SemanticSchema\SemanticSchema as SemanticSchema;


use Laravel\Lumen\Routing\Controller as BaseController;



/**
 * Class SimpleController
 * @package Iebele\SemanticSchema\Http\Controllers
 */
class SimpleController extends BaseController
{


    public function index(){


        $data= null;

        $mainTypes = SemanticSchema::mainTypes();
        foreach ($mainTypes as $type) {
            $data['mainTypes'][$type->name]['name'] = $type->name;
            $data['mainTypes'][$type->name]['description'] = $type->description;
            $data['mainTypes'][$type->name]['url'] = $type->url;
            $data['mainTypes'][$type->name]['parents'] = $type->getParentTypes();
            $data['mainTypes'][$type->name]['childs'] = $type->getChildTypes();
        }

        $types= SemanticSchema::allTypes();
        foreach ($types as $type) {
            $data['types'][$type->name]['name'] = $type->name;
            $data['types'][$type->name]['description'] = $type->description;
            $data['types'][$type->name]['url'] = $type->url;
            $data['types'][$type->name]['parents'] = $type->getParentTypes();
            $data['types'][$type->name]['childs'] = $type->getChildTypes();
        }



        $properties= SemanticSchema::allProperties();
        foreach ($properties as $property){
            $data['properties'][$property->name] = $property;
        }


       return view('semantic-schema::index')->with('data', $data);

    }

    public function type($name){

        return SemanticSchema::getType($name);

    }

    public function typeProperties($name){

        return SemanticSchema::getTypeProperties($name);

    }



}

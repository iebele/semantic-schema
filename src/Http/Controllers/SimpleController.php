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


    /**
     * Return a view with all main types and types (including parents and childs).
     * The view includes javascript functions with basic AJAX requests to get the properties of each type.
     *
     * @return $this
     */
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

    /**
     * API controller method GET /main
     * @param null $name
     * @return mixed
     */
    public function mainTypes(){

        return SemanticSchema::mainTypes();

    }


    /**
     * API controller method GET /type
     * @param null $name
     * @return mixed
     */
    public function type( $name = null ){

        if ($name){
            return SemanticSchema::getType($name);
        }
        return SemanticSchema::allTypes();

    }

    /**
     * API controller method GET /type/{name}/parents
     * @param null $name
     * @return mixed
     */
    public function typeParents( $name  ){

        if ($name){
            return SemanticSchema::getType($name)->parents()->get();
           // return SemanticSchema::getType($name)->getParentTypes();
        }
        return null;

    }

    /**
     * API controller method GET /type/{name}/childs
     * @param null $name
     * @return mixed
     */
    public function typeChilds( $name ){

        if ($name){
            return SemanticSchema::getType($name)->getChildTypes();
        }
        return null;

    }


    /**
     * API controller method GET /main/{name}/properties
     * @param $name
     * @return mixed
     */
    public function typeProperties($name){

        return SemanticSchema::getTypeProperties($name);

    }



}

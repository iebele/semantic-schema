<?php namespace Iebele\SemanticSchema;

/**
 *
 * @copyright  Copyright (C) 2017 Iebele Abel
 * @license    Licensed under the MIT License; see LICENSE

*/


use Iebele\SemanticSchema\Models\SchemaTypes as SchemaTypes;
use Iebele\SemanticSchema\Models\SchemaProperties as SchemaProperties;


class SemanticSchema 
{


    /**
     * @return mixed
     */
    static public function allTypes(){

        return SchemaTypes::all();

    }


    /**
     * @return mixed
     */
    static public function getType($name){

        return SchemaTypes::where('name', $name)->first();

    }

    /**
     * @return mixed
     */
    static public function getTypeProperties($name){

        $type= SchemaTypes::where('name', $name)->first();
        return $type->getProperties($type->name);

    }

    /**
     * @return mixed
     */
    static public function mainTypes(){

        $types[] = SemanticSchema::getType('Action');
        $types[] = SemanticSchema::getType('CreativeWork');
        $types[] = SemanticSchema::getType('Event');
        $types[] = SemanticSchema::getType('Intangible');
        $types[] = SemanticSchema::getType('Organization');
        $types[] = SemanticSchema::getType('Person');
        $types[] = SemanticSchema::getType('Place');
        $types[] = SemanticSchema::getType('DataType');
        return $types;

    }


    /**
     * @return mixed
     */
    static public function allProperties(){

        return SchemaProperties::all();

    }




}


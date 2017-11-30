<?php namespace Iebele\SemanticSchema;

/**
 *
 * @copyright  Copyright (C) 2017 Iebele Abel
 * @license    Licensed under the MIT License; see LICENSE

*/


use Iebele\SemanticSchema\Models\SchemaTypes as SchemaTypes;


class SemanticSchema 
{
    

    public function getTypes(){

        $types = SchemaTypes::all()->get();
        return $types;

    }


}


<?php


namespace Iebele\SemanticSchema\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SchemaTypes extends Model  {


    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */

    protected $table = 'schema_types';


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['position','favorite','name','description','url','extends'];


    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    /**
     *
     * @return $this
     */
    public function properties()
    {

        die('TODO ' . __METHOD__);
        //return $this->hasMany('Iebele\SemanticSchema\Models\SchemaProperties')->orderBy('position', 'asc');
    }

    
    public static function addType( $name, $description, $extends, $url )
    {

        $result = null;
        // Do not overwrite existing types
        $check = SchemaTypes::where('name', $name)->first();
        if (!$check){
            $type = [
                'name' => $name,
                'description' => $description,
                'extends' => $extends,
                'url' => $url
            ];
            $result = SchemaTypes::create($type);
        }
        
        return $result;

    }
/*
    public static function addPropertyToType( $typeName, $propertyName, $propertyDescription, $propertyUrl , $expectedTypeNames)
    {

        // Do not overwrite existing property
        $check = SchemaProperties::find('name' , $propertyName)->first();
        if (!$check){
            $property = [
                'name' => $propertyName,
                'description' => $propertyDescription,
                'url' => $propertyUrl
            ];
            $result = SchemaProperties::create($property);
        }

        return $result;


    }
*/

}
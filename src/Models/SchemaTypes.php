<?php


namespace Iebele\SemanticSchema\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Iebele\SemanticSchema\Models\SchemaProperties as SchemaProperties;
use Iebele\SemanticSchema\Models\SchemaExpectedTypes as SchemaExpectedTypes;
use Iebele\SemanticSchema\Models\SchemaPropertyType as SchemaPropertyType;

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
        return $this->belongsToMany('Iebele\SemanticSchema\Models\SchemaPropertyType', 'schema_property_type',  'type_id', 'property_id'); // ->orderBy('schema_property_type.position', 'asc');

    }

    
    public static function addType( $name, $description, $extends, $url )
    {


        $type = null;
        // Do not overwrite existing types
        $type = SchemaTypes::where('name', $name)->first();
        if (!$type){
            $typeValues = [
                'name' => $name,
                'description' => $description,
                'extends' => $extends,
                'url' => $url
            ];
            $type = SchemaTypes::create($typeValues);
            return $type;
        }

        if ($type){
            return $type;
        }
        return null;

    }


    public function addPropertyToType( $typeName, $propertyName, $propertyDescription, $propertyUrl , $expectedTypeNames)
    {

        $type=SchemaTypes::where('name', $typeName)->first();
        $property = null;
        if ($type){
            // Do not overwrite existing property
            $check = SchemaProperties::where('name' , $propertyName)->first();
            if (!$check ){
                $propertyValues = [
                    'name' => $propertyName,
                    'description' => $propertyDescription,
                    'url' => $propertyUrl
                ];
                $property = SchemaProperties::create($propertyValues);
            }
            else {
                $property = $check;
            }

            if(!$property){
                die( __METHOD__ . " -  NULL property for type  " . $typeName);
            }

            // Add expected types of property to table schema_expected_types
            foreach ($expectedTypeNames as $expectedTypeName ){
                $expectedTypeValues = [
                    'property_id' => $property->id,
                    'typeName' => $expectedTypeName
                ];
                SchemaExpectedTypes::create($expectedTypeValues);
            }

            // add property to list of properties of type (type hasMany properties)
            $type->properties()->attach($property->id);

            return $property;

        }
        die( __METHOD__ . " -  No such type " . $typeName);
        return null;
    }


}
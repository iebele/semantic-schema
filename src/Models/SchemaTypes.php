<?php


namespace Iebele\SemanticSchema\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// raw queries
use Illuminate\Support\Facades\DB as DB;

use Iebele\SemanticSchema\Models\SchemaProperties as SchemaProperties;
use Iebele\SemanticSchema\Models\SchemaExpectedTypes as SchemaExpectedTypes;
use Iebele\SemanticSchema\Models\SchemaPropertyType as SchemaPropertyType;
use Iebele\SemanticSchema\Models\SchemaParentType as SchemaParentType;

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

    public function getProperties($typeName)
    {

        $result = null;
        $type=SchemaTypes::where('name' , $typeName)->first();
        $properties =  SchemaPropertyType::where('type_id', $type->id)->get();
        foreach ($properties as $property){
            $result[$property->property_id] = SchemaProperties::where('id', $property->property_id)->get();
        }
        return $result;

    }

    /**
     *
     * @return $this
     */
    public function parents()
    {

        return $this->belongsToMany('Iebele\SemanticSchema\Models\SchemaTypes', 'schema_parent_type', 'type_id', 'parent_id' );

    }

    /**
     *
     * @return $this
     */
    public function getParentTypes()
    {
        $parents =  SchemaParentType::where('type_id', $this->id)->get();

        $result = [];
        if ($parents){
            foreach ($parents as $parent){
                $type = SchemaTypes::where('id' , $parent->parent_id)->first();
                if ($type)
                {
                    $result[] = $type->name;
                }
            }
        }
        return $result;

    }

    /**
     *
     * @return $this
     */
    public function getChildTypes()
    {
        $childs =  SchemaParentType::where('parent_id', $this->id)->get();

        $result = [];
        if ($childs){
            foreach ($childs as $child){
                $type = SchemaTypes::where('id' , $child->type_id)->first();
                if ($type)
                {
                    $result[] = $type->name;
                }
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public static function updateParents(){

        $types = SchemaTypes::all();
        foreach ( $types as $type){
            //var_dump($type->id);
            $extends = explode(",",$type->extends);
            foreach ($extends as $parentName){
                // check if parent already exists
                $parent = SchemaTypes::where('name', $parentName)->first();

                if ( $parent)
                {
                    $type->parents()->attach($parent->id);
                }
                else {
                    echo (PHP_EOL .  __METHOD__ .  PHP_EOL . "No parent found for type " . $type->name . PHP_EOL );
                }
            }
        }
        return true;
   }

    /**
     *
     * @return $this
     */
    public function propertiesOfType()
    {
        $result = [];

        $pivot = DB::table('schema_property_type')
            ->where('type_id', $this->id)
            ->get();

        //die($pivot);
        foreach ($pivot as $record) {
            $properties = SchemaProperties::where('id' , $record->property_id)->get();
            foreach ($properties as $property){
                $result[] = $property->name;
            }
        }
        //var_dump($result);
        //die();
        return  $result;

    }

    
    public static function addType( $name, $description, $extends, $url , $parents )
    {


        $type = null;
        // Do not overwrite existing types
        $type = SchemaTypes::where('name', $name)->first();

        $extends = implode(",",$parents);

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
                // check if relation already exists
                $doit = SchemaExpectedTypes::where([['type_name', '=', $expectedTypeName], ['property_name' , '=',  $propertyName]])->first();
                if ( !$doit)
                {
                    $expectedTypeValues = [
                        'property_id' => $property->id,
                        'type_name' => $expectedTypeName,
                        'property_name' => $propertyName
                    ];
                    SchemaExpectedTypes::create($expectedTypeValues);
                }

            }

            // add property to list of properties of type (type hasMany properties)
            $type->properties()->attach($property->id);

            return $property;

        }
        die( __METHOD__ . " -  No such type " . $typeName);
        return null;
    }


}
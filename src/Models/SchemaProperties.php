<?php


namespace Iebele\SemanticSchema\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SchemaProperties extends Model  {


    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */

    protected $table = 'schema_properties';


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
    protected $fillable = ['position','favorite','name','description','url', 'from_type'];


    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    /*
    public static function addProperty( $name, $description, $extends, $url )
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
    */

    /**
     *
     * @return $this
     */
    public function expectedTypes()
    {
        return $this->hasMany('Iebele\SemanticSchema\Models\SchemaExpectedTypes');

    }


}
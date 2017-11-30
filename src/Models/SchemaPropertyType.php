<?php


namespace Iebele\SemanticSchema\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SchemaPropertyType extends Model  {


    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */

    protected $table = 'schema_property_type';

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
    protected $fillable = ['property_id', 'type_id','position','favorite'];


    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];




}
<?php


namespace Iebele\SemanticSchema\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SchemaExpectedTypes extends Model  {


    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */

    protected $table = 'schema_expected_types';

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
    protected $fillable = ['property_id', 'typeName'];


    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];




}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;

    public $timestamps = true;
    public $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = ['name', 'is_public'];

}

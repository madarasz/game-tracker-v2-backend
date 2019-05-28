<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, SoftDeletes;

    public $timestamps = true;
    protected $fillable = ['name', 'email', 'image_id'];
    protected $hidden = ['password'];

    public function groups() {
        return $this->belongsToMany('App\Group', 'group_user', 'user_id', 'group_id');
    }
    
    public function image() {
        return $this->hasOne('App\Image', 'id', 'image_id');
    }

    public function imageFile() {
        $image = $this->image;
        if (is_null($image)) {
            return null;
        }
        return $image->filename;
    }
}

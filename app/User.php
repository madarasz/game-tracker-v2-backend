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
    protected $hidden = ['password', 'created_at', 'updated_at', 'deleted_at', 'pivot', 'image', 'image_id'];
    protected $appends = ['imageFile'];

    public function groups() {
        return $this->belongsToMany('App\Group', 'group_user', 'user_id', 'group_id')->withPivot('is_group_admin');
    }
    
    public function image() {
        return $this->hasOne('App\Image', 'id', 'image_id');
    }

    public function getImageFileAttribute() {
        $image = $this->image;
        if (is_null($image)) {
            return null;
        }
        return $image->filename;
    }
}

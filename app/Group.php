<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    public $timestamps = true;
    public $hidden = ['created_at', 'updated_at', 'deleted_at', 'is_public'];

    protected $fillable = ['name', 'is_public', 'image_id'];

    public function imageFile() {
        $image = $this->hasOne('App\Image', 'image_id');
        if (is_null($image)) {
            return null;
        }
        return $image->filename;
    }

}

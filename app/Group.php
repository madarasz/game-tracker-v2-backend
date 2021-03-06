<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    public $timestamps = true;
    public $hidden = ['created_at', 'updated_at', 'deleted_at', 'image_id', 'image', 'created_by', 'pivot'];
    protected $appends = ['imageFile', 'memberCount', 'gameCount', 'sessionCount'];
    protected $fillable = ['name', 'is_public', 'image_id', 'created_by'];

    public function image() {
        return $this->hasOne('App\Image', 'id', 'image_id');
    }

    public function creator() {
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function members() {
        return $this->belongsToMany('App\User', 'group_user', 'group_id', 'user_id')->withPivot('is_group_admin')
            ->orderBy('is_admin', 'desc')->orderBy('pivot_is_group_admin', 'desc');
    }

    public function games() {
        return $this->belongsToMany('App\Game', 'group_game', 'group_id', 'game_id');
    }

    public function getImageFileAttribute() {
        $image = $this->image;
        if (is_null($image)) {
            return null;
        }
        return $image->filename;
    }

    public function getMemberCountAttribute() {
        return $this->members->count();
    }

    public function getGameCountAttribute() {
        return $this->games->count();
    }

    public function getSessionCountAttribute() {
        return \DB::table('sessions')->where('group_id', $this->id)->count();
    }
}

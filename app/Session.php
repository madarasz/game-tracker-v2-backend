<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    public $timestamps = true;
    public $hidden = ['created_at', 'updated_at', 'deleted_at', 'pivot'];
    protected $fillable = ['group_id', 'game_id', 'created_by', 'notes', 'place', 'season_id', 'date', 'concluded'];

    public function images() {
        return $this->belongsToMany('App\Image', 'session_image', 'session_id', 'image_id');
    }
}

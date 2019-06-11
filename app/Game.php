<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use SoftDeletes;

    public $timestamps = true;
    public $hidden = ['created_at', 'updated_at', 'deleted_at', 'pivot'];
    protected $fillable = ['id', 'name', 'designers', 'year', 'created_by', 'type', 'thumbnail'];

    public function groups() {
        return $this->belongsToMany('App\Group', 'group_game', 'game_id', 'group_id');
    }
}
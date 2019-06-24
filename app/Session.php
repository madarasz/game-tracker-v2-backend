<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    public $timestamps = true;
    public $hidden = ['created_at', 'updated_at', 'deleted_at', 'created_by', 'pivot'];
    protected $fillable = ['group_id', 'game_id', 'created_by', 'notes', 'place', 'season_id', 'date', 'concluded'];
}

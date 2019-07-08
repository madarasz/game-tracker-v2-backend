<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    public $timestamps = false;
    protected $fillable = ['game_id', 'group_id', 'title', 'description', 'created_by', 'start_date', 'end_date'];

}

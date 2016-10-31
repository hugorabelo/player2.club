<?php

use Illuminate\Database\Eloquent\Model;

class Atividade extends Model
{
    protected $guarded = array();

    protected $table = 'atividade';

    public static $rules = array(
        'users_id' => 'required'
    );
}

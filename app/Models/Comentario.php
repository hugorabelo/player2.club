<?php

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $guarded = array();

    protected $table = 'comentario';

    public static $rules = array();
}

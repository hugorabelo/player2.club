<?php

use Illuminate\Database\Eloquent\Model;

class ConviteUsuario extends Model
{
    protected $guarded = array();

    protected $table = 'convite_usuario';

    public static $rules = array();
}

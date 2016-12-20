<?php

use Illuminate\Database\Eloquent\Model;

class ImagemPost extends Model
{
    protected $guarded = array();

    protected $table = 'imagens';

    public static $rules = array();
}

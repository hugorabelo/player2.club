<?php

class Mensagem extends Eloquent
{
    protected $table = 'mensagem';

    protected $guarded = array();

    public static $rules = array(
        'mensagem' => 'required'
    );
}

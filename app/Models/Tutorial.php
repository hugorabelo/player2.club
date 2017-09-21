<?php

class Tutorial extends \Eloquent
{
    protected $guarded = array();

    protected $table = 'tutorial';

    public static $rules = array(
        'descricao' => 'required',
        'key' => 'required'
    );
}

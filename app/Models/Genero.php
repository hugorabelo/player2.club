<?php

class Genero extends Eloquent {
    protected $guarded = array();

    protected $table = 'genero';

    public static $rules = array(
        'nome' => 'required'
    );
}

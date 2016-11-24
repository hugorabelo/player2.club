<?php

class Produtora extends Eloquent {
    protected $guarded = array();

    protected $table = 'produtora';

    public static $rules = array(
        'nome' => 'required'
    );
}

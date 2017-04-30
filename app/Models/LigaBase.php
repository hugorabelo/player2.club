<?php
/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 4/24/17
 * Time: 9:24 PM
 */
class LigaBase extends Eloquent
{
    protected $table = 'liga_base';

    protected $guarded = array();

    public static $rules = array(
        'nome' => 'required'
    );
}

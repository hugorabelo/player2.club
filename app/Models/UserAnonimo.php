<?php

/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 10/2/18
 * Time: 9:08 AM
 */

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class UserAnonimo extends Eloquent
{
    protected $guarded = array();

    protected $table = 'users_anonimos';

    protected $fillable = array('nome','gamertag','sigla');

    public static $rules = array('nome'=>'required',
        'gamertag'=>'required');

}

<?php
/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 23/02/15
 * Time: 21:23
 */

class UserPlataforma {

    protected $guarded = array();

    protected $table = 'users_plataformas';

    public static $rules = array(
        'users_id' => 'required',
        'plataformas_id' => 'required'
    );

    public function usuario()	{
        return User::find($this->users_id);
    }

    public function plataforma() {
        return Plataforma::find($this->plataformas_id);
    }

}

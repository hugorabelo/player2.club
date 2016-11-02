<?php

use Illuminate\Database\Eloquent\Model;

class Atividade extends Eloquent
{
    protected $guarded = array();

    protected $table = 'atividade';

    public static $rules = array(
        'users_id' => 'required'
    );

    public function curtidas() {
        return $this->belongsToMany('User', 'curtida', 'atividade_id', 'users_id')->withTimestamps();
    }

    public function curtir($idUsuario) {
        if($this->curtiu($idUsuario)) {
            $this->curtidas()->detach($idUsuario);
        } else {
            $this->curtidas()->attach($idUsuario);
        }
    }

    public function curtiu($idUsuario) {
        return $this->curtidas()->wherePivot('users_id', '=', $idUsuario)->get()->count() > 0;
    }
}

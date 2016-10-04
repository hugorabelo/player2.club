<?php

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $guarded = array();

    protected $table = 'comentario';

    public static $rules = array();

    public function curtidas() {
        return $this->belongsToMany('User', 'curtida_comentario', 'comentario_id', 'users_id');
    }

    public function quantidadeCurtidas() {
        $curtidas = $this->curtidas()->get();
        return $curtidas->count();
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

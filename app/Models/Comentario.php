<?php

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $guarded = array();

    protected $table = 'comentario';

    public static $rules = array();

    public function curtidas() {
        return $this->belongsToMany('User', 'curtida_comentario', 'comentario_id', 'users_id')->withTimestamps();
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

            $curtida_id = $this->curtidas()->withPivot('id')->first()->pivot->id;

            $atividade = new Atividade();
            $atividade->users_id = $idUsuario;
            $atividade->curtida_comentario_id = $curtida_id;
            $atividade->save();
        }
    }

    public function curtiu($idUsuario) {
        return $this->curtidas()->wherePivot('users_id', '=', $idUsuario)->get()->count() > 0;
    }
}

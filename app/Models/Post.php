<?php

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = array();

    protected $table = 'post';

    public static $rules = array();

    public function comentarios() {
        $comentarios = $this->hasMany('Comentario', 'post_id')->get();
        foreach ($comentarios as $comentario) {
            $comentario->usuario = User::find($comentario->users_id);
            $comentario->quantidade_curtidas = $comentario->quantidadeCurtidas();
        }
        return $comentarios;
    }

    public function curtidas() {
        return $this->belongsToMany('User', 'curtida', 'post_id', 'users_id');
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

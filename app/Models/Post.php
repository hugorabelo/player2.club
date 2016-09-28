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
        $this->curtidas()->attach($idUsuario);
    }
}

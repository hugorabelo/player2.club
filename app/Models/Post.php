<?php

use Illuminate\Database\Eloquent\Model;

class Post extends Eloquent
{
    protected $guarded = array();

    protected $table = 'post';

    public static $rules = array();

    public function comentarios($idUsuarioLeitor) {
        $comentarios = $this->hasMany('Comentario', 'post_id')->orderBy('created_at')->get();
        foreach ($comentarios as $comentario) {
            $comentario->usuario = User::find($comentario->users_id);
            //$comentario->quantidade_curtidas = $comentario->quantidadeCurtidas();
            $comentario->curtiu = $comentario->curtiu($idUsuarioLeitor);
        }
        return $comentarios;
    }

    public function curtidas() {
        return $this->getAtividade()->curtidas();
    }

    public function quantidadeCurtidas() {
        $curtidas = $this->curtidas()->get();
        return $curtidas->count();
    }

    public function curtir($idUsuario) {
        $this->getAtividade()->curtir($idUsuario);
    }

    public function curtiu($idUsuario) {
        return $this->getAtividade()->curtiu($idUsuario);
    }

    public function getAtividade() {
        $atividade = $this->hasOne('Atividade', 'post_id')->first();
        return $atividade;
    }
}

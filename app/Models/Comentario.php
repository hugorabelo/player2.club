<?php

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $guarded = array();

    protected $table = 'comentario';

    public static $rules = array();

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
        $atividade = $this->hasOne('Atividade', 'comentarios_id')->first();
        return $atividade;
    }
}

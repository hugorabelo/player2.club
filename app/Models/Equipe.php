<?php
/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 5/26/17
 * Time: 11:01 PM
 */

class Equipe extends Eloquent
{
    protected $table = 'equipe';

    protected $guarded = array();

    public static $rules = array(
        'descricao' => 'required'
    );

    public function integrantes() {
        return $this->belongsToMany('User', 'integrante_equipe', 'equipe_id', 'users_id')->withPivot('funcao_equipe_id')->withTimestamps();
    }

    public function adicionarIntegrante($idUsuario, $idFuncao) {
        $this->integrantes()->attach($idUsuario, ['funcao_equipe_id'=>$idFuncao]);
    }

    public function removerIntegrante($idUsuario) {
        $this->integrantes()->detach($idUsuario);
    }

    public function campeonatos() {
        return $this->belongsToMany('Campeonato', 'campeonato_equipe', 'equipe_id', 'campeonatos_id')->withTimestamps();
    }

    public function inscreverCampeonato($idCampeonato) {
        $this->campeonatos()->attach($idCampeonato);
    }

    public function desistirCampeonato($idCampeonato) {
        $this->campeonatos()->detach($idCampeonato);
    }

    public function getAtividades() {
        //TODO
    }

    public function seguidores() {
        //TODO
    }
}

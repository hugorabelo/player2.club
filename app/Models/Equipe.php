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

    public function administradores() {
        $funcoesAdministrativas = DB::table('funcao_equipe')->whereIn('descricao',array('Capitão','Vice-Capitão'))->implode('id', ',');
        $funcoesAdministrativas = explode(',', $funcoesAdministrativas);
        return $this->belongsToMany('User', 'integrante_equipe', 'equipe_id', 'users_id')->withPivot('funcao_equipe_id')->wherePivotIn('funcao_equipe_id', $funcoesAdministrativas)->withTimestamps();
    }

    public function verificaFuncaoAdministrador($idUsuario) {
        foreach ($this->administradores()->get() as $administrador) {
            if($administrador->id == $idUsuario) {
                return true;
            }
        }
        return false;
    }

    public function updateIntegrante($idIntegrante, $idFuncao) {
        DB::table('integrante_equipe')->where('users_id','=',$idIntegrante)->where('equipe_id','=',$this->id)->update(array('funcao_equipe_id'=>$idFuncao));
    }

    public function getAtividades() {
        //TODO
    }

    public function seguidores() {
        //TODO
    }

    public function partidas() {

    }
}

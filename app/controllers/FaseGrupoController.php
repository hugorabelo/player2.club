<?php

class FaseGrupoController extends BaseController {

    public function show($id)
    {
        $faseGrupos = FaseGrupo::where('campeonato_fases_id','=',$id)->get();
        return Response::json($faseGrupos);
    }


    public function store()
    {
        //grupo_tipo
        //qtde_grupos
        //campeonato_fases_id

        $grupo_tipo = Input::get('grupo_tipo');
        $qtde_grupos = Input::get('qtde_grupos');
        $campeonato_fases_id = Input::get('campeonato_fases_id');

        $fase = CampeonatoFase::find($campeonato_fases_id);
        $letras = array('#', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        $quantidade_participantes_fase = $fase['quantidade_usuarios'];

        if(($quantidade_participantes_fase % $qtde_grupos) != 0) {
            return Response::json(array('success'=>false,
                'message'=>'Número de grupos a serem criados não corresponde ao número de participantes da fase: '.$quantidade_participantes_fase),300);
        }

        if(FaseGrupo::where('campeonato_fases_id', '=', $campeonato_fases_id)->get()->count() > 0) {
            return Response::json(array('success'=>false,
                'message'=>'Já existem grupos criados na fase. Eles devem ser excluídos, antes de criar novos grupos'),300);
        }

        $quantidade_usuarios = $quantidade_participantes_fase / $qtde_grupos;

        for($i = 1; $i <= $qtde_grupos; $i++) {
            $grupo = array('campeonato_fases_id'=>$campeonato_fases_id, 'quantidade_usuarios'=>$quantidade_usuarios);
            if($grupo_tipo == 'letra') {
                $grupo['descricao'] = $letras[$i];
            } else if($grupo_tipo == 'numero') {
                $grupo['descricao'] = $i;
            }
            FaseGrupo::create($grupo);
        }

        return Response::json(array('success'=>true));
    }

    public function destroy($campeonato_fases_id)
    {

        $grupos = FaseGrupo::where('campeonato_fases_id', '=', $campeonato_fases_id)->get();
        foreach($grupos as $grupo) {
            FaseGrupo::destroy($grupo['id']);
        }

        return Response::json(array('success'=>true));
    }

    public function getUsuariosComClassificacao($id_grupo) {
        $grupo = FaseGrupo::find($id_grupo);
        return Response::json($grupo->usuariosComClassificacao());
    }

}

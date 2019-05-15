<?php

use App\Http\Requests;

class AtividadeController extends Controller
{
    /**
     * Atividade Repository
     *
     * @var Atividade
     */
    protected $atividade;

    public function __construct(Atividade $atividade)
    {
        $this->atividade = $atividade;
    }

    public function show($id) {
        $usuarioLogado = Auth::getUser();
        $atividade = Atividade::find($id);
        if(isset($atividade->partidas_id)) {
            $partida = Partida::find($atividade->partidas_id);
            $partida->usuarios = $partida->usuarios();
            $partida->campeonato = $partida->campeonato();
            $atividade->objeto = $partida;
            $atividade->descricao = 'messages.disputou_partida';
        } else if(isset($atividade->campeonato_usuarios_id)) {
            $campeonatoUsuario = CampeonatoUsuario::find($atividade->campeonato_usuarios_id);
            $campeonato = Campeonato::find($campeonatoUsuario->campeonatos_id);
            $atividade->objeto = $campeonato;
            $atividade->descricao = 'messages.inscreveu_campeonato';
        }
        $usuario = User::find($atividade->users_id);
        $atividade->usuario = $usuario;
        return Response::json($atividade);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $input = Input::all();
        $validation = Validator::make($input, Atividade::$rules);

        if ($validation->passes())
        {

            Atividade::create(Input::all());

            return Response::json(array('success'=>true));
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $input = array_except(Input::all(), array('_method', '_token'));
        $validation = Validator::make($input, Atividade::$rules);

        if ($validation->passes())
        {
            $atividade = $this->atividade->find($id);
            $dadosAtividade = array('id'=>$id, 'texto'=>$input['texto']);
            $atividade->update($dadosAtividade);

            return Response::json(array('success'=>true, 'atividade'=>$atividade));
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $atividade = $this->atividade->find($id);
        $idUsuario = $atividade->users_id;
        $atividade->delete();

        return Response::json(array('success'=>true, 'idUsuario'=>$idUsuario));
    }

    public function getItensPesquisa($textoPesquisa) {
        $atividade = new Atividade();
        return Response::json($atividade->getItensPesquisa($textoPesquisa));
    }
}

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
        $atividade = Atividade::find($id);
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

    public function curtir() {
        $input = Input::all();
        $atividade = Atividade::find($input['atividade_id']);
        $atividade->curtir($input['users_id']);
        $quantidadeCurtidas = $atividade->quantidadeCurtidas();
        return Response::json(array('success'=>true, 'quantidadeCurtidas'=>$quantidadeCurtidas));
    }

    public function usuarioCurtiu() {
        $input = Input::all();
        $atividade = Atividade::find($input['atividade_id']);
        $curtiu = $atividade->curtiu($input['users_id']);
        return Response::json(array('success'=>true, 'curtiu'=>$curtiu));
    }

    public function getCurtidas($idAtividade) {
        $atividade = Atividade::find($idAtividade);
        $curtidas = $atividade->curtidas()->get();
        return Response::json($curtidas);
    }

    public function getComentarios() {
        $input = Input::all();
        $atividade = Atividade::find($input['idAtividade']);
        $comentarios = $atividade->comentarios($input['idUsuarioLeitor']);
        foreach ($comentarios as $comentario) {
            $comentario->atividade = $comentario->getAtividade();
        }
        return Response::json($comentarios);
    }
}

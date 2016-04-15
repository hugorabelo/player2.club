<?php

class PartidasController extends BaseController
{

    /**
     * Partida Repository
     *
     * @var Partida
     */
    protected $partida;

    public function __construct(Partida $partida)
    {
        $this->partida = $partida;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $partidas = Partida::get();

        Log::info('entrou aqui');

        return Response::json(compact('partidas'));
        //return View::make('partidas.index', compact('partidas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('partidas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $dados = Input::all();
        $partida = $this->partida->find($dados['id']);
        $retorno = $partida->salvarPlacar($dados);
        switch ($retorno) {
            case 1:
                return Response::json(array('success' => true));
                break;
            case 2;
                $mensagem_erro = 'messages.placares_invalidos';
                break;
            case 3:
                $mensagem_erro = 'messages.empate_nao_permitido';
                break;
            case 4:
                $mensagem_erro = 'messages.pontuacao_nao_cadastrada';
                break;
        }
        return Response::json(array('success' => false,
            'errors' => $mensagem_erro,
            'message' => 'There were validation errors.'), 300);

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $partida = $this->partida->findOrFail($id);

        return Response::json($partida);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $partida = $this->partida->find($id);

        if (is_null($partida)) {
            return Redirect::route('partidas.index');
        }

        return Response::json($partida);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $input = Input::all();

        $partida = $this->partida->find($id);
        $partida->confirmarPlacar($input['usuarioLogado']);

        return Response::json(array('success' => true));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->partida->find($id)->delete();

        return Redirect::route('partidas.index');
    }

}

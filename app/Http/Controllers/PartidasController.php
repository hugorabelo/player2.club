<?php

class PartidasController extends Controller
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
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
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
        $campeonato = $partida->campeonato();
        $retorno = $campeonato->salvarPlacar($dados);
        if($retorno != '') {
            return Response::json(array('success' => false,
                'errors' => array($retorno)), 300);
        }
        $novaPartida = $this->partida->find($dados['id']);

        foreach ($novaPartida->usuarios() as $usuarioPartida) {
            $atividade = new Atividade();
            $atividade->users_id = $usuarioPartida->users_id;
            $atividade->partidas_id = $novaPartida->id;
            $atividade->save();
        }
        return Response::json(array('success' => true));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
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
        $partida->confirmarPlacar($input['usuarioLogado'], isset($input['placarContestado']));

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
    }

    public function contestarResultado($id) {
        $input = Input::all();
        $validation = Validator::make($input, ContestacaoResultado::$rules);

        if ($validation->passes())
        {
            /*
             * Movendo o arquivo para o diretório correto
             */

            $arquivo = Input::hasFile('imagem') ? Input::file('imagem')
                : null;

            if (isset($arquivo) && $arquivo->isValid()) {
                $destinationPath = 'uploads/';
                $fileName = 'contestacao_'.str_replace('.', '', microtime(true)).'.'.$arquivo->getClientOriginalExtension();
                $arquivo->move($destinationPath, $fileName);
                $input['imagem'] = $fileName;
            }

            ContestacaoResultado::create($input);

            return Response::json(array('success'=>true));
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
    }

    public function cancelarResultado($id) {
        $input = Input::all();

        $partida = $this->partida->find($id);
        $partida->cancelarPlacar($input['usuarioLogado']);

        $atividades = Atividade::where('partidas_id','=',$id)->get();
        foreach ($atividades as $atividade) {
            Atividade::destroy($atividade->id);
        }

        return Response::json(array('success' => true));
    }

}

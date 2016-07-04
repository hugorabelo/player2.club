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
        return Response::json(array('success' => true));

//        $retorno = $partida->salvarPlacar($dados);
//        switch ($retorno) {
//            case 1:
//                return Response::json(array('success' => true));
//                break;
//            case 2;
//                $mensagem_erro = 'messages.placares_invalidos';
//                break;
//            case 3:
//                $mensagem_erro = 'messages.empate_nao_permitido';
//                break;
//            case 4:
//                $mensagem_erro = 'messages.pontuacao_nao_cadastrada';
//                break;
//        }
//        return Response::json(array('success' => false,
//            'errors' => $mensagem_erro,
//            'message' => 'There were validation errors.'), 300);

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

}

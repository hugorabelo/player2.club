<?php
/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 5/26/17
 * Time: 11:47 PM
 */

class EquipeController extends Controller
{

    /**
     * Equipe Repository
     *
     * @var Equipe
     */
    protected $equipe;

    public function __construct(Equipe $equipe)
    {
        $this->equipe = $equipe;
    }

    public function show($id)
    {
        $idUsuarioLogado = Auth::getUser()->id;
        $equipe = Equipe::find($id);
        $equipe->integrantes = $equipe->integrantes()->get();
        foreach ($equipe->integrantes as $integrante) {
            if($integrante->id == $idUsuarioLogado) {
                $equipe->participa = true;
                if($equipe->verificaFuncaoAdministrador($integrante->id)) {
                    $equipe->administrador = true;
                }
                if($equipe->id_criador == $idUsuarioLogado) {
                    $equipe->criador = true;
                }
                break;
            }
        }
        $equipe->campeonatos = $equipe->campeonatos()->get();
        return Response::json($equipe);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $equipes = Equipe::get();
        return Response::json($equipes->values()->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {

        $input = Input::all();
        $validation = Validator::make($input, Equipe::$rules);

        if ($validation->passes()) {
            /*
             * Movendo o arquivo para o diretório correto
             */

            $arquivo = Input::hasFile('imagem_logomarca') ? Input::file('imagem_logomarca')
                : null;

            if (isset($arquivo) && $arquivo->isValid()) {
                $destinationPath = 'uploads/';
                $fileName = 'equipe_' . str_replace('.', '', microtime(true)) . '.' . $arquivo->getClientOriginalExtension();
                $arquivo->move($destinationPath, $fileName);
                $input['imagem_logomarca'] = $fileName;
            }

            $equipe = Equipe::create($input);

            return Response::json(array('success' => true));
        }

        return Response::json(array('success' => false,
            'errors' => $validation->getMessageBag()->all(),
            'message' => 'There were validation errors.'), 300);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        return Response::json(Equipe::find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $input = array_except(Input::all(), array('_method'));
        $validation = Validator::make($input, Equipe::$rules);

        if ($validation->passes()) {
            /*
             * Movendo o arquivo para o diretório correto
             */

            $arquivo = Input::hasFile('imagem_logomarca') ? Input::file('imagem_logomarca')
                : null;

            if (isset($arquivo) && $arquivo->isValid()) {
                $destinationPath = 'uploads/';
                $fileName = 'equipe_' . str_replace('.', '', microtime(true)) . '.' . $arquivo->getClientOriginalExtension();
                $arquivo->move($destinationPath, $fileName);
                $input['imagem_logomarca'] = $fileName;
            }

            $equipe = $this->equipe->find($id);

            $equipe->update($input);

            return Response::json(array('success' => true));
        }

        return Response::json(array('success' => false,
            'errors' => $validation->getMessageBag()->all(),
            'message' => 'There were validation errors.'), 300);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        Equipe::destroy($id);

        return Response::json(array('success' => true));
    }

    public function enviarMensagem() {
        $input = Input::all();
        $validation = Validator::make($input, Mensagem::$rules);

        if ($validation->passes())
        {
            $mensagem['id_remetente'] = Auth::getUser()->id;
            $mensagem['mensagem'] = $input['mensagem'];
            $equipe = Equipe::find($input['id_equipe']);
            if(!isset($equipe)) {
                return Response::json(array('success'=>false,
                    'errors'=>$validation->getMessageBag()->all(),
                    'message'=>'Equipe não encontrada'),300);
            }

            foreach ($equipe->integrantes()->get() as $integrante) {
                if($integrante->id != $mensagem['id_remetente']) {
                    $mensagem['id_destinatario'] = $integrante->id;
                    Mensagem::create($mensagem);
                }
            }

            return Response::json(array('success'=>true));
        }

        return Response::json(array('success'=>false,
            'errors'=>$validation->getMessageBag()->all(),
            'message'=>'There were validation errors.'),300);
    }

    public function removeIntegrante($idEquipe, $idIntegrante) {
        if(!isset($idEquipe)) {
            return null;
        }
        $equipe = Equipe::find($idEquipe);
        if($equipe->integrantes()->get()->count() == 1) {
            return Response::json(array('success'=>false,
                'errors'=>'messages.unico_integrante_equipe',
                'message'=>'There were validation errors.'),300);
        }
        if($equipe->administradores()->get()->count() == 1 && $equipe->verificaFuncaoAdministrador($idIntegrante)) {
            return Response::json(array('success'=>false,
                'errors'=>'messages.unico_administrador_equipe',
                'message'=>'There were validation errors.'),300);
        }
        $equipe->removerIntegrante($idIntegrante);

        return Response::json(array('success' => true));
    }

    public function getIntegrantes($idEquipe) {
        if(!isset($idEquipe)) {
            return null;
        }
        $equipe = Equipe::find($idEquipe);
        return Response::json($equipe->integrantes()->get());
    }
}

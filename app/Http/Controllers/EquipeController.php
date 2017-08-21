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
        $equipe->integrantes = $equipe->integrantes()->orderBy('funcao_equipe_id')->orderBy('nome')->getResults();
        foreach ($equipe->integrantes as $integrante) {
            $funcao_equipe = DB::table('funcao_equipe')->where('id', '=', $integrante->pivot->funcao_equipe_id)->first();
            $integrante->descricao_funcao = $funcao_equipe->descricao;
            if($integrante->id == $idUsuarioLogado) {
                $equipe->participa = true;
                if($equipe->verificaFuncaoAdministrador($integrante->id)) {
                    $equipe->administrador = true;
                }
                if($equipe->id_criador == $idUsuarioLogado) {
                    $equipe->criador = true;
                }
            }
        }
        $solicitado = DB::table('equipe_solicitacao')->where('users_id','=',Auth::getUser()->id)->where('convite','=',false)->count();
        $equipe->aguardando = $solicitado;
        $convidado = DB::table('equipe_solicitacao')->where('users_id','=',Auth::getUser()->id)->where('convite','=',true)->count();
        $equipe->convite = $convidado;

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

            $input['id_criador'] = Auth::getUser()->id;
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

    public function removeIntegrante($idEquipe, $idIntegrante = null) {
        if(!isset($idEquipe)) {
            return null;
        }
        $equipe = Equipe::find($idEquipe);
        if(isset($idIntegrante) && !$equipe->verificaFuncaoAdministrador(Auth::getUser()->id)) {
            return Response::json(array('success'=>false,
                'errors'=>'messages.sem_permissao_funcao',
                'message'=>'There were validation errors.'),300);
        }
        if(!isset($idIntegrante)) {
            $idIntegrante = Auth::getUser()->id;
        }
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

    public function adicionaIntegrante($idEquipe, $idusuario) {
        $equipe = Equipe::find($idEquipe);
        if(!isset($equipe)) {
            return null;
        }
        if(!isset($idusuario)) {
            return null;
        }
        if(!$equipe->verificaFuncaoAdministrador(Auth::getUser()->id)) {
            return Response::json(array('success'=>false,
                'errors'=>'messages.sem_permissao_funcao',
                'message'=>'There were validation errors.'),300);
        }
        $idFuncao = DB::table('funcao_equipe')->whereRaw('prioridade = (select min(prioridade) from funcao_equipe)')->first(array('id'))->id;
        $equipe->adicionarIntegrante($idusuario, $idFuncao);

        $equipe->removerSolicitacao($idusuario);

        //TODO enviar notificação de aceitação

        return Response::json(array('success' => true));
    }

    public function getIntegrantes($idEquipe) {
        if(!isset($idEquipe)) {
            return null;
        }
        $equipe = Equipe::find($idEquipe);
        $integrantes = $equipe->integrantes()->orderBy('funcao_equipe_id')->orderBy('nome')->getResults();
        foreach ($integrantes as $integrante) {
            $funcao_equipe = DB::table('funcao_equipe')->where('id', '=', $integrante->pivot->funcao_equipe_id)->first();
            $integrante->descricao_funcao = $funcao_equipe->descricao;
        }
        return Response::json($integrantes);
    }

    public function getFuncoes() {
        $funcoes = DB::table('funcao_equipe')->orderBy('id')->get();
        return Response::json($funcoes);
    }

    public function updateIntegrante() {
        $integrante = (Object)Input::all();
        $equipe = Equipe::find($integrante->pivot['equipe_id']);
        if(!$equipe->verificaFuncaoAdministrador(Auth::getUser()->id)) {
            return Response::json(array('success'=>false,
                'errors'=>'messages.sem_permissao_funcao',
                'message'=>'There were validation errors.'),300);
        }
        if(!isset($equipe)) {
            return null;
        }
        $equipe->updateIntegrante($integrante->pivot['users_id'], $integrante->pivot['funcao_equipe_id']);
        return Response::json(array('success'=>true));
    }

    public function solicitarEntrada($idEquipe, $idUsuario = null) {
        $equipe = Equipe::find($idEquipe);
        if(!isset($equipe)) {
            return null;
        }
        if(isset($idUsuario)) {
            //TODO enviar notificação de convite
            $equipe->adicionarSolicitacao($idUsuario, true);
        } else {
            //TODO enviar notificação para os administradores
            $equipe->adicionarSolicitacao(Auth::getUser()->id, false);
        }
        return Response::json(array('success'=>true));
    }

    public function cancelarSolicitacao($idEquipe, $idUsuario = null) {
        $equipe = Equipe::find($idEquipe);
        if(!isset($equipe)) {
            return null;
        }
        if(isset($idUsuario)) {
            //TODO enviar notificação para usuário
            $equipe->removerSolicitacao($idUsuario);
        } else {
            $equipe->removerSolicitacao(Auth::getUser()->id);
        }
        return Response::json(array('success'=>true));
    }

    public function getSolicitacoes($idEquipe) {
        $equipe = Equipe::find($idEquipe);
        if(!isset($equipe)) {
            return null;
        }
        $solicitacoes = $equipe->solicitacoes()->where('convite','=',false)->get();
        return Response::json($solicitacoes);
    }

    public function getConvites($idEquipe) {
        $equipe = Equipe::find($idEquipe);
        if(!isset($equipe)) {
            return null;
        }
        $convites = $equipe->solicitacoes()->where('convite','=',true)->get();
        return Response::json($convites);
    }

    public function getConvitesDisponiveis($idEquipe) {
        $idUsuario = Auth::getUser()->id;
        $usuario = User::find($idUsuario);
        if(!isset($usuario)) {
            return null;
        }

        $seguindo = DB::table('seguidor')->where('users_id_seguidor','=',$idUsuario)->pluck('users_id_mestre');
        $integrantes = DB::table('integrante_equipe')->where('equipe_id','=',$idEquipe)->pluck('users_id');
        $solicitacoes = DB::table('equipe_solicitacao')->where('equipe_id','=',$idEquipe)->pluck('users_id');

        $usuarios = User::whereIn('id', $seguindo)->whereNotIn('id', $integrantes)->whereNotIn('id', $solicitacoes)->orderBy('nome')->get();

        /*
        $usuarios = DB::select("select * from users where id IN (
	                                select users_id_mestre FROM seguidor where users_id_seguidor = $idUsuario
                                ) AND id NOT IN (
                                    select users_id FROM integrante_equipe where equipe_id = $idEquipe
                                ) AND id NOT in (
                                    select users_id FROM equipe_solicitacao where equipe_id = $idEquipe
                                )
                                order by nome");
        /**/

        return Response::json($usuarios);
    }
}

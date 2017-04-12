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
        if(isset($atividade->post_id)) {
            $post = Post::find($atividade->post_id);
            if(isset($post->jogos_id)) {
                $post->descricao_jogo = Jogo::find($post->jogos_id)->descricao;
                $atividade->descricao = 'messages.escreveu_sobre_jogo';
            }
            if(isset($post->destinatario_id)) {
                $post->descricao_destinatario = User::find($post->destinatario_id)->nome;
                $atividade->descricao = 'messages.mensagem_para_usuario';
            }
            $post->imagens = $post->getimages();
            if(isset($post->post_id)) {
                $post_compartilhado = Post::find($post->post_id);
                $post_compartilhado->usuario = User::find($post_compartilhado->users_id);
                $post_compartilhado->imagens = $post_compartilhado->getimages();
                $post->compartilhamento = $post_compartilhado;
                $atividade->objeto = $post;
                $atividade->descricao = isset($atividade->descricao) ? $atividade->descricao : 'messages.compartilhou';
            } else {
                $atividade->objeto = $post;
                $atividade->descricao = isset($atividade->descricao) ? $atividade->descricao : 'messages.publicou';
            }
        } else if(isset($atividade->comentarios_id)) {
            $comentario = Comentario::find($atividade->comentarios_id);
            $atividade->objeto = $comentario;
            $atividade->descricao = 'messages.comentou';
        } else if(isset($atividade->seguidor_id)) {
            $seguidor = DB::table('seguidor')->where('id','=',$atividade->seguidor_id)->first();
            $usuarioMestre = User::find($seguidor->users_id_mestre);
            $atividade->objeto = $usuarioMestre;
            $atividade->descricao = 'messages.seguiu';
        } else if(isset($atividade->seguidor_jogo_id)) {
            $seguidor_jogo = DB::table('seguidor_jogo')->where('id','=',$atividade->seguidor_jogo_id)->first();
            $jogo = Jogo::find($seguidor_jogo->jogos_id);
            $atividade->objeto = $jogo;
            $atividade->descricao = 'messages.seguiu_jogo';
        } else if(isset($atividade->partidas_id)) {
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
        $atividade->comentarios = $atividade->comentarios($usuarioLogado->id);
        $atividade->curtidas = $atividade->curtidas()->get();
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

        $usuarioLogado = Auth::getUser();

        if($usuarioLogado->id != $atividade->users_id) {
            $idAtividadeNotificada = $atividade->id;

            if(isset($atividade->post_id)) {
                $evento = NotificacaoEvento::where('valor','=','curtir_post')->first();
            } else if(isset($atividade->comentario_id)) {
                $evento = NotificacaoEvento::where('valor','=','curtir_comentario')->first();
                $idAtividadeNotificada = Comentario::find($atividade->comentario_id)->atividade_id;
            }
            if(isset($evento)) {
                $idEvento = $evento->id;

                $notificacao = new Notificacao();
                $notificacao->id_remetente = $usuarioLogado->id;
                $notificacao->id_destinatario = $atividade->users_id;
                $notificacao->evento_notificacao_id = $idEvento;
                $notificacao->item_id = $idAtividadeNotificada;
                $notificacao->save();
            }
        }


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

    public function getItensPesquisa($textoPesquisa) {
        $atividade = new Atividade();
        return Response::json($atividade->getItensPesquisa($textoPesquisa));
    }
}

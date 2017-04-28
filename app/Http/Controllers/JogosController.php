<?php

use Illuminate\Database\Eloquent\Collection;

class JogosController extends Controller {

	/**
	 * Jogo Repository
	 *
	 * @var Jogo
	 */
	protected $jogo;

	public function __construct(Jogo $jogo)
	{
		$this->jogo = $jogo;
	}

    public function show($id) {
        $jogo = Jogo::find($id);
        $jogo->seguidores = $jogo->seguidores()->get();
		$jogo->produtora = $jogo->produtora() != null ? $jogo->produtora()->nome : '';
		$jogo->genero = $jogo->genero() != null ? $jogo->genero()->nome : '';
        return Response::json($jogo);
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        $jogos = Jogo::get();
        foreach ($jogos as $jogo) {
            $modelo = ModeloCampeonato::find($jogo->modelo_campeonato_id);
            if(isset($modelo)) {
                $jogo->modelo_campeonato = $modelo->descricao;
            }
            $jogo->produtora = $jogo->produtora() != null ? $jogo->produtora()->nome : '';
            $jogo->genero = $jogo->genero() != null ? $jogo->genero()->nome : '';
        }
        $jogos = $jogos->sortBy('modelo_campeonato');
        return Response::json($jogos->values()->all());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

		$input = array_except(Input::all(), array('plataformas_do_jogo'));
		$inputPlataforma = Input::all();
		$plataformasDoJogo = isset($inputPlataforma['plataformas_do_jogo']) ? $inputPlataforma['plataformas_do_jogo'] : array();
		$validation = Validator::make($input, Jogo::$rules);

		if ($validation->passes())
		{
			/*
			 * Movendo o arquivo para o diretório correto
			 */

			$arquivo = Input::hasFile('imagem_capa') ? Input::file('imagem_capa')
			: null;

			if (isset($arquivo) && $arquivo->isValid()) {
				$destinationPath = 'uploads/';
				$fileName = 'jogo_'.str_replace('.', '', microtime(true)).'.'.$arquivo->getClientOriginalExtension();
				$arquivo->move($destinationPath, $fileName);
				$input['imagem_capa'] = $fileName;
			}

			$jogo = Jogo::create($input);
			$novoJogo = Jogo::find($jogo->id);

			foreach ($plataformasDoJogo as $plataforma) {
				$novoJogo->adicionaPlataforma($plataforma);
			}

			return Response::json(array('success'=>true));
		}

		return Response::json(array('success'=>false,
			'errors'=>$validation->getMessageBag()->all(),
			'message'=>'There were validation errors.'),300);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		return Response::json(Jogo::find($id));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$input = array_except(Input::all(), array('_method', 'imagem_capa', 'plataformas_do_jogo'));
        $inputPlataforma = Input::all();
        $plataformasDoJogo = isset($inputPlataforma['plataformas_do_jogo']) ? $inputPlataforma['plataformas_do_jogo'] : array();
		$validation = Validator::make($input, Jogo::$rules);

		if ($validation->passes())
		{
			/*
			 * Movendo o arquivo para o diretório correto
			 */

			$arquivo = Input::hasFile('imagem_capa') ? Input::file('imagem_capa')
			: null;

			if (isset($arquivo) && $arquivo->isValid()) {
				$destinationPath = 'uploads/';
				$fileName = 'jogo_'.str_replace('.', '', microtime(true)).'.'.$arquivo->getClientOriginalExtension();
				$arquivo->move($destinationPath, $fileName);
				$input['imagem_capa'] = $fileName;
			}

			$jogo = $this->jogo->find($id);

			$jogo->update($input);

            foreach ($jogo->plataformas()->get() as $plataformaDoJogo) {
                $jogo->removePlataforma($plataformaDoJogo->id);
            }

            foreach ($plataformasDoJogo as $plataforma) {
                $jogo->adicionaPlataforma($plataforma);
            }

			return Response::json(array('success'=>true));
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
		Jogo::destroy($id);

		return Response::json(array('success'=>true));
	}

	public function getTiposDeCampeonato($id) {
		$jogo = Jogo::find($id);
		if(isset($jogo)) {
			return Response::json($jogo->tiposCampeonato());
		}
		return Response::json();
	}

	public function getCampeonatos($idJogo) {
		//TODO filtrar por campeonatos abertos e em andamento
		$campeonatosInscricoesAbertas = app()->make(Collection::class);
		$campeonatosAIniciar = app()->make(Collection::class);
		$campeonatosEmAndamento = app()->make(Collection::class);
		$campeonatosEncerrados = app()->make(Collection::class);
		$campeonatosDoJogo = Campeonato::where("jogos_id", "=", $idJogo)->get();

		foreach ($campeonatosDoJogo as $campeonato) {
			if($campeonato->faseInicial() != null) {
				$campeonato->dataInicio = $campeonato->faseInicial()->data_inicio;
			}
			if($campeonato->faseFinal() != null) {
				$campeonato->dataFinal = $campeonato->faseFinal()->data_fim;
			}
			if($campeonato->plataforma() != null) {
				$campeonato->plataforma = $campeonato->plataforma()->descricao;
			}
		}

		return $campeonatosDoJogo;

		/*
		foreach ($campeonatosDoJogo as $campeonato) {
		$campeonato->dataInicio = $campeonato->faseInicial()->data_inicio;
        $campeonato->dataFinal = $campeonato->faseFinal()->data_fim;
			switch ($campeonato->status()) {
				case 1:
					$campeonatosInscricoesAbertas->add($campeonato);
					break;
				case 2:
					$campeonatosAIniciar->add($campeonato);
					break;
				case 3:
					$campeonatosEmAndamento->add($campeonato);
					break;
				case 4:
					$campeonatosEncerrados->add($campeonato);
					break;
			}
		}
		*/
		//return Response::json(compact('campeonatosInscricoesAbertas', 'campeonatosAIniciar', 'campeonatosEmAndamento', 'campeonatosEncerrados'));
	}

	public function getFeed($idJogo, $offset = 0, $quantidade = 5)
	{
		$jogo = $this->jogo->find($idJogo);
		if($jogo == null) {
			return Response::json();
		}
		$atividades = $jogo->getAtividades($offset, $quantidade);
		foreach ($atividades as $atividade) {
			if(isset($atividade->post_id)) {
				$post = Post::find($atividade->post_id);
				if(isset($post->jogos_id)) {
					$post->descricao_jogo = $jogo->descricao;
					$atividade->descricao = 'messages.escreveu_sobre_jogo';
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
		}
		return Response::json($atividades);
	}

	public function getPlataformas($idJogo) {
        $jogo = Jogo::find($idJogo);
		$plataformas = $jogo->plataformas()->orderBy('descricao')->getResults();
        return Response::json($plataformas->values()->all());
    }

}

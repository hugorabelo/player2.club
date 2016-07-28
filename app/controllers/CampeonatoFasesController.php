<?php

use Illuminate\Database\Eloquent\Collection;

class CampeonatoFasesController extends BaseController {

	/**
	 * CampeonatoFase Repository
	 *
	 * @var CampeonatoFase
	 */
	protected $campeonatoFase;

	public function __construct(CampeonatoFase $campeonatoFase)
	{
		$this->campeonatoFase = $campeonatoFase;
	}

	public function index() {
		return null;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function show($id)
	{
		$campeonatoFases = $this->getFasesOrdenadas($id);
		return Response::json($campeonatoFases);
	}

	public function create($id_campeonato)
	{
		$fases = CampeonatoFase::where('campeonatos_id', '=', $id_campeonato)->get();
		return Response::json(compact('fases'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		$validation = Validator::make($input, CampeonatoFase::$rules);

		if ($validation->passes())
		{
			$input['data_inicio'] = date('Y-m-d', strtotime($input['data_inicio']));
			$input['data_fim'] = date('Y-m-d', strtotime($input['data_fim']));
			if(empty($input['fase_anterior_id'])) {
				$input['fase_anterior_id'] = null;
			}
			if($this->existeFaseInicial($input['campeonatos_id'])) {
				return Response::json(array('success'=>false,
					'message'=>'Já existe uma Fase Inicial cadastrada para este campeonato'),300);
			}
			if($this->existeFaseFinal($input['campeonatos_id'])) {
				return Response::json(array('success'=>false,
					'message'=>'Já existe uma Fase Final cadastrada para este campeonato'),300);
			}
			$this->campeonatoFase->create($input);

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
		$fase = CampeonatoFase::find($id);
		$fases = CampeonatoFase::where('campeonatos_id', '=', $fase['campeonatos_id'])->get()->except($id);
		return Response::json(compact('fases', 'fase'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$input = array_except(Input::all(), array('_method', 'imagem_capa'));
		$validation = Validator::make($input, CampeonatoFase::$rules);

		if ($validation->passes())
		{
			$fase = $this->campeonatoFase->find($id);
			$input['data_inicio'] = date('Y-m-d', strtotime($input['data_inicio']));
			$input['data_fim'] = date('Y-m-d', strtotime($input['data_fim']));
			if(empty($input['fase_anterior_id'])) {
				$input['fase_anterior_id'] = null;
			}
			if(($input['inicial'] == 'true') && $this->existeFaseInicial($input['campeonatos_id'], $id)) {
				return Response::json(array('success'=>false,
					'message'=>'Já existe uma Fase Inicial cadastrada para este campeonato'),300);
			}
			if(($input['final'] == 'true') && $this->existeFaseFinal($input['campeonatos_id'], $id)) {
				return Response::json(array('success'=>false,
					'message'=>'Já existe uma Fase Final cadastrada para este campeonato'),300);
			}
			$fase->update($input);

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
		$this->campeonatoFase->find($id)->delete();

		return Response::json(array('success'=>true));
	}

	public function abreFase() {
        /*
         * Objeto Fase deve conter os seguintes atributos:
         * - id : ID da fase
         * - data_encerramento: Data de encerramento da fase a ser iniciada (Para cada fase seguinte, atualizar as datas de início, baseadas nesta)
         * - tipo_sorteio mata-mata: Se for uma fase de mata mata, definir o tipo de sorteio (melhor geral x pior geral | melhor grupo x pior grupo | aleatória)
         */

        $dadosFase = Input::all();
        $faseAtual = CampeonatoFase::find($dadosFase['id']);
		$campeonato = Campeonato::find($faseAtual->campeonatos_id);

		if($faseAtual->inicial) {
			if($campeonato->usuariosInscritos()->count() < $faseAtual->quantidade_usuarios) {
				return Response::json(array('success'=>false,
					'messages'=>array('messages.fase_sem_quantidade_minima_usuarios')),300);
			}
		} else {
			if($faseAtual->usuarios()->count() < $faseAtual->quantidade_usuarios) {
				return Response::json(array('success'=>false,
					'messages'=>array('messages.fase_sem_quantidade_minima_usuarios')),300);
			}
		}
		$faseAnterior = $faseAtual->faseAnterior();
		if(isset($faseAnterior) && $faseAnterior->aberta) {
			return Response::json(array('success'=>false,
                'messages'=>array('messages.fase_anterior_aberta')),300);
		}

		$usuariosDaFase = $campeonato->abreFase($dadosFase);

        return Response::json($usuariosDaFase);
    }

	public function fechaFase($id) {
		// contabilizar jogos sem resultado (0 pontos para todos os participantes)
		// contabilizar pontuação e quantidade de classificados (por grupo)
		// Desabilitar inserção de resultados

		/*
		 * Cronograma de inserção de resultados para uma partida de campeonato
		 * - Usuário tem até a data final da fase para inserir o resultado (Caso não exista resultado, o jogo será definido como sem resultado, onde ambos os participantes ficam com pontos de último colocado)
		 * - Outro Usuário tem até 24 horas depois da hora de inserção do resultado para confirmar o mesmo (Caso não seja confirmado o resultado por outro usuário, o placar inserido será dado como definitivo).
		 * -
		 */
	}

    private function getFasesOrdenadas($id) {
        $campeonato = Campeonato::find($id);
        $fasesOrdenadas = new Collection();
        $faseAdicionada = $campeonato->faseFinal();
        $fasesOrdenadas->prepend($faseAdicionada);
        while($faseAdicionada = $faseAdicionada->faseAnterior()) {
            $fasesOrdenadas->prepend($faseAdicionada);
        }
        return $fasesOrdenadas;
    }

	private function existeFaseInicial($campeonatos_id, $id_fase) {
		return CampeonatoFase::where('campeonatos_id', '=', $campeonatos_id)->where('inicial','=', true)->where('id', '!=', $id_fase)->get()->count();
	}

	private function existeFaseFinal($campeonatos_id, $id_fase) {
		return CampeonatoFase::where('campeonatos_id', '=', $campeonatos_id)->where('final','=', true)->where('id', '!=', $id_fase)->get()->count();
	}

    private function sorteioGrupos($grupos, $usuarios) {
        $usuariosInseridos = array();
        foreach($grupos as $grupo) {
            for($i = 0; $i < $grupo->quantidade_usuarios; $i++) {
                $usuario = $usuarios->random(1);
                while(in_array($usuario, $usuariosInseridos)) {
                    $usuario = $usuarios->random(1);
                }
                //UsuarioGrupo::create(['users_id'=> $usuario->id,'fase_grupos_id' => $grupo->id]);
                array_push($usuariosInseridos, $usuario);
            }
        }
    }

    private function sorteioJogosUmContraUm($grupo, $turnos) {
        $usuarios = $grupo->usuarios();
        $n = $usuarios->count();
        $m = $n / 2;
        $numero_rodadas_por_turno = ($n - 1);
        $numero_rodada = 1;
        for($t = 0; $t < $turnos; $t++) {
            for($i = 0; $i < $numero_rodadas_por_turno; $i++) {
                for($j = 0; $j < $m; $j++) {
                    $partida = Partida::create(['fase_grupos_id'=>$grupo->id, 'rodada'=>$numero_rodada]);
                    if($t % 2 == 1) {
                        if($j % 2 == 1 || $i % 2 == 1 && $j == 0) {
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($n - $j - 1)->id]);
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($j)->id]);
                        } else {
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($j)->id]);
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($n - $j - 1)->id]);
                        }
                    } else {
                        if($j % 2 == 1 || $i % 2 == 1 && $j == 0) {
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($j)->id]);
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($n - $j - 1)->id]);
                        } else {
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($n - $j - 1)->id]);
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($j)->id]);
                        }
                    }
                }
                $numero_rodada++;
                $usuarios = $this->sorteioReordena($usuarios);
            }
        }
    }

    private function sorteioReordena($colecao) {
        $novaColecao = new Collection();
        $novaColecao->add($colecao->shift());
        $novaColecao->add($colecao->pop());
        foreach($colecao as $elemento) {
            $novaColecao->add($elemento);
        }
        return $novaColecao;
    }

}

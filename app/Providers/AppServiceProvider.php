<?php namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Mail\Message;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{

		$this->base_path = "http://beta.player2.club/#/";

		\Campeonato::created(function ($campeonato) {
			$administrador = new \CampeonatoAdmin();
			$administrador->users_id = $campeonato->criador;
			$administrador->campeonatos_id = $campeonato->id;
			$administrador->save();

			if($campeonato->tipo_competidor != 'equipe') {
				$usuario = new \CampeonatoUsuario();
				$usuario->users_id = $campeonato->criador;
				$usuario->campeonatos_id = $campeonato->id;
				$usuario->save();
			}
		});

        \CampeonatoUsuario::created(function ($campeonatoUsuario) {
			if(isset($campeonatoUsuario->users_id)) {
				$atividade = new \Atividade();
				$atividade->users_id = $campeonatoUsuario->users_id;
				$atividade->campeonato_usuarios_id = $campeonatoUsuario->id;
				$atividade->save();
			} else if(isset($campeonatoUsuario->equipe_id)) {
				$equipe = \Equipe::find($campeonatoUsuario->equipe_id);
				foreach ($equipe->integrantes()->get() as $integrante) {
					$atividade = new \Atividade();
					$atividade->users_id = $integrante->id;
					$atividade->campeonato_usuarios_id = $campeonatoUsuario->id;
					$atividade->save();
				}
			}
        });

        \Notificacao::created(function ($notificacao) {
			if(\DB::table('notificacao_email')->where('evento_notificacao_id','=',$notificacao->evento_notificacao_id)->where('users_id','=',$notificacao->id_destinatario)->count('id') > 0) {
				$evento = \NotificacaoEvento::find($notificacao->evento_notificacao_id);

				$destinatario = \User::find($notificacao->id_destinatario);
				$nome_completo = explode(' ', $destinatario->nome);
				$nome_completo = count($nome_completo) > 2 ? array_shift($nome_completo).' '.array_pop($nome_completo) : $destinatario->nome;
				$destinatario->nome = $nome_completo;

				$remetente = \User::find($notificacao->id_remetente);
				if(isset($remetente)) {
					$nome_completo = explode(' ', $remetente->nome);
					$nome_completo = count($nome_completo) > 2 ? array_shift($nome_completo).' '.array_pop($nome_completo) : $remetente->nome;
					$remetente->nome = $nome_completo;
					$notificacao->remetente = $remetente;
				}
				switch ($evento->valor) {
					case 'fase_iniciada':
					case 'fase_encerrada':
					case 'fase_encerramento_breve':
						$fase = \CampeonatoFase::find($notificacao->item_id);
						$notificacao->nome_campeonato = $fase->campeonato()->descricao;
						$notificacao->nome_fase = trans($fase->descricao);
						$notificacao->item_id = $fase->campeonato()->id;
						break;
					case 'sorteou_clubes':
						$campeonato = \Campeonato::find($notificacao->item_id);
						$notificacao->nome_campeonato = $campeonato->descricao;
						break;
				}

				switch ($evento->valor) {
					case "salvou_placar":
					case "confirmou_placar":
					case "contestou_resultado":
						$link = $this->base_path."home/partidas_usuario";
						break;
					case "fase_iniciada":
					case "fase_encerrada":
					case "fase_encerramento_breve":
					case "sorteou_clubes":
						$link = $this->base_path."campeonato/".$notificacao->item_id;
						break;
					$link = $this->base_path."home/atividade/".$notificacao->item_id;
						break;
					case "seguir_usuario":
						$link = $this->base_path."profile/".$notificacao->id_remetente;
						break;
				}


				$notificacao->mensagem = $evento->mensagem;
				$notificacao->tipo_evento = $evento->valor;
                $nome_remetente = isset($notificacao->remetente) ? $notificacao->remetente->nome : '';

				$conteudo = trans($notificacao->mensagem, ['nome_remetente' => $nome_remetente, 'nome_fase' => $notificacao->nome_fase, 'nome_campeonato' => $notificacao->nome_campeonato]);


				$texto_link = trans(("messages.visualizar_notificacao"));

				\Mail::send('notificacao', ['conteudo' =>  $conteudo, 'destinatario' => $destinatario, 'link' => $link, 'texto_link' => $texto_link], function($message) use ($destinatario) {
					$message->from('contato@player2.club', $name = 'player2.club');
					$message->to($destinatario->email, $name = $destinatario->nome);
					$message->subject('Você possui uma nova notificação');
				});
			}
		});

		// Enviar um e-mail caso a última mensagem enviada anteriormente tenha ocorido em um tempo acima de 6h
		\Mensagem::created(function ($mensagem) {

			$hora_ultima = $mensagem->created_at;
			$penultima_mensagem = \Mensagem::where('id_remetente','=',$mensagem->id_remetente)->where('id_destinatario','=',$mensagem->id_destinatario)->where('id', '<>', $mensagem->id)->latest()->first();
			if(isset($penultima_mensagem)) {
				$hora_penultima = $penultima_mensagem->created_at;
			} else {
				$hora_penultima = Carbon::createFromDate(2000, 1, 1, 'America/Toronto');
			}
			$diferenca = $hora_ultima->diffInMinutes($hora_penultima);

			$remetente = \User::find($mensagem->id_remetente);
			$nome_remetente = isset($remetente) ? $remetente->nome: '';

			$destinatario = \User::find($mensagem->id_destinatario);

			$conteudo = trans("messages.recebeu_mensagem", ['nome_remetente' => $nome_remetente]);

			$link = $this->base_path."home/mensagens";
			$texto_link = trans(("messages.visualizar_notificacao"));

			if($diferenca > 180) {
				\Mail::send('notificacao', ['conteudo' =>  $conteudo, 'destinatario' => $destinatario, 'link' => $link, 'texto_link' => $texto_link], function($message) use ($destinatario) {
					$message->from('contato@player2.club', $name = 'player2.club');
					$message->to($destinatario->email, $name = $destinatario->nome);
					$message->subject('Você recebeu uma nova mensagem');
				});
			}

		});

        \Equipe::created(function ($equipe) {
            $equipe->adicionarIntegrante(\Auth::getUser()->id, 1);
        });

        \ConviteUsuario::created(function ($convite) {
            $usuario = \User::find($convite->users_id);
            $nome_remetente = $usuario->nome;

            $conteudo = trans("messages.texto_convidado", ['nome_remetente' => $nome_remetente]);

            $destinatario = $convite;
            $destinatario->nome = $convite->email;
            $link = $this->base_path;

            $texto_link = trans("messages.link_convidado");

            \Mail::send('notificacao', ['conteudo' =>  $conteudo, 'destinatario' => $destinatario, 'link' => $link, 'texto_link' => $texto_link], function($message) use ($destinatario) {
                $message->from('contato@player2.club', $name = 'player2.club');
                $message->to($destinatario->email, $name = $destinatario->nome);
                $message->subject('Você foi convidado para participar da player2.club');
            });
        });

	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'App\Services\Registrar'
		);

		$this->app->bind("collection.multiSort", function ($app, $criteria){
			return function ($first, $second) use ($criteria) {
				foreach ($criteria as $key => $orderType) {
					// normalize sort direction
					$orderType = strtolower($orderType);
					if ($first[$key] < $second[$key]) {
						return $orderType === "menor" ? -1 : 1;
					} else if ($first[$key] > $second[$key]) {
						return $orderType === "menor" ? 1 : -1;
					}
				}
				// all elements were equal
				return 0;
			};
		});

		$this->app->bind(
			'\Auth0\Login\Contract\Auth0UserRepository',
			'\App\Repository\MyCustomUserRepository');
	}

}

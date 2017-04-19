<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{

		\Campeonato::created(function ($campeonato) {
			$administrador = new \CampeonatoAdmin();
			$administrador->users_id = $campeonato->criador;
			$administrador->campeonatos_id = $campeonato->id;
			$administrador->save();

			$usuario = new \CampeonatoUsuario();
			$usuario->users_id = $campeonato->criador;
			$usuario->campeonatos_id = $campeonato->id;
			$usuario->save();
		});

		\Post::created(function ($post) {
			$atividade = new \Atividade();
			$atividade->users_id = $post->users_id;
			$atividade->post_id = $post->id;
			$atividade->save();
		});

		\Comentario::created(function ($comentario) {
			$atividade = new \Atividade();
			$atividade->users_id = $comentario->users_id;
			$atividade->comentario_id = $comentario->id;
			$atividade->save();
		});

        \CampeonatoUsuario::created(function ($campeonatoUsuario) {
            $atividade = new \Atividade();
            $atividade->users_id = $campeonatoUsuario->users_id;
            $atividade->campeonato_usuarios_id = $campeonatoUsuario->id;
            $atividade->save();
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
				}
				$notificacao->mensagem = $evento->mensagem;
				$notificacao->tipo_evento = $evento->valor;

				$conteudo = trans($notificacao->mensagem, ['nome_remetente' => $notificacao->remetente, 'nome_fase' => $notificacao->nome_fase, 'nome_campeonato' => $notificacao->nome_campeonato]);

				\Mail::send('notificacao', ['conteudo' =>  $conteudo, 'destinatario' => $destinatario], function($message) use ($destinatario) {
					$message->from('contato@player2.club', $name = 'player2.club');
					$message->to($destinatario->email, $name = $destinatario->nome);
					$message->subject('Você possui uma nova notificação');
				});
			}
		});

		/*
		\Mensagem::created(function ($mensagem) {
			$evento = \NotificacaoEvento::where('valor','=','enviar_mensagem')->first();
			if(isset($evento)) {
				$idEvento = $evento->id;
			}

			$notificacao = new \Notificacao();
			$notificacao->id_remetente = $mensagem->id_remetente;
			$notificacao->id_destinatario = $mensagem->id_destinatario;
			$notificacao->evento_notificacao_id = $idEvento;
			$notificacao->save();
		});
		*/

		\Atividade::deleted(function ($atividade) {
			if(isset($atividade->post_id)) {
				\Post::destroy($atividade->post_id);
			}
			if(isset($atividade->comentario_id)) {
				\Comentario::destroy($atividade->comentario_id);
			}
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

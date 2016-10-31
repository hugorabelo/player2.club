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
		//
		\Post::created(function ($post) {
			$atividade = new \Atividade();
			$atividade->users_id = $post->users_id;
			$atividade->post_id = $post->id;
			$atividade->save();
		});

		\Comentario::created(function ($comentario) {
			$atividade = new \Atividade();
			$atividade->users_id = $comentario->users_id;
			$atividade->comentarios_id = $comentario->id;
			$atividade->save();
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
	}

}

<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'auth' => 'App\Http\Middleware\Authenticate',
		'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
		'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
		'csrf' => 'App\Http\Middleware\VerifyCsrfToken',
		'cors' => '\App\Http\Middleware\Cors',
		'auth0.jwt' => '\Auth0\Login\Middleware\Auth0JWTMiddleware',
		'auth0.jwt_verification' => '\Auth0\Login\Middleware\Auth0OptionalJWTMiddleware',
		'auth0.jwt_force' => '\Auth0\Login\Middleware\ForceAuthMiddleware',
	];

}

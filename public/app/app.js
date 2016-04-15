/**
 * INSPINIA - Responsive Admin Theme
 * Copyright 2014 Webapplayers.com
 *
 */

var AplicacaoLiga = angular.module('aplicacaoLiga', [
    'ui.router',
    'ui.bootstrap',
    'pascalprecht.translate',
    'jcs-autoValidate',
    'ui.tree',
    'ngCookies',
	'summernote'
]);

AplicacaoLiga.config(function ($locationProvider) {
	$locationProvider.html5Mode(true);
});

AplicacaoLiga.config(function($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise("/");
    $stateProvider
    .state('login', {
        url: "/login",
        templateUrl: "app/views/comum/login.html",
        controller: 'AuthController',
        acesso: 'publico',
        data: { pageTitle: "fields.login" }
    })
    .state('home', {
        url: "/",
        templateUrl: "app/views/dashboard/home.html",
        acesso: 'registrado'
    })
    .state('naoAutorizado', {
        url: "/erro",
        templateUrl: "app/views/comum/acessoNaoAutorizado.html",
        acesso: 'registrado'
    })
    .state('campeonato', {
        url: "/campeonato",
        templateUrl: "app/views/campeonato/index.html",
        controller: 'CampeonatoController',
        data: { pageTitle: "menus.campeonatos" }
    })
    .state('plataforma', {
        url: "/plataforma",
        templateUrl: "app/views/plataforma/index.html",
        controller: 'PlataformaController',
        data: { pageTitle: "menus.plataformas" }
    })
    .state('campeonatoTipo', {
        url: "/campeonatoTipo",
        templateUrl: "app/views/campeonatoTipo/index.html",
        controller: 'CampeonatoTipoController',
        data: { pageTitle: "menus.campeonatoTipos" }
    })
    .state('jogo', {
        url: "/jogo",
        templateUrl: "app/views/jogo/index.html",
        controller: 'JogoController',
        data: { pageTitle: "menus.jogos" }
    })
    .state('usuarioTipo', {
        url: "/usuarioTipo",
        templateUrl: "app/views/usuarioTipo/index.html",
        controller: 'UsuarioTipoController',
        data: { pageTitle: "menus.usuarioTipos" }
    })
    .state('usuario', {
        url: "/usuario",
        templateUrl: "app/views/usuario/index.html",
        controller: 'UsuarioController',
        data: { pageTitle: "menus.usuarios" }
    })
    .state('menu', {
        url: "/menu",
        templateUrl: "app/views/menu/index.html",
        controller: 'MenuController',
        data: { pageTitle: "menus.menus" }
    })
    .state('permissao', {
        url: "/permissao",
        templateUrl: "app/views/permissao/index.html",
        controller: 'PermissaoController',
        data: { pageTitle: "menus.permissoes" }
    })
    .state('meus_campeonatos', {
        url: "/meus_campeonatos",
        templateUrl: "app/views/meus_campeonatos/index.html",
        controller: 'MeuCampeonatoController',
        data: { pageTitle: "menus.meus_campeonatos" }
    })
    .state('meus_campeonatos_disponiveis', {
        url: "/campeonatos_disponiveis",
        templateUrl: "app/views/meus_campeonatos/campeonatosDisponiveis.html",
        controller: 'MeuCampeonatoController',
        data: { pageTitle: "menus.meus_campeonatos_disponiveis" }
    })
    .state('minhas_partidas', {
        url: "/minhas_partidas",
        templateUrl: "app/views/meus_campeonatos/minhasPartidas.html",
        controller: 'PartidaController',
        data: { pageTitle: "menus.minhas_partidas" }
    })
	 .state('tabela_campeonato', {
        url: "/tabela_campeonato",
        templateUrl: "app/views/campeonatoFront/tabelaCampeonato.html",
        controller: 'CampeonatoFrontController',
        data: { pageTitle: "menus.tabela_campeonato" }
    })
    .state('logout', {
        url: "/logout"
    })
})
.run(function($rootScope, $state) {
   $rootScope.$state = $state;
	if($rootScope.usuarioLogado == null) {
   	$rootScope.usuarioLogado = 1;
	}
});

//AplicacaoLiga.run(['$rootScope', '$state', 'Auth', '$cookieStore', function ($rootScope, $state, Auth, $cookieStore) {
//
//    $cookieStore.put('permissoes', ['campeonato', 'plataforma', 'campeonatoTipo', 'jogo']);
//
//    $rootScope.$on("$stateChangeStart", function (event, toState, toParams, fromState, fromParams) {
//        if(toState.acesso != 'publico') {
//            if(Auth.estaLogado()) {
//                if (!Auth.autoriza(toState.name) && toState.acesso != 'registrado') {
//                    $rootScope.error = "Access denied";
//                    event.preventDefault();
//                    $state.go('naoAutorizado');
//                }
//            } else {
//                $rootScope.error = null;
//                event.preventDefault();
//                $state.go('login');
//            }
//        }
//    });
//
//}]);

AplicacaoLiga.config(['$translateProvider', function ($translateProvider) {
    $translateProvider.useStaticFilesLoader({
        prefix: 'app/lang/locale-',
        suffix: '.json'
    });

    $translateProvider.preferredLanguage('pt_br');
    $translateProvider.fallbackLanguage('pt_br');

    bootbox.setLocale('br');

}]);

AplicacaoLiga.run([
    'bootstrap3ElementModifier',
        function (bootstrap3ElementModifier) {
            bootstrap3ElementModifier.enableValidationStateIcons(true);
        }
]);

AplicacaoLiga.run([
    'defaultErrorMessageResolver',
        function (defaultErrorMessageResolver) {
            defaultErrorMessageResolver.setI18nFileRootPath('app/plugins/angular-validate/lang');
            defaultErrorMessageResolver.setCulture('pt-br');
        }
]);

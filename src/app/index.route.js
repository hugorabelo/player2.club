(function () {
    'use strict';

    angular
        .module('player2')
        .config(routerConfig);

    /** @ngInject */
    function routerConfig($stateProvider, $urlRouterProvider, $locationProvider) {
        $stateProvider

            .state('index', {
                abstract: true,
                url: "/index",
                templateUrl: "app/components/common/content.html"
            })
            .state('index.main', {
                url: "/main",
                templateUrl: "app/main/main.html",
                data: {
                    pageTitle: 'Example view'
                }
            })
            .state('index.minor', {
                url: "/minor",
                templateUrl: "app/minor/minor.html",
                data: {
                    pageTitle: 'Example view'
                }
            })
            .state('login', {
                url: "/login",
                templateUrl: "app/components/comum/login.html",
                controller: 'AuthController',
                acesso: 'publico',
                data: {
                    pageTitle: "fields.login"
                }
            })
            .state('index.home', {
                url: "/home",
                templateUrl: "app/components/dashboard/home.html",
                acesso: 'registrado'
            })
            .state('index.naoAutorizado', {
                url: "/erro",
                templateUrl: "app/components/comum/acessoNaoAutorizado.html",
                acesso: 'registrado'
            })
            .state('index.campeonato', {
                url: "/campeonato",
                templateUrl: "app/components/campeonato/index.html",
                controller: 'CampeonatoController',
                data: {
                    pageTitle: "menus.campeonatos"
                }
            })
            .state('index.plataforma', {
                url: "/plataforma",
                templateUrl: "app/components/plataforma/index.html",
                controller: 'PlataformaController as vmPlataforma',
                data: {
                    pageTitle: "menus.plataformas"
                }
            })
            .state('index.campeonatoTipo', {
                url: "/campeonatoTipo",
                templateUrl: "app/components/campeonatoTipo/index.html",
                controller: 'CampeonatoTipoController as vmCampeonatoTipo',
                data: {
                    pageTitle: "menus.campeonatoTipos"
                }
            })
            .state('index.jogo', {
                url: "/jogo",
                templateUrl: "app/components/jogo/index.html",
                controller: 'JogoController as vmJogo',
                data: {
                    pageTitle: "menus.jogos"
                }
            })
            .state('index.usuarioTipo', {
                url: "/usuarioTipo",
                templateUrl: "app/components/usuarioTipo/index.html",
                controller: 'UsuarioTipoController as vmUsuarioTipo',
                data: {
                    pageTitle: "menus.usuarioTipos"
                }
            })
            .state('index.usuario', {
                url: "/usuario",
                templateUrl: "app/components/usuario/index.html",
                controller: 'UsuarioController as vmUsuario',
                data: {
                    pageTitle: "menus.usuarios"
                }
            })
            .state('index.menu', {
                url: "/menu",
                templateUrl: "app/components/menu/index.html",
                controller: 'MenuController',
                data: {
                    pageTitle: "menus.menus"
                }
            })
            .state('index.permissao', {
                url: "/permissao",
                templateUrl: "app/components/permissao/index.html",
                controller: 'PermissaoController',
                data: {
                    pageTitle: "menus.permissoes"
                }
            })
            .state('index.meus_campeonatos', {
                url: "/meus_campeonatos",
                templateUrl: "app/components/meus_campeonatos/index.html",
                controller: 'MeuCampeonatoController',
                data: {
                    pageTitle: "menus.meus_campeonatos"
                }
            })
            .state('index.meus_campeonatos_disponiveis', {
                url: "/campeonatos_disponiveis",
                templateUrl: "app/components/meus_campeonatos/campeonatosDisponiveis.html",
                controller: 'MeuCampeonatoController',
                data: {
                    pageTitle: "menus.meus_campeonatos_disponiveis"
                }
            })
            .state('index.minhas_partidas', {
                url: "/minhas_partidas",
                templateUrl: "app/components/meus_campeonatos/minhasPartidas.html",
                controller: 'PartidaController',
                data: {
                    pageTitle: "menus.minhas_partidas"
                }
            })
            .state('index.tabela_campeonato', {
                url: "/tabela_campeonato",
                templateUrl: "app/components/campeonatoFront/tabelaCampeonato.html",
                controller: 'CampeonatoFrontController',
                data: {
                    pageTitle: "menus.tabela_campeonato"
                }
            })
            .state('index.criar_campeonato', {
                url: "/criar_campeonato",
                templateUrl: "app/components/campeonato_novo/cadastroCampeonato.html",
                controller: 'CriarCampeonatoController',
                data: {
                    pageTitle: "messages.campeonato_create"
                }
            })
            .state('logout', {
                url: "/logout"
            });

        $urlRouterProvider.otherwise('/index/main');
        //        $locationProvider.html5Mode(true);
    }

})();

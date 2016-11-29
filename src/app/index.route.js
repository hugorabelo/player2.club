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
            .state('login', {
                url: "/login",
                templateUrl: "app/components/comum/login.html",
                controller: 'AuthController',
                acesso: 'publico',
                data: {
                    pageTitle: "fields.login"
                }
            })
            .state('naoAutorizado', {
                url: "/erro",
                templateUrl: "app/components/comum/acessoNaoAutorizado.html",
                acesso: 'registrado'
            })
            .state('home', {
                url: "/home",
                templateUrl: "app/components/dashboard/home.html",
                controller: 'HomeController as vmHome',
                acesso: 'registrado'
            })
            .state('home.seguidores', {
                url: "/seguidores",
                templateUrl: "app/components/rede_social/seguidores.html",
                controller: 'SeguidoresController as vmSeguidores',
                acesso: 'registrado'
            })
            .state('home.seguindo', {
                url: "/seguindo",
                templateUrl: "app/components/rede_social/seguindo.html",
                controller: 'SeguidoresController as vmSeguidores',
                acesso: 'registrado'
            })
            .state('home.campeonatos', {
                url: "/campeonatos",
                templateUrl: "app/components/campeonato/listaCampeonatos.html",
                controller: 'ProfileController as vmProfile',
                acesso: 'registrado'
            })
            .state('home.editar_perfil', {
                url: "/editar_perfil",
                templateUrl: "app/components/dashboard/editarPerfil.html",
                controller: 'HomeController as vmHome',
                acesso: 'registrado'
            })
            .state('home.partidas_usuario', {
                url: "/partidas_usuario",
                templateUrl: "app/components/dashboard/partidasUsuario.html",
                controller: 'HomeController as vmHome',
                acesso: 'registrado'
            })
            .state('campeonato', {
                url: "/campeonato/{idCampeonato}",
                templateUrl: "app/components/campeonato/index.html",
                controller: 'CampeonatoController as vmCampeonato',
                data: {
                    pageTitle: "menus.campeonatos"
                }
            })
            .state('plataforma', {
                url: "/plataforma",
                templateUrl: "app/components/plataforma/index.html",
                controller: 'PlataformaController as vmPlataforma',
                data: {

                    pageTitle: "menus.plataformas"
                }
            })
            .state('campeonatoTipo', {
                url: "/campeonatoTipo",
                templateUrl: "app/components/campeonatoTipo/index.html",
                controller: 'CampeonatoTipoController as vmCampeonatoTipo',
                data: {
                    pageTitle: "menus.campeonatoTipos"
                }
            })
            .state('cadastroJogo', {
                url: "/cadastroJogo",
                templateUrl: "app/components/cadastroJogo/index.html",
                controller: 'CadastroJogoController as vmCadastroJogo',
                data: {
                    pageTitle: "menus.jogos"
                }
            })
            .state('usuarioTipo', {
                url: "/usuarioTipo",
                templateUrl: "app/components/usuarioTipo/index.html",
                controller: 'UsuarioTipoController as vmUsuarioTipo',
                data: {
                    pageTitle: "menus.usuarioTipos"
                }
            })
            .state('usuario', {
                url: "/usuario",
                templateUrl: "app/components/usuario/index.html",
                controller: 'UsuarioController as vmUsuario',
                data: {
                    pageTitle: "menus.usuarios"
                }
            })
            .state('menu', {
                url: "/menu",
                templateUrl: "app/components/menu/index.html",
                controller: 'MenuController as vmMenu',
                data: {
                    pageTitle: "menus.menus"
                }
            })
            .state('permissao', {
                url: "/permissao",
                templateUrl: "app/components/permissao/index.html",
                controller: 'PermissaoController as vmPermissao',
                data: {
                    pageTitle: "menus.permissoes"
                }
            })
            .state('meus_campeonatos', {
                url: "/meus_campeonatos",
                templateUrl: "app/components/meus_campeonatos/index.html",
                controller: 'MeuCampeonatoController',
                data: {
                    pageTitle: "menus.meus_campeonatos"
                }
            })
            .state('meus_campeonatos_disponiveis', {
                url: "/campeonatos_disponiveis",
                templateUrl: "app/components/meus_campeonatos/campeonatosDisponiveis.html",
                controller: 'MeuCampeonatoController',
                data: {
                    pageTitle: "menus.meus_campeonatos_disponiveis"
                }
            })
            .state('minhas_partidas', {
                url: "/minhas_partidas",
                templateUrl: "app/components/meus_campeonatos/minhasPartidas.html",
                controller: 'PartidaController as vmPartida',
                data: {
                    pageTitle: "menus.minhas_partidas"
                }
            })
            .state('tabela_campeonato', {
                url: "/tabela_campeonato",
                templateUrl: "app/components/campeonatoFront/tabelaCampeonato.html",
                controller: 'CampeonatoFrontController as vmCampeonatoFront',
                data: {
                    pageTitle: "menus.tabela_campeonato"
                }
            })
            .state('criar_campeonato', {
                url: "/criar_campeonato",
                templateUrl: "app/components/campeonato_novo/cadastroCampeonato.html",
                controller: 'CriarCampeonatoController as vmCriarCampeonato',
                data: {
                    pageTitle: "messages.campeonato_create",
                    novo: true
                }
            })
            .state('profile', {
                url: "/profile/{idUsuario}",
                templateUrl: "app/components/profile/index.html",
                controller: 'ProfileController as vmProfile',
                data: {
                    pageTitle: "messages.profile_usuario"
                }
            })
            .state('profile.seguidores', {
                url: "/seguidores",
                templateUrl: "app/components/rede_social/seguidores.html",
                controller: 'SeguidoresController as vmSeguidores',
                acesso: 'registrado'
            })
            .state('profile.seguindo', {
                url: "/seguindo",
                templateUrl: "app/components/rede_social/seguindo.html",
                controller: 'SeguidoresController as vmSeguidores',
                acesso: 'registrado'
            })
            .state('profile.campeonatos', {
                url: "/campeonatos",
                templateUrl: "app/components/campeonato/listaCampeonatos.html",
                controller: 'ProfileController as vmProfile',
                acesso: 'registrado'
            })
            .state('jogo', {
                url: "/jogo/{idJogo}",
                templateUrl: "app/components/jogo/index.html",
                controller: 'JogoController as vmJogo',
                data: {
                    pageTitle: "menus.jogos"
                }
            })
            .state('logout', {
                url: "/logout"
            });

        $urlRouterProvider.otherwise('/home');

    }

})();

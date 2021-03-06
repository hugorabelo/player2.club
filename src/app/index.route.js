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
                templateUrl: "app/components/auth/login.html",
                controller: 'LoginController as vmAuth',
                acesso: 'publico',
                data: {
                    pageTitle: "fields.login"
                }
            })
            .state('recuperar_senha', {
                url: "/recuperar_senha",
                templateUrl: "app/components/auth/recuperar_senha.html",
                controller: 'LoginController as vmAuth',
                acesso: 'publico',
                data: {
                    pageTitle: "fields.login"
                }
            })
            .state('redefinir_senha', {
                url: "/redefinir_senha/{token}",
                templateUrl: "app/components/auth/redefinir_senha.html",
                controller: 'LoginController as vmAuth',
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
                controller: 'ProfileController as vmCampeonato',
                acesso: 'registrado'
            })
            .state('home.editar_perfil', {
                url: "/editar_perfil",
                templateUrl: "app/components/dashboard/editarPerfil.html",
                controller: 'HomeController as vmHome',
                acesso: 'registrado'
            })
            .state('home.criar_campeonato', {
                url: "/criar_campeonato",
                templateUrl: "app/components/campeonato_novo/cadastroCampeonato.html",
                controller: 'CriarCampeonatoController as vmCriarCampeonato',
                data: {
                    pageTitle: "messages.campeonato_create",
                    novo: true
                }
            })
            .state('home.partidas_usuario', {
                url: "/partidas_usuario",
                templateUrl: "app/components/dashboard/partidasUsuario.html",
                controller: 'CampeonatoController as vmCampeonato',
                acesso: 'registrado'
            })
            .state('home.notificacoes', {
                url: "/notificacoes",
                templateUrl: "app/components/dashboard/notificacoes.html",
                controller: 'TopNavController as vmTopNav',
                data: {
                    pageTitle: "messages.notificacoes"
                }
            })
            .state('home.mensagens', {
                url: "/mensagens",
                templateUrl: "app/components/dashboard/mensagens.html",
                controller: 'TopNavController as vmTopNav',
                data: {
                    pageTitle: "messages.mensagens"
                }
            })
            .state('home.chat', {
                url: "/chat/{idUsuario}",
                templateUrl: "app/components/dashboard/chatMensagem.html",
                controller: 'HomeController as vmHome',
                data: {
                    pageTitle: "messages.mensagens"
                }
            })
            .state('home.pesquisar_campeonatos', {
                url: "/pesquisar_campeonatos",
                templateUrl: "app/components/campeonato/pesquisar.html",
                controller: 'CampeonatoController as vmCampeonato',
                data: {
                    pageTitle: "messages.mensagens"
                }
            })
            .state('home.equipes', {
                url: "/equipes/{idUsuario}",
                templateUrl: "app/components/equipe/minhasEquipes.html",
                controller: 'EquipeController as vmEquipe',
                data: {
                    pageTitle: "messages.mensagens"
                }
            })
            .state('equipe', {
                url: "/equipe/{idEquipe}",
                templateUrl: "app/components/equipe/index.html",
                controller: 'EquipeController as vmEquipe',
                data: {
                    pageTitle: "messages.mensagens"
                }
            })
            .state('equipe.integrantes', {
                url: "/integrantes",
                templateUrl: "app/components/equipe/formIntegrantes.html",
                controller: 'EquipeController as vmEquipe',
                data: {
                    pageTitle: "messages.mensagens"
                }
            })
            .state('equipe.inscricoes', {
                url: "/inscricoes",
                templateUrl: "app/components/equipe/formSolicitacoes.html",
                controller: 'EquipeController as vmEquipe',
                data: {
                    pageTitle: "messages.mensagens"
                }
            })
            .state('home.convidar_amigos', {
                url: "/convidar_amigos",
                templateUrl: "app/components/dashboard/convidarAmigos.html",
                controller: 'HomeController as vmHome',
                data: {
                    pageTitle: "messages.mensagens"
                }
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
                templateUrl: "app/components/cadastroPlataforma/index.html",
                controller: 'PlataformaController as vmPlataforma',
                data: {
                    pageTitle: "menus.plataformas"
                }
            })
            .state('modeloCampeonato', {
                url: "/modeloCampeonato",
                templateUrl: "app/components/cadastroModeloCampeonato/index.html",
                controller: 'ModeloCampeonatoController as vmModeloCampeonato',
                data: {
                    pageTitle: "menus.jogos"
                }
            })
            .state('campeonatoTipo', {
                url: "/campeonatoTipo",
                templateUrl: "app/components/cadastroCampeonatoTipo/index.html",
                controller: 'CampeonatoTipoController as vmCampeonatoTipo',
                data: {
                    pageTitle: "menus.campeonatoTipos"
                }
            })
            .state('cadastroJogo', {
                // Layout
                url: "/cadastroJogo",
                templateUrl: "app/components/cadastroJogo/index.html",
                controller: 'CadastroJogoController as vmCadastroJogo',
                data: {
                    pageTitle: "menus.jogos"
                }
            })
            .state('usuarioTipo', {
                // N??o est?? funcionando
                url: "/usuarioTipo",
                templateUrl: "app/components/usuarioTipo/index.html",
                controller: 'UsuarioTipoController as vmUsuarioTipo',
                data: {
                    pageTitle: "menus.usuarioTipos"
                }
            })
            .state('usuario', {
                // Verificar
                url: "/usuario",
                templateUrl: "app/components/usuario/index.html",
                controller: 'UsuarioController as vmUsuario',
                data: {
                    pageTitle: "menus.usuarios"
                }
            })
            .state('menu', {
                // Remover
                url: "/menu",
                templateUrl: "app/components/menu/index.html",
                controller: 'MenuController as vmMenu',
                data: {
                    pageTitle: "menus.menus"
                }
            })
            .state('permissao', {
                // Remover
                url: "/permissao",
                templateUrl: "app/components/permissao/index.html",
                controller: 'PermissaoController as vmPermissao',
                data: {
                    pageTitle: "menus.permissoes"
                }
            })
            .state('cadastroTime', {
                // Criar
                url: "/cadastroTime",
                templateUrl: "app/components/cadastroTime/index.html",
                controller: 'CadastroTimeController as vmCadastroTime',
                data: {
                    pageTitle: "menus.jogos"
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
                controller: 'ProfileController as vmCampeonato',
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
            .state('jogo.campeonatos', {
                url: "/campeonatos",
                templateUrl: "app/components/campeonato/listaCampeonatos.html",
                controller: 'JogoController as vmCampeonato',
                acesso: 'registrado'
            })
            .state('jogo.seguidores', {
                url: "/seguidores",
                templateUrl: "app/components/rede_social/seguidores.html",
                controller: 'SeguidoresController as vmSeguidores',
                acesso: 'registrado'
            })
            .state('logout', {
                url: "/logout"
            });

        $urlRouterProvider.otherwise('/home');

    }

})();

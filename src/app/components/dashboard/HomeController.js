/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('HomeController', ['$rootScope', '$scope', '$filter', '$mdDialog', '$translate', '$window', 'Atividade', 'Post', 'Usuario', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', function ($rootScope, $scope, $filter, $mdDialog, $translate, $window, Atividade, Post, Usuario, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario) {

        var vm = this;

        $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no', 'messages.confirma_desistir_campeonato', 'messages.inscrever_titulo', 'messages.inscrever']).then(function (translations) {
            $scope.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
            $scope.textoYes = translations['messages.yes'];
            $scope.textoNo = translations['messages.no'];
            $scope.textoDesistirCampeonato = translations['messages.confirma_desistir_campeonato'];
            $scope.textoInscreverTitulo = translations['messages.inscrever_titulo'];
            $scope.textoInscrever = translations['messages.inscrever'];
        });

        //vm.getFeedDoUsuario();

        vm.criarPost = function () {
            var post = {};
            post.users_id = $rootScope.usuarioLogado.id;
            post.texto = vm.novoPost;
            Post.salvar(post)
                .success(function (data) {
                    vm.novoPost = '';
                    //TODO
                })
        };

        vm.getFeedDoUsuario = function () {
            Usuario.getFeed($rootScope.usuarioLogado.id)
                .success(function (data) {
                    vm.atividades = data;
                    angular.forEach(vm.atividades, function (atividade) {
                        if (atividade.post_id) {
                            vm.getCurtidas(atividade);
                            vm.usuarioCurtiu(atividade);
                            vm.getComentariosDoPost(atividade.objeto);
                        }
                    })
                });
        };

        vm.exibeData = function (data) {
            var dataExibida = new Date(data);
            return $filter('date')(dataExibida, 'dd/MM/yyyy HH:mm');
        };

        vm.curtir = function (atividade) {
            console.log(atividade);
            var curtida = {};
            curtida.atividade_id = atividade.id;
            curtida.users_id = $rootScope.usuarioLogado.id;
            Atividade.curtir(curtida)
                .success(function (data) {
                    vm.getCurtidas(atividade);
                    atividade.curtiu = !atividade.curtiu;
                }).error(function (data, status) {
                    vm.messages = data.errors;
                    vm.status = status;
                });
        };

        vm.getCurtidas = function (atividade) {
            Atividade.getCurtidas(atividade.id)
                .success(function (data) {
                    atividade.curtidas = data;
                })
                .error(function (data, status) {
                    return [];
                });
        };

        vm.usuarioCurtiu = function (atividade) {
            var curtida = {};
            curtida.atividade_id = atividade.id;
            curtida.users_id = $rootScope.usuarioLogado.id;
            Atividade.usuarioCurtiuAtividade(curtida)
                .success(function (data) {
                    atividade.curtiu = data.curtiu;
                })
        };

        vm.comentar = function (elemento) {
            var elementoNovo = $window.document.getElementById(elemento);
            elementoNovo.focus();
        };

        vm.salvarComentario = function (ev, post) {
            if (ev.keyCode === 13) {
                var comentario = {};
                comentario.post_id = post.id;
                comentario.users_id = $rootScope.usuarioLogado.id;
                comentario.texto = post.novoComentario;
                ev.preventDefault();
                Post.salvarComentario(comentario)
                    .success(function (data) {
                        post.comentarios = data;
                        post.novoComentario = '';
                    })
            }
        };

        vm.getComentariosDoPost = function (post) {
            Post.getComentarios(post.id, $rootScope.usuarioLogado.id)
                .success(function (data) {
                    post.comentarios = data;
                    $rootScope.loading = false;
                });
        };

        /*
        vm.usuario = {};
        vm.exibeFormulario = false;
        vm.exibeFormularioPerfil = false;
        vm.exibeFormularioImagem = false;

        vm.files = [];

        //$rootScope.loading = true;
        Usuario.show($rootScope.usuarioLogado)
            .success(function (data) {
                vm.usuario = data;
                vm.carregaDadosUsuario(vm.usuario.id);
            })
            .error(function (data, status) {});

        vm.abreFormularioGamertag = function () {
            vm.exibeFormulario = !vm.exibeFormulario;
        };

        vm.abreFormularioPerfil = function () {
            vm.exibeFormularioPerfil = true;
        };

        vm.abreFormularioImagemPerfil = function () {
            vm.exibeFormularioImagem = true;
        };

        vm.getPlataformasDoUsuario = function () {
            vm.userPlataformas = {};
            UserPlataforma.getPlataformasDoUsuario(vm.usuario.id)
                .success(function (data) {
                    vm.userPlataformas = data;
                })
                .error(function (data) {

                });
        };

        vm.getPlataformas = function () {
            vm.plataformas = {};
            vm.userPlataforma = {};
            Plataforma.get()
                .success(function (data) {
                    vm.plataformas = data;
                    vm.userPlataforma.users_id = vm.usuario.id;
                })
                .error(function (data) {

                });
        };

        vm.salvaUserPlataforma = function () {
            UserPlataforma.save(vm.userPlataforma)
                .success(function (data) {
                    vm.carregaDadosUsuario(vm.usuario.id);
                    vm.exibeFormulario = false;
                }).error(function (data, status) {
                    vm.messagePontuacao = data.message;
                    vm.status = status;
                });
        };

        vm.excluiUserPlataforma = function (ev, id) {
            vm.idRegistroExcluir = id;
            var confirm = $mdDialog.confirm(id)
                .title($scope.textoConfirmaExclusao)
                .ariaLabel($scope.textoConfirmaExclusao)
                .targetEvent(ev)
                .ok($scope.textoYes)
                .cancel($scope.textoNo)
                .theme('player2');

            $mdDialog.show(confirm).then(function () {
                $rootScope.loading = true;
                UserPlataforma.destroy(vm.idRegistroExcluir)
                    .success(function (data) {
                        vm.carregaDadosUsuario(vm.usuario.id);
                        $rootScope.loading = false;
                    });
            }, function () {

            });
        };

        vm.salvaPerfil = function () {
            Usuario.update(vm.usuario, vm.files[0])
                .success(function (data) {
                    vm.carregaDadosUsuario(vm.usuario.id);
                    vm.exibeFormularioPerfil = false;
                    vm.exibeFormularioImagem = false;
                    vm.files = [];
                });
        };

        vm.carregaDadosUsuario = function (id) {
            Usuario.show(id)
                .success(function (data) {
                    vm.usuario = data;
                    vm.getPlataformasDoUsuario();
                    vm.getPlataformas();
                    vm.getCampeonatosInscritos();
                    vm.getCampeonatosDisponiveis();
                })
                .error(function (data, status) {});
        };

        vm.getCampeonatosInscritos = function () {
            vm.userCampeonatosInscritos = {};
            Usuario.getCampeonatosInscritos(vm.usuario.id)
                .success(function (data) {
                    vm.userCampeonatosInscritos = data;
                })
                .error(function (data) {});
        };

        vm.getCampeonatosDisponiveis = function () {
            vm.userCampeonatosDisponiveis = {};
            Usuario.getCampeonatosDisponiveis(vm.usuario.id)
                .success(function (data) {
                    vm.userCampeonatosDisponiveis = data;
                })
                .error(function (data) {});
        };

        vm.inscreverCampeonato = function (ev, id) {
            vm.idCampeonato = id;
            Campeonato.getInformacoes(id)
                .success(function (data) {
                    vm.campeonatoSelecionado = data;
                    //                    var mensagem = vm.campeonatoSelecionado.detalhes;
                    var confirm = $mdDialog.confirm(id)
                        .title($scope.textoInscreverTitulo)
                        .ariaLabel($scope.textoInscreverTitulo)
                        .targetEvent(ev)
                        .ok($scope.textoInscrever)
                        .cancel($scope.textoNo)
                        .theme('player2');

                    $mdDialog.show(confirm).then(function () {
                        $rootScope.loading = true;
                        CampeonatoUsuario.save(vm.usuario.id, vm.idCampeonato)
                            .success(function (data) {
                                vm.getCampeonatosInscritos();
                                vm.getCampeonatosDisponiveis();
                            });
                    }, function () {

                    });
                });
        };

        vm.sairCampeonato = function (ev, id) {
            vm.idRegistroExcluir = id;
            var confirm = $mdDialog.confirm(id)
                .title($scope.textoDesistirCampeonato)
                .ariaLabel($scope.textoDesistirCampeonato)
                .targetEvent(ev)
                .ok($scope.textoYes)
                .cancel($scope.textoNo)
                .theme('player2');

            $mdDialog.show(confirm).then(function () {
                var i, id_campeonato_usuario;
                $rootScope.loading = true;
                CampeonatoUsuario.getUsuarios(vm.idRegistroExcluir)
                    .success(function (data) {
                        for (i = 0; i < data.length; i = i + 1) {
                            if (data[i].users_id === vm.usuario.id) {
                                id_campeonato_usuario = data[i].id;
                                break;
                            }
                        }
                        CampeonatoUsuario.destroy(id_campeonato_usuario)
                            .success(function (data) {
                                vm.getCampeonatosInscritos();
                                vm.getCampeonatosDisponiveis();
                            })
                            .error(function (data) {

                            });
                    });
            }, function () {

            });
        };
        */

    }]);
}());

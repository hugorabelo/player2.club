/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('FeedController', ['$rootScope', '$scope', '$filter', '$mdDialog', '$translate', '$window', '$stateParams', 'Atividade', 'Post', 'Usuario', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', function ($rootScope, $scope, $filter, $mdDialog, $translate, $window, $stateParams, Atividade, Post, Usuario, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario) {

        var vm = this;

        $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no', 'messages.confirma_desistir_campeonato', 'messages.inscrever_titulo', 'messages.inscrever']).then(function (translations) {
            vm.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
            vm.textoYes = translations['messages.yes'];
            vm.textoNo = translations['messages.no'];
            vm.textoDesistirCampeonato = translations['messages.confirma_desistir_campeonato'];
            vm.textoInscreverTitulo = translations['messages.inscrever_titulo'];
            vm.textoInscrever = translations['messages.inscrever'];
        });

        vm.idUsuario = $stateParams.idUsuario

        vm.inicializa = function () {
            if (vm.idUsuario !== undefined) {
                Usuario.show(vm.idUsuario)
                    .success(function (data) {
                        vm.usuario = data;
                        vm.getFeedDoUsuario(false);
                    });
            } else {
                vm.idUsuario = $rootScope.usuarioLogado.id;
                Usuario.show(vm.idUsuario)
                    .success(function (data) {
                        vm.usuario = data;
                        vm.getFeedDoUsuario(true);
                    });
            }
        }

        vm.criarPost = function () {
            var post = {};
            post.users_id = $rootScope.usuarioLogado.id;
            post.texto = vm.novoPost;
            Post.salvar(post)
                .success(function (data) {
                    vm.novoPost = '';
                    vm.getFeedDoUsuario();
                })
        };

        vm.getFeedDoUsuario = function (todos) {
            if (todos == undefined) {
                todos = false;
            }
            Usuario.getFeed(vm.idUsuario, todos)
                .success(function (data) {
                    vm.atividades = data;
                    angular.forEach(vm.atividades, function (atividade) {
                        if (atividade.post_id) {
                            vm.getCurtidas(atividade);
                            vm.usuarioCurtiu(atividade);
                            vm.getComentarios(atividade);
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

        vm.salvarComentario = function (ev, atividade) {
            if (ev.keyCode === 13) {
                var comentario = {};
                comentario.atividade_id = atividade.id;
                comentario.users_id = $rootScope.usuarioLogado.id;
                comentario.texto = atividade.novoComentario;
                ev.preventDefault();
                Atividade.salvarComentario(comentario)
                    .success(function (data) {
                        atividade.comentarios = data;
                        atividade.novoComentario = '';
                    })
            }
        };

        vm.getComentarios = function (atividade) {
            Atividade.getComentarios(atividade.id, $rootScope.usuarioLogado.id)
                .success(function (data) {
                    atividade.comentarios = data;
                    $rootScope.loading = false;
                });
        };

        vm.salvarCompartilhar = function (novoPost) {
            novoPost.users_id = $rootScope.usuarioLogado.id;
            Post.salvar(novoPost)
                .success(function (data) {
                    vm.getFeedDoUsuario();
                });
        };

        vm.compartilhar = function (ev, atividade) {
            $mdDialog.show({
                    locals: {
                        atividade: atividade,
                        novoPost: {}
                    },
                    controller: DialogController,
                    templateUrl: 'app/components/rede_social/compartilhar.tmpl.html',
                    targetEvent: ev,
                    parent: angular.element(document.body),
                    clickOutsideToClose: true,
                    fullscreen: true
                })
                .then(function (novoPost) {
                    vm.salvarCompartilhar(novoPost);
                }, function () {
                    $scope.status = 'cancel';
                });
        };

        function DialogController($scope, $mdDialog, atividade) {
            $scope.atividade = atividade;
            $scope.novoPost = {};
            $scope.novoPost.post_id = atividade.post_id;

            $scope.exibeData = function (novaData) {
                return vm.exibeData(novaData);
            }

            $scope.adicionarImagem = function () {
                console.log('adicionar imagem');
            };

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.compartilhar = function () {
                $mdDialog.hide($scope.novoPost);
            };
        };

        vm.excluir = function (ev, atividade, index) {

            vm.idRegistroExcluir = atividade.id;
            var confirm = $mdDialog.confirm(atividade.id)
                .title(vm.textoConfirmaExclusao)
                .ariaLabel(vm.textoConfirmaExclusao)
                .targetEvent(ev)
                .ok(vm.textoYes)
                .cancel(vm.textoNo)
                .theme('player2');

            $mdDialog.show(confirm).then(function () {
                $rootScope.loading = true;
                Atividade.destroy(vm.idRegistroExcluir)
                    .success(function (data) {
                        vm.atividades.splice(index, 1);
                    });
            }, function () {

            });
        };

        vm.atualizar = function (post) {
            Post.update(post)
                .success(function (data) {
                    //vm.getFeedDoUsuario();
                });
        };

        vm.editar = function (ev, atividade) {
            $mdDialog.show({
                    locals: {
                        atividade: atividade
                    },
                    controller: DialogControllerEditar,
                    templateUrl: 'app/components/rede_social/editar.tmpl.html',
                    targetEvent: ev,
                    parent: angular.element(document.body),
                    clickOutsideToClose: true,
                    fullscreen: true
                })
                .then(function (post) {
                    vm.atualizar(post);
                }, function () {
                    $scope.status = 'cancel';
                });
        };

        function DialogControllerEditar($scope, $mdDialog, atividade) {
            $scope.atividade = atividade;
            $scope.post = atividade.objeto;

            $scope.exibeData = function (novaData) {
                return vm.exibeData(novaData);
            }

            $scope.adicionarImagem = function () {
                console.log('adicionar imagem');
            };

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.salvar = function () {
                $mdDialog.hide($scope.post);
            };
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

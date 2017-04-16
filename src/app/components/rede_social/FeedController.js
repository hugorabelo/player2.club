/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('FeedController', ['$rootScope', '$scope', '$filter', '$mdDialog', '$translate', '$window', '$stateParams', '$timeout', 'toastr', 'localStorageService', 'Atividade', 'Post', 'Usuario', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', 'Jogo', 'Lightbox', 'Feed', function ($rootScope, $scope, $filter, $mdDialog, $translate, $window, $stateParams, $timeout, toastr, localStorageService, Atividade, Post, Usuario, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario, Jogo, Lightbox, Feed) {

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

        vm.idJogo = $stateParams.idJogo;

        vm.novoPost = {};

        vm.feedFactory = {};

        $scope.$on('userProfileSet', function () {
            vm.inicializa();
        });

        vm.inicializa = function () {
            if (vm.idJogo !== undefined) {
                var usuarioLogado = localStorageService.get('usuarioLogado');
                vm.idUsuario = usuarioLogado.id;
                vm.feedFactory = new Feed(vm.idUsuario, 0, vm.idJogo);
                vm.feedFactory.proximaPagina();
            } else if (vm.idUsuario !== undefined) {
                Usuario.show(vm.idUsuario)
                    .success(function (data) {
                        vm.usuario = data;
                        vm.feedFactory = new Feed(vm.idUsuario, 0);
                        vm.feedFactory.proximaPagina();
                    });
            } else {
                var usuarioLogado = localStorageService.get('usuarioLogado');
                if (usuarioLogado !== null) {
                    Usuario.show(usuarioLogado.id)
                        .success(function (data) {
                            vm.usuario = data;
                            vm.feedFactory = new Feed(usuarioLogado.id, 1);
                            vm.feedFactory.proximaPagina();
                        });
                }
            }

        };

        vm.criarPost = function () {
            var post = {};
            if ((vm.idUsuario != undefined) && (vm.idUsuario != localStorageService.get('usuarioLogado').id)) {
                post.destinatario_id = vm.idUsuario;
            }
            if (vm.idJogo !== undefined) {
                post.jogos_id = vm.idJogo;
            }
            post.users_id = localStorageService.get('usuarioLogado').id;
            post.texto = vm.novoPost.texto;
            post.imagens = vm.novoPost.imagens;
            Post.salvar(post)
                .success(function (data) {
                    vm.novoPost = {};
                    if (vm.idJogo !== undefined) {
                        vm.feedFactory = new Feed(vm.idUsuario, 0, vm.idJogo);
                    } else if (vm.idUsuario !== undefined) {
                        vm.feedFactory = new Feed(vm.idUsuario, 0);
                    } else {
                        vm.feedFactory = new Feed(post.users_id, 1);
                    }
                    vm.feedFactory.proximaPagina();
                })
        };

        vm.exibeData = function (data) {
            var dataExibida = moment(data, "YYYY-MM-DD HH:mm:ss").toDate();
            return $filter('date')(dataExibida, 'dd/MM/yyyy HH:mm');
        };

        vm.curtir = function (atividade) {
            var curtida = {};
            curtida.atividade_id = atividade.id;
            curtida.users_id = localStorageService.get('usuarioLogado').id;
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
            curtida.users_id = localStorageService.get('usuarioLogado').id;
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
                comentario.users_id = localStorageService.get('usuarioLogado').id;
                comentario.texto = atividade.novoComentario;
                ev.preventDefault();
                Atividade.salvarComentario(comentario)
                    .success(function (data) {
                        vm.getComentarios(atividade);
                        atividade.novoComentario = '';
                    })
            }
        };

        vm.getComentarios = function (atividade) {
            Atividade.getComentarios(atividade.id, localStorageService.get('usuarioLogado').id)
                .success(function (data) {
                    atividade.comentarios = data;
                    angular.forEach(atividade.comentarios, function (comentario) {
                        vm.getCurtidas(comentario.atividade);
                        vm.usuarioCurtiu(comentario.atividade);
                        vm.getComentarios(comentario.atividade);
                    });
                });
        };

        vm.salvarCompartilhar = function (novoPost) {
            novoPost.users_id = localStorageService.get('usuarioLogado').id;
            Post.salvar(novoPost)
                .success(function (data) {
                    if (vm.idUsuario !== undefined) {
                        vm.feedFactory = new Feed(vm.idUsuario, 0);
                    } else {
                        vm.feedFactory = new Feed(novoPost.users_id, 1);
                    }
                    vm.feedFactory.proximaPagina();
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
                        vm.feedFactory.items.splice(index, 1);
                    });
            }, function () {

            });
        };

        vm.atualizar = function (post, atividade) {
            Post.update(post)
                .success(function (data) {
                    toastr.success('Post atualizado com sucesso');
                    Post.getImagens(post.id)
                        .success(function (data) {
                            atividade.objeto.imagens = data;
                        });
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
                    vm.atualizar(post, atividade);
                }, function () {
                    $scope.status = 'cancel';
                });
        };

        function DialogControllerEditar($scope, $mdDialog, atividade) {
            $timeout(function () {
                $scope.$broadcast("elastic:adjust"); // <-- this is the workaround
            }, 500);
            $scope.atividade = atividade;
            $scope.post = atividade.objeto;
            $scope.imagensDoPost = [];
            angular.copy(atividade.objeto.imagens, $scope.imagensDoPost);
            $scope.post.imagensRemover = [];

            $scope.exibeData = function (novaData) {
                return vm.exibeData(novaData);
            }

            $scope.adicionarImagem = function () {
                if ($scope.inserirImagem) {
                    $scope.inserirImagem = false;
                } else {
                    $scope.inserirImagem = true;
                }
            }

            $scope.removeImagemEditar = function (id) {
                $scope.post.imagensRemover.push(id);
                var indice = 0;
                angular.forEach($scope.imagensDoPost, function (imagem) {
                    if (imagem.id == id) {
                        $scope.imagensDoPost.splice(indice, 1);
                    }
                    indice++;
                });
            }

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.salvar = function () {
                $mdDialog.hide($scope.post);
            };
        };

        vm.excluirComentario = function (ev, atividade, atividadePai) {

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
                        vm.getComentarios(atividadePai);
                    });
            }, function () {

            });
        };

        vm.editarComentario = function ($ev, comentario) {
            comentario.edicao = true;
        };

        vm.atualizarComentario = function (ev, comentario, atividade) {
            if (ev.keyCode === 13) {
                ev.preventDefault();
                Atividade.atualizarComentario(comentario)
                    .success(function (data) {
                        vm.getComentarios(atividade);
                    })
            }
        };

        vm.exibeCurtidas = function ($mdOpenMenu, ev) {
            $mdOpenMenu(ev);
        };

        vm.adicionarImagem = function (ev, post) {
            $mdDialog.show({
                    locals: {
                        post: post,
                        imagens: {}
                    },
                    controller: DialogControllerImagem,
                    templateUrl: 'app/components/rede_social/postarImagem.tmpl.html',
                    targetEvent: ev,
                    parent: angular.element(document.body),
                    clickOutsideToClose: true,
                    fullscreen: true
                })
                .then(function (imagens) {
                    vm.salvarImagens(imagens);
                }, function () {
                    $scope.status = 'cancel';
                });
        };

        function DialogControllerImagem($scope, $mdDialog) {
            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.salvarImagens = function () {
                $mdDialog.hide($scope.files);
            };
        };

        vm.salvarImagens = function (imagens) {
            vm.novoPost.imagens = imagens;
        };

        vm.openLightboxModal = function (images, index) {
            Lightbox.openModal(images, index);
        };

        vm.carregaAtividadeEspecifica = function () {
            var idAtividade = $stateParams.idAtividade;
            Atividade.show(idAtividade)
                .success(function (data) {
                    $scope.atividade = data;
                });
        };

        }]);

}());

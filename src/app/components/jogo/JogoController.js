/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('JogoController', ['$stateParams', '$rootScope', '$scope', '$filter', '$mdDialog', '$translate', 'Jogo', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', 'Post', 'Usuario', '$window', '$location', function ($stateParams, $rootScope, $scope, $filter, $mdDialog, $translate, Jogo, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario, Post, Usuario, $window, $location) {

        var vm = this;

        $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no', 'messages.confirma_desistir_campeonato', 'messages.inscrever_titulo', 'messages.inscrever']).then(function (translations) {
            vm.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
            vm.textoYes = translations['messages.yes'];
            vm.textoNo = translations['messages.no'];
            vm.textoDesistirCampeonato = translations['messages.confirma_desistir_campeonato'];
            vm.textoInscreverTitulo = translations['messages.inscrever_titulo'];
            vm.textoInscrever = translations['messages.inscrever'];
        });

        vm.idJogo = $stateParams.idJogo;
        vm.jogo = {};
        vm.exibeFormulario = false;
        vm.exibeFormularioPerfil = false;
        vm.exibeFormularioImagem = false;

        vm.inicializa = function () {
            vm.carregaDadosJogo(vm.idJogo);
        };

        function DialogController($scope, $mdDialog, tituloModal, post) {
            $scope.tituloModal = tituloModal;
            $scope.post = post;
            $scope.novoPost = {};
            $scope.novoPost.post_id = post.id;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.save = function () {
                vm.saveCompartilhamento($scope.novoPost);
                $mdDialog.hide();
            };
        }

        vm.carregaDadosJogo = function (id) {
            Jogo.show(id)
                .success(function (data) {
                    vm.jogo = data;
                    vm.segue();
                    vm.getCampeonatos(id);
                    //                            vm.getJogos(id);
                    //                    vm.getPlataformasDoUsuario();
                    //                    vm.getPlataformas();
                    //                    vm.getCampeonatosDisponiveis();
                })
                .error(function (data, status) {});
        };

        vm.seguir = function (idJogo) {
            Usuario.seguirJogo($rootScope.usuarioLogado.id, vm.jogo)
                .success(function (data) {
                    vm.jogo.seguido = true;
                })
        };

        vm.deixarDeSeguir = function (idJogo) {
            Usuario.deixarDeSeguirJogo($rootScope.usuarioLogado.id, vm.jogo)
                .success(function (data) {
                    vm.jogo.seguido = false;
                })
        };

        //        vm.carregaPosts = function (idUsuario) {
        //            Usuario.getPosts(idUsuario, $rootScope.usuarioLogado.id, 5)
        //                .success(function (data) {
        //                    vm.posts = data;
        //                })
        //        };

        vm.getCampeonatos = function (idJogo) {
            Jogo.getCampeonatos(idJogo)
                .success(function (data) {
                    vm.campeonatos = data;
//                    vm.campeonatosInscricoesAbertas = data.campeonatosInscricoesAbertas;
//                    vm.campeonatosAIniciar = data.campeonatosAIniciar;
//                    vm.campeonatosEmAndamento = data.campeonatosEmAndamento;
//                    vm.campeonatosEncerrados = data.campeonatosEncerrados;
                })
        };

        //        vm.salvarComentario = function (ev, post) {
        //            if (ev.keyCode === 13) {
        //                var comentario = {};
        //                comentario.post_id = post.id;
        //                comentario.users_id = $rootScope.usuarioLogado.id;
        //                comentario.texto = post.novoComentario;
        //                ev.preventDefault();
        //                Post.salvarComentario(comentario)
        //                    .success(function (data) {
        //                        post.comentarios = data;
        //                        post.novoComentario = '';
        //                    })
        //            }
        //        };
        //
        //        vm.curtirPost = function (post) {
        //            var curtida = {};
        //            curtida.post_id = post.id;
        //            curtida.users_id = $rootScope.usuarioLogado.id;
        //            Post.curtir(curtida)
        //                .success(function (data) {
        //                    post.quantidade_curtidas = data.quantidadeCurtidas;
        //                    post.curtiu = !post.curtiu;
        //                }).error(function (data, status) {
        //                    vm.messages = data.errors;
        //                    vm.status = status;
        //                });
        //        };
        //
        //        vm.curtirComentario = function (comentario) {
        //            var curtida = {};
        //            curtida.comentario_id = comentario.id;
        //            curtida.users_id = $rootScope.usuarioLogado.id;
        //            Post.curtirComentario(curtida)
        //                .success(function (data) {
        //                    comentario.quantidade_curtidas = data.quantidadeCurtidas;
        //                    comentario.curtiu = !comentario.curtiu;
        //                }).error(function (data, status) {
        //                    vm.messages = data.errors;
        //                    vm.status = status;
        //                });
        //        };
        //
        //        vm.curtiuPost = function (post) {
        //            var curtida = {};
        //            curtida.post_id = post.id;
        //            curtida.users_id = $rootScope.usuarioLogado.id;
        //            Post.usuarioCurtiuPost(curtida)
        //                .success(function (data) {
        //                    return data.curtiu;
        //                })
        //        };

        vm.segue = function () {
            Usuario.segueJogo($rootScope.usuarioLogado.id, vm.jogo)
                .success(function (data) {
                    vm.jogo.seguido = data.segue;
                })
        };

        vm.exibeData = function (data) {
            var dataExibida = new Date(data);
            return $filter('date')(dataExibida, 'dd/MM/yyyy');
        };

        //        vm.editPost = function (post) {
        //            post.editar = true;
        //        };
        //
        //        vm.deletePost = function (ev, post, index) {
        //
        //            vm.idRegistroExcluir = post.id;
        //            var confirm = $mdDialog.confirm(post.id)
        //                .title(vm.textoConfirmaExclusao)
        //                .ariaLabel(vm.textoConfirmaExclusao)
        //                .targetEvent(ev)
        //                .ok(vm.textoYes)
        //                .cancel(vm.textoNo)
        //                .theme('player2');
        //
        //            $mdDialog.show(confirm).then(function () {
        //                $rootScope.loading = true;
        //                Post.destroy(vm.idRegistroExcluir)
        //                    .success(function (data) {
        //                        vm.posts.splice(index, 1);
        //                        $rootScope.loading = false;
        //                    });
        //            }, function () {
        //
        //            });
        //        };
        //
        //        vm.ediComentario = function (comentario) {
        //            comentario.editar = true;
        //        };
        //
        //        vm.deleteComentario = function (ev, comentario, post) {
        //            vm.idRegistroExcluir = comentario.id;
        //            var confirm = $mdDialog.confirm(comentario.id)
        //                .title(vm.textoConfirmaExclusao)
        //                .ariaLabel(vm.textoConfirmaExclusao)
        //                .targetEvent(ev)
        //                .ok(vm.textoYes)
        //                .cancel(vm.textoNo)
        //                .theme('player2');
        //
        //            $mdDialog.show(confirm).then(function () {
        //                $rootScope.loading = true;
        //                Post.destroyComentario(vm.idRegistroExcluir)
        //                    .success(function (data) {
        //                        Post.getComentarios(data.id_post, $rootScope.usuarioLogado.id)
        //                            .success(function (data) {
        //                                post.comentarios = data;
        //                                $rootScope.loading = false;
        //                            });
        //                        $rootScope.loading = false;
        //                    });
        //            }, function () {
        //
        //            });
        //        };
        //
        //        vm.updateComentario = function (ev, comentario) {
        //            if (ev.keyCode === 13) {
        //                ev.preventDefault();
        //                Post.updateComentario(comentario)
        //                    .success(function (data) {
        //                        comentario.editar = false;
        //                        comentario = data.comentario;
        //                    })
        //                    .error(function (data, status) {});
        //            }
        //        };
        //
        //        vm.updatePost = function (ev, post) {
        //            Post.update(post)
        //                .success(function (data) {
        //                    post.editar = false;
        //                    post = data.post;
        //                })
        //                .error(function (data, status) {});
        //        };
        //
        //        vm.compartilharPost = function (ev, post) {
        //            $mdDialog
        //                .show({
        //                    locals: {
        //                        tituloModal: 'messages.compartilhar_post',
        //                        post: post
        //                    },
        //                    controller: DialogController,
        //                    templateUrl: 'app/components/profile/compartilhaModal.html',
        //                    parent: angular.element(document.body),
        //                    targetEvent: ev,
        //                    clickOutsideToClose: true,
        //                    fullscreen: true // Only for -xs, -sm breakpoints.
        //                })
        //                .then(function () {
        //
        //                }, function () {
        //
        //                });
        //        };
        //
        //        vm.saveCompartilhamento = function (novoPost) {
        //            novoPost.users_id = $rootScope.usuarioLogado.id;
        //            Post.salvar(novoPost)
        //                .success(function (data) {
        //
        //                });
        //        };
        //
        //        vm.exibeData = function (data) {
        //            var dataExibida = new Date(data);
        //            return $filter('date')(dataExibida, 'dd/MM/yyyy HH:mm');
        //        };
        //
        //        vm.comentar = function (elemento) {
        //            var elementoNovo = $window.document.getElementById(elemento);
        //            elementoNovo.focus();
        //        };
        //
        //        vm.carregaCampeonato = function (idCampeonato) {
        //        };
        //
        //        vm.getJogos = function (id) {
        //            Usuario.getJogos(id)
        //                .success(function (data) {
        //                    vm.usuario.jogos = data.jogos;
        //                });
        //        };

        vm.carregaCampeonato = function (id) {
            $location.path('/campeonato/' + id);
        }

    }]);
}());

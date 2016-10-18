/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('JogoController', ['$stateParams', '$rootScope', '$scope', '$filter', '$mdDialog', '$translate', 'Jogo', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', 'Post', '$window', function ($stateParams, $rootScope, $scope, $filter, $mdDialog, $translate, Jogo, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario, Post, $window) {

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

        //$rootScope.loading = true;
        Jogo.show(vm.idJogo)
            .success(function (data) {
                vm.jogo = data;
                //                vm.carregaDadosUsuario(vm.usuario.id);
                //                vm.carregaPosts(vm.idUsuario);

            })
            .error(function (data, status) {});

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

        //        vm.carregaDadosUsuario = function (id) {
        //            Usuario.show(id)
        //                .success(function (data) {
        //                    vm.usuario = data;
        //                    vm.segue();
        //                    vm.getCampeonatosInscritos(id);
        //                    vm.getJogos(id);
        //                    //                    vm.getPlataformasDoUsuario();
        //                    //                    vm.getPlataformas();
        //                    //                    vm.getCampeonatosDisponiveis();
        //                })
        //                .error(function (data, status) {});
        //        };

        vm.seguir = function (idJogo) {
            Jogo.seguir($rootScope.usuarioLogado.id, vm.jogo)
                .success(function (data) {
                    vm.jogo.seguido = true;
                })
        };

        vm.deixarDeSeguir = function (idJogo) {
            Jogo.deixarDeSeguir($rootScope.usuarioLogado.id, vm.jogo)
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

        //        vm.getCampeonatosInscritos = function (idUsuario) {
        //            Usuario.getCampeonatosInscritos(idUsuario)
        //                .success(function (data) {
        //                    vm.campeonatosDoUsuario = data;
        //                })
        //        };

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
            Jogo.segue($rootScope.usuarioLogado.id, vm.jogo)
                .success(function (data) {
                    vm.jogo.seguido = data.segue;
                })
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
        //                .theme('default');
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
        //                .theme('default');
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
        //            console.log(idCampeonato);
        //        };
        //
        //        vm.getJogos = function (id) {
        //            Usuario.getJogos(id)
        //                .success(function (data) {
        //                    vm.usuario.jogos = data.jogos;
        //                });
        //        };

    }]);
}());

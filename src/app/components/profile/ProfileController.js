/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('ProfileController', ['$stateParams', '$rootScope', '$scope', '$filter', '$mdDialog', '$translate', 'Usuario', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', 'Post', function ($stateParams, $rootScope, $scope, $filter, $mdDialog, $translate, Usuario, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario, Post) {

        var vm = this;

        $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no', 'messages.confirma_desistir_campeonato', 'messages.inscrever_titulo', 'messages.inscrever']).then(function (translations) {
            vm.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
            vm.textoYes = translations['messages.yes'];
            vm.textoNo = translations['messages.no'];
            vm.textoDesistirCampeonato = translations['messages.confirma_desistir_campeonato'];
            vm.textoInscreverTitulo = translations['messages.inscrever_titulo'];
            vm.textoInscrever = translations['messages.inscrever'];
        });

        vm.idUsuario = $stateParams.idUsuario;
        vm.usuario = {};
        vm.exibeFormulario = false;
        vm.exibeFormularioPerfil = false;
        vm.exibeFormularioImagem = false;

        //$rootScope.loading = true;
        Usuario.show(vm.idUsuario)
            .success(function (data) {
                vm.usuario = data;
                vm.carregaDadosUsuario(vm.usuario.id);
                vm.carregaPosts(vm.idUsuario);

            })
            .error(function (data, status) {});

        vm.carregaDadosUsuario = function (id) {
            Usuario.show(id)
                .success(function (data) {
                    vm.usuario = data;
                    vm.segue()
                        //                    vm.getPlataformasDoUsuario();
                        //                    vm.getPlataformas();
                        //                    vm.getCampeonatosInscritos();
                        //                    vm.getCampeonatosDisponiveis();
                })
                .error(function (data, status) {});
        };

        vm.seguir = function (idUsuario) {
            Usuario.seguir($rootScope.usuarioLogado, vm.usuario)
                .success(function (data) {
                    vm.usuario.seguido = true;
                })
        };

        vm.deixarDeSeguir = function (idUsuario) {
            Usuario.deixarDeSeguir($rootScope.usuarioLogado, vm.usuario)
                .success(function (data) {
                    vm.usuario.seguido = false;
                })
        };

        vm.carregaPosts = function (idUsuario) {
            Usuario.getPosts(idUsuario, $rootScope.usuarioLogado, 5)
                .success(function (data) {
                    vm.posts = data;
                })
        };

        vm.salvarComentario = function (ev, post) {
            if (ev.keyCode === 13) {
                var comentario = {};
                comentario.post_id = post.id;
                comentario.users_id = $rootScope.usuarioLogado;
                comentario.texto = post.novoComentario;
                ev.preventDefault();
                Post.salvarComentario(comentario)
                    .success(function (data) {
                        post.comentarios = data;
                        post.novoComentario = '';
                    })
            }
        };

        vm.curtirPost = function (post) {
            var curtida = {};
            curtida.post_id = post.id;
            curtida.users_id = $rootScope.usuarioLogado;
            Post.curtir(curtida)
                .success(function (data) {
                    post.quantidade_curtidas = data.quantidadeCurtidas;
                    post.curtiu = !post.curtiu;
                }).error(function (data, status) {
                    vm.messages = data.errors;
                    vm.status = status;
                });
        };

        vm.curtirComentario = function (comentario) {
            var curtida = {};
            curtida.comentario_id = comentario.id;
            curtida.users_id = $rootScope.usuarioLogado;
            Post.curtirComentario(curtida)
                .success(function (data) {
                    comentario.quantidade_curtidas = data.quantidadeCurtidas;
                    comentario.curtiu = !comentario.curtiu;
                }).error(function (data, status) {
                    vm.messages = data.errors;
                    vm.status = status;
                });
        };

        vm.curtiuPost = function (post) {
            var curtida = {};
            curtida.post_id = post.id;
            curtida.users_id = $rootScope.usuarioLogado;
            Post.usuarioCurtiuPost(curtida)
                .success(function (data) {
                    console.log(data.curtiu);
                    return data.curtiu;
                })
        };

        vm.segue = function () {
            Usuario.segue($rootScope.usuarioLogado, vm.usuario)
                .success(function (data) {
                    vm.usuario.seguido = data.segue;
                })
        };

        vm.editPost = function (post) {
            post.editar = true;
        };

        vm.deletePost = function (post) {
            console.log('Remove: ' + post.id);
        };

        vm.ediComentario = function (comentario) {
            comentario.editar = true;
        };

        vm.deleteComentario = function (ev, comentario, post) {
            vm.idRegistroExcluir = comentario.id;
            var confirm = $mdDialog.confirm(comentario.id)
                .title(vm.textoConfirmaExclusao)
                .ariaLabel(vm.textoConfirmaExclusao)
                .targetEvent(ev)
                .ok(vm.textoYes)
                .cancel(vm.textoNo)
                .theme('default');

            $mdDialog.show(confirm).then(function () {
                $rootScope.loading = true;
                Post.destroyComentario(vm.idRegistroExcluir)
                    .success(function (data) {
                        Post.getComentarios(data.id_post, $rootScope.usuarioLogado)
                            .success(function (data) {
                                post.comentarios = data;
                                $rootScope.loading = false;
                            });
                        $rootScope.loading = false;
                    });
            }, function () {

            });
        };

        vm.updateComentario = function (ev, comentario) {
            if (ev.keyCode === 13) {
                ev.preventDefault();
                Post.updateComentario(comentario)
                    .success(function (data) {
                        comentario.editar = false;
                        comentario = data.comentario;
                    })
                    .error(function (data, status) {});
            }
        };

        vm.updatePost = function (ev, post) {
            Post.update(post)
                .success(function (data) {
                    post.editar = false;
                    post = data.post;
                })
                .error(function (data, status) {});
        };

    }]);
}());

/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('ProfileController', ['$stateParams', '$rootScope', '$scope', '$filter', '$mdDialog', '$translate', 'Usuario', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', 'Post', function ($stateParams, $rootScope, $scope, $filter, $mdDialog, $translate, Usuario, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario, Post) {

        var vm = this;

        $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no', 'messages.confirma_desistir_campeonato', 'messages.inscrever_titulo', 'messages.inscrever']).then(function (translations) {
            $scope.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
            $scope.textoYes = translations['messages.yes'];
            $scope.textoNo = translations['messages.no'];
            $scope.textoDesistirCampeonato = translations['messages.confirma_desistir_campeonato'];
            $scope.textoInscreverTitulo = translations['messages.inscrever_titulo'];
            $scope.textoInscrever = translations['messages.inscrever'];
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
            var comentario = {};
            comentario.post_id = post.id;
            comentario.users_id = $rootScope.usuarioLogado;
            comentario.texto = post.novoComentario;
            if (ev.keyCode === 13) {
                Post.salvarComentario(comentario)
                    .success(function (data) {
                        post.comentarios = data;
                        post.novoComentario = '';
                    })
            }
        };

        //TODO verificar se usuário já curtiu post e marcar botão de forma diferente
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

        //TODO verificar se usuário já curtiu comentário e marcar botão de forma diferente
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
            console.log('Edita: ' + post.id);
        };

        vm.deletePost = function (post) {
            console.log('Remove: ' + post.id);
        };

        vm.ediComentario = function (comentario) {

        };

        vm.deleteComentario = function (comentario) {

        };

    }]);
}());

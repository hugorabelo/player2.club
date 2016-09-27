/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('ProfileController', ['$stateParams', '$rootScope', '$scope', '$filter', '$mdDialog', '$translate', 'Usuario', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', function ($stateParams, $rootScope, $scope, $filter, $mdDialog, $translate, Usuario, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario) {

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
                    //                    vm.getPlataformasDoUsuario();
                    //                    vm.getPlataformas();
                    //                    vm.getCampeonatosInscritos();
                    //                    vm.getCampeonatosDisponiveis();
                })
                .error(function (data, status) {});
        };

        vm.seguir = function (idUsuario) {
            Usuario.seguir($rootScope.usuarioLogado, idUsuario)
                .success(function (data) {
                    // sad
                })
        };

        vm.carregaPosts = function (idUsuario) {
            Usuario.getPosts(idUsuario, 5)
                .success(function (data) {
                    posts = data;
                })
        };

    }]);
}());

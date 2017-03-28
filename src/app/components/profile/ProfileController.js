/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('ProfileController', ['$stateParams', '$rootScope', '$scope', '$filter', '$mdDialog', '$translate', 'Usuario', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', 'Post', '$window', '$location', function ($stateParams, $rootScope, $scope, $filter, $mdDialog, $translate, Usuario, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario, Post, $window, $location) {

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
                vm.getGamertagsDoUsuario(vm.idUsuario);
                vm.currentNavItem = 'feed';
            })
            .error(function (data, status) {});

        vm.carregaDadosUsuario = function (id) {
            Usuario.show(id)
                .success(function (data) {
                    vm.usuario = data;
                    vm.segue();
                    vm.getCampeonatosInscritos(id);
                    vm.getJogos(id);
                    //                    vm.getPlataformasDoUsuario();
                    //                    vm.getPlataformas();
                    //                    vm.getCampeonatosDisponiveis();
                })
                .error(function (data, status) {});
        };

        vm.seguir = function (idUsuario) {
            Usuario.seguir($rootScope.usuarioLogado.id, vm.usuario)
                .success(function (data) {
                    vm.usuario.seguido = true;
                })
        };

        vm.deixarDeSeguir = function (idUsuario) {
            Usuario.deixarDeSeguir($rootScope.usuarioLogado.id, vm.usuario)
                .success(function (data) {
                    vm.usuario.seguido = false;
                })
        };

        vm.getCampeonatosInscritos = function (idUsuario) {
            Usuario.getCampeonatosInscritos(idUsuario)
                .success(function (data) {
                    vm.campeonatosDoUsuario = data;
                })
        };

        vm.segue = function () {
            Usuario.segue($rootScope.usuarioLogado.id, vm.usuario)
                .success(function (data) {
                    vm.usuario.seguido = data.segue;
                })
        };

        vm.exibeData = function (data) {
            var dataExibida = new Date(data);
            return $filter('date')(dataExibida, 'dd/MM/yyyy HH:mm');
        };

        vm.getJogos = function (id) {
            Usuario.getJogos(id)
                .success(function (data) {
                    vm.usuario.jogos = data.jogos;
                });
        };

        vm.carregaCampeonato = function (id) {
            $location.path('/campeonato/' + id);
        };

        vm.getGamertagsDoUsuario = function (idUsuario) {
            vm.gamertags = {};
            UserPlataforma.getPlataformasDoUsuario(idUsuario)
                .success(function (data) {
                    vm.gamertags = data;
                })
                .error(function (data) {

                });
        };

        vm.getCampeonatosUsuario = function () {
            if (vm.idUsuario === undefined) {
                vm.getCampeonatosInscritos($rootScope.usuarioLogado.id);
            }
        };

    }]);
}());

/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('JogoController', ['$stateParams', '$rootScope', '$scope', '$filter', '$mdDialog', '$translate', 'Jogo', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', 'Usuario', '$window', '$location', function ($stateParams, $rootScope, $scope, $filter, $mdDialog, $translate, Jogo, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario, Usuario, $window, $location) {

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

        vm.getCampeonatosUsuario = function () {
            vm.getCampeonatos(vm.idJogo);
        };


        vm.segue = function () {
            Usuario.segueJogo($rootScope.usuarioLogado.id, vm.jogo)
                .success(function (data) {
                    vm.jogo.seguido = data.segue;
                })
        };

        vm.exibeData = function (data) {
            var dataExibida = moment(data, "YYYY-MM-DD HH:mm:ss").toDate();
            return $filter('date')(dataExibida, 'dd/MM/yyyy');
        };

        vm.carregaCampeonato = function (id) {
            $location.path('/campeonato/' + id);
        }

    }]);
}());

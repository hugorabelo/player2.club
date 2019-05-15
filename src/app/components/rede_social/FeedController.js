/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('FeedController', ['$rootScope', '$scope', '$filter', '$mdDialog', '$translate', '$window', '$stateParams', '$timeout', 'toastr', 'localStorageService', 'Atividade', 'Usuario', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', 'Jogo', 'Lightbox', 'Feed', function ($rootScope, $scope, $filter, $mdDialog, $translate, $window, $stateParams, $timeout, toastr, localStorageService, Atividade, Usuario, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario, Jogo, Lightbox, Feed) {

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

        vm.idJogo = $stateParams.idJogo;

        vm.idEquipe = $stateParams.idEquipe;

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
            } else if (vm.idEquipe !== undefined) {
                var usuarioLogado = localStorageService.get('usuarioLogado');
                vm.idUsuario = usuarioLogado.id;
                vm.feedFactory = new Feed(vm.idUsuario, 0, undefined, vm.idEquipe);
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

        vm.exibeData = function (data) {
            var dataExibida = moment(data, "YYYY-MM-DD HH:mm:ss").toDate();
            return $filter('date')(dataExibida, 'dd/MM/yyyy HH:mm');
        };

    }]);

}());

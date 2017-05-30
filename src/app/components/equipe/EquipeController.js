/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('EquipeController', ['$scope', '$rootScope', '$mdDialog', '$translate', '$location', '$q', '$mdSidenav', '$stateParams', '$filter', '$interval', 'toastr', 'localStorageService', 'Usuario', 'Campeonato', 'CampeonatoUsuario', 'UserPlataforma', 'Plataforma', 'Jogo', 'NotificacaoEvento',
        function ($scope, $rootScope, $mdDialog, $translate, $location, $q, $mdSidenav, $stateParams, $filter, $interval, toastr, localStorageService, Usuario, Campeonato, CampeonatoUsuario, UserPlataforma, Plataforma, Jogo, NotificacaoEvento) {
            var vm = this;

            vm.getEquipesUsuario = function (idUsuario) {
                Usuario.getEquipes(idUsuario)
                    .success(function (data) {
                        vm.equipesDoUsuario = data;
                    })
                    .error(function (error) {})
            }
    }]);

}());

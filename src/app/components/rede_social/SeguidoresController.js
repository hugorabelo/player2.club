/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('SeguidoresController', ['$rootScope', '$scope', '$filter', '$mdDialog', '$translate', '$window', '$stateParams', 'Atividade', 'Post', 'Usuario', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', function ($rootScope, $scope, $filter, $mdDialog, $translate, $window, $stateParams, Atividade, Post, Usuario, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario) {

        var vm = this;

        vm.idUsuario = $stateParams.idUsuario;

        vm.inicializa = function () {
            if (vm.idUsuario == undefined) {
                vm.idUsuario = $rootScope.usuarioLogado.id;
            }
            Usuario.getSeguidores(vm.idUsuario)
                .success(function (data) {
                    vm.seguidores = data;
                })
                .error(function (data, status) {});
        };

    }]);
}());

/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('SeguidoresController', ['$rootScope', '$scope', '$filter', '$mdDialog', '$translate', '$window', '$stateParams', 'Atividade', 'Post', 'Usuario', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', function ($rootScope, $scope, $filter, $mdDialog, $translate, $window, $stateParams, Atividade, Post, Usuario, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario) {

        var vm = this;

        vm.idUsuario = $stateParams.idUsuario;

        vm.inicializa = function (tipo) {
            if (vm.idUsuario == undefined) {
                vm.idUsuario = $rootScope.usuarioLogado.id;
            }
            if (tipo == undefined) {
                tipo = 'seguidor';
            }

            if (tipo == 'seguidor') {
                Usuario.getSeguidores(vm.idUsuario)
                    .success(function (data) {
                        vm.seguidores = data;
                    })
                    .error(function (data, status) {});
            }
            if (tipo == 'seguindo') {
                Usuario.getSeguindo(vm.idUsuario)
                    .success(function (data) {
                        vm.seguindo = data;
                    })
                    .error(function (data, status) {});
            }
        };

        vm.querySearchSeguidores = function (query) {
            var results = query ? vm.seguidores.filter(vm.createFilterForSeguidores(query)) : vm.seguidores,
                deferred;
            return results;
        };

        vm.createFilterForSeguidores = function (query) {
            var lowercaseQuery = angular.lowercase(query);

            return function filterFn(seguidor) {
                var lowercaseNome = angular.lowercase(seguidor.nome);
                return (lowercaseNome.indexOf(lowercaseQuery) >= 0);
            };

        };

        vm.querySearchSeguindo = function (query) {
            var results = query ? vm.seguindo.filter(vm.createFilterForSeguindo(query)) : vm.seguindo,
                deferred;
            return results;
        };

        vm.createFilterForSeguindo = function (query) {
            var lowercaseQuery = angular.lowercase(query);

            return function filterFn(seguindo) {
                var lowercaseNome = angular.lowercase(seguindo.nome);
                return (lowercaseNome.indexOf(lowercaseQuery) >= 0);
            };

        };

    }]);
}());

/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('EquipeController', ['$scope', '$rootScope', '$mdDialog', '$translate', '$location', '$q', '$mdSidenav', '$stateParams', '$filter', '$interval', 'toastr', 'localStorageService', 'Usuario', 'Equipe',
        function ($scope, $rootScope, $mdDialog, $translate, $location, $q, $mdSidenav, $stateParams, $filter, $interval, toastr, localStorageService, Usuario, Equipe) {
            var vm = this;

            vm.getEquipesUsuario = function (idUsuario) {
                Usuario.getEquipes(idUsuario)
                    .success(function (data) {
                        vm.equipesDoUsuario = data;
                    })
                    .error(function (error) {})
            }

            vm.create = function (ev) {
                vm.equipeNovo = {};
                $mdDialog
                    .show({
                        locals: {
                            tituloModal: 'messages.equipe_add',
                            novoItem: true,
                            equipe: vm.equipeNovo
                        },
                        controller: DialogController,
                        templateUrl: 'app/components/equipe/formModal.html',
                        parent: angular.element(document.body),
                        targetEvent: ev,
                        clickOutsideToClose: true,
                        fullscreen: true // Only for -xs, -sm breakpoints.
                    })
                    .then(function () {

                    }, function () {

                    });
            };

            vm.save = function (equipe, arquivo) {
                $rootScope.loading = true;
                Equipe.save(equipe, arquivo)
                    .success(function (data) {
                        vm.getEquipesUsuario();
                        $rootScope.loading = false;
                    }).error(function (data, status) {
                        vm.messages = data.errors;
                        vm.status = status;
                        $rootScope.loading = false;
                    });
            };

            function DialogController($scope, $mdDialog, tituloModal, novoItem, equipe) {
                $scope.tituloModal = tituloModal;
                $scope.novoItem = novoItem;
                $scope.equipe = equipe;

                $scope.cancel = function () {
                    $mdDialog.cancel();
                };

                $scope.save = function () {
                    vm.save($scope.equipe, $scope.files[0]);
                    $mdDialog.hide();
                };

                $scope.update = function () {
                    vm.update($scope.equipe, $scope.files[0]);
                    $mdDialog.hide();
                };

                $scope.$watch('files.length', function (newVal, oldVal) {});
            }
    }]);

}());

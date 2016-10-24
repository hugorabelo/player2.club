/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('CampeonatoTipoController', ['$scope', '$rootScope', '$mdDialog', '$translate', 'CampeonatoTipo', 'ModeloCampeonato', function ($scope, $rootScope, $mdDialog, $translate, CampeonatoTipo, ModeloCampeonato) {
        var vm = this;

        $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no']).then(function (translations) {
            vm.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
            vm.textoYes = translations['messages.yes'];
            vm.textoNo = translations['messages.no'];
        });

        function DialogController($scope, $mdDialog, tituloModal, novoItem, campeonatoTipo, modelosCampeonato) {
            $scope.tituloModal = tituloModal;
            $scope.novoItem = novoItem;
            $scope.campeonatoTipo = campeonatoTipo;
            $scope.modelosCampeonato = modelosCampeonato;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.save = function () {
                vm.save($scope.campeonatoTipo);
                $mdDialog.hide();
            };

            $scope.update = function () {
                vm.update($scope.campeonatoTipo);
                $mdDialog.hide();
            };

        }

        $rootScope.loading = true;

        CampeonatoTipo.get()
            .success(function (data) {
                vm.campeonatoTipos = data;
                $rootScope.loading = false;
            });

        ModeloCampeonato.get()
            .success(function (data) {
                vm.modelosCampeonato = data;
                $rootScope.loading = false;
            });

        vm.create = function (ev) {
            $mdDialog
                .show({
                    locals: {
                        tituloModal: 'messages.campeonatoTipo_create',
                        novoItem: true,
                        campeonatoTipo: {},
                        modelosCampeonato: vm.modelosCampeonato
                    },
                    controller: DialogController,
                    templateUrl: 'app/components/campeonatoTipo/formModal.html',
                    parent: angular.element(document.body),
                    targetEvent: ev,
                    clickOutsideToClose: true,
                    fullscreen: true // Only for -xs, -sm breakpoints.
                })
                .then(function () {

                }, function () {

                });
        };

        vm.edit = function (ev, id) {
            CampeonatoTipo.edit(id)
                .success(function (data) {
                    $mdDialog
                        .show({
                            locals: {
                                tituloModal: 'messages.campeonatoTipo_edit',
                                novoItem: false,
                                campeonatoTipo: data,
                                modelosCampeonato: vm.modelosCampeonato
                            },
                            controller: DialogController,
                            templateUrl: 'app/components/campeonatoTipo/formModal.html',
                            parent: angular.element(document.body),
                            targetEvent: ev,
                            clickOutsideToClose: true,
                            fullscreen: true // Only for -xs, -sm breakpoints.
                        })
                        .then(function () {

                        }, function () {

                        });

                });
        };

        vm.save = function (campeonatoTipo) {
            $rootScope.loading = true;
            CampeonatoTipo.save(campeonatoTipo)
                .success(function (data) {
                    CampeonatoTipo.get()
                        .success(function (getData) {
                            vm.campeonatoTipos = getData;
                            $rootScope.loading = false;
                        }).error(function (getData) {
                            vm.message = getData;
                            $rootScope.loading = false;
                        });
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    vm.messages = data.errors;
                    vm.status = status;
                    $rootScope.loading = false;
                });
        };

        vm.update = function (campeonatoTipo) {
            $rootScope.loading = true;
            CampeonatoTipo.update(campeonatoTipo)
                .success(function (data) {
                    CampeonatoTipo.get()
                        .success(function (getData) {
                            vm.campeonatoTipos = getData;
                            $rootScope.loading = false;
                        });
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    vm.message = data.errors;
                    vm.status = status;
                    $rootScope.loading = false;
                });
        };

        vm.destroy = function (ev, id) {
            vm.idRegistroExcluir = id;
            var confirm = $mdDialog.confirm(id)
                .title(vm.textoConfirmaExclusao)
                .ariaLabel(vm.textoConfirmaExclusao)
                .targetEvent(ev)
                .ok(vm.textoYes)
                .cancel(vm.textoNo)
                .theme('player2');

            $mdDialog.show(confirm).then(function () {
                $rootScope.loading = true;
                CampeonatoTipo.destroy(vm.idRegistroExcluir)
                    .success(function (data) {
                        CampeonatoTipo.get()
                            .success(function (data) {
                                vm.campeonatoTipos = data;
                                $rootScope.loading = false;
                            });
                        $rootScope.loading = false;
                    });
            }, function () {

            });
        };

    }]);
}());

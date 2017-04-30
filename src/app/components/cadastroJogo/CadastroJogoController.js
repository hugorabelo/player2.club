/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('CadastroJogoController', ['$scope', '$rootScope', '$mdDialog', '$translate', 'Jogo', 'ModeloCampeonato', 'Plataforma', function ($scope, $rootScope, $mdDialog, $translate, Jogo, ModeloCampeonato, Plataforma) {
        var vm = this;

        $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no']).then(function (translations) {
            vm.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
            vm.textoYes = translations['messages.yes'];
            vm.textoNo = translations['messages.no'];
        });

        function DialogController($scope, $mdDialog, tituloModal, novoItem, jogo, modelosCampeonato, plataformasDisponiveis) {
            $scope.tituloModal = tituloModal;
            $scope.novoItem = novoItem;
            $scope.jogo = jogo;
            $scope.modelosCampeonato = modelosCampeonato;
            $scope.plataformasDisponiveis = plataformasDisponiveis;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.save = function () {
                vm.save($scope.jogo, $scope.files[0]);
                $mdDialog.hide();
            };

            $scope.update = function () {
                vm.update($scope.jogo, $scope.files[0]);
                $mdDialog.hide();
            };

            $scope.exists = function (item, list) {
                return list.indexOf(item) > -1;
            };

            $scope.toggle = function (item, list) {
                var idx = list.indexOf(item);
                if (idx > -1) {
                    list.splice(idx, 1);
                } else {
                    list.push(item);
                }
            };

            $scope.$watch('files.length', function (newVal, oldVal) {});
        }

        $rootScope.loading = true;

        Jogo.get()
            .success(function (data) {
                vm.jogos = data;
                $rootScope.loading = false;
            }).error(function (data) {
                vm.message = data;
                $rootScope.loading = false;
            });

        ModeloCampeonato.get()
            .success(function (data) {
                vm.modelosCampeonato = data;
                $rootScope.loading = false;
            });

        Plataforma.get()
            .success(function (data) {
                vm.plataformasDisponiveis = data;
            });

        vm.create = function (ev) {
            vm.jogoNovo = {};
            vm.jogoNovo.plataformasDoJogo = [];
            $mdDialog
                .show({
                    locals: {
                        tituloModal: 'messages.jogo_create',
                        novoItem: true,
                        jogo: vm.jogoNovo,
                        modelosCampeonato: vm.modelosCampeonato,
                        plataformasDisponiveis: vm.plataformasDisponiveis
                    },
                    controller: DialogController,
                    templateUrl: 'app/components/cadastroJogo/formModal.html',
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
            Jogo.edit(id)
                .success(function (data) {
                    vm.jogoEdita = data;
                    Jogo.getPlataformas(vm.jogoEdita.id)
                        .success(function (data) {
                            vm.jogoEdita.plataformasDoJogo = [];
                            angular.forEach(data, function (plataforma) {
                                this.push(plataforma.id);
                            }, vm.jogoEdita.plataformasDoJogo);
                            $mdDialog
                                .show({
                                    locals: {
                                        tituloModal: 'messages.jogo_edit',
                                        novoItem: false,
                                        jogo: vm.jogoEdita,
                                        modelosCampeonato: vm.modelosCampeonato,
                                        plataformasDisponiveis: vm.plataformasDisponiveis
                                    },
                                    controller: DialogController,
                                    templateUrl: 'app/components/cadastroJogo/formModal.html',
                                    parent: angular.element(document.body),
                                    targetEvent: ev,
                                    clickOutsideToClose: true,
                                    fullscreen: true // Only for -xs, -sm breakpoints.
                                })
                                .then(function () {

                                }, function () {

                                });
                        })

                });
        };

        vm.save = function (jogo, arquivo) {
            $rootScope.loading = true;
            Jogo.save(jogo, arquivo)
                .success(function (data) {
                    Jogo.get()
                        .success(function (getData) {
                            vm.jogos = getData;
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

        vm.update = function (jogo, arquivo) {
            $rootScope.loading = true;
            Jogo.update(jogo, arquivo)
                .success(function (data) {
                    Jogo.get()
                        .success(function (getData) {
                            vm.jogos = getData;
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
                Jogo.destroy(vm.idRegistroExcluir)
                    .success(function (data) {
                        Jogo.get()
                            .success(function (data) {
                                vm.jogos = data;
                                $rootScope.loading = false;
                            });
                        $rootScope.loading = false;
                    });
            }, function () {

            });
        };

    }]);
}());

angular.module('player2').controller('ModeloCampeonatoController', ['$scope', '$rootScope', '$mdDialog', '$translate', 'ModeloCampeonato', function ($scope, $rootScope, $mdDialog, $translate, ModeloCampeonato) {
    var vm = this;

    $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no']).then(function (translations) {
        vm.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
        vm.textoYes = translations['messages.yes'];
        vm.textoNo = translations['messages.no'];
    });

    $rootScope.loading = true;

    ModeloCampeonato.get()
        .success(function (data) {
            vm.modelos = data;
            $rootScope.loading = false;
        }).error(function (data) {
            vm.message = data;
            $rootScope.loading = false;
        });

    vm.create = function (ev) {
        $mdDialog.show({
                locals: {
                    tituloModal: 'messages.modelo_create',
                    novoItem: true,
                    modelo: {}
                },
                controller: DialogController,
                templateUrl: 'app/components/cadastroModeloCampeonato/formModal.html',
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
        ModeloCampeonato.edit(id)
            .success(function (data) {
                $mdDialog.show({
                        locals: {
                            tituloModal: 'messages.modelo_edit',
                            novoItem: false,
                            modelo: data
                        },
                        controller: DialogController,
                        templateUrl: 'app/components/cadastroModeloCampeonato/formModal.html',
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

    vm.save = function (modelo) {
        $rootScope.loading = true;
        ModeloCampeonato.save(modelo)
            .success(function (data) {
                ModeloCampeonato.get()
                    .success(function (getData) {
                        vm.modelos = getData;
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

    vm.update = function (modelo) {
        $rootScope.loading = true;
        ModeloCampeonato.update(modelo)
            .success(function (data) {
                ModeloCampeonato.get()
                    .success(function (getData) {
                        vm.modelos = getData;
                        $rootScope.loading = false;
                    });
                $rootScope.loading = false;
            }).error(function (data, status) {
                console.log(data);
                vm.message = data.errors;
                vm.status = status;
                $rootScope.loading = false;
            });
    };

    vm.delete = function (ev, id) {
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
            ModeloCampeonato.destroy(vm.idRegistroExcluir)
                .success(function (data) {
                    ModeloCampeonato.get()
                        .success(function (data) {
                            vm.modelos = data;
                            $rootScope.loading = false;
                        });
                    $rootScope.loading = false;
                });
        }, function () {

        });
    };

    function DialogController($scope, $mdDialog, tituloModal, novoItem, modelo) {
        $scope.tituloModal = tituloModal;
        $scope.novoItem = novoItem;
        $scope.modelo = modelo;

        $scope.cancel = function () {
            $mdDialog.cancel();
        };

        $scope.save = function () {
            vm.save($scope.modelo);
            $mdDialog.hide();
        }

        $scope.update = function () {
            vm.update($scope.modelo);
            $mdDialog.hide();
        }

        $scope.$watch('files.length', function (newVal, oldVal) {});
    }
}]);

angular.module('player2').controller('PlataformaController', ['$scope', '$rootScope', '$mdDialog', '$translate', 'Plataforma', function ($scope, $rootScope, $mdDialog, $translate, Plataforma) {
    var vm = this;

    $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no']).then(function (translations) {
        vm.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
        vm.textoYes = translations['messages.yes'];
        vm.textoNo = translations['messages.no'];
    });

    $rootScope.loading = true;

    Plataforma.get()
        .success(function (data) {
            vm.plataformas = data;
            $rootScope.loading = false;
        }).error(function (data) {
            vm.message = data;
            $rootScope.loading = false;
        });

    vm.create = function (ev) {
        $mdDialog.show({
                locals: {
                    tituloModal: 'messages.plataforma_create',
                    novoItem: true,
                    plataforma: {}
                },
                controller: DialogController,
                templateUrl: 'app/components/plataforma/formModal.html',
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
        Plataforma.edit(id)
            .success(function (data) {
                $mdDialog.show({
                        locals: {
                            tituloModal: 'messages.plataforma_edit',
                            novoItem: false,
                            plataforma: data
                        },
                        controller: DialogController,
                        templateUrl: 'app/components/plataforma/formModal.html',
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

    vm.save = function (plataforma, arquivo) {
        $rootScope.loading = true;
        Plataforma.save(plataforma, arquivo)
            .success(function (data) {
                Plataforma.get()
                    .success(function (getData) {
                        vm.plataformas = getData;
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

    vm.update = function (plataforma, arquivo) {
        $rootScope.loading = true;
        Plataforma.update(plataforma, arquivo)
            .success(function (data) {
                Plataforma.get()
                    .success(function (getData) {
                        vm.plataformas = getData;
                        $rootScope.loading = false;
                    });
                $rootScope.loading = false;
            }).error(function (data, status) {
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
            .theme('default');

        $mdDialog.show(confirm).then(function () {
            $rootScope.loading = true;
            Plataforma.destroy(vm.idRegistroExcluir)
                .success(function (data) {
                    Plataforma.get()
                        .success(function (data) {
                            vm.plataformas = data;
                            $rootScope.loading = false;
                        });
                    $rootScope.loading = false;
                });
        }, function () {

        });
    };

    function DialogController($scope, $mdDialog, tituloModal, novoItem, plataforma) {
        $scope.tituloModal = tituloModal;
        $scope.novoItem = novoItem;
        $scope.plataforma = plataforma;

        $scope.cancel = function () {
            $mdDialog.cancel();
        };

        $scope.save = function () {
            vm.save($scope.plataforma, $scope.files[0]);
            $mdDialog.hide();
        }

        $scope.update = function () {
            vm.update($scope.plataforma, $scope.files[0]);
            $mdDialog.hide();
        }

        $scope.$watch('files.length', function (newVal, oldVal) {});
    }
}]);

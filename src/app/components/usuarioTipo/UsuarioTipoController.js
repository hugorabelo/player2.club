angular.module('player2').controller('UsuarioTipoController', ['$scope', '$rootScope', '$mdDialog', '$translate', 'UsuarioTipo', function ($scope, $rootScope, $mdDialog, $translate, UsuarioTipo) {

    var vm = this;

    $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no']).then(function (translations) {
        $scope.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
        $scope.textoYes = translations['messages.yes'];
        $scope.textoNo = translations['messages.no'];
    });

    $rootScope.loading = true;

    UsuarioTipo.get()
        .success(function (data) {
            vm.usuarioTipos = data;
            $rootScope.loading = false;
        });

    vm.create = function (ev) {
        $mdDialog.show({
                locals: {
                    tituloModal: 'messages.usuarioTipo_create',
                    novoItem: true,
                    usuarioTipo: {}
                },
                controller: DialogController,
                templateUrl: 'app/components/usuarioTipo/formModal.html',
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
        UsuarioTipo.edit(id)
            .success(function (data) {
                $mdDialog.show({
                        locals: {
                            tituloModal: 'messages.usuarioTipo_edit',
                            novoItem: false,
                            usuarioTipo: data
                        },
                        controller: DialogController,
                        templateUrl: 'app/components/usuarioTipo/formModal.html',
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

    vm.save = function (usuarioTipo) {
        $rootScope.loading = true;
        UsuarioTipo.save(usuarioTipo)
            .success(function (data) {
                UsuarioTipo.get()
                    .success(function (getData) {
                        vm.usuarioTipos = getData;
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

    vm.update = function (usuarioTipo) {
        $rootScope.loading = true;
        UsuarioTipo.update(usuarioTipo)
            .success(function (data) {
                UsuarioTipo.get()
                    .success(function (getData) {
                        vm.usuarioTipos = getData;
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
            .title($scope.textoConfirmaExclusao)
            .ariaLabel($scope.textoConfirmaExclusao)
            .targetEvent(ev)
            .ok($scope.textoYes)
            .cancel($scope.textoNo)
            .theme('player2');

        $mdDialog.show(confirm).then(function () {
            $rootScope.loading = true;
            UsuarioTipo.destroy(vm.idRegistroExcluir)
                .success(function (data) {
                    UsuarioTipo.get()
                        .success(function (data) {
                            vm.usuarioTipos = data;
                            $rootScope.loading = false;
                        });
                    $rootScope.loading = false;
                });
        }, function () {

        });
    };

    function DialogController($scope, $mdDialog, tituloModal, novoItem, usuarioTipo) {
        $scope.tituloModal = tituloModal;
        $scope.novoItem = novoItem;
        $scope.usuarioTipo = usuarioTipo;

        $scope.cancel = function () {
            $mdDialog.cancel();
        };

        $scope.save = function () {
            vm.save($scope.usuarioTipo);
            $mdDialog.hide();
        }

        $scope.update = function () {
            vm.update($scope.usuarioTipo);
            $mdDialog.hide();
        }

    }

}]);

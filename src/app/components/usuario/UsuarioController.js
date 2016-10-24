angular.module('player2').controller('UsuarioController', ['$scope', '$rootScope', '$mdDialog', '$translate', 'Usuario', 'UsuarioTipo', function ($scope, $rootScope, $mdDialog, $translate, Usuario, UsuarioTipo) {

    var vm = this;

    $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no']).then(function (translations) {
        $scope.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
        $scope.textoYes = translations['messages.yes'];
        $scope.textoNo = translations['messages.no'];
    });

    $rootScope.loading = true;

    Usuario.get()
        .success(function (data) {
            vm.usuarios = data;
            $rootScope.loading = false;
        });

    UsuarioTipo.get()
        .success(function (data) {
            vm.usuarioTipos = data;
            $rootScope.loading = false;
        });

    vm.create = function (ev) {
        $mdDialog.show({
                locals: {
                    tituloModal: 'messages.usuario_create',
                    novoItem: true,
                    usuario: {},
                    usuarioTipos: vm.usuarioTipos
                },
                controller: DialogController,
                templateUrl: 'app/components/usuario/formModal.html',
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
        Usuario.edit(id)
            .success(function (data) {
                $mdDialog.show({
                        locals: {
                            tituloModal: 'messages.usuario_edit',
                            novoItem: false,
                            usuario: data,
                            usuarioTipos: vm.usuarioTipos
                        },
                        controller: DialogController,
                        templateUrl: 'app/components/usuario/formModal.html',
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

    vm.save = function (usuario) {
        $rootScope.loading = true;
        Usuario.save(usuario)
            .success(function (data) {
                Usuario.get()
                    .success(function (getData) {
                        vm.usuarios = getData;
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

    vm.update = function (usuario) {
        $rootScope.loading = true;
        Usuario.update(usuario)
            .success(function (data) {
                Usuario.get()
                    .success(function (getData) {
                        vm.usuarios = getData;
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
            Usuario.destroy(vm.idRegistroExcluir)
                .success(function (data) {
                    Usuario.get()
                        .success(function (data) {
                            vm.usuarios = data;
                            $rootScope.loading = false;
                        });
                    $rootScope.loading = false;
                });
        }, function () {

        });
    };

    function DialogController($scope, $mdDialog, tituloModal, novoItem, usuario, usuarioTipos) {
        $scope.tituloModal = tituloModal;
        $scope.novoItem = novoItem;
        $scope.usuario = usuario;
        $scope.usuarioTipos = usuarioTipos;

        $scope.cancel = function () {
            $mdDialog.cancel();
        };

        $scope.save = function () {
            vm.save($scope.usuario);
            $mdDialog.hide();
        }

        $scope.update = function () {
            vm.update($scope.usuario);
            $mdDialog.hide();
        }

    }

}]);

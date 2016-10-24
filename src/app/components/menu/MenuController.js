angular.module('player2').controller('MenuController', ['$scope', '$rootScope', '$mdDialog', '$translate', 'Menu', function ($scope, $rootScope, $mdDialog, $translate, Menu) {

    var vm = this;

    $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no']).then(function (translations) {
        $scope.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
        $scope.textoYes = translations['messages.yes'];
        $scope.textoNo = translations['messages.no'];
    });

    $rootScope.loading = true;

    Menu.get()
        .success(function (data) {
            vm.menus = data;
            $rootScope.loading = false;
        });

    vm.create = function (ev) {
        Menu.create()
            .success(function (data) {
                vm.menuPais = data.menuPais;
                $mdDialog.show({
                        locals: {
                            tituloModal: 'messages.menu_create',
                            novoItem: true,
                            menu: {},
                            menuPais: vm.menuPais
                        },
                        controller: DialogController,
                        templateUrl: 'app/components/menu/formModal.html',
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

    vm.edit = function (ev, id) {
        Menu.edit(id)
            .success(function (data) {
                $mdDialog.show({
                        locals: {
                            tituloModal: 'messages.menu_edit',
                            novoItem: false,
                            menu: data.menu,
                            menuPais: data.menuPais
                        },
                        controller: DialogController,
                        templateUrl: 'app/components/menu/formModal.html',
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

    vm.save = function (menu) {
        $rootScope.loading = true;
        Menu.save(menu)
            .success(function (data) {
                Menu.get()
                    .success(function (getData) {
                        vm.menus = getData;
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

    vm.update = function (menu) {
        $rootScope.loading = true;
        Menu.update(menu)
            .success(function (data) {
                Menu.get()
                    .success(function (getData) {
                        vm.menus = getData;
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
            Menu.destroy(vm.idRegistroExcluir)
                .success(function (data) {
                    Menu.get()
                        .success(function (data) {
                            vm.menus = data;
                            $rootScope.loading = false;
                        });
                    $rootScope.loading = false;
                });
        }, function () {

        });
    };

    function DialogController($scope, $mdDialog, tituloModal, novoItem, menu, menuPais) {
        $scope.tituloModal = tituloModal;
        $scope.novoItem = novoItem;
        $scope.menu = menu;
        $scope.menuPais = menuPais;

        $scope.cancel = function () {
            $mdDialog.cancel();
        };

        $scope.save = function () {
            vm.save($scope.menu);
            $mdDialog.hide();
        }

        $scope.update = function () {
            vm.update($scope.menu);
            $mdDialog.hide();
        }

    }

}]);

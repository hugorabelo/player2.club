angular.module('player2').controller('PlataformaController', ['$scope', '$rootScope', '$mdDialog', 'Plataforma', function ($scope, $rootScope, $mdDialog, Plataforma) {
    var vm = this;

    vm.plataforma = {};

    $rootScope.loading = true;

    Plataforma.get()
        .success(function (data) {
            vm.plataformas = data;
            $rootScope.loading = false;
        }).error(function (data) {
            vm.message = data;
            $rootScope.loading = false;
        });

    vm.create = function () {
        vm.plataforma = {};
        vm.messages = null;
        $('#formModal').modal();
        vm.tituloModal = 'messages.plataforma_create';
        vm.novoItem = true;
        vm.formulario.$setPristine();
    };

    vm.formCadastro = function (ev) {
        $mdDialog.show({
                locals: {
                    tituloModal: 'messages.plataforma_create',
                    novoItem: true
                },
                controller: DialogController,
                templateUrl: 'app/components/plataforma/formModal.html',
                parent: angular.element(document.body),
                targetEvent: ev,
                clickOutsideToClose: true,
                fullscreen: true // Only for -xs, -sm breakpoints.
            })
            .then(function (answer) {
                vm.status = 'You said the information was "' + answer + '".';
                console.log(vm.status);
            }, function () {
                vm.status = 'You cancelled the dialog.';
                console.log(vm.status);
            });
    };

    vm.edit = function (id) {
        $rootScope.loading = true;
        Plataforma.edit(id)
            .success(function (data) {
                vm.plataforma = data;
                vm.messages = null;
                $('#formModal').modal();
                vm.tituloModal = 'messages.plataforma_edit';
                vm.novoItem = false;
                vm.formulario.$setPristine();
                $rootScope.loading = false;
            });
    };

    vm.submit = function () {
        if (vm.novoItem) {
            this.save();
        } else {
            this.update();
        }
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

    vm.update = function () {
        console.log('update');
        //        $rootScope.loading = true;
        //        Plataforma.update(vm.plataforma, vm.files[0])
        //            .success(function (data) {
        //                Plataforma.get()
        //                    .success(function (getData) {
        //                        vm.plataformas = getData;
        //                        $rootScope.loading = false;
        //                    });
        //                $('#formModal').modal('hide');
        //                vm.files = [];
        //                $rootScope.loading = false;
        //            }).error(function (data, status) {
        //                vm.message = data.errors;
        //                vm.status = status;
        //                $rootScope.loading = false;
        //            });
    };

    vm.delete = function (id) {
        $('#confirmaModal').modal();
        vm.mensagemModal = 'messages.confirma_exclusao';
        vm.idRegistro = id;
    };

    vm.confirmacaoModal = function (id) {
        $rootScope.loading = true;
        Plataforma.destroy(id)
            .success(function (data) {
                Plataforma.get()
                    .success(function (data) {
                        vm.plataformas = data;
                        $rootScope.loading = false;
                    });
                $('#confirmaModal').modal('hide');
                $rootScope.loading = false;
            });
    };

    function DialogController($scope, $mdDialog, tituloModal, novoItem) {
        $scope.tituloModal = tituloModal;
        $scope.novoItem = novoItem;

        $scope.cancel = function () {
            $mdDialog.cancel();
        };

        $scope.save = function () {
            vm.save();
            $mdDialog.hide();
        }

        $scope.update = function () {
            vm.update();
            $mdDialog.hide();
        }

        $scope.submit = function () {
            if (novoItem) {
                vm.save($scope.plataforma, $scope.files[0]);
            } else {
                //                vm.update();
                console.log('update');
            }
            $mdDialog.hide();
        }

        $scope.$watch('files.length', function (newVal, oldVal) {
            console.log($scope.files);
        });
    }
}]);

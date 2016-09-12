angular.module('player2').controller('PlataformaController', ['$scope', '$rootScope', '$mdDialog', 'Plataforma', function ($scope, $rootScope, $mdDialog, Plataforma) {
    $scope.plataforma = {};

    $scope.files = [];

    $scope.$on("fileSelected", function (event, args) {
        $scope.$apply(function () {
            //add the file object to the scope's files collection
            $scope.files.push(args.file);
        });
    });

    $rootScope.loading = true;

    Plataforma.get()
        .success(function (data) {
            $scope.plataformas = data;
            $rootScope.loading = false;
        }).error(function (data) {
            $scope.message = data;
            $rootScope.loading = false;
        });

    $scope.create = function () {
        $scope.plataforma = {};
        $scope.messages = null;
        $('#formModal').modal();
        $scope.tituloModal = 'messages.plataforma_create';
        $scope.novoItem = true;
        $scope.formulario.$setPristine();
    };

    $scope.showAdvanced = function (ev) {
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
                $scope.status = 'You said the information was "' + answer + '".';
                console.log($scope.status);
            }, function () {
                $scope.status = 'You cancelled the dialog.';
                console.log($scope.status);
            });
    };

    $scope.edit = function (id) {
        $rootScope.loading = true;
        Plataforma.edit(id)
            .success(function (data) {
                $scope.plataforma = data;
                $scope.messages = null;
                $('#formModal').modal();
                $scope.tituloModal = 'messages.plataforma_edit';
                $scope.novoItem = false;
                $scope.formulario.$setPristine();
                $rootScope.loading = false;
            });
    };

    $scope.submit = function () {
        if ($scope.novoItem) {
            this.save();
        } else {
            this.update();
        }
    };

    $scope.save = function () {
        console.log('save');
        //        $rootScope.loading = true;
        //        Plataforma.save($scope.plataforma, $scope.files[0])
        //            .success(function (data) {
        //                Plataforma.get()
        //                    .success(function (getData) {
        //                        $scope.plataformas = getData;
        //                        $rootScope.loading = false;
        //                    }).error(function (getData) {
        //                        $scope.message = getData;
        //                        $rootScope.loading = false;
        //                    });
        //                $('#formModal').modal('hide');
        //                $scope.files = [];
        //                $rootScope.loading = false;
        //            }).error(function (data, status) {
        //                $scope.messages = data.errors;
        //                $scope.status = status;
        //                $rootScope.loading = false;
        //            });
    };

    $scope.update = function () {
        console.log('update');
        //        $rootScope.loading = true;
        //        Plataforma.update($scope.plataforma, $scope.files[0])
        //            .success(function (data) {
        //                Plataforma.get()
        //                    .success(function (getData) {
        //                        $scope.plataformas = getData;
        //                        $rootScope.loading = false;
        //                    });
        //                $('#formModal').modal('hide');
        //                $scope.files = [];
        //                $rootScope.loading = false;
        //            }).error(function (data, status) {
        //                $scope.message = data.errors;
        //                $scope.status = status;
        //                $rootScope.loading = false;
        //            });
    };

    $scope.delete = function (id) {
        $('#confirmaModal').modal();
        $scope.mensagemModal = 'messages.confirma_exclusao';
        $scope.idRegistro = id;
    };

    $scope.confirmacaoModal = function (id) {
        $rootScope.loading = true;
        Plataforma.destroy(id)
            .success(function (data) {
                Plataforma.get()
                    .success(function (data) {
                        $scope.plataformas = data;
                        $rootScope.loading = false;
                    });
                $('#confirmaModal').modal('hide');
                $rootScope.loading = false;
            });
    };

    function DialogController($scope, $mdDialog, tituloModal, novoItem) {
        $scope.tituloModal = tituloModal;
        $scope.novoItem = novoItem;
        $scope.hide = function () {
            $mdDialog.hide();
        };

        $scope.cancel = function () {
            $mdDialog.cancel();
        };

        $scope.answer = function (answer) {
            $mdDialog.hide(answer);
        };
    }
}]);

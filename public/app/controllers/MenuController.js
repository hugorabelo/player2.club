AplicacaoLiga.controller('MenuController', ['$scope', '$rootScope', 'Menu', function ($scope, $rootScope, Menu) {

    $scope.menu = {};

    $rootScope.loading = true;

    Menu.get()
    .success(function(data) {
        $scope.menus = data;
        $rootScope.loading = false;
    });

    $scope.create = function() {
        $rootScope.loading = true;
        Menu.create()
        .success(function (data) {
            $scope.menu = {};
            $scope.menuPais = data.menuPais;
            $scope.messages = null;
            $('#formModal').modal();
            $scope.tituloModal = 'messages.menu_create';
            $scope.novoItem = true;
            $scope.formulario.$setPristine();
            $rootScope.loading = false;
        });
    }

    $scope.edit = function(id) {
        $rootScope.loading = true;
        Menu.edit(id)
            .success(function(data) {
                $scope.menu = data.menu;
                $scope.menuPais = data.menuPais;
                $scope.messages = null;
                $('#formModal').modal();
                $scope.tituloModal = 'messages.menu_edit';
                $scope.novoItem = false;
                $scope.formulario.$setPristine();
                $rootScope.loading = false;
        });
    };

    $scope.submit = function() {
        if($scope.novoItem) {
            this.save();
        } else {
            this.update();
        }
    };

    $scope.save = function() {
        Menu.save($scope.menu)
                .success(function (data) {
                    Menu.get()
                        .success(function (getData) {
                            $scope.menus = getData;
                            $rootScope.loading = false;
                    });
                    $('#formModal').modal('hide');
                    $rootScope.loading = false;
                }).error(function(data, status) {
                    $scope.messages = data.errors;
                    $scope.status = status;
                });
    };

    $scope.update = function() {
        $rootScope.loading = true;
        Menu.update($scope.menu)
                .success(function (data) {
                    Menu.get()
                        .success(function (getData) {
                            $scope.menus = getData;
                            $rootScope.loading = false;
                    });
                    $('#formModal').modal('hide');
                    $rootScope.loading = false;
                }).error(function(data, status) {
                    $scope.message = data.errors;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
    };

    $scope.delete = function(id) {
        $('#confirmaModal').modal();
        $scope.mensagemModal = 'messages.confirma_exclusao';
        $scope.idRegistro = id;
    };

    $scope.confirmacaoModal = function(id) {
        $rootScope.loading = true;
        Menu.destroy(id)
            .success(function(data) {
                Menu.get()
                    .success(function(data) {
                        $scope.menus = data;
                        $rootScope.loading = false;
                });
                $('#confirmaModal').modal('hide');
                $rootScope.loading = false;
        });
    };

}]);

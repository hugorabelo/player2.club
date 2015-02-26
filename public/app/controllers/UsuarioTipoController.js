AplicacaoLiga.controller('UsuarioTipoController', ['$scope', '$rootScope', 'UsuarioTipo', function ($scope, $rootScope, UsuarioTipo) {

    $scope.usuarioTipo = {};

    $rootScope.loading = true;

    UsuarioTipo.get()
    .success(function(data) {
        $scope.usuarioTipos = data;
        $rootScope.loading = false;
    });

    $scope.create = function() {
        $scope.usuarioTipo = {};
        $scope.messages = null;
        $('#formModal').modal();
        $scope.tituloModal = 'messages.usuarioTipo_create';
        $scope.novoItem = true;
        $scope.formulario.$setPristine();
    }

    $scope.edit = function(id) {
        $rootScope.loading = true;
        UsuarioTipo.edit(id)
            .success(function(data) {
                $scope.usuarioTipo = data;
                $scope.messages = null;
                $('#formModal').modal();
                $scope.tituloModal = 'messages.usuarioTipo_edit';
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
        $rootScope.loading = true;
        UsuarioTipo.save($scope.usuarioTipo)
                .success(function (data) {
                    UsuarioTipo.get()
                        .success(function (getData) {
                            $scope.usuarioTipos = getData;
                            $rootScope.loading = false;
                    });
                    $('#formModal').modal('hide');
                    $rootScope.loading = false;
                }).error(function(data, status) {
                    $scope.messages = data.errors;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
    };

    $scope.update = function() {
        $rootScope.loading = true;
        UsuarioTipo.update($scope.usuarioTipo)
                .success(function (data) {
                    UsuarioTipo.get()
                        .success(function (getData) {
                            $scope.usuarioTipos = getData;
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
        UsuarioTipo.destroy(id)
            .success(function(data) {
                UsuarioTipo.get()
                    .success(function(data) {
                        $scope.usuarioTipos = data;
                        $rootScope.loading = false;
                });
                $('#confirmaModal').modal('hide');
                $rootScope.loading = false;
        });
    };

}]);

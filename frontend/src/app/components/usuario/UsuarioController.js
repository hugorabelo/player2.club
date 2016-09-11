angular.module('player2').controller('UsuarioController', ['$scope', '$rootScope', 'Usuario', 'UsuarioTipo', function ($scope, $rootScope, Usuario, UsuarioTipo) {

    $scope.usuario = {};

    $rootScope.loading = true;

    Usuario.get()
    .success(function(data) {
        $scope.usuarios = data;
        $rootScope.loading = false;
    });

    $scope.create = function() {
        $rootScope.loading = true;
        UsuarioTipo.get()
            .success(function(data) {
                $scope.usuario = {};
                $scope.usuarioTipos = data;
                $scope.messages = null;
                $('#formModal').modal();
                $scope.tituloModal = 'messages.usuario_create';
                $scope.novoItem = true;
                $scope.formulario.$setPristine();
                $rootScope.loading = false;
        });
    }

    $scope.edit = function(id) {
        $rootScope.loading = true;
        Usuario.edit(id)
            .success(function(data) {
                $scope.usuario = data.usuario;
                $scope.usuarioTipos = data.usuarioTipos;
                $scope.messages = null;
                $('#formModal').modal();
                $scope.tituloModal = 'messages.usuario_edit';
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
        Usuario.save($scope.usuario)
                .success(function (data) {
                    Usuario.get()
                        .success(function (getData) {
                            $scope.usuarios = getData;
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
        Usuario.update($scope.usuario)
                .success(function (data) {
                    Usuario.get()
                        .success(function (getData) {
                            $scope.usuarios = getData;
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
        Usuario.destroy(id)
            .success(function(data) {
                Usuario.get()
                    .success(function(data) {
                        $scope.usuarios = data;
                        $rootScope.loading = false;
                });
                $('#confirmaModal').modal('hide');
                $rootScope.loading = false;
        });
    };

}]);

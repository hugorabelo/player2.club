angular.module('player2').controller('PermissaoController', ['$scope', '$rootScope', 'Permissao', 'UsuarioTipo', 'Menu', function ($scope, $rootScope, Permissao, UsuarioTipo, Menu) {

    $scope.permissao = {};

    $rootScope.loading = true;

    UsuarioTipo.get()
    .success(function(data) {
        $scope.usuarioTipos = data;
        Menu.getTree()
        .success(function(dataMenu) {
            $scope.menus = dataMenu;
            $rootScope.loading = false;
        });
        $rootScope.loading = false;
    });

    $scope.getPermissoes = function() {
        Permissao.get($scope.permissao.usuario_tipos_id)
        .success(function(data) {
            $scope.permissoes = [];
            $scope.permissao.lista = {};
            angular.forEach(data, function(item) {
                $scope.permissoes.push(item.menu_id);
                $scope.permissao.lista[item.menu_id] = true;
            });
            $rootScope.loading = false;
        });
    };

    $scope.save = function() {
        Permissao.save($scope.permissao)
                .success(function (data) {
                    Permissao.get($scope.permissao.usuario_tipos_id)
                        .success(function (getData) {
                            $scope.permissoes = [];
                            $scope.permissao.lista = {};
                            angular.forEach(getData, function(item) {
                                $scope.permissoes.push(item.menu_id);
                                $scope.permissao.lista[item.menu_id] = true;
                            });
                            $rootScope.loading = false;
                    });
                    $rootScope.loading = false;
                }).error(function(data, status) {
                    $scope.messages = data.errors;
                    $scope.status = status;
                });
    };

    $scope.cancel = function() {
        $scope.getPermissoes();
    };

}]);

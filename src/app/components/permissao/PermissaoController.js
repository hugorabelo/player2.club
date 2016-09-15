angular.module('player2').controller('PermissaoController', ['$scope', '$rootScope', 'Permissao', 'UsuarioTipo', 'Menu', function ($scope, $rootScope, Permissao, UsuarioTipo, Menu) {

    var vm = this;

    vm.permissao = {};

    $rootScope.loading = true;

    UsuarioTipo.get()
        .success(function (data) {
            vm.usuarioTipos = data;
            Menu.getTree()
                .success(function (dataMenu) {
                    vm.menus = dataMenu;
                    $rootScope.loading = false;
                });
            $rootScope.loading = false;
        });

    vm.getPermissoes = function () {
        Permissao.get(vm.permissao.usuario_tipos_id)
            .success(function (data) {
                vm.permissoes = [];
                vm.permissao.lista = {};
                angular.forEach(data, function (item) {
                    vm.permissoes.push(item.menu_id);
                    vm.permissao.lista[item.menu_id] = true;
                });
                console.log(vm.permissoes);
                console.log(vm.permissao.lista);
                $rootScope.loading = false;
            });
    };

    vm.save = function () {
        Permissao.save($scope.permissao)
            .success(function (data) {
                Permissao.get($scope.permissao.usuario_tipos_id)
                    .success(function (getData) {
                        vm.permissoes = [];
                        vm.permissao.lista = {};
                        angular.forEach(getData, function (item) {
                            vm.permissoes.push(item.menu_id);
                            vm.permissao.lista[item.menu_id] = true;
                        });
                        $rootScope.loading = false;
                    });
                $rootScope.loading = false;
            }).error(function (data, status) {
                vm.messages = data.errors;
                vm.status = status;
            });
    };

    vm.cancel = function () {
        vm.getPermissoes();
    };

}]);

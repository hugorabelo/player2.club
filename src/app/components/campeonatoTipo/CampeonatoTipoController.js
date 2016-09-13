angular.module('player2').controller('CampeonatoTipoController', ['$scope', '$rootScope', 'CampeonatoTipo', function ($scope, $rootScope, CampeonatoTipo) {

    $scope.campeonatoTipo = {};

    $rootScope.loading = true;

    CampeonatoTipo.get()
    .success(function(data) {
        $scope.campeonatoTipos = data;
        $rootScope.loading = false;
    });

    $scope.create = function() {
        $scope.campeonatoTipo = {};
        $scope.messages = null;
        $('#formModal').modal();
        $scope.tituloModal = 'messages.campeonatoTipo_create';
        $scope.novoItem = true;
        $scope.formulario.$setPristine();
    }

    $scope.edit = function(id) {
        $rootScope.loading = true;
        CampeonatoTipo.edit(id)
            .success(function(data) {
                $scope.campeonatoTipo = data;
                $scope.messages = null;
                $('#formModal').modal();
                $scope.tituloModal = 'messages.campeonatoTipo_edit';
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
        CampeonatoTipo.save($scope.campeonatoTipo)
                .success(function (data) {
                    CampeonatoTipo.get()
                        .success(function (getData) {
                            $scope.campeonatoTipos = getData;
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
        CampeonatoTipo.update($scope.campeonatoTipo)
                .success(function (data) {
                    CampeonatoTipo.get()
                        .success(function (getData) {
                            $scope.campeonatoTipos = getData;
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
        CampeonatoTipo.destroy(id)
            .success(function(data) {
                CampeonatoTipo.get()
                    .success(function(data) {
                        $scope.campeonatoTipos = data;
                        $rootScope.loading = false;
                });
                $('#confirmaModal').modal('hide');
                $rootScope.loading = false;
        });
    };

}]);

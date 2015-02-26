AplicacaoLiga.controller('JogoController', ['$scope', '$rootScope', 'Jogo', function ($scope, $rootScope, Jogo) {
    $scope.jogo = {};

    $scope.files = [];

    $rootScope.loading = true;

    $scope.$on("fileSelected", function (event, args) {
        $scope.$apply(function () {
            //add the file object to the scope's files collection
            $scope.files.push(args.file);
        });
    });

    Jogo.get()
    .success(function(data) {
        $scope.jogos = data;
        $rootScope.loading = false;
    }).error(function(data) {
        $scope.message = data;
        $rootScope.loading = false;
    });

    $scope.create = function() {
        $scope.jogo = {};
        $scope.messages = null;
        $('#formModal').modal();
        $scope.tituloModal = 'messages.jogo_create';
        $scope.novoItem = true;
        $scope.formulario.$setPristine();
    }

    $scope.edit = function(id) {
        $rootScope.loading = true;
        Jogo.edit(id)
            .success(function(data) {
                $scope.jogo = data;
                $scope.messages = null;
                $('#formModal').modal();
                $scope.tituloModal = 'messages.jogo_edit';
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
        Jogo.save($scope.jogo, $scope.files[0])
                .success(function (data) {
                    Jogo.get()
                        .success(function (getData) {
                            $scope.jogos = getData;
                            $rootScope.loading = false;
                    }).error(function (getData) {
                        $scope.message = getData;
                        $rootScope.loading = false;
                    });
                    $('#formModal').modal('hide');
                    $scope.files = [];
                    $rootScope.loading = false;
                }).error(function(data, status) {
                    $scope.messages = data.errors;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
    };

    $scope.update = function() {
        $rootScope.loading = true;
        Jogo.update($scope.jogo, $scope.files[0])
                .success(function (data) {
                    Jogo.get()
                        .success(function (getData) {
                            $scope.jogos = getData;
                            $rootScope.loading = false;
                    });
                    $('#formModal').modal('hide');
                    $scope.files = [];
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
        Jogo.destroy(id)
            .success(function(data) {
                Jogo.get()
                    .success(function(data) {
                        $scope.jogos = data;
                        $rootScope.loading = false;
                });
                $('#confirmaModal').modal('hide');
                $rootScope.loading = false;
        });
    };

}]);

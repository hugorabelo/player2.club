angular.module('player2').controller('PartidaController', ['$scope', '$rootScope', '$filter', 'Campeonato', 'Usuario', 'Partida', '$state', '$timeout', function ($scope, $rootScope, $filter, Campeonato, Usuario, Partida, $state, $timeout) {

    $scope.campeonato = {};

    $scope.exibeDetalhes = false;

    $scope.files = [];

    $scope.$on("fileSelected", function (event, args) {
        $scope.$apply(function () {
            $scope.files.push(args.file);
        });
    });

    $scope.carregaPartidas = function (id_usuario) {
        $rootScope.loading = true;
        Usuario.getPartidas(id_usuario)
            .success(function (data) {
                $scope.partidas = data;
                $rootScope.loading = false;
            });
    };

    $scope.salvarPlacar = function (partida) {
        $rootScope.loading = true;

        partida.usuarioLogado = $rootScope.usuarioLogado;
        Partida.salvarPlacar(partida)
            .success(function () {
                $scope.carregaPartidas($rootScope.usuarioLogado);
                $rootScope.loading = false;
            })
            .error(function (data) {
                console.log(data.errors);
                $rootScope.loading = false;
            });

    }

    $scope.confirmarPlacar = function (id_partida) {
        $rootScope.loading = true;

        var dados = {};
        dados.id_partida = id_partida;
        dados.usuarioLogado = $rootScope.usuarioLogado;
        Partida.confirmarPlacar(dados)
            .success(function () {
                $scope.carregaPartidas($rootScope.usuarioLogado);
                $rootScope.loading = false;
            })
            .error(function (data) {
                console.log(data.errors);
                $rootScope.loading = false;
            });
    }

    $scope.contestarPlacar = function (id_partida) {
        $scope.contestacao_resultado = {};
        $scope.contestacao_resultado.partidas_id = id_partida;
        $scope.contestacao_resultado.usuario_partidas_id = $rootScope.usuarioLogado;
        $('#formModal').modal();
        $scope.tituloModal = 'messages.partida_contestar';
        $scope.formulario.$setPristine();
    }

    $scope.salvarContestacao = function () {
        console.log($scope.contestacao_resultado);
        Partida.contestarResultado($scope.contestacao_resultado, $scope.files[0])
            .success(function (data) {
                $scope.carregaPartidas($rootScope.usuarioLogado);
                $('#formModal').modal('hide');
                $scope.files = [];
                $rootScope.loading = false;
            }).error(function (data, status) {
                $scope.messages = data.errors;
                $scope.status = status;
                $rootScope.loading = false;
            });
    }

    $scope.exibeDataLimite = function (data_limite) {
        dataLimite = new Date(data_limite);
        return $filter('date')(dataLimite, 'dd/MM/yyyy HH:mm');
    }
}]);

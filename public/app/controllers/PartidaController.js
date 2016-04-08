AplicacaoLiga.controller('PartidaController', ['$scope', '$rootScope', '$filter', 'Campeonato', 'Usuario', '$state', '$modal', function ($scope, $rootScope, $filter, Campeonato, Usuario, $state, $modal) {

	$scope.campeonato = {};

	$scope.exibeDetalhes = false;

	$rootScope.loading = true;

	$rootScope.loading = false;

	$scope.carregaPartidas = function(id_usuario) {
		$rootScope.loading = true;
		Usuario.getPartidas(id_usuario)
			.success(function(data) {
				 $scope.partidas = data;
				 $rootScope.loading = false;
		});
	};

    $scope.salvar = function(partida) {
        $rootScope.loading = true;
        Usuario.salvarPartida(partida)
            .success(function(data)) {
                //$scope.partidas = data;
                $rootScope.loading = false;
             }
//        console.log("salvar " + partida.id);

    }

    $scope.confirmar = function(id_partida) {
        console.log("confirmar " + id_partida);
    }

    $scope.contestar = function(id_partida) {
        console.log("contestar " + id_partida);
    }

//	$scope.carregaPartidas($rootScope.usuarioLogado);

}]);

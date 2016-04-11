AplicacaoLiga.controller('PartidaController', ['$scope', '$rootScope', '$filter', 'Campeonato', 'Usuario', 'Partida','$state', '$modal', function ($scope, $rootScope, $filter, Campeonato, Usuario, Partida,$state, $modal) {

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

	$scope.salvarPlacar = function(partida) {
		$rootScope.loading = true;

		partida.usuarioLogado = $rootScope.usuarioLogado;
		Partida.salvarPlacar(partida)
			.success(function() {
				$scope.carregaPartidas($rootScope.usuarioLogado);
				$rootScope.loading = false;
			})
			.error(function(data) {
				console.log(data.errors);
				$rootScope.loading = false;
			});

	}

	$scope.confirmarPlacar = function(id_partida) {
		console.log("confirmar " + id_partida);
	}

	$scope.contestarPlacar = function(id_partida) {
		console.log("contestar " + id_partida);
	}

//	$scope.carregaPartidas($rootScope.usuarioLogado);

}]);

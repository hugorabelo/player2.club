AplicacaoLiga.controller('PartidaController', ['$scope', '$rootScope', '$filter', 'Campeonato', 'Usuario', 'Partida','$state', '$modal', '$timeout', function ($scope, $rootScope, $filter, Campeonato, Usuario, Partida,$state, $modal, $timeout) {

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
		$rootScope.loading = true;

		var dados = {};
		dados.id_partida = id_partida;
		dados.usuarioLogado = $rootScope.usuarioLogado;
		Partida.confirmarPlacar(dados)
			.success(function() {
				$scope.carregaPartidas($rootScope.usuarioLogado);
				$rootScope.loading = false;
			})
			.error(function(data) {
				console.log(data.errors);
				$rootScope.loading = false;
			});
	}

	$scope.contestarPlacar = function(id_partida) {
		console.log("contestar " + id_partida);
	}

    $scope.exibeDataLimite = function(data_limite) {
        dataLimite = new Date(data_limite);
        return $filter('date')(dataLimite, 'dd/MM/yyyy HH:mm');
    }
}]);

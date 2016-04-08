AplicacaoLiga.controller('PartidaController', ['$scope', '$rootScope', '$filter', 'Campeonato', 'Usuario', '$state', '$modal', function ($scope, $rootScope, $filter, Campeonato, Usuario, $state, $modal) {

	$scope.campeonato = {};

	$scope.exibeDetalhes = false;

	$rootScope.loading = true;

	$rootScope.loading = false;

	$scope.carregaPartidas = function(id) {
		$rootScope.loading = true;
		Usuario.getPartidas(id)
			.success(function(data) {
				 $scope.partidas = data;
				 $rootScope.loading = false;
		});
	};

	var id = 1;
	$scope.carregaPartidas(id);


}]);

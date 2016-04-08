AplicacaoLiga.controller('CampeonatoFrontController', ['$scope', '$rootScope', '$filter', 'Campeonato', '$state', '$modal',
    function ($scope, $rootScope, $filter, Campeonato, $state, $modal) {

		$scope.carregaFases = function(id) {
			$rootScope.loading = true;
			Campeonato.getFases(id)
				.success(function(data) {
					 $scope.campeonatoFases = data;
					 $rootScope.loading = false;
					 $scope.indice_fase = -1;
					 $scope.exibeProximaFase();
			});
		};

		$scope.carregaGrupos = function(id) {
			$rootScope.loading = true;
			Campeonato.faseGrupo(id)
				.success(function(data) {
					 $scope.gruposDaFase = data;
					 $rootScope.loading = false;
			})
		};


        var id = 7;
		$scope.carregaFases(id);

		$scope.exibeFaseAnterior = function() {
			if($scope.indice_fase > 0) {
				$scope.indice_fase--;
				$scope.fase_atual = $scope.campeonatoFases[$scope.indice_fase];
				$scope.carregaGrupos($scope.fase_atual.id);
			}
		}

		$scope.exibeProximaFase = function() {
			if($scope.indice_fase < $scope.campeonatoFases.length-1) {
				$scope.indice_fase++;
				$scope.fase_atual = $scope.campeonatoFases[$scope.indice_fase];
				$scope.carregaGrupos($scope.fase_atual.id);
			}
		}

 }]);

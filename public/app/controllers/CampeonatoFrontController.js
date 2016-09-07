AplicacaoLiga.controller('CampeonatoFrontController', ['$scope', '$rootScope', '$filter', 'Campeonato', '$state', '$modal',
    function ($scope, $rootScope, $filter, Campeonato, $state, $modal) {

		$scope.rodada_atual = [];

		$scope.partidasDaRodada = [];

		$scope.carregaFases = function (id) {
			$rootScope.loading = true;
			Campeonato.getFases(id)
				.success(function (data) {
					$scope.campeonatoFases = data;
					$rootScope.loading = false;
					$scope.indice_fase = -1;
					$scope.exibeProximaFase();
				});
		};

		$scope.carregaGrupos = function (id) {
			$rootScope.loading = true;
			Campeonato.faseGrupo(id)
				.success(function (data) {
					$scope.gruposDaFase = data;
					$rootScope.loading = false;
					$scope.inicializaRodadas(data);
				})
		};

        $scope.carregaInformacoesCampeonato = function (id) {
            $rootScope.loading = true;
            Campeonato.getInformacoes(id)
                .success(function (data) {
                    $scope.campeonato = data;
		            $scope.carregaFases(id);
                    $rootScope.loading = false;
                })
        };

        $scope.carregaListaCampeonatos = function() {
            $rootScope.loading = true;
            Campeonato.get()
                .success(function (data) {
                    $scope.campeonatos = data;
                    $rootScope.loading = false;
                })
        };

        $scope.carregaListaCampeonatos();

		$scope.exibeFaseAnterior = function () {
			if ($scope.indice_fase > 0) {
				$scope.indice_fase--;
				$scope.fase_atual = $scope.campeonatoFases[$scope.indice_fase];
				$scope.carregaGrupos($scope.fase_atual.id);
			}
		}

		$scope.exibeProximaFase = function () {
			if ($scope.indice_fase < $scope.campeonatoFases.length - 1) {
				$scope.indice_fase++;
				$scope.fase_atual = $scope.campeonatoFases[$scope.indice_fase];
				$scope.carregaGrupos($scope.fase_atual.id);
			}
		}

		$scope.inicializaRodadas = function(listaDeGrupos) {
            var indice = 0;
            var partidas;
            angular.forEach(listaDeGrupos, function(item) {
                if(!$scope.fase_atual.matamata) {
                    $scope.rodada_atual.push(1);
                    $scope.carregaJogosDaRodada(indice, item.id);
                    indice++;
                    $scope.rodada_maxima = Object.keys(item.rodadas).length;
                }
            });
		}

		$scope.exibeRodadaAnterior = function(indice, id_grupo) {
			if($scope.rodada_atual[indice] > 1) {
				$scope.rodada_atual[indice]--;
				$scope.carregaJogosDaRodada(indice, id_grupo);
			}
		}

		$scope.exibeProximaRodada = function(indice, id_grupo) {
			if($scope.rodada_atual[indice] < $scope.rodada_maxima) {
				$scope.rodada_atual[indice]++;
				$scope.carregaJogosDaRodada(indice, id_grupo);
			}
		}

		$scope.carregaJogosDaRodada = function(indice, id_grupo) {
			$rootScope.loading = true;
			var rodada = $scope.rodada_atual[indice];
			Campeonato.partidasPorRodada(rodada, id_grupo)
				.success(function (data) {
					$scope.partidasDaRodada[indice] = data;
					$rootScope.loading = false;
				})
		}

        $scope.funcaoTeste = function(grupo, indice) {
            grupo.placarNovo = indice;
        }

        /*
            angular.forEach(listaDeGrupos, function(item) {
				$scope.rodada_atual.push(1);
				$scope.carregaJogosDaRodada(indice, item.id);
				indice++;
				$scope.rodada_maxima = Object.keys(item.rodadas).length;
			});
        */

 }]);

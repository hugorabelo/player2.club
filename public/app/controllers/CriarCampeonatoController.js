AplicacaoLiga
	.controller('CriarCampeonatoController', ['$scope', '$rootScope', 'Campeonato', 'Plataforma', 'Jogo', 'CampeonatoTipo', function ($scope, $rootScope, Campeonato, Plataforma, Jogo, CampeonatoTipo) {
		$scope.items = ['Pontos', 'Vitórias', 'Saldo de Gols', 'Gols Pró', 'Gols Contra', 'Confronto Direto'];
		$scope.barConfig = {
			group: 'foobar',
			animation: 150,
			onSort: function ( /** ngSortEvent */ evt) {
				// @see https://github.com/RubaXa/Sortable/blob/master/ng-sortable.js#L18-L24
			}
		};

		$scope.criteriosClassificacao = {};

		$scope.criaZonaClassificacao = function () {
			$scope.pontosZonaClassificacao = []
			for (i = 0; i < $scope.campeonato.zona_classificacao; i++) {
				$scope.pontosZonaClassificacao[i] = 0;
			}
		}

		$scope.create = function () {
			$rootScope.loading = true;
			Plataforma.get()
				.success(function (data) {
					$scope.plataformas = data;
					$scope.campeonato = {};
					$scope.messages = null;
					$rootScope.loading = false;
				});
		}

		$scope.carregaJogosDaPlataforma = function() {
			$rootScope.loading = true;
			Jogo.get()
				.success(function (data) {
					$scope.jogos = data;
					$scope.messages = null;
					$rootScope.loading = false;
				});
		}

		$scope.carregaTiposDeCampeonatoDoJogo = function() {
			$rootScope.loading = true;
			CampeonatoTipo.get()
				.success(function (data) {
					$scope.campeonatoTipos = data;
					$scope.messages = null;
					$rootScope.loading = false;
				});
		}

		$scope.carregaDetalhesCampeonato = function() {
			$rootScope.loading = true;
			var includes = ['app/views/campeonato_novo/detalhes_copa.html',
								 'app/views/campeonato_novo/detalhes_mata-mata.html'];
			$scope.incluir = includes[0];
			$rootScope.loading = false;
		}
    }]);

AplicacaoLiga
	.controller('CriarCampeonatoController', ['$scope', function ($scope) {
		$scope.items = ['Pontos', 'Vitórias', 'Saldo de Gols', 'Gols Pró', 'Gols Contra', 'Confronto Direto'];
		$scope.barConfig = {
			group: 'foobar',
			animation: 150,
			onSort: function ( /** ngSortEvent */ evt) {
				// @see https://github.com/RubaXa/Sortable/blob/master/ng-sortable.js#L18-L24
			}
		};

		$scope.criteriosClassificacao = {}
    }]);

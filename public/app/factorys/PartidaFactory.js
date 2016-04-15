AplicacaoLiga.factory('Partida', ['$http', function ($http) {
	return {
		salvarPlacar: function (partida) {
			return $http({
				method: 'POST',
				url: 'api/partidas',
				data: $.param(partida),
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			});
		},

		confirmarPlacar: function(dados) {
			return $http({
				method: 'PUT',
				url: 'api/partidas/' + dados.id_partida,
				data: $.param(dados),
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
		}
	}
}]);

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
		},

		contestarResultado: function(dados, arquivo) {
			return $http({
				method: 'POST',
				url: 'api/partidas/contestar/' + dados.partidas_id,
				headers: { 'Content-Type' : undefined },
				transformRequest: function(data) {
					var formData = new FormData();
					formData.append("comentarios", dados.comentarios);
					formData.append("partidas_id", dados.partidas_id);
					formData.append("usuario_partidas_id", dados.usuario_partidas_id);
					formData.append("imagem", arquivo);
					return formData;
				},
				data: {comentarios: dados.comentarios, partidas_id: dados.partidas_id, usuario_partidas_id: dados.usuario_partidas_id, imagem: arquivo}
			});
		},

        cancelarPlacar: function(dados) {
			return $http({
				method: 'PUT',
				url: 'api/partidas/cancelar/' + dados.id_partida,
				data: $.param(dados),
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
		}
	}
}]);

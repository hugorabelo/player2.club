angular.module('player2').factory('Plataforma', ['$http', function ($http) {
	return {
		get: function () {
			return $http.get('api/plataformas');
		},

		save: function (plataforma, arquivo) {
			return $http({
				method: 'POST',
				url: 'api/plataformas',
				headers: {
					'Content-Type': undefined
				},
				transformRequest: function (data) {
					var formData = new FormData();
					formData.append("descricao", plataforma.descricao);
					formData.append("imagem_logomarca", arquivo);
					return formData;
				}
			});
		},

		edit: function (id) {
			return $http.get('api/plataformas/' + id + '/edit');
		},

		update: function (plataforma, arquivo) {
			return $http({
				method: 'POST',
				url: 'api/plataformas/' + plataforma.id,
				headers: {
					'Content-Type': undefined
				},
				transformRequest: function (data) {
					var formData = new FormData();
					formData.append("descricao", plataforma.descricao);
					formData.append("imagem_logomarca", arquivo);
					return formData;
				}
			});
		},

		destroy: function (id) {
			return $http.delete('api/plataformas/' + id);
		},

		getJogos: function(id) {
			return $http.get('api/jogosDaPlataforma/' + id);
		}

	}
}])

AplicacaoLiga.factory('CampeonatoTipo', ['$http', function ($http) {
	return {
		get: function () {
			return $http.get('api/campeonatoTipos');
		},

		save: function (campeonatoTipo) {
			return $http({
				method: 'POST',
				url: 'api/campeonatoTipos',
				data: $.param(campeonatoTipo),
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			});
		},

		edit: function (id) {
			return $http.get('api/campeonatoTipos/' + id + '/edit');
		},

		update: function (dados) {
			return $http({
				method: 'PUT',
				url: 'api/campeonatoTipos/' + dados.id,
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				data: $.param(dados)
			});
		},

		destroy: function (id) {
			return $http.delete('api/campeonatoTipos/' + id);
		}

	}
}]);

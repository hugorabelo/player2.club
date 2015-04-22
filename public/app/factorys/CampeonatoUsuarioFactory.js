AplicacaoLiga.factory('CampeonatoUsuario', ['$http', function ($http) {
	return {

		getCampeonatos : function(id_usuario) {
			return $http.get('api/campeonatosInscritosParaUsuario/' + id_usuario);
		},

		getUsuarios : function(id_campeonato) {
			return $http.get('api/campeonatoUsuario/' + id_campeonato);
		},

		save : function(id_usuario, id_campeonato) {
			return $http({
                method: 'POST',
                url: 'api/campeonatoUsuario',
                headers: { 'Content-Type' : 'application/x-www-form-urlencoded' },
				transformRequest: function(data) {
                    var formData = new FormData();
                    formData.append("users_id", id_usuario);
					formData.append("campeonatos_id", id_campeonato);
					return formData;
				}
            });
		},

		destroy : function(id) {
			return $http.delete('api/campeonatoUsuario/' + id);
		}

	};
}]);

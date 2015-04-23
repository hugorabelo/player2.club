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
				data: $.param({
					'users_id' : id_usuario,
					'campeonatos_id' : id_campeonato
				}),
                headers: { 'Content-Type' : 'application/x-www-form-urlencoded' }
            });
		},

		destroy : function(id) {
			return $http.delete('api/campeonatoUsuario/' + id);
		}

	};
}]);

AplicacaoLiga.factory('Usuario', ['$http', function ($http) {
    return {
        show: function(id) {
            return $http.get('api/usuario/' + id);
        },

        get : function() {
            return $http.get('api/usuario');
        },

		save : function(usuario, arquivo) {
			if (typeof(arquivo)==='undefined') arquivo = null;
            return $http({
                method: 'POST',
                url: 'api/usuario',
                headers: { 'Content-Type' : undefined },
                transformRequest: function(data) {
                    var formData = new FormData();
                    angular.forEach(usuario, function(value, key) {
						formData.append(key, value);
					});
                    formData.append("imagem_perfil", arquivo);
                    return formData;
                }
            });
        },

        edit : function(id) {
            return $http.get('api/usuario/' + id + '/edit');
        },

		update : function(usuario, arquivo) {
			if (typeof(arquivo)==='undefined') arquivo = null;
            return $http({
                method: 'POST',
                url: 'api/usuario/' + usuario.id,
                headers: { 'Content-Type' : undefined },
                transformRequest: function(data) {
                    var formData = new FormData();
                    angular.forEach(usuario, function(value, key) {
						formData.append(key, value);
					});
                    formData.append("imagem_perfil", arquivo);
                    return formData;
                }
            });
        },

        destroy : function(id) {
            return $http.delete('api/usuario/' + id);
        },

        getCampeonatosInscritos : function(id) {
            return $http.get('api/campeonatosInscritosParaUsuario/' + id);
        },

        getCampeonatosDisponiveis : function(id) {
            return $http.get('api/campeonatosDisponiveisParaUsuario/' + id);
        }
    }
}]);

AplicacaoLiga.factory('UserPlataforma', ['$http', function ($http) {
	return {

        getPlataformasDoUsuario: function(id) {
            return $http.get('api/userPlataforma/' + id);
        },

        save : function(userPlataforma) {
            return $http({
                method: 'POST',
                url: 'api/userPlataforma',
                data: $.param(userPlataforma),
                headers: { 'Content-Type' : 'application/x-www-form-urlencoded' }
            });
        },

        destroy : function(id) {
            return $http.delete('api/userPlataforma/' + id);
        }

	};
}]);

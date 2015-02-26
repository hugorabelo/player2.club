AplicacaoLiga.factory('Auth', ['$http', '$rootScope', '$cookieStore', function ($http, $rootScope, $cookieStore) {
	return {
        autoriza: function(pagina) {
            var permissoes = $cookieStore.get('permissoes');
            if(permissoes.indexOf(pagina) > -1) {
                return true;
            } else {
                return false;
            }
        },

        estaLogado: function() {
            return true;
        },

        cadastrar: function() {

        },

        login: function() {

        },

        logout: function() {
            return $http.get('api/logout');
        }
	};
}])

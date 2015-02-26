AplicacaoLiga.factory('UsuarioTipo', ['$http', function ($http) {
    return {
        get : function() {
            return $http.get('api/usuarioTipo');
        },

        save : function(usuarioTipo) {
            return $http({
                method: 'POST',
                url: 'api/usuarioTipo',
                data: $.param(usuarioTipo),
                headers: { 'Content-Type' : 'application/x-www-form-urlencoded' }
            });
        },

        edit : function(id) {
            return $http.get('api/usuarioTipo/' + id + '/edit');
        },

        update : function(dados) {
            return $http({
                method: 'PUT',
                url: 'api/usuarioTipo/' + dados.id,
                headers: {
                    'Content-Type' : 'application/x-www-form-urlencoded'
                },
                data: $.param(dados)
            });
        },

        destroy : function(id) {
            return $http.delete('api/usuarioTipo/' + id);
        }
    }
}]);

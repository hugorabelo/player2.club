AplicacaoLiga.factory('Usuario', ['$http', function ($http) {
    return {
        show: function(id) {
            return $http.get('api/usuario/' + id);
        },

        get : function() {
            return $http.get('api/usuario');
        },

        save : function(usuario) {
            return $http({
                method: 'POST',
                url: 'api/usuario',
                data: $.param(usuario),
                headers: { 'Content-Type' : 'application/x-www-form-urlencoded' }
            });
        },

        edit : function(id) {
            return $http.get('api/usuario/' + id + '/edit');
        },

        update : function(dados) {
            return $http({
                method: 'PUT',
                url: 'api/usuario/' + dados.id,
                headers: {
                    'Content-Type' : 'application/x-www-form-urlencoded'
                },
                data: $.param(dados)
            });
        },

        destroy : function(id) {
            return $http.delete('api/usuario/' + id);
        }
    }
}]);

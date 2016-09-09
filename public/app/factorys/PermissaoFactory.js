angular.module('player2').factory('Permissao', ['$http', function ($http) {
    return {
        get : function(usuario_tipos_id) {
            return $http.get('api/permissao/' + usuario_tipos_id);
        },

        save : function(permissao) {
            return $http({
                method: 'POST',
                url: 'api/permissao',
                data: $.param(permissao),
                headers: { 'Content-Type' : 'application/x-www-form-urlencoded' }
            });
        },

        destroy : function(id) {
            return $http.delete('api/permissao/' + id);
        }
    }
}]);

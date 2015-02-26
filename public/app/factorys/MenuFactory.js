AplicacaoLiga.factory('Menu', ['$http', function ($http) {
    return {
        get : function() {
            return $http.get('api/menu');
        },

        getTree : function() {
            return $http.get('api/menuTree');
        },

        create : function() {
            return $http.get('api/menu/create');
        },

        save : function(menu) {
            return $http({
                method: 'POST',
                url: 'api/menu',
                data: $.param(menu),
                headers: { 'Content-Type' : 'application/x-www-form-urlencoded' }
            });
        },

        edit : function(id) {
            return $http.get('api/menu/' + id + '/edit');
        },

        update : function(dados) {
            return $http({
                method: 'PUT',
                url: 'api/menu/' + dados.id,
                headers: {
                    'Content-Type' : 'application/x-www-form-urlencoded'
                },
                data: $.param(dados)
            });
        },

        destroy : function(id) {
            return $http.delete('api/menu/' + id);
        }
    }
}]);

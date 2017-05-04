angular.module('player2').factory('ModeloCampeonato', ['$http', function ($http) {
    return {
        get: function () {
            return $http.get('api/modeloCampeonato');
        },

        save: function (modelo) {
            return $http({
                method: 'POST',
                url: 'api/modeloCampeonato',
                data: $.param(modelo),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        edit: function (id) {
            return $http.get('api/modeloCampeonato/' + id + '/edit');
        },

        update: function (modelo) {
            return $http({
                method: 'PUT',
                url: 'api/modeloCampeonato/' + modelo.id,
                data: $.param(modelo),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        destroy: function (id) {
            return $http.delete('api/modeloCampeonato/' + id);
        },

        getCriteriosClassificacao: function (id) {
            return $http.get('api/modeloCampeonato/getCriteriosClassificacao/' + id);
        }

    }
}])

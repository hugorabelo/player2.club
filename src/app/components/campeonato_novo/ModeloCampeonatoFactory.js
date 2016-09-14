angular.module('player2').factory('ModeloCampeonato', ['$http', function ($http) {
    return {

        get: function () {
            return $http.get('api/modeloCampeonato');
        },

        getCriteriosClassificacao: function (id) {
            return $http.get('api/modeloCampeonato/getCriteriosClassificacao/' + id);
        }
    };
}]);

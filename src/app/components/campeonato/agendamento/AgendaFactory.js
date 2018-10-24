angular.module('player2').factory('Agenda', ['$http', function ($http) {
    return {
        get: function () {
            return $http.get('api/agenda');
        }

    }
}]);

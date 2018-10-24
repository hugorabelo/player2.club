angular.module('player2').factory('Agenda', ['$http', function ($http) {
    return {
        addEvent: function (dados) {
            return $http({
                method: 'POST',
                url: 'api/agenda',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        }

    }
}]);

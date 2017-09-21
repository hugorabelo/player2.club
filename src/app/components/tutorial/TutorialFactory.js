angular.module('player2').factory('Tutorial', ['$http', function ($http) {
    return {
        get: function () {
            return $http.get('api/tutorial');
        },

        show: function (id) {
            return $http.get('api/tutorial/' + id);
        },

        save: function (tutorial) {
            return $http({
                method: 'POST',
                url: 'api/tutorial',
                data: $.param(tutorial),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        edit: function (id) {
            return $http.get('api/tutorial/' + id + '/edit');
        },

        update: function (dados) {
            return $http({
                method: 'PUT',
                url: 'api/tutorial/' + dados.id,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                data: $.param(dados)
            });
        },

        destroy: function (id) {
            return $http.delete('api/tutorial/' + id);
        }
    }
}]);

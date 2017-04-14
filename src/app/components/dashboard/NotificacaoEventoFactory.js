angular.module('player2').factory('NotificacaoEvento', ['$http', function ($http) {
    return {
        get: function () {
            return $http.get('api/notificacaoEvento');
        },


        save: function (notificacaoEvento) {
            return $http({
                method: 'POST',
                url: 'api/notificacaoEvento',
                data: $.param(notificacaoEvento),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        edit: function (id) {
            return $http.get('api/notificacaoEvento/' + id + '/edit');
        },

        update: function (dados) {
            return $http({
                method: 'PUT',
                url: 'api/notificacaoEvento/' + dados.id,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                data: $.param(dados)
            });
        },

        destroy: function (id) {
            return $http.delete('api/notificacaoEvento/' + id);
        }
    }
}]);

/*global angular */
(function () {
    'use strict';
    angular.module('player2').factory('Atividade', ['$http', function ($http) {
        return {
            curtir: function (curtida) {
                return $http({
                    method: 'POST',
                    url: 'api/atividade/curtir',
                    data: $.param(curtida),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
            },

            getCurtidas: function (idAtividade) {
                console.log('api/atividade/curtidas/' + idAtividade);
                return $http.get('api/atividade/curtidas/' + idAtividade);
            },

            usuarioCurtiuAtividade: function (curtida) {
                return $http({
                    method: 'POST',
                    url: 'api/atividade/usuarioCurtiu',
                    data: $.param(curtida),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
            }
        }
    }]);
}());

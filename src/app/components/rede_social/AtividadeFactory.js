/*global angular */
(function () {
    'use strict';
    angular.module('player2').factory('Atividade', ['$http', function ($http) {
        return {
            show: function (id) {
                return $http.get('api/atividade/' + id);
            },

            destroy: function (id) {
                return $http.delete('api/atividade/' + id);
            },

            getPesquisaveis: function (texto) {
                return $http.get('api/atividade/pesquisa/' + texto);
            }
        }
    }]);
}());

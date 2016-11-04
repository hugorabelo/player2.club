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
            },

            getComentarios: function (idAtividade, idUsuarioLeitor) {
                var dados = {
                    idAtividade: idAtividade,
                    idUsuarioLeitor: idUsuarioLeitor
                };
                return $http({
                    method: 'POST',
                    url: 'api/atividade/getComentarios',
                    data: $.param(dados),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
            },

            salvarComentario: function (comentario) {
                return $http({
                    method: 'POST',
                    url: 'api/comentario',
                    data: $.param(comentario),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
            }
        }
    }]);
}());

/*global angular */
(function () {
    'use strict';
    angular.module('player2').factory('Post', ['$http', function ($http) {
        return {
            salvar: function (post) {
                return $http({
                    method: 'POST',
                    url: 'api/post',
                    data: $.param(post),
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
            },

            curtir: function (curtida) {
                return $http({
                    method: 'POST',
                    url: 'api/post/curtir',
                    data: $.param(curtida),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
            }
        }
    }]);
}());

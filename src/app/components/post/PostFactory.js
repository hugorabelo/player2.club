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

            update: function (post) {
                return $http({
                    method: 'PUT',
                    url: 'api/post/' + post.id,
                    data: $.param(post),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
            },

            destroy: function (id) {
                return $http.delete('api/post/' + id);
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

            updateComentario: function (comentario) {
                return $http({
                    method: 'PUT',
                    url: 'api/comentario/' + comentario.id,
                    data: $.param(comentario),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
            },

            destroyComentario: function (id) {
                return $http.delete('api/comentario/' + id);
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
            },

            curtirComentario: function (curtida) {
                return $http({
                    method: 'POST',
                    url: 'api/comentario/curtir',
                    data: $.param(curtida),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
            },

            usuarioCurtiuPost: function (curtida) {
                return $http({
                    method: 'POST',
                    url: 'api/post/usuarioCurtiu',
                    data: $.param(curtida),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
            },

            getComentarios: function (idPost, idUsuarioLeitor) {
                dados = {
                    idPost: idPost,
                    idUsuarioLeitor: idUsuarioLeitor
                };
                return $http({
                    method: 'POST',
                    url: 'api/post/getComentarios',
                    data: $.param(dados),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
            }
        }
    }]);
}());

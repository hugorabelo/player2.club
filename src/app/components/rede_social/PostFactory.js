/*global angular */
(function () {
    'use strict';
    angular.module('player2').factory('Post', ['$http', function ($http) {
        return {
            salvar: function (post) {
                return $http({
                    method: 'POST',
                    url: 'api/post',
                    headers: {
                        'Content-Type': undefined
                    },
                    transformRequest: function (data) {
                        var formData = new FormData();
                        angular.forEach(post.imagens, function (obj) {
                            formData.append('imagens[]', obj.lfFile);
                        });

                        angular.forEach(post, function (value, key) {
                            formData.append(key, value);
                        });

                        return formData;
                    }
                });
            },

            update: function (post) {
                if (post.files.length > 0) {
                    return $http({
                        method: 'POST',
                        url: 'api/post/' + post.id,
                        headers: {
                            'Content-Type': undefined
                        },
                        transformRequest: function (data) {
                            var formData = new FormData();
                            angular.forEach(post.files, function (obj) {
                                formData.append('files[]', obj.lfFile);
                            });

                            angular.forEach(post, function (value, key) {
                                formData.append(key, value);
                            });

                            return formData;
                        }
                    });
                }
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
                var dados = {
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

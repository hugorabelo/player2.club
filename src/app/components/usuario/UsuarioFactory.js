angular.module('player2').factory('Usuario', ['$http', function ($http) {
    return {
        show: function (id) {
            return $http.get('api/usuario/' + id);
        },

        get: function () {
            return $http.get('api/usuario');
        },

        save: function (usuario, arquivo) {
            if (typeof (arquivo) === 'undefined') arquivo = null;
            return $http({
                method: 'POST',
                url: 'api/usuario',
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    angular.forEach(usuario, function (value, key) {
                        formData.append(key, value);
                    });
                    formData.append("imagem_perfil", arquivo);
                    return formData;
                }
            });
        },

        edit: function (id) {
            return $http.get('api/usuario/' + id + '/edit');
        },

        update: function (usuario, arquivoPerfil, arquivoCapa) {
            return $http({
                method: 'POST',
                url: 'api/usuario/' + usuario.id,
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    angular.forEach(usuario, function (value, key) {
                        formData.append(key, value);
                    });
                    if (arquivoPerfil != null) {
                        formData.append("imagem_perfil", arquivoPerfil.lfFile);
                    }
                    if (arquivoCapa != null) {
                        formData.append("imagem_capa", arquivoCapa.lfFile);
                    }
                    return formData;
                }
            });
        },

        destroy: function (id) {
            return $http.delete('api/usuario/' + id);
        },

        getJogos: function (id, count) {
            if (count == undefined) {
                count = '0';
            }
            return $http.get('api/usuario/getJogos/' + id + '/' + count);
        },

        getCampeonatosInscritos: function (id) {
            return $http.get('api/campeonatosInscritosParaUsuario/' + id);
        },

        getCampeonatosDisponiveis: function (id) {
            return $http.get('api/campeonatosDisponiveisParaUsuario/' + id);
        },

        getPartidas: function (id, idCampeonato) {
            if (idCampeonato !== undefined) {
                stringCampeonato = '/' + idCampeonato;
            } else {
                stringCampeonato = '';
            }
            return $http.get('api/partidasParaUsuario/' + id + stringCampeonato);
        },

        getPartidasEmAberto: function (id, idCampeonato) {
            if (idCampeonato !== undefined) {
                stringCampeonato = '/' + idCampeonato;
            } else {
                stringCampeonato = '';
            }
            return $http.get('api/partidasEmAberto/' + id + stringCampeonato);
        },

        getPartidasDisputadas: function (id, idCampeonato) {
            if (idCampeonato !== undefined) {
                stringCampeonato = '/' + idCampeonato;
            } else {
                stringCampeonato = '';
            }
            return $http.get('api/partidasDisputadas/' + id + stringCampeonato);
        },

        seguir: function (idSeguidor, usuario) {
            dados = {
                idUsuarioMestre: usuario.id,
                idUsuarioSeguidor: idSeguidor
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/adicionaSeguidor',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        deixarDeSeguir: function (idSeguidor, usuario) {
            dados = {
                idUsuarioMestre: usuario.id,
                idUsuarioSeguidor: idSeguidor
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/removeSeguidor',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        getPosts: function (idUsuario, idUsuarioLeitor, quantidade) {
            dados = {
                idUsuario: idUsuario,
                idUsuarioLeitor: idUsuarioLeitor,
                quantidade: quantidade
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/getPosts',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        segue: function (idSeguidor, usuario) {
            dados = {
                idUsuarioMestre: usuario.id,
                idUsuarioSeguidor: idSeguidor
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/segue',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        seguirJogo: function (idSeguidor, jogo) {
            dados = {
                idJogo: jogo.id,
                idUsuarioSeguidor: idSeguidor
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/adicionaSeguidorJogo',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        deixarDeSeguirJogo: function (idSeguidor, jogo) {
            dados = {
                idJogo: jogo.id,
                idUsuarioSeguidor: idSeguidor
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/removeSeguidorJogo',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        segueJogo: function (idSeguidor, jogo) {
            dados = {
                idJogo: jogo.id,
                idUsuarioSeguidor: idSeguidor
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/segueJogo',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        getFeed: function (idUsuario, todos) {
            if (todos) {
                return $http.get('api/usuario/feed/' + idUsuario + '/' + todos);
            } else {
                return $http.get('api/usuario/feed/' + idUsuario);
            }
        },

        getSeguidores: function (idUsuario) {
            return $http.get('api/usuario/seguidores/' + idUsuario);
        },

        getSeguindo: function (idUsuario) {
            return $http.get('api/usuario/seguindo/' + idUsuario);
        },

        desistirCampeonato: function (idCampeonato) {
            return $http.delete('api/usuario/desistirCampeonato/' + idCampeonato);
        }
    }
}]);

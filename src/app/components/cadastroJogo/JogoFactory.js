angular.module('player2').factory('Jogo', ['$http', function ($http) {
    return {
        show: function (id) {
            return $http.get('api/jogos/' + id);
        },

        get: function () {
            return $http.get('api/jogos');
        },

        save: function (jogo, arquivo) {
            return $http({
                method: 'POST',
                url: 'api/jogos',
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("descricao", jogo.descricao);
                    formData.append("permite_campeonato", jogo.permite_campeonato);
                    formData.append("modelo_campeonato_id", jogo.modelo_campeonato_id);
                    if (arquivo != null) {
                        formData.append("imagem_capa", arquivo.lfFile);
                    }
                    return formData;
                }
            });
        },

        edit: function (id) {
            return $http.get('api/jogos/' + id + '/edit');
        },

        update: function (jogo, arquivo) {
            return $http({
                method: 'POST',
                url: 'api/jogos/' + jogo.id,
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("descricao", jogo.descricao);
                    formData.append("permite_campeonato", jogo.permite_campeonato);
                    formData.append("modelo_campeonato_id", jogo.modelo_campeonato_id);
                    if (arquivo != null) {
                        formData.append("imagem_capa", arquivo.lfFile);
                    }
                    return formData;
                }
            });
        },

        destroy: function (id) {
            return $http.delete('api/jogos/' + id);
        },

        getTiposDeCampeonato: function (id) {
            return $http.get('api/tiposDeCampeonatoDoJogo/' + id);
        },

        getCampeonatos: function (id) {
            return $http.get('api/campeonatosDoJogo/' + id);
        },
        getFeed: function (id) {
            return $http.get('api/jogos/feed/' + id);
        }
    }
}]);

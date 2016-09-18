angular.module('player2').factory('Jogo', ['$http', function ($http) {
    return {
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
                    if (arquivo != null) {
                        formData.append("imagem_capa", arquivo.lfFile);
                    }
                    return formData;
                },
                data: {
                    descricao: jogo.descricao,
                    imagem_capa: arquivo
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
                    if (arquivo != null) {
                        formData.append("imagem_capa", arquivo.lfFile);
                    }
                    return formData;
                },
                data: {
                    descricao: jogo.descricao,
                    imagem_capa: arquivo
                }
            });
        },

        destroy: function (id) {
            return $http.delete('api/jogos/' + id);
        },

        getTiposDeCampeonato: function (id) {
            return $http.get('api/tiposDeCampeonatoDoJogo/' + id);
        }
    }
}]);
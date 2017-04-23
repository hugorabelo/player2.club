angular.module('player2').factory('Jogo', ['$http', function ($http) {
    return {
        show: function (id) {
            return $http.get('api/times/' + id);
        },

        get: function () {
            return $http.get('api/times');
        },

        getTimesPorModelo: function (idModeloCampeonato) {
            return $http.get('api/times/porModelo/' + idModeloCampeonato);
        },

        save: function (time, arquivo) {
            return $http({
                method: 'POST',
                url: 'api/times',
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("descricao", time.descricao);
                    if (arquivo != null) {
                        formData.append("distintivo", arquivo.lfFile);
                    }
                    return formData;
                },
                data: {
                    descricao: time.descricao,
                    distintivo: arquivo
                }
            });
        },

        edit: function (id) {
            return $http.get('api/times/' + id + '/edit');
        },

        update: function (jogo, arquivo) {
            return $http({
                method: 'POST',
                url: 'api/times/' + jogo.id,
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("descricao", jogo.descricao);
                    if (arquivo != null) {
                        formData.append("distintivo", arquivo.lfFile);
                    }
                    return formData;
                },
                data: {
                    descricao: time.descricao,
                    distintivo: arquivo
                }
            });
        },

        destroy: function (id) {
            return $http.delete('api/times/' + id);
        }
    }
}]);

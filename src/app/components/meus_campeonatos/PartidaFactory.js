angular.module('player2').factory('Partida', ['$http', function ($http) {
    return {
        salvarPlacar: function (partida) {
            return $http({
                method: 'POST',
                url: 'api/partidas',
                data: $.param(partida),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        confirmarPlacar: function (dados) {
            return $http({
                method: 'PUT',
                url: 'api/partidas/' + dados.id_partida,
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
        },

        contestarResultado: function (dados, arquivo) {
            return $http({
                method: 'POST',
                url: 'api/partidas/contestar/' + dados.partidas_id,
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("motivo", dados.motivo);
                    formData.append("comentarios", dados.comentarios);
                    formData.append("partidas_id", dados.partidas_id);
                    formData.append("users_id", dados.users_id);
                    if (arquivo != null) {
                        formData.append("imagem", arquivo.lfFile);
                    }
                    return formData;
                },
                data: {
                    comentarios: dados.comentarios,
                    partidas_id: dados.partidas_id,
                    users_id: dados.users_id,
                    imagem: arquivo
                }
            });
        },
        cancelarPlacar: function (dados) {
            return $http({
                method: 'PUT',
                url: 'api/partidas/cancelar/' + dados.id_partida,
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
        }
    }
}]);

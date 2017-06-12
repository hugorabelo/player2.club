angular.module('player2').factory('Equipe', ['$http', function ($http) {
    return {
        show: function (id) {
            return $http.get('api/equipe/' + id);
        },

        get: function () {
            return $http.get('api/equipe');
        },

        save: function (equipe, arquivo) {
            return $http({
                method: 'POST',
                url: 'api/equipe',
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("descricao", equipe.descricao);
                    if (arquivo != null) {
                        formData.append("imagem_logomarca", arquivo.lfFile);
                    }
                    return formData;
                }
            });
        },

        edit: function (id) {
            return $http.get('api/equipe/' + id + '/edit');
        },

        update: function (equipe, arquivo) {
            return $http({
                method: 'POST',
                url: 'api/equipe/' + equipe.id,
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("descricao", equipe.descricao);
                    if (arquivo != null) {
                        formData.append("imagem_logomarca", arquivo.lfFile);
                    }
                    return formData;
                }
            });
        },

        destroy: function (id) {
            return $http.delete('api/equipe/' + id);
        }

    }
}])

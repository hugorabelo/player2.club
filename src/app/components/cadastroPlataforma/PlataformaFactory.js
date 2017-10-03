angular.module('player2').factory('Plataforma', ['$http', function ($http) {
    return {
        get: function () {
            return $http.get('api/plataformas');
        },

        save: function (plataforma, arquivo) {
            return $http({
                method: 'POST',
                url: 'api/plataformas',
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("descricao", plataforma.descricao);
                    if (plataforma.permite_exibir == undefined) {
                        plataforma.permite_exibir = false;
                    }
                    formData.append("permite_exibir", plataforma.permite_exibir);
                    //                    angular.forEach(arquivos, function (obj) {
                    //                        formData.append("files[]", obj.lfFile);
                    //                    });
                    if (arquivo != null) {
                        formData.append("imagem_logomarca", arquivo.lfFile);
                    }
                    return formData;
                }
            });
        },

        edit: function (id) {
            return $http.get('api/plataformas/' + id + '/edit');
        },

        update: function (plataforma, arquivo) {
            return $http({
                method: 'POST',
                url: 'api/plataformas/' + plataforma.id,
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    formData.append("descricao", plataforma.descricao);
                    if (plataforma.permite_exibir == undefined) {
                        plataforma.permite_exibir = false;
                    }
                    formData.append("permite_exibir", plataforma.permite_exibir);
                    if (arquivo != null) {
                        formData.append("imagem_logomarca", arquivo.lfFile);
                    }
                    return formData;
                }
            });
        },

        destroy: function (id) {
            return $http.delete('api/plataformas/' + id);
        },

        getJogos: function (id, apenasCampeonato) {
            var stringCampeonato = '';
            if ((apenasCampeonato != null) || (apenasCampeonato != undefined) || (apenasCampeonato === true)) {
                var stringCampeonato = '/1';
            }
            return $http.get('api/jogosDaPlataforma/' + id + stringCampeonato);
        }

    }
}])

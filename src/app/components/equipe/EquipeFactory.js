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
        },

        enviarMensagem: function (mensagem) {
            return $http({
                method: 'POST',
                url: 'api/equipe/mensagem',
                data: $.param(mensagem),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        removeIntegrante: function (idEquipe, idIntegrante) {
            return $http.delete('api/equipe/integrante/' + idEquipe + '/' + idIntegrante);
        },

        getIntegrantes: function (idEquipe) {
            return $http.get('api/equipe/integrante/' + idEquipe);
        },

        getFuncoes: function () {
            return $http.get('api/equipe/funcoes');
        },

        atualizarIntegrante: function (integrante) {
            return $http({
                method: 'PUT',
                url: 'api/equipe/integrante',
                data: $.param(integrante),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        sair: function (idEquipe) {
            return $http.delete('api/equipe/integrante/' + idEquipe);
        },

        entrar: function (idEquipe) {
            return $http.post('api/equipe/solicitacao/' + idEquipe);
        },

        cancelarSolicitacao: function (idEquipe) {
            return $http.delete('api/equipe/solicitacao/' + idEquipe);
        },

        getSolicitacoes: function (idEquipe) {
            return $http.get('api/equipe/solicitacao/' + idEquipe);
        },

        getConvites: function (idEquipe) {
            return $http.get('api/equipe/convites/' + idEquipe);
        },

        inserirIntegrante: function (idEquipe, idUsuario) {
            return $http.post('api/equipe/integrante/' + idEquipe + '/' + idUsuario);
        },

        recusarSolicitacao: function (idEquipe, idUsuario) {
            return $http.delete('api/equipe/solicitacao/' + idEquipe + '/' + idUsuario);
        },

        getConvitesDisponiveis: function (idEquipe) {
            return $http.get('api/equipe/convitesDisponiveis/' + idEquipe);
        },

        enviarConvite: function (idEquipe, idUsuario) {
            return $http.post('api/equipe/solicitacao/' + idEquipe + '/' + idUsuario);
        }

    }
}])

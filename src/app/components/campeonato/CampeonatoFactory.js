angular.module('player2').factory('Campeonato', ['$http', function ($http) {
    return {
        get: function () {
            return $http.get('api/campeonato');
        },

        getInformacoes: function (id) {
            return $http.get('api/campeonato/' + id);
        },

        getParticipantes: function (id) {
            return $http.get('api/campeonato/participantes/' + id);
        },

        create: function () {
            return $http.get('api/campeonato/create');
        },

        save: function (campeonato) {
            return $http({
                method: 'POST',
                url: 'api/campeonato',
                data: $.param(campeonato),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        edit: function (id) {
            return $http.get('api/campeonato/' + id + '/edit');
        },

        update: function (dados) {
            return $http({
                method: 'PUT',
                url: 'api/campeonato/' + dados.id,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                data: $.param(dados)
            });
        },

        destroy: function (id) {
            return $http.delete('api/campeonato/' + id);
        },

        getAdministradores: function (id) {
            return $http.get('api/campeonatoAdmin/' + id);
        },

        getUsuarios: function (id) {
            return $http.get('api/campeonatoUsuarioNaoAdministrador/' + id);
        },

        getFases: function (id) {
            return $http.get('api/campeonatoFase/' + id);
        },

        adicionaAdministrador: function (id, id_administrador) {
            dados = {
                users_id: id_administrador,
                campeonatos_id: id
            };
            return $http({
                method: 'POST',
                url: 'api/campeonatoAdmin',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        excluiAdministrador: function (id) {
            return $http.delete('api/campeonatoAdmin/' + id);
        },

        criaFase: function (id) {
            return $http.get('api/campeonatoFase/create/' + id);
        },

        salvaFase: function (campeonatoFase) {
            return $http({
                method: 'POST',
                url: 'api/campeonatoFase',
                data: $.param(campeonatoFase),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        editaFase: function (id) {
            return $http.get('api/campeonatoFase/' + id + '/edit');
        },

        updateFase: function (dados) {
            return $http({
                method: 'PUT',
                url: 'api/campeonatoFase/' + dados.id,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                data: $.param(dados)
            });
        },

        destroyFase: function (id) {
            return $http.delete('api/campeonatoFase/' + id);
        },

        pontuacaoFase: function (id) {
            return $http.get('api/pontuacaoRegra/' + id);
        },

        salvaPontuacao: function (pontuacaoRegra) {
            return $http({
                method: 'POST',
                url: 'api/pontuacaoRegra',
                data: $.param(pontuacaoRegra),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        destroyPontuacao: function (id) {
            return $http.delete('api/pontuacaoRegra/' + id);
        },

        salvaGrupos: function (faseGrupo) {
            return $http({
                method: 'POST',
                url: 'api/faseGrupo',
                data: $.param(faseGrupo),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        faseGrupo: function (id) {
            return $http.get('api/faseGrupo/' + id);
        },

        destroyGrupos: function (id) {
            return $http.delete('api/faseGrupo/' + id);
        },

        partidasPorRodada: function (rodada, id_grupo) {
            dados = {
                rodada: rodada,
                id_grupo: id_grupo
            };
            return $http({
                method: 'POST',
                url: 'api/faseGrupo/partidasPorRodada',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        getTiposDeCompetidores: function () {
            return $http.get('api/tipoCompetidor');
        },

        getTiposDeAcessoDoCampeonato: function () {
            return $http.get('api/acessoCampeonato');
        },

        abreFase: function (dadosFase) {
            return $http({
                method: 'POST',
                url: 'api/campeonatoFase/abreFase',
                data: $.param(dadosFase),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        fechaFase: function (dadosFase) {
            return $http({
                method: 'POST',
                url: 'api/campeonatoFase/fechaFase',
                data: $.param(dadosFase),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        }

    }
}]);

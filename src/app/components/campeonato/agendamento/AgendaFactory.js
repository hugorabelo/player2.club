angular.module('player2').factory('Agenda', ['$http', function ($http) {
    return {
        addEvent: function (dados) {
            return $http({
                method: 'POST',
                url: 'api/agenda',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        editEvento: function (evento) {
            return $http({
                method: 'PUT',
                url: 'api/agenda/' + evento.id,
                data: $.param(evento),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        deleteEvento: function (id) {
            return $http.delete('api/agenda/' + id);
        },

        getEventos: function (idCampeonato, idUsuario) {
            if (idUsuario !== undefined) {
                stringUsuario = '/' + idUsuario;
            } else {
                stringUsuario = '';
            }
            return $http.get('api/agenda/' + idCampeonato + stringUsuario);
        },

        listaAgenda: function (idCampeonato, idUsuario, data) {
            if (data != undefined) {
                stringData = '/' + data;
            } else {
                stringData = '';
            }
            return $http.get('api/agenda/listaHorarios/' + idCampeonato + '/' + idUsuario + stringData);
        },

        agendarPartida: function (agendamento) {
            return $http({
                method: 'POST',
                url: 'api/agenda/agendarPartida',
                data: $.param(agendamento),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        }

    }
}]);

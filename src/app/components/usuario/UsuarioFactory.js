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
                    if ((arquivoPerfil !== null) && (arquivoPerfil !== undefined)) {
                        formData.append("imagem_perfil", arquivoPerfil.lfFile);
                    }
                    if ((arquivoCapa !== null) && (arquivoCapa !== undefined)) {
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
            if (count === undefined) {
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

        getPartidas: function (id, idCampeonato, confirmadas) {
            if (idCampeonato !== undefined) {
                if (confirmadas !== undefined) {
                    stringCampeonato = '/' + idCampeonato + '/' + confirmadas;
                } else {
                    stringCampeonato = '/' + idCampeonato;
                }
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

        getPartidasNaoDisputadas: function (id, idCampeonato) {
            if (idCampeonato !== undefined) {
                stringCampeonato = '/' + idCampeonato;
            } else {
                stringCampeonato = '';
            }
            return $http.get('api/partidasNaoDisputadas/' + id + stringCampeonato);
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

        seguirEquipe: function (idSeguidor, equipe) {
            dados = {
                idEquipe: equipe.id,
                idUsuarioSeguidor: idSeguidor
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/adicionaSeguidorEquipe',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        deixarDeSeguirEquipe: function (idSeguidor, equipe) {
            dados = {
                idEquipe: equipe.id,
                idUsuarioSeguidor: idSeguidor
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/removeSeguidorEquipe',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        segueEquipe: function (idSeguidor, equipe) {
            dados = {
                idEquipe: equipe.id,
                idUsuarioSeguidor: idSeguidor
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/segueEquipe',
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
        },

        getNotificacoes: function (todas) {
            if (todas === undefined) {
                return $http.get('api/usuario/notificacoes');
            } else {
                return $http.get('api/usuario/notificacoes/' + todas);
            }
        },

        lerNotificacao: function (notificacao) {
            return $http({
                method: 'POST',
                url: 'api/usuario/lerNotificacao',
                data: $.param(notificacao),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        adicionarNotificacaoEmail: function (idEvento) {
            dados = {
                id_evento: idEvento
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/adicionarNotificacaoEmail',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        removerNotificacaoEmail: function (idEvento) {
            dados = {
                id_evento: idEvento
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/removerNotificacaoEmail',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        enviarMensagem: function (mensagem) {
            return $http({
                method: 'POST',
                url: 'api/mensagem',
                data: $.param(mensagem),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        getConversas: function () {
            return $http.get('api/usuario/conversas');
        },

        getMensagens: function (idRemetente) {
            return $http.get('api/usuario/mensagens/' + idRemetente);
        },

        getEquipes: function (idUsuario, tipo) {
            if (idUsuario === undefined) {
                return $http.get('api/usuario/equipes');
            } else {
                if (tipo == undefined) {
                    return $http.get('api/usuario/equipes/' + idUsuario);
                } else {
                    return $http.get('api/usuario/equipes/' + idUsuario + '/' + tipo);
                }
            }
        },

        getEquipesAdministradas: function () {
            return $http.get('api/usuario/equipesAdministradas');
        },

        getConvites: function () {
            return $http.get('api/usuario/convites');
        },

        convidarUsuario: function (email) {
            return $http({
                method: 'POST',
                url: 'api/usuario/convidarUsuario',
                data: $.param(email),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        enviarConviteCampeonato: function (idCampeonato, idAmigo) {
            return $http.post('api/usuario/conviteCampeonato/' + idCampeonato + '/' + idAmigo);
        },

        finalizarWizard: function (idUsuario) {
            return $http.post('api/usuario/finalizarWizard/' + idUsuario);
        },

        saveAnonimo: function (usuario) {
            return $http({
                method: 'POST',
                url: 'api/usuario/saveAnonimo',
                data: $.param(usuario),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        pesquisaNome: function (texto) {
            return $http.get('api/usuario/pesquisa/' + texto);
        },

        associarAnonimo: function (usuarioCadastrado, usuarioAnonimo) {
            dados = {
                usuarioCadastrado: usuarioCadastrado,
                usuarioAnonimo: usuarioAnonimo
            };
            return $http({
                method: 'POST',
                url: 'api/usuario/associarAnonimo',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        verificarPendencias: function() {
            return $http.get('api/usuario/pendencias');
        },

        updateSenha: function (senhaEditar) {
            return $http({
                method: 'POST',
                url: 'api/usuario/trocarSenha',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                data: $.param(senhaEditar)
            });
        },
    }
}]);

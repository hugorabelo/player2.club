angular.module('player2').controller('CampeonatoController', ['$scope', '$rootScope', '$filter', 'Campeonato', '$state',
    function ($scope, $rootScope, $filter, Campeonato, $state) {

        $scope.campeonato = {};

        $scope.exibeDetalhes = false;

        $rootScope.loading = true;

        Campeonato.get()
            .success(function (data) {
                $scope.campeonatos = data;
                $rootScope.loading = false;
            });

        $scope.create = function () {
            $rootScope.loading = true;
            Campeonato.create()
                .success(function (data) {
                    $scope.campeonatoTipos = data.campeonatoTipos;
                    $scope.jogos = data.jogos;
                    $scope.plataformas = data.plataformas;
                    $scope.campeonato = {};
                    $scope.messages = null;
                    $('#formModal').modal();
                    $scope.tituloModal = 'messages.campeonato_create';
                    $scope.novoItem = true;
                    $scope.formulario.$setPristine();
                    $rootScope.loading = false;
                });
        }

        $scope.edit = function (id) {
            $rootScope.loading = true;
            Campeonato.edit(id)
                .success(function (data) {
                    $scope.campeonato = data.campeonato;
                    $scope.campeonatoTipos = data.campeonatoTipos;
                    $scope.jogos = data.jogos;
                    $scope.plataformas = data.plataformas;
                    $scope.messages = null;
                    $('#formModal').modal();
                    $scope.tituloModal = 'messages.campeonato_edit';
                    $scope.novoItem = false;
                    $scope.formulario.$setPristine();
                    $rootScope.loading = false;
                });
        };

        $scope.submit = function () {
            if ($scope.novoItem) {
                $scope.save();
            } else {
                $scope.update();
            }
        };

        $scope.save = function () {
            $rootScope.loading = true;
            Campeonato.save($scope.campeonato)
                .success(function (data) {
                    Campeonato.get()
                        .success(function (getData) {
                            $scope.campeonatos = getData;
                        });
                    $('#formModal').modal('hide');
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    $scope.messages = data.errors;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        };

        $scope.update = function () {
            $rootScope.loading = true;
            Campeonato.update($scope.campeonato)
                .success(function (data) {
                    Campeonato.get()
                        .success(function (getData) {
                            $scope.campeonatos = getData;
                            $rootScope.loading = false;
                        });
                    $('#formModal').modal('hide');
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    $scope.message = data.errors;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        };

        $scope.delete = function (id) {
            $('#confirmaModal').modal();
            $scope.mensagemModal = 'messages.confirma_exclusao';
            $scope.idRegistro = id;
        };

        $scope.confirmacaoModal = function (id) {
            $rootScope.loading = true;
            Campeonato.destroy(id)
                .success(function (data) {
                    Campeonato.get()
                        .success(function (data) {
                            $scope.campeonatos = data;
                            $rootScope.loading = false;
                        });
                    $('#confirmaModal').modal('hide');
                    $rootScope.loading = false;
                });
        };

        $scope.detalhes = function (id) {
            $scope.exibeDetalhes = true;
            $scope.idCampeonatoAtual = id;
            $scope.carregaAdministradores(id);
            $scope.carregaUsuarios(id);
            $scope.carregaFases(id);
            $scope.tab = 'pontuacao';
        };

        $scope.fechaDetalhes = function () {
            $scope.exibeDetalhes = false;
        }

        $scope.salvaAdministrador = function () {
            $rootScope.loading = true;
            Campeonato.adicionaAdministrador($scope.idCampeonatoAtual, $scope.novoAdministrador)
                .success(function (data) {
                    $scope.carregaAdministradores($scope.idCampeonatoAtual);
                    $scope.carregaUsuarios($scope.idCampeonatoAtual);
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    $scope.message = data.errors;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        };

        $scope.excluiAdministrador = function (idAdministrador) {
            $rootScope.loading = true;
            Campeonato.excluiAdministrador(idAdministrador)
                .success(function (data) {
                    $scope.carregaAdministradores($scope.idCampeonatoAtual);
                    $scope.carregaUsuarios($scope.idCampeonatoAtual);
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    $scope.message = data.errors;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        }

        $scope.adicionaFase = function () {
            $rootScope.loading = true;
            Campeonato.criaFase($scope.idCampeonatoAtual)
                .success(function (data) {
                    $scope.campeonatoFase = {};
                    $scope.messagesFase = null;
                    $scope.fases = data.fases;
                    $('#formModalFase').modal();
                    $scope.tituloModalFase = 'messages.campeonatoFase_create';
                    $scope.novoItemFase = true;
                    $scope.messageFase = '';
                    $scope.formularioFase.$setPristine();
                    $scope.campeonatoFase.campeonatos_id = $scope.idCampeonatoAtual;
                    $rootScope.loading = false;
                });
        };

        $scope.editaFase = function (id) {
            $rootScope.loading = true;
            Campeonato.editaFase(id)
                .success(function (data) {
                    $scope.campeonatoFase = data.fase;
                    $scope.campeonatoFase.data_inicio = new Date(data.fase.data_inicio);
                    $scope.campeonatoFase.data_fim = new Date(data.fase.data_fim);
                    $scope.messages = null;
                    $scope.fases = data.fases;
                    $('#formModalFase').modal();
                    $scope.tituloModalFase = 'messages.campeonatoFase_edit';
                    $scope.novoItemFase = false;
                    $scope.messageFase = '';
                    $scope.formulario.$setPristine();
                    $scope.campeonatoFase.campeonatos_id = $scope.idCampeonatoAtual;
                    $rootScope.loading = false;
                });
        };

        $scope.submitFase = function () {
            if ($scope.novoItemFase) {
                $scope.salvaFase();
            } else {
                $scope.atualizaFase();
            }
        };

        $scope.salvaFase = function () {
            Campeonato.salvaFase($scope.campeonatoFase)
                .success(function (data) {
                    $scope.carregaFases($scope.idCampeonatoAtual);
                    $('#formModalFase').modal('hide');
                }).error(function (data, status) {
                    $scope.messageFase = data.message;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        };

        $scope.atualizaFase = function () {
            $rootScope.loading = true;
            Campeonato.updateFase($scope.campeonatoFase)
                .success(function (data) {
                    $scope.carregaFases($scope.idCampeonatoAtual);
                    $('#formModalFase').modal('hide');
                }).error(function (data, status) {
                    $scope.messageFase = data.message;
                    $rootScope.loading = false;
                    $scope.status = status;
                });
        };

        $scope.excluiFase = function (id) {
            $rootScope.loading = true;
            Campeonato.destroyFase(id)
                .success(function (data) {
                    $scope.carregaFases($scope.idCampeonatoAtual);
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    $scope.message = data.errors;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        };

        $scope.detalhesFase = function (id, descricao) {
            $scope.idFaseAtual = id;
            $scope.descricaoFase = descricao;
            $scope.abrePontuacaoFase();
            $scope.abreFaseGrupo();
            Campeonato.editaFase(id)
                .success(function (data) {
                    $scope.campeonatoFaseSelecionada = data.fase;
                }).error(function (data, status) {
                    $scope.message = data.errors;
                    $scope.status = status;
                });
            $scope.dadosFase = {};
            $scope.messageOperacaoFase = '';
            $('#formModalDetalhesFase').modal();
        };

        $scope.abrePontuacaoFase = function () {
            $scope.pontuacaoRegra = {};
            $scope.carregaPontuacao($scope.idFaseAtual);
            $scope.pontuacaoRegra.campeonato_fases_id = $scope.idFaseAtual;
        };

        $scope.abreFaseGrupo = function () {
            $scope.faseGrupo = {};
            $scope.carregaGrupos($scope.idFaseAtual);
            $scope.faseGrupo.campeonato_fases_id = $scope.idFaseAtual;
        };

        $scope.salvaPontuacao = function () {
            $rootScope.loading = true;
            Campeonato.salvaPontuacao($scope.pontuacaoRegra)
                .success(function (data) {
                    $scope.carregaPontuacao($scope.idFaseAtual);
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    $scope.messagePontuacao = data.message;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        };

        $scope.deletePontuacao = function (id) {
            $rootScope.loading = true;
            Campeonato.destroyPontuacao(id)
                .success(function (data) {
                    $scope.carregaPontuacao($scope.idFaseAtual);
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    $scope.messagePontuacao = data.errors;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        };

        $scope.salvaGrupos = function () {
            $rootScope.loading = true;
            Campeonato.salvaGrupos($scope.faseGrupo)
                .success(function (data) {
                    $scope.carregaGrupos($scope.idFaseAtual);
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    $scope.messageGrupo = data.message;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        };

        $scope.iniciaFase = function () {
            $rootScope.loading = true;
            $scope.dadosFase.id = $scope.campeonatoFaseSelecionada.id;
            Campeonato.abreFase($scope.dadosFase)
                .success(function (data) {
                    $rootScope.loading = false;
                    $('#formModalDetalhesFase').modal('hide');
                }).error(function (data, status) {
                    $scope.messageOperacaoFase = data.messages;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        };

        $scope.encerraFase = function () {
            $rootScope.loading = true;
            $scope.campeonatoFaseSelecionada.usuarioLogado = $rootScope.usuarioLogado;
            Campeonato.fechaFase($scope.campeonatoFaseSelecionada)
                .success(function (data) {
                    $rootScope.loading = false;
                    $('#formModalDetalhesFase').modal('hide');
                }).error(function (data, status) {
                    $scope.messageOperacaoFase = data.messages;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        };

        $scope.carregaPontuacao = function (id) {
            $rootScope.loading = true;
            Campeonato.pontuacaoFase(id)
                .success(function (data) {
                    $scope.pontuacaoRegras = data;
                    $scope.messagePontuacao = '';
                    $rootScope.loading = false;
                })
        };

        $scope.deleteGrupos = function () {
            $rootScope.loading = true;
            Campeonato.destroyGrupos($scope.idFaseAtual)
                .success(function (data) {
                    $scope.carregaGrupos($scope.idFaseAtual);
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    $scope.messageGrupo = data.message;
                    $scope.status = status;
                    $rootScope.loading = false;
                });
        };

        $scope.carregaGrupos = function (id) {
            $rootScope.loading = true;
            Campeonato.faseGrupo(id)
                .success(function (data) {
                    $scope.faseGrupos = data;
                    $scope.messageGrupo = '';
                    $rootScope.loading = false;
                })
        };

        $scope.carregaAdministradores = function (id) {
            $rootScope.loading = true;
            Campeonato.getAdministradores(id)
                .success(function (data) {
                    $scope.campeonatoAdministradores = data;
                    $rootScope.loading = false;
                });
        };

        $scope.carregaUsuarios = function (id) {
            $rootScope.loading = true;
            Campeonato.getUsuarios(id)
                .success(function (data) {
                    $scope.campeonatoUsuarios = data;
                    $rootScope.loading = false;
                });
        };

        $scope.carregaFases = function (id) {
            $rootScope.loading = true;
            Campeonato.getFases(id)
                .success(function (data) {
                    $scope.campeonatoFases = data;
                    $rootScope.loading = false;
                });
        };

        $scope.exibirRegrasCampeonato = function (id) {
            Campeonato.getInformacoes(id)
                .success(function (data) {
                    bootbox.dialog({
                        message: data.detalhes,
                        title: data.descricao
                    });
                })
                .error(function (data) {

                });
        };

        $scope.openCalendar = function ($event, objeto) {
            $event.preventDefault();
            $event.stopPropagation();

            if (objeto == 'inicio') {
                $scope.openedInicio = true;
            } else {
                $scope.openedFim = true;
            }
        };

}]);

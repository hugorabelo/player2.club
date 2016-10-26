/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('CampeonatoController', ['$scope', '$rootScope', '$filter', '$mdDialog', '$translate', '$state', '$mdSidenav', '$stateParams', 'Campeonato', 'UserPlataforma', 'Usuario', 'Partida', function ($scope, $rootScope, $filter, $mdDialog, $translate, $state, $mdSidenav, $stateParams, Campeonato, UserPlataforma, Usuario, Partida) {

        var vm = this;

        $translate(['messages.confirma_exclusao', 'messages.confirma_iniciar_fase', 'messages.explica_iniciar_fase', 'messages.confirma_fechar_fase', 'messages.yes', 'messages.no', 'messages.close']).then(function (translations) {
            vm.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
            vm.textoConfirmaIniciarFase = translations['messages.confirma_iniciar_fase'];
            vm.textoExplicaIniciarfase = translations['messages.explica_iniciar_fase'];
            vm.textoConfirmaFecharFase = translations['messages.confirma_fechar_fase'];
            vm.textoYes = translations['messages.yes'];
            vm.textoNo = translations['messages.no'];
            vm.textoClose = translations['messages.close'];
        });

        vm.exibeDetalhes = false;

        vm.idCampeonato = $stateParams.idCampeonato;
        vm.campeonato = {};

        vm.rodada_atual = [];

        vm.partidasDaRodada = [];

        vm.carregaCampeonato = function () {
            vm.carregaInformacoesCampeonato(vm.idCampeonato);
            $scope.currentNavItem = 'tabela';
        };

        vm.carregaFases = function (id) {
            $rootScope.loading = true;
            Campeonato.getFases(id)
                .success(function (data) {
                    vm.campeonatoFases = data;
                    $rootScope.loading = false;
                    vm.indice_fase = -1;
                    vm.exibeProximaFase();
                });
        };

        vm.carregaGrupos = function (id) {
            $rootScope.loading = true;
            Campeonato.faseGrupo(id)
                .success(function (data) {
                    vm.gruposDaFase = data;
                    $rootScope.loading = false;
                    vm.inicializaRodadas(data);
                });
        };

        vm.carregaInformacoesCampeonato = function (id) {
            $rootScope.loading = true;
            Campeonato.getInformacoes(id)
                .success(function (data) {
                    vm.campeonato = data;
                    vm.carregaFases(id);
                    vm.getParticipantes(id);
                    vm.carregaAdministradores(id);
                    vm.carregaPartidasDoUsuario()
                    $rootScope.loading = false;
                });
        };

        vm.getParticipantes = function (id) {
            Campeonato.getParticipantes(id)
                .success(function (data) {
                    vm.campeonato.participantes = data;
                });
        };

        vm.carregaAdministradores = function (id) {
            Campeonato.getAdministradores(id)
                .success(function (data) {
                    vm.campeonato.campeonatoAdministradores = data;
                });
        };

        vm.carregaListaCampeonatos = function () {
            $rootScope.loading = true;
            Campeonato.get()
                .success(function (data) {
                    vm.campeonatos = data;
                    $rootScope.loading = false;
                });
        };

        vm.carregaListaCampeonatos();

        vm.exibeFaseAnterior = function () {
            if (vm.indice_fase > 0) {
                vm.indice_fase = vm.indice_fase - 1;
                vm.fase_atual = vm.campeonatoFases[vm.indice_fase];
                vm.carregaGrupos(vm.fase_atual.id);
            }
        };

        vm.exibeProximaFase = function () {
            if (vm.indice_fase < vm.campeonatoFases.length - 1) {
                vm.indice_fase = vm.indice_fase + 1;
                vm.fase_atual = vm.campeonatoFases[vm.indice_fase];
                vm.carregaGrupos(vm.fase_atual.id);
            }
        };

        vm.inicializaRodadas = function (listaDeGrupos) {
            var indice = 0,
                partidas;
            angular.forEach(listaDeGrupos, function (item) {
                if (!vm.fase_atual.matamata) {
                    vm.rodada_atual.push(1);
                    vm.carregaJogosDaRodada(indice, item.id);
                    indice = indice + 1;
                    vm.rodada_maxima = Object.keys(item.rodadas).length;
                }
            });
        };

        vm.exibeRodadaAnterior = function (indice, id_grupo) {
            if (vm.rodada_atual[indice] > 1) {
                vm.rodada_atual[indice] = vm.rodada_atual[indice] - 1;
                vm.carregaJogosDaRodada(indice, id_grupo);
            }
        };

        vm.exibeProximaRodada = function (indice, id_grupo) {
            if (vm.rodada_atual[indice] < vm.rodada_maxima) {
                vm.rodada_atual[indice] = vm.rodada_atual[indice] + 1;
                vm.carregaJogosDaRodada(indice, id_grupo);
            }
        };

        vm.carregaJogosDaRodada = function (indice, id_grupo) {
            $rootScope.loading = true;
            var rodada = vm.rodada_atual[indice];
            Campeonato.partidasPorRodada(rodada, id_grupo)
                .success(function (data) {
                    vm.partidasDaRodada[indice] = data;
                    $rootScope.loading = false;
                });
        };

        vm.querySearch = function (query) {
            var results = query ? vm.campeonato.participantes.filter(vm.createFilterFor(query)) : vm.campeonato.participantes,
                deferred;
            return results;
        };

        vm.createFilterFor = function (query) {
            var lowercaseQuery = angular.lowercase(query);

            return function filterFn(participante) {
                return (participante.nome.indexOf(lowercaseQuery) >= 0);
            };

        };

        vm.participanteDestaque = {};

        vm.exibeData = function (data) {
            var dataExibida = new Date(data);
            return $filter('date')(dataExibida, 'dd/MM/yyyy');
        };

        vm.carregaParticipanteDestaque = function (participante) {
            vm.participanteDestaque = participante;
            Campeonato.getUltimasPartidasDoUsuario(participante.id, vm.campeonato.id)
                .success(function (data) {
                    vm.participanteDestaque.ultimos_jogos = data;
                    vm.getPlataformasDoUsuario(vm.participanteDestaque);
                });
        };

        vm.salvaAdministrador = function () {
            Campeonato.adicionaAdministrador(vm.campeonato.id, vm.novoAdministrador)
                .success(function (data) {
                    vm.carregaAdministradores(vm.campeonato.id);
                }).error(function (data, status) {
                    vm.message = data.errors;
                    vm.status = status;
                    $rootScope.loading = false;
                });
        };

        vm.excluiAdministrador = function (ev, idAdministrador) {
            var confirm = $mdDialog.confirm(idAdministrador)
                .title(vm.textoConfirmaExclusao)
                .ariaLabel(vm.textoConfirmaExclusao)
                .targetEvent(ev)
                .ok(vm.textoYes)
                .cancel(vm.textoNo)
                .theme('player2');

            $mdDialog.show(confirm).then(function () {
                Campeonato.excluiAdministrador(idAdministrador)
                    .success(function (data) {
                        vm.carregaAdministradores(vm.campeonato.id);
                    }).error(function (data, status) {
                        vm.message = data.errors;
                        vm.status = status;
                    });
            }, function () {

            });

        };

        //
        vm.iniciaFase = function (ev, fase) {
            var confirm = $mdDialog.confirm(fase.id)
                .title(vm.textoConfirmaIniciarFase)
                .textContent(vm.textoExplicaIniciarfase)
                .ariaLabel(vm.textoConfirmaIniciarFase)
                .targetEvent(ev)
                .ok(vm.textoYes)
                .cancel(vm.textoNo)
                .theme('player2');

            $mdDialog.show(confirm).then(function () {
                fase.dadosFase.id = fase.id;
                Campeonato.abreFase(fase.dadosFase)
                    .success(function (data) {
                        fase.aberta = true;
                    }).error(function (data, status) {
                        vm.messageOperacaoFase = data.messages;
                        vm.status = status;
                    });
            }, function () {

            });
        };

        vm.encerraFase = function (ev, fase) {
            var confirm = $mdDialog.confirm(fase.id)
                .title(vm.textoConfirmaFecharFase)
                .ariaLabel(vm.textoConfirmaFecharFase)
                .targetEvent(ev)
                .ok(vm.textoYes)
                .cancel(vm.textoNo)
                .theme('player2');

            $mdDialog.show(confirm).then(function () {
                fase.usuarioLogado = $rootScope.usuarioLogado.id;
                Campeonato.fechaFase(fase)
                    .success(function (data) {
                        fase.encerrada = true;
                        fase.aberta = false;
                    }).error(function (data, status) {
                        vm.messageOperacaoFase = data.messages;
                        vm.status = status;
                    });
            }, function () {

            });
        };

        vm.getPlataformasDoUsuario = function (usuario) {
            usuario.plataformas = {};
            UserPlataforma.getPlataformasDoUsuario(usuario.id)
                .success(function (data) {
                    angular.forEach(data, function (userPlataforma) {
                        if (userPlataforma.plataformas_id == vm.campeonato.plataformas_id) {
                            usuario.gamertag = userPlataforma.gamertag;
                            usuario.imagem_plataforma = userPlataforma.imagem_plataforma;
                            return;
                        }
                    })
                })
                .error(function (data) {

                });
        };

        vm.carregaPartidasDoUsuario = function () {
            Usuario.getPartidas($rootScope.usuarioLogado.id, vm.campeonato.id)
                .success(function (data) {
                    vm.partidasDoUsuario = data;
                });
        };

        vm.salvarPlacar = function (partida) {

            partida.usuarioLogado = $rootScope.usuarioLogado.id;
            Partida.salvarPlacar(partida)
                .success(function () {
                    vm.carregaPartidasDoUsuario();
                })
                .error(function (data) {
                    //TODO melhorar a exibição deste erro
                });

        };

        vm.confirmarPlacar = function (id_partida) {
            var dados = {};
            dados.id_partida = id_partida;
            dados.usuarioLogado = $rootScope.usuarioLogado.id;
            Partida.confirmarPlacar(dados)
                .success(function () {
                    vm.carregaPartidasDoUsuario();
                })
                .error(function (data) {
                    //                    $rootScope.loading = false;
                });
        };

        vm.contestarPlacar = function (ev, id_partida) {
            vm.contestacao_resultado = {};
            vm.contestacao_resultado.partidas_id = id_partida;
            vm.contestacao_resultado.users_id = $rootScope.usuarioLogado.id;
            $mdDialog.show({
                    locals: {
                        tituloModal: 'messages.partida_contestar',
                        contestacao_resultado: vm.contestacao_resultado
                    },
                    controller: DialogControllerContestacao,
                    templateUrl: 'app/components/campeonato/formContestacaoResultado.html',
                    parent: angular.element(document.body),
                    targetEvent: ev,
                    clickOutsideToClose: true,
                    fullscreen: true // Only for -xs, -sm breakpoints.
                })
                .then(function () {

                }, function () {

                });
        };

        vm.cancelarPlacar = function (id_partida) {
            var dados = {};
            dados.id_partida = id_partida;
            dados.usuarioLogado = $rootScope.usuarioLogado.id;
            Partida.cancelarPlacar(dados)
                .success(function () {
                    vm.carregaPartidasDoUsuario();
                })
                .error(function (data) {
                    console.log(data.errors);
                });
        }

        vm.salvarContestacao = function (contestacao_resultado, arquivo) {
            Partida.contestarResultado(contestacao_resultado, arquivo)
                .success(function (data) {
                    vm.carregaPartidasDoUsuario();
                }).error(function (data, status) {
                    vm.messages = data.errors;
                    vm.status = status;
                });
        };

        vm.exibeDataLimite = function (data_limite) {
            var dataLimite = new Date(data_limite);
            return $filter('date')(dataLimite, 'dd/MM/yyyy HH:mm');
        };

        function DialogControllerContestacao($scope, $mdDialog, tituloModal, contestacao_resultado) {
            $scope.tituloModal = tituloModal;
            $scope.contestacao_resultado = contestacao_resultado;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.salvarContestacao = function () {
                vm.salvarContestacao($scope.contestacao_resultado, $scope.files[0]);
                $mdDialog.hide();
            }

            $scope.$watch('files.length', function (newVal, oldVal) {});
        };

        vm.edit = function () {
            console.log(vm.campeonato);
            Campeonato.edit(vm.campeonato.id)
                .success(function (data) {
                    vm.campeonatoEditar = data.campeonato;
                    //                    $mdDialog
                    //                        .show({
                    //                            locals: {
                    //                                tituloModal: 'messages.campeonato_edit',
                    //                                novoItem: false,
                    //                                campeonato: data.campeonato,
                    //                                campeonatoTipos: data.campeonatoTipos,
                    //                                jogos: data.jogos,
                    //                                plataformas: data.plataformas
                    //                            },
                    //                            controller: DialogController,
                    //                            templateUrl: 'app/components/campeonato/formModal.html',
                    //                            parent: angular.element(document.body),
                    //                            targetEvent: ev,
                    //                            clickOutsideToClose: true,
                    //                            fullscreen: true // Only for -xs, -sm breakpoints.
                    //                        })
                    //                        .then(function () {
                    //
                    //                        }, function () {
                    //
                    //                        });

                });
        };

        //        vm.create = function (ev) {
        //            Campeonato.create()
        //                .success(function (data) {
        //                    $mdDialog
        //                        .show({
        //                            locals: {
        //                                tituloModal: 'messages.campeonato_create',
        //                                novoItem: true,
        //                                campeonato: {},
        //                                campeonatoTipos: data.campeonatoTipos,
        //                                jogos: data.jogos,
        //                                plataformas: data.plataformas
        //                            },
        //                            controller: DialogController,
        //                            templateUrl: 'app/components/campeonato/formModal.html',
        //                            parent: angular.element(document.body),
        //                            targetEvent: ev,
        //                            clickOutsideToClose: true,
        //                            fullscreen: true // Only for -xs, -sm breakpoints.
        //                        })
        //                        .then(function () {
        //
        //                        }, function () {
        //
        //                        });
        //                });
        //        };
        //
        //
        //
        //        vm.save = function (campeonato) {
        //            $rootScope.loading = true;
        //            Campeonato.save(campeonato)
        //                .success(function (data) {
        //                    Campeonato.get()
        //                        .success(function (getData) {
        //                            vm.campeonatos = getData;
        //                            $rootScope.loading = false;
        //                        }).error(function (getData) {
        //                            vm.message = getData;
        //                            $rootScope.loading = false;
        //                        });
        //                    $rootScope.loading = false;
        //                }).error(function (data, status) {
        //                    vm.messages = data.errors;
        //                    vm.status = status;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.update = function (campeonato) {
        //            $rootScope.loading = true;
        //            Campeonato.update(campeonato)
        //                .success(function (data) {
        //                    Campeonato.get()
        //                        .success(function (getData) {
        //                            vm.campeonatos = getData;
        //                            $rootScope.loading = false;
        //                        });
        //                    $rootScope.loading = false;
        //                }).error(function (data, status) {
        //                    vm.message = data.errors;
        //                    vm.status = status;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.delete = function (ev, id) {
        //            vm.idRegistroExcluir = id;
        //            var confirm = $mdDialog.confirm(id)
        //                .title(vm.textoConfirmaExclusao)
        //                .ariaLabel(vm.textoConfirmaExclusao)
        //                .targetEvent(ev)
        //                .ok(vm.textoYes)
        //                .cancel(vm.textoNo)
        //                .theme('player2');
        //
        //            $mdDialog.show(confirm).then(function () {
        //                $rootScope.loading = true;
        //                Campeonato.destroy(vm.idRegistroExcluir)
        //                    .success(function (data) {
        //                        Campeonato.get()
        //                            .success(function (data) {
        //                                vm.campeonatos = data;
        //                                $rootScope.loading = false;
        //                            });
        //                        $rootScope.loading = false;
        //                    });
        //            }, function () {
        //
        //            });
        //        };
        //
        //        vm.detalhes = function (id) {
        //            vm.idCampeonatoAtual = id;
        //            vm.carregaAdministradores(id);
        //            vm.carregaUsuarios(id);
        //            vm.carregaFases(id);
        //            vm.tab = 'pontuacao';
        //            vm.toggleRight();
        //        };
        //
        //        vm.carregaAdministradores = function (id) {
        //            $rootScope.loading = true;
        //            Campeonato.getAdministradores(id)
        //                .success(function (data) {
        //                    vm.campeonatoAdministradores = data;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.carregaUsuarios = function (id) {
        //            $rootScope.loading = true;
        //            Campeonato.getUsuarios(id)
        //                .success(function (data) {
        //                    vm.campeonatoUsuarios = data;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.carregaFases = function (id) {
        //            $rootScope.loading = true;
        //            Campeonato.getFases(id)
        //                .success(function (data) {
        //                    vm.campeonatoFases = data;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.salvaAdministrador = function () {
        //            $rootScope.loading = true;
        //            Campeonato.adicionaAdministrador(vm.idCampeonatoAtual, vm.novoAdministrador)
        //                .success(function (data) {
        //                    vm.carregaAdministradores(vm.idCampeonatoAtual);
        //                    vm.carregaUsuarios(vm.idCampeonatoAtual);
        //                    $rootScope.loading = false;
        //                }).error(function (data, status) {
        //                    vm.message = data.errors;
        //                    vm.status = status;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.excluiAdministrador = function (idAdministrador) {
        //            $rootScope.loading = true;
        //            Campeonato.excluiAdministrador(idAdministrador)
        //                .success(function (data) {
        //                    vm.carregaAdministradores(vm.idCampeonatoAtual);
        //                    vm.carregaUsuarios(vm.idCampeonatoAtual);
        //                    $rootScope.loading = false;
        //                }).error(function (data, status) {
        //                    vm.message = data.errors;
        //                    vm.status = status;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.adicionaFase = function (ev) {
        //            vm.campeonatoFase = {};
        //            vm.campeonatoFase.campeonatos_id = vm.idCampeonatoAtual;
        //            Campeonato.criaFase(vm.idCampeonatoAtual)
        //                .success(function (data) {
        //                    $mdDialog
        //                        .show({
        //                            locals: {
        //                                tituloModal: 'messages.campeonatoFase_create',
        //                                novoItem: true,
        //                                campeonatoFase: vm.campeonatoFase,
        //                                fases: data.fases
        //                            },
        //                            controller: DialogControllerFase,
        //                            templateUrl: 'app/components/campeonato/formModalFase.html',
        //                            parent: angular.element(document.body),
        //                            targetEvent: ev,
        //                            clickOutsideToClose: true,
        //                            fullscreen: true // Only for -xs, -sm breakpoints.
        //                        })
        //                        .then(function () {
        //
        //                        }, function () {
        //
        //                        });
        //                });
        //        };
        //
        //        vm.editaFase = function (ev, id) {
        //            Campeonato.editaFase(id)
        //                .success(function (data) {
        //                    vm.campeonatoFase = data.fase;
        //                    vm.campeonatoFase.data_inicio = new Date(data.fase.data_inicio);
        //                    vm.campeonatoFase.data_fim = new Date(data.fase.data_fim);
        //                    vm.campeonatoFase.campeonatos_id = vm.idCampeonatoAtual;
        //                    $mdDialog
        //                        .show({
        //                            locals: {
        //                                tituloModal: 'messages.campeonatoFase_edit',
        //                                novoItem: false,
        //                                campeonatoFase: vm.campeonatoFase,
        //                                fases: data.fases
        //                            },
        //                            controller: DialogControllerFase,
        //                            templateUrl: 'app/components/campeonato/formModalFase.html',
        //                            parent: angular.element(document.body),
        //                            targetEvent: ev,
        //                            clickOutsideToClose: true,
        //                            fullscreen: true // Only for -xs, -sm breakpoints.
        //                        })
        //                        .then(function () {
        //
        //                        }, function () {
        //
        //                        });
        //
        //                });
        //        };
        //
        //        vm.salvaFase = function (campeonatoFase) {
        //            $rootScope.loading = true;
        //            Campeonato.salvaFase(campeonatoFase)
        //                .success(function (data) {
        //                    vm.carregaFases(vm.idCampeonatoAtual);
        //                    $rootScope.loading = false;
        //                }).error(function (data, status) {
        //                    vm.messages = data.errors;
        //                    vm.status = status;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.atualizaFase = function (campeonatoFase) {
        //            $rootScope.loading = true;
        //            Campeonato.updateFase(campeonatoFase)
        //                .success(function (data) {
        //                    vm.carregaFases(vm.idCampeonatoAtual);
        //                    $rootScope.loading = false;
        //                }).error(function (data, status) {
        //                    vm.message = data.errors;
        //                    vm.status = status;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.excluiFase = function (ev, id) {
        //            vm.idRegistroExcluir = id;
        //            var confirm = $mdDialog.confirm(id)
        //                .title(vm.textoConfirmaExclusao)
        //                .ariaLabel(vm.textoConfirmaExclusao)
        //                .targetEvent(ev)
        //                .ok(vm.textoYes)
        //                .cancel(vm.textoNo)
        //                .theme('player2');
        //
        //            $mdDialog.show(confirm).then(function () {
        //                $rootScope.loading = true;
        //                Campeonato.destroyFase(vm.idRegistroExcluir)
        //                    .success(function (data) {
        //                        vm.carregaFases(vm.idCampeonatoAtual);
        //                        $rootScope.loading = false;
        //                    });
        //            }, function () {
        //
        //            });
        //        };
        //
        //        // INICIO NÃO FUNCIONA
        //        vm.detalhesFase = function (id, descricao) {
        //            vm.idFaseAtual = id;
        //            vm.descricaoFase = descricao;
        //            vm.abrePontuacaoFase();
        //            vm.abreFaseGrupo();
        //            Campeonato.editaFase(id)
        //                .success(function (data) {
        //                    vm.campeonatoFaseSelecionada = data.fase;
        //                }).error(function (data, status) {
        //                    vm.message = data.errors;
        //                    vm.status = status;
        //                });
        //            vm.dadosFase = {};
        //            vm.messageOperacaoFase = '';
        //            $('#formModalDetalhesFase').modal();
        //        };
        //
        //        vm.abrePontuacaoFase = function () {
        //            vm.pontuacaoRegra = {};
        //            vm.carregaPontuacao(vm.idFaseAtual);
        //            vm.pontuacaoRegra.campeonato_fases_id = vm.idFaseAtual;
        //        };
        //
        //        vm.abreFaseGrupo = function () {
        //            vm.faseGrupo = {};
        //            vm.carregaGrupos(vm.idFaseAtual);
        //            vm.faseGrupo.campeonato_fases_id = vm.idFaseAtual;
        //        };
        //
        //        vm.salvaPontuacao = function () {
        //            $rootScope.loading = true;
        //            Campeonato.salvaPontuacao(vm.pontuacaoRegra)
        //                .success(function (data) {
        //                    vm.carregaPontuacao(vm.idFaseAtual);
        //                    $rootScope.loading = false;
        //                }).error(function (data, status) {
        //                    vm.messagePontuacao = data.message;
        //                    vm.status = status;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.deletePontuacao = function (id) {
        //            $rootScope.loading = true;
        //            Campeonato.destroyPontuacao(id)
        //                .success(function (data) {
        //                    vm.carregaPontuacao(vm.idFaseAtual);
        //                    $rootScope.loading = false;
        //                }).error(function (data, status) {
        //                    vm.messagePontuacao = data.errors;
        //                    vm.status = status;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.salvaGrupos = function () {
        //            $rootScope.loading = true;
        //            Campeonato.salvaGrupos(vm.faseGrupo)
        //                .success(function (data) {
        //                    vm.carregaGrupos(vm.idFaseAtual);
        //                    $rootScope.loading = false;
        //                }).error(function (data, status) {
        //                    vm.messageGrupo = data.message;
        //                    vm.status = status;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //
        //
        //
        //
        //        vm.carregaPontuacao = function (id) {
        //            $rootScope.loading = true;
        //            Campeonato.pontuacaoFase(id)
        //                .success(function (data) {
        //                    vm.pontuacaoRegras = data;
        //                    vm.messagePontuacao = '';
        //                    $rootScope.loading = false;
        //                })
        //        };
        //
        //        vm.deleteGrupos = function () {
        //            $rootScope.loading = true;
        //            Campeonato.destroyGrupos(vm.idFaseAtual)
        //                .success(function (data) {
        //                    vm.carregaGrupos(vm.idFaseAtual);
        //                    $rootScope.loading = false;
        //                }).error(function (data, status) {
        //                    vm.messageGrupo = data.message;
        //                    vm.status = status;
        //                    $rootScope.loading = false;
        //                });
        //        };
        //
        //        vm.carregaGrupos = function (id) {
        //            $rootScope.loading = true;
        //            Campeonato.faseGrupo(id)
        //                .success(function (data) {
        //                    vm.faseGrupos = data;
        //                    vm.messageGrupo = '';
        //                    $rootScope.loading = false;
        //                })
        //        };
        //
        //        vm.exibirRegrasCampeonato = function (ev, id) {
        //            Campeonato.getInformacoes(id)
        //                .success(function (data) {
        //                    $mdDialog.show(
        //                        $mdDialog.alert()
        //                        .parent(angular.element(document.body))
        //                        .clickOutsideToClose(true)
        //                        .title(data.descricao)
        //                        .textContent(data.regras)
        //                        .ariaLabel(data.descricao)
        //                        .ok(vm.textoClose)
        //                        .targetEvent(ev)
        //                    );
        //                })
        //                .error(function (data) {
        //
        //                });
        //        };
        //
        //
        //
        //        vm.openCalendar = function ($event, objeto) {
        //            $event.preventDefault();
        //            $event.stopPropagation();
        //
        //            if (objeto == 'inicio') {
        //                vm.openedInicio = true;
        //            } else {
        //                vm.openedFim = true;
        //            }
        //        };
        //        // FIM NÃO FUNCIONA
        //
        //        function DialogController($scope, $mdDialog, tituloModal, novoItem, campeonato, campeonatoTipos, jogos, plataformas) {
        //            $scope.tituloModal = tituloModal;
        //            $scope.novoItem = novoItem;
        //            $scope.campeonato = campeonato;
        //            $scope.campeonatoTipos = campeonatoTipos;
        //            $scope.jogos = jogos;
        //            $scope.plataformas = plataformas;
        //
        //            $scope.cancel = function () {
        //                $mdDialog.cancel();
        //            };
        //
        //            $scope.save = function () {
        //                vm.save($scope.campeonato);
        //                $mdDialog.hide();
        //            }
        //
        //            $scope.update = function () {
        //                vm.update($scope.campeonato);
        //                $mdDialog.hide();
        //            }
        //        };
        //
        //        function DialogControllerFase($scope, $mdDialog, tituloModal, novoItem, campeonatoFase, fases) {
        //            $scope.tituloModal = tituloModal;
        //            $scope.novoItem = novoItem;
        //            $scope.campeonatoFase = campeonatoFase;
        //            $scope.fases = fases;
        //
        //            $scope.cancel = function () {
        //                $mdDialog.cancel();
        //            };
        //
        //            $scope.save = function () {
        //                vm.salvaFase($scope.campeonatoFase);
        //                $mdDialog.hide();
        //            }
        //
        //            $scope.update = function () {
        //                vm.atualizaFase($scope.campeonatoFase);
        //                $mdDialog.hide();
        //            }
        //        };
        //
        //        vm.toggleRight = buildToggler('detalhesCampeonato');
        //        vm.isOpenRight = function () {
        //            return $mdSidenav('detalhesCampeonato').isOpen();
        //        };
        //
        //        vm.close = function () {
        //            // Component lookup should always be available since we are not using `ng-if`
        //            $mdSidenav('detalhesCampeonato').close()
        //                .then(function () {});
        //        };
        //
        //        function buildToggler(navID) {
        //            return function () {
        //                // Component lookup should always be available since we are not using `ng-if`
        //                $mdSidenav(navID)
        //                    .toggle()
        //                    .then(function () {});
        //            }
        //        };

                }]);
}());

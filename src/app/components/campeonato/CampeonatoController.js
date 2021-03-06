/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('CampeonatoController', ['$scope', '$rootScope', '$filter', '$mdDialog', '$translate', '$state', '$mdSidenav', '$stateParams', '$location', '$timeout', '$mdExpansionPanel', '$mdBottomSheet', 'toastr', 'localStorageService', 'Campeonato', 'UserPlataforma', 'Usuario', 'Partida', 'ModeloCampeonato', 'Plataforma', 'Jogo', 'CampeonatoTipo', 'CampeonatoUsuario', 'Time', 'Agenda', function ($scope, $rootScope, $filter, $mdDialog, $translate, $state, $mdSidenav, $stateParams, $location, $timeout, $mdExpansionPanel, $mdBottomSheet, toastr, localStorageService, Campeonato, UserPlataforma, Usuario, Partida, ModeloCampeonato, Plataforma, Jogo, CampeonatoTipo, CampeonatoUsuario, Time, Agenda) {

        var vm = this;

        $translate(['messages.confirma_exclusao', 'messages.confirma_iniciar_fase', 'messages.explica_iniciar_fase', 'messages.confirma_fechar_fase', 'messages.yes', 'messages.no', 'messages.close', 'messages.confirma_desistir_campeonato', 'messages.inscrever_titulo', 'messages.inscrever', 'messages.erro_inscricao', 'messages.usuario_sem_plataforma_um', 'messages.usuario_sem_plataforma_dois', 'fields.gamertag', 'fields.save']).then(function (translations) {
            vm.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
            vm.textoConfirmaIniciarFase = translations['messages.confirma_iniciar_fase'];
            vm.textoExplicaIniciarfase = translations['messages.explica_iniciar_fase'];
            vm.textoConfirmaFecharFase = translations['messages.confirma_fechar_fase'];
            vm.textoYes = translations['messages.yes'];
            vm.textoNo = translations['messages.no'];
            vm.textoClose = translations['messages.close'];
            vm.textoDesistirCampeonato = translations['messages.confirma_desistir_campeonato'];
            vm.textoInscreverTitulo = translations['messages.inscrever_titulo'];
            vm.textoInscrever = translations['messages.inscrever'];
            vm.erro_inscricao = translations['messages.erro_inscricao'];
            vm.usuario_sem_plataforma_um = translations['messages.usuario_sem_plataforma_um'];
            vm.usuario_sem_plataforma_dois = translations['messages.usuario_sem_plataforma_dois'];
            vm.gamertag = translations['fields.gamertag'];
            vm.saveField = translations['fields.save'];
        });

        vm.exibeDetalhes = false;

        vm.idCampeonato = $stateParams.idCampeonato;
        vm.campeonato = {};
        vm.campeonatoEditar = {};
        vm.campeonatoEditar.detalhes = {};

        vm.tipoCompetidores = ['individual', 'equipe'];

        vm.rodada_atual = [];
        vm.rodada_atual_gerenciar = [];
        vm.exibeSomenteAbertas = 0;

        vm.partidasDaRodada = [];
        vm.rodadasGerenciar = {};

        vm.partidasAbertas = false;

        vm.exibeConfirmadas = 0;

        vm.opcoesEditor = {
            language: 'pt_br',
            //                toolbarButtons: ["bold", "italic", "underline", "|", "align", "formatOL", "formatUL"],
        };

        vm.abaTabela = function () {
            vm.currentNavItem = 'tabela';
            vm.exibeTabelaCompleta();
        };

        vm.abaPartidas = function () {
            vm.currentNavItem = 'minhasPartidas';
            vm.carregaPartidasDoUsuario(vm.partidasAbertas);
        };

        vm.abaParticipantes = function () {
            vm.currentNavItem = 'participantes';
            vm.getParticipantes(vm.idCampeonato);
        };

        vm.abaGerenciar = function () {
            var date = new Date();
            $rootScope.timezone = ((date.getTimezoneOffset() / 60) * -100);

            vm.currentNavItem = 'detalhes';
            vm.carregaAdministradores(vm.idCampeonato);
            vm.carregaPartidasEmAberto();
            vm.getParticipantes(vm.idCampeonato);
            vm.carregaRodadasGerenciar();
        };

        vm.abaContestacoes = function () {
            vm.currentNavItem = 'contestacoes';
            vm.carregaPartidasContestadas();
        };

        vm.abaPartidasAbertas = function () {
            vm.currentNavItem = 'partidasAbertas';
            vm.rodada_atual_gerenciar = 1;
            vm.carregaPartidas();
            vm.carregaRodadasGerenciar();
        };

        vm.abaEditar = function () {
            vm.currentNavItem = 'editar';
            vm.edit();
        };

        vm.carregaCampeonato = function () {
            $rootScope.pageLoading = true;
            vm.carregaInformacoesCampeonato(vm.idCampeonato);
        };

        vm.carregaFases = function (id) {
            $rootScope.loading = true;
            Campeonato.getFases(id)
                .success(function (data) {
                    vm.campeonatoFases = data;
                    $rootScope.loading = false;
                    vm.indice_fase = -1;
                    vm.exibeProximaFase();
                    $rootScope.pageLoading = false;
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
            Campeonato.getInformacoes(id)
                .success(function (data) {
                    vm.campeonato = data;
                    if ((vm.campeonato.status < 3) && ($rootScope.telaMobile)) {
                        vm.currentNavItem = 'informacoes';
                    } else {
                        vm.currentNavItem = 'tabela';
                    }
                    vm.carregaAdministradores(vm.idCampeonato);
                    //                    vm.carregaFases(vm.campeonato.id);
                    vm.exibeTabelaCompleta();
                });
        };

        vm.getParticipantes = function (id) {
            $rootScope.pageLoading = true;
            Campeonato.getParticipantes(id)
                .success(function (data) {
                    vm.campeonato.participantes = data;
                    $rootScope.pageLoading = false;
                });
        };

        vm.carregaAdministradores = function (id) {
            Campeonato.getAdministradores(id)
                .success(function (data) {
                    vm.campeonato.campeonatoAdministradores = data;
                    $rootScope.pageLoading = false;
                });
        };

        vm.carregaListaCampeonatos = function () {
            Campeonato.get()
                .success(function (data) {
                    vm.campeonatos = data;
                });
        };

        //TODO Identificar para que isto estava sendo carregado
        //vm.carregaListaCampeonatos();

        vm.getCampeonatosUsuario = function () {
            Usuario.getCampeonatosInscritos($rootScope.usuarioLogado.id)
                .success(function (data) {
                    vm.campeonatosDoUsuario = data;
                });
        };

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

        vm.inicializaRodadasLimpas = function (listaDeGrupos) {
            var indice = 0,
                partidas;
            if (vm.fase_atual !== undefined) {
                angular.forEach(listaDeGrupos, function (item) {
                    if (!vm.fase_atual.matamata) {
                        vm.rodada_atual.push(1);
                        indice = indice + 1;
                        vm.rodada_maxima = Object.keys(item.rodadas).length;
                    }
                });
            }
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

        vm.exibeRodadaAnteriorGerenciar = function (indice) {
            if (vm.rodada_atual_gerenciar > 1) {
                vm.rodada_atual_gerenciar = vm.rodada_atual_gerenciar - 1;
                vm.carregaPartidas();
            }
        };

        vm.exibeProximaRodadaGerenciar = function (indice) {
            if (vm.rodada_atual_gerenciar < vm.rodada_maxima_gerenciar) {
                vm.rodada_atual_gerenciar = vm.rodada_atual_gerenciar + 1;
                vm.carregaPartidas();
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
                var lowercaseNome = angular.lowercase(participante.nome);
                return (lowercaseNome.indexOf(lowercaseQuery) >= 0);
            };

        };

        vm.participanteDestaque = {};

        vm.exibeData = function (data) {
            var dataExibida = moment(data, "YYYY-MM-DD HH:mm:ss").toDate();
            return $filter('date')(dataExibida, 'dd/MM/yyyy');
        };

        vm.carregaParticipanteDestaque = function (participante) {
            if ($rootScope.telaMobile) {
                Usuario.getPartidasNaoDisputadas(participante.id, vm.campeonato.id)
                    .success(function (data) {
                        participante.partidasNaoDisputadas = data;
                        Usuario.getPartidasDisputadas(participante.id, vm.campeonato.id)
                            .success(function (disputadas) {
                                participante.partidasDisputadas = disputadas;
                                if (!participante.anonimo) {
                                    vm.getPlataformasDoUsuario(participante);
                                }
                            })
                    });
                participante.tipo_competidor_campeonato = vm.campeonato.tipo_competidor;
                $mdDialog.show({
                        locals: {
                            tituloModal: 'fields.info_participante',
                            participanteDestaque: participante
                        },
                        controller: DialogControllerParticipante,
                        templateUrl: 'app/components/campeonato/participanteDestaque.html',
                        parent: angular.element(document.body),
                        targetEvent: null,
                        clickOutsideToClose: true,
                        fullscreen: true
                    })
                    .then(function () {

                    }, function () {

                    });
            } else {
                vm.participanteDestaque = participante;
                Usuario.getPartidasNaoDisputadas(participante.id, vm.campeonato.id)
                    .success(function (data) {
                        vm.participanteDestaque.partidasNaoDisputadas = data;
                        Usuario.getPartidasDisputadas(participante.id, vm.campeonato.id)
                            .success(function (disputadas) {
                                vm.participanteDestaque.partidasDisputadas = disputadas;
                                if (!participante.anonimo) {
                                    vm.getPlataformasDoUsuario(vm.participanteDestaque);
                                }
                            })
                    });
            }
        };

        function DialogControllerParticipante($scope, $mdDialog, tituloModal, participanteDestaque) {
            $scope.tituloModal = tituloModal;
            $scope.participanteDestaque = participanteDestaque;

            $scope.fechar = function () {
                $mdDialog.hide();
            }
        };

        vm.ocultaParticipanteDestaque = function () {
            vm.participanteDestaque = {};
        }

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
                    if (fase.dadosFase == undefined) {
                        toastr.error($filter('translate')('messages.preencher_campos'), $filter('translate')('messages.dados_invalidos'));
                    } else {
                        fase.dadosFase.id = fase.id;
                        vm.loadingFase = true;
                        Campeonato.abreFase(fase.dadosFase)
                            .success(function (data) {
                                fase.aberta = true;
                                vm.loadingFase = false;
                                vm.carregaRodadasGerenciar();
                                vm.campeonato.status = 3;
                            }).error(function (data, status) {
                                if (data.messages == undefined) {
                                    toastr.error($filter('translate')('messages.erro_operacao'), $filter('translate')('messages.operacao_nao_concluida'));
                                } else {
                                    toastr.error($filter('translate')(data.messages[0]), $filter('translate')('messages.operacao_nao_concluida'));
                                }
                                vm.loadingFase = false;
                            });
                    }
                },
                function () {

                });
        };

        vm.encerraFase = function (ev, fase) {
            var mensagemConfirmacao;
            if (vm.partidasEmAberto.length > 0) {
                mensagemConfirmacao = $filter('translate')('messages.confirma_fechar_fase_partidas_aberto');
            } else {
                mensagemConfirmacao = $filter('translate')('messages.confirma_fechar_fase');
            }

            var confirm = $mdDialog.confirm(fase.id)
                .title(mensagemConfirmacao)
                .ariaLabel(vm.textoConfirmaFecharFase)
                .targetEvent(ev)
                .ok(vm.textoYes)
                .cancel(vm.textoNo)
                .theme('player2');

            $mdDialog.show(confirm).then(function () {
                fase.usuarioLogado = $rootScope.usuarioLogado.id;
                vm.loadingFase = true;
                Campeonato.fechaFase(fase)
                    .success(function (data) {
                        fase.encerrada = true;
                        fase.aberta = false;
                        vm.loadingFase = false;
                    }).error(function (data, status) {
                        if (data.messages == undefined) {
                            toastr.error($filter('translate')('messages.erro_operacao'), $filter('translate')('messages.operacao_nao_concluida'));
                        } else {
                            toastr.error($filter('translate')(data.messages[0]), $filter('translate')('messages.operacao_nao_concluida'));
                        }
                        vm.loadingFase = false;
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

        vm.carregaPartidasDoUsuario = function (abertas, confirmadas) {
            if (abertas === undefined) {
                abertas = false;
            }
            if (confirmadas === undefined) {
                confirmadas = 0;
            }
            if (abertas) {
                Usuario.getPartidasEmAberto($rootScope.usuarioLogado.id, vm.campeonato.id)
                    .success(function (data) {
                        vm.partidasDoUsuario = data;
                        vm.partidasAbertas = true;
                    });
            } else {
                Usuario.getPartidas($rootScope.usuarioLogado.id, vm.campeonato.id, confirmadas)
                    .success(function (data) {
                        vm.partidasDoUsuario = data;
                        vm.partidasAbertas = false;
                    });

            }
        };

        vm.carregaPartidas = function () {
            Campeonato.getPartidasPorRodada(vm.campeonato.id, vm.exibeSomenteAbertas, vm.rodada_atual_gerenciar)
                .success(function (data) {
                    vm.partidasDoCampeonato = data;
                })
        }

        vm.carregaPartidasContestadas = function () {
            Campeonato.getPartidasContestadas(vm.campeonato.id)
                .success(function (data) {
                    vm.partidasContestadas = data;
                });
        };

        vm.carregaPartidasEmAberto = function () {
            Campeonato.getPartidasEmAberto(vm.campeonato.id)
                .success(function (data) {
                    vm.partidasEmAberto = data;
                });
        };

        function DialogControllerPlacarExtra($scope, $mdDialog, tituloModal, textoModal, partida) {
            $scope.tituloModal = tituloModal;
            $scope.textoModal = textoModal;
            $scope.partida = partida;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.salvarPlacar = function () {
                vm.salvarPlacar(partida);
                $mdDialog.hide();
            }

        };

        vm.salvarPlacar = function (partida, ev) {
            partida.usuarioLogado = $rootScope.usuarioLogado.id;
            Partida.salvarPlacar(partida)
                .success(function () {
                    toastr.success($filter('translate')('messages.sucesso_placar'));
                    vm.carregaPartidasDoUsuario(vm.partidasAbertas);
                    vm.carregaPartidasEmAberto();
                })
                .error(function (data) {
                    if (data.errors[0] == 'messages.precisa_placar_extra') {
                        $mdDialog.show({
                                locals: {
                                    tituloModal: 'messages.inserir_placar_extra',
                                    textoModal: 'messages.precisa_placar_extra',
                                    partida: partida
                                },
                                controller: DialogControllerPlacarExtra,
                                templateUrl: 'app/components/campeonato/formPlacarExtra.html',
                                parent: angular.element(document.body),
                                targetEvent: ev,
                                clickOutsideToClose: true,
                                fullscreen: true // Only for -xs, -sm breakpoints.
                            })
                            .then(function () {

                            }, function () {

                            });
                    } else {
                        toastr.error($filter('translate')(data.errors[0]));
                    }
                });
        };

        vm.confirmarPlacar = function (id_partida) {
            var dados = {};
            dados.id_partida = id_partida;
            dados.usuarioLogado = $rootScope.usuarioLogado.id;
            Partida.confirmarPlacar(dados)
                .success(function () {
                    vm.carregaPartidasDoUsuario(vm.partidasAbertas);
                    toastr.success($filter('translate')('messages.sucesso_confirmacao'));
                })
                .error(function (data) {
                    toastr.error($filter('translate')(data.errors[0]));
                });
        };

        vm.contestarPlacar = function (ev, id_partida, origemPendencias) {
            vm.contestacao_resultado = {};
            vm.contestacao_resultado.partidas_id = id_partida;
            vm.contestacao_resultado.users_id = $rootScope.usuarioLogado.id;
            if(origemPendencias != undefined) {
                vm.contestacao_resultado.origemPendencias = true;
            }
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
                    fullscreen: true, // Only for -xs, -sm breakpoints.
                    multiple: true
                })
                .then(function () {
                    toastr.success($filter('translate')('messages.sucesso_contestacao_solicitada'));
                }, function () {

                });
        };

        vm.cancelarPlacar = function (id_partida) {
            var dados = {};
            dados.id_partida = id_partida;
            dados.usuarioLogado = $rootScope.usuarioLogado.id;
            Partida.cancelarPlacar(dados)
                .success(function () {
                    vm.carregaPartidasDoUsuario(vm.partidasAbertas);
                    vm.carregaPartidasContestadas();
                    toastr.success($filter('translate')('messages.sucesso_cancelar_placar'));
                })
                .error(function (data) {
                    toastr.error($filter('translate')(data.errors[0]));
                });
        }

        vm.salvarContestacao = function (contestacao_resultado, arquivo) {
            Partida.contestarResultado(contestacao_resultado, arquivo)
                .success(function (data) {
                    if(contestacao_resultado.origemPendencias === true) {
                        $rootScope.$broadcast('salvouContestacaoPendencia', true);
                    } else {
                        vm.carregaPartidasDoUsuario(vm.partidasAbertas);
                    }
                    // Lan??ar o evento da janela de pend??ncias
                }).error(function (data, status) {
                    vm.messages = data.errors;
                    vm.status = status;
                });
        };

        vm.editarPlacarContestado = function (partida) {
            partida.edita_contestacao = true;
        };

        vm.editarPlacarAdministrador = function (partida) {
            partida.edita_placar = true;
        };

        vm.confirmarPlacarContestacao = function (id_partida) {
            var dados = {};
            dados.id_partida = id_partida;
            dados.usuarioLogado = $rootScope.usuarioLogado.id;
            dados.placarContestado = true;
            Partida.confirmarPlacar(dados)
                .success(function () {
                    vm.carregaPartidasContestadas();
                    vm.carregaPartidasDoUsuario(vm.partidasAbertas);
                    toastr.success($filter('translate')('messages.sucesso_confirmacao'));
                })
                .error(function (data) {});
        };

        vm.salvarPlacarContestacao = function (partida) {
            partida.usuarioLogado = $rootScope.usuarioLogado.id;
            Partida.salvarPlacar(partida)
                .success(function () {
                    vm.confirmarPlacarContestacao(partida.id);
                    toastr.success($filter('translate')('messages.sucesso_placar'));
                })
                .error(function (data) {
                    toastr.error($filter('translate')(data.errors[0]));
                });
        };

        vm.salvarPlacarAdministrador = function (partida) {
            partida.usuarioLogado = $rootScope.usuarioLogado.id;
            partida.placar_administrador = true;
            Partida.salvarPlacar(partida)
                .success(function () {
                    var dados = {};
                    dados.id_partida = partida.id;
                    dados.usuarioLogado = $rootScope.usuarioLogado.id;
                    Partida.confirmarPlacar(dados)
                        .success(function () {
                            vm.carregaPartidas();
                            toastr.success($filter('translate')('messages.sucesso_placar'));
                        })
                        .error(function (data) {});
                })
                .error(function (data) {
                    toastr.error($filter('translate')(data.errors[0]));
                });
        };

        vm.getInformacaoUsuarioContestacao = function (contestacao) {
            Usuario.show(contestacao.users_id)
                .success(function (data) {
                    contestacao.usuario = data;
                })
        };

        vm.exibirInformacoesContestacao = function (ev, contestacao) {
            contestacao.usuario = vm.getInformacaoUsuarioContestacao(contestacao);
            $mdDialog.show({
                    locals: {
                        tituloModal: 'fields.info_contestacao',
                        contestacao: contestacao
                    },
                    controller: DialogControllerContestacaoInformacao,
                    templateUrl: 'app/components/campeonato/infoContestacaoResultado.html',
                    parent: angular.element(document.body),
                    targetEvent: ev,
                    clickOutsideToClose: true,
                    fullscreen: true
                })
                .then(function () {

                }, function () {

                });
        };

        function DialogControllerContestacaoInformacao($scope, $mdDialog, tituloModal, contestacao) {
            $scope.tituloModal = tituloModal;
            $scope.contestacao = contestacao;

            $scope.fechar = function () {
                $mdDialog.hide();
            }
        };

        vm.exibeDataLimite = function (data_limite) {
            var dataLimite = moment(data_limite, "YYYY-MM-DD HH:mm:ss").toDate();
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
            Campeonato.edit(vm.campeonato.id)
                .success(function (data) {
                    vm.campeonatoEditar = data;
                    vm.campeonatoEditar.novo = false;
                    vm.campeonatoEditar.alterouCriterios = 0;
                    vm.campeonatoEditar.dataInicio = moment(vm.campeonato.dataInicio, 'YYYY-MM-DD', true).toDate();
                    vm.campeonatoEditar.dataFinal = moment(vm.campeonato.dataFinal, 'YYYY-MM-DD', true).toDate();
                    vm.carregaTiposDeAcessoDoCampeonato();
                    vm.carregaTiposDeCompetidores();
                    vm.carregaCriteriosClassificacao(vm.campeonato.tipo.modelo_campeonato_id);
                });
        };

        vm.carregaTiposDeAcessoDoCampeonato = function () {
            Campeonato.getTiposDeAcessoDoCampeonato()
                .success(function (data) {
                    vm.tiposDeAcessosDoCampeonato = data;
                });
        };

        vm.carregaCriteriosClassificacao = function (id) {
            ModeloCampeonato.getCriteriosClassificacao(id)
                .success(function (data) {
                    vm.criteriosClassificacao = data;
                    vm.messages = null;
                });
        };

        vm.carregaTiposDeCompetidores = function () {
            Campeonato.getTiposDeCompetidores()
                .success(function (data) {
                    vm.tiposDeCompetidores = data;
                });
        };

        vm.update = function () {
            Campeonato.update(vm.campeonatoEditar)
                .success(function (data) {
                    Campeonato.get()
                        .success(function (getData) {
                            vm.carregaCampeonato();
                        });
                    $rootScope.loading = false;
                }).error(function (data, status) {
                    vm.message = data.errors;
                    vm.status = status;
                });
        };

        vm.create = function (ev) {
            Campeonato.create()
                .success(function (data) {
                    vm.campeonatoEditar = {}
                    vm.campeonatoEditar.novo = true;
                    vm.carregaTiposDeAcessoDoCampeonato();
                    vm.carregaTiposDeCompetidores();
                    vm.listaPlataformas = data.plataformas;
                });
        };

        vm.carregaJogosDaPlataforma = function () {
            Plataforma.getJogos(vm.campeonatoEditar.plataformas_id)
                .success(function (data) {
                    vm.listaJogos = data;
                    if (vm.listaJogos.length > 0) {
                        vm.campeonatoEditar.jogos_id = vm.listaJogos[0].id;
                        vm.carregaTiposDeCampeonatoDoJogo();
                    }
                    vm.messages = null;
                });
        };

        vm.carregaTiposDeCampeonatoDoJogo = function () {
            Jogo.getTiposDeCampeonato(vm.campeonatoEditar.jogos_id)
                .success(function (data) {
                    vm.listaCampeonatoTipos = data;
                    if (vm.listaCampeonatoTipos.length > 0) {
                        vm.campeonatoEditar.campeonato_tipos_id = vm.listaCampeonatoTipos[0].id;
                        vm.carregaDetalhesCampeonato();
                    }
                    vm.messages = null;
                });
        };


        vm.carregaDetalhesCampeonato = function () {
            CampeonatoTipo.edit(vm.campeonatoEditar.campeonato_tipos_id)
                .success(function (data) {
                    vm.campeonatoEditar.arquivo_detalhes = data.arquivo_detalhes;
                    vm.carregaCriteriosClassificacao(data.modelo_campeonato_id);
                    vm.messages = null;
                });
        };

        vm.save = function () {
            Campeonato.save(vm.campeonatoEditar)
                .success(function (data) {
                    //                    Campeonato.get()
                    //                        .success(function (getData) {
                    //                            vm.campeonatos = getData;
                    //                        }).error(function (getData) {
                    //                            vm.message = getData;
                    //                        });
                }).error(function (data, status) {
                    //                    vm.messages = data.errors;
                    //                    vm.status = status;
                });
        };

        vm.adicionaNovoCriterio = function() {
            var criterioExiste = false;
            var novoObjeto = JSON.parse(vm.novoCriterio);
            angular.forEach(vm.campeonatoEditar.criterios, function (criterio) {
                if (novoObjeto.id === criterio.id) {
                    criterioExiste = true;
                }
            });
            if(!criterioExiste) {
                vm.campeonatoEditar.criterios.push(novoObjeto);
                vm.campeonatoEditar.alterouCriterios = 1;
            }
            vm.novoCriterio = {};
        };

        vm.removerCriterio = function(idCriterio) {
            angular.forEach(vm.campeonatoEditar.criterios, function (criterio, index) {
                if (idCriterio === criterio.id) {
                    vm.campeonatoEditar.criterios.splice(index, 1);
                    vm.campeonatoEditar.alterouCriterios = 1;
                }
            });
        }

        function DialogControllerInscricaoEscolherEquipe($scope, $mdDialog, tituloModal, equipes) {
            $scope.tituloModal = tituloModal;
            $scope.equipes = equipes;

            $scope.fechar = function () {
                $mdDialog.hide();
            }

            $scope.inscreverEquipe = function () {
                $scope.inscricao = {};
                $scope.inscricao.idCampeonato = vm.campeonato.id;
                $scope.inscricao.idEquipe = $scope.idEquipe;
                CampeonatoUsuario.inscreverEquipe($scope.inscricao)
                    .success(function (data) {
                        vm.campeonato.usuarioInscrito = true;
                        vm.getParticipantes(vm.campeonato.id);
                        toastr.success($filter('translate')('messages.sucesso_inscricao'));
                    })
                    .error(function (error) {
                        if (error.errors[0] == 'messages.inscricao_equipe_sem_quantidade_minima') {
                            toastr.error($filter('translate')('messages.inscricao_equipe_sem_quantidade_minima', {
                                'quantidade_minima_competidores': vm.campeonato.quantidade_minima_competidores
                            }));
                            return;
                        }

                        if (error.errors[0] == 'messages.inscricao_usuario_nao_administrador_equipe') {
                            toastr.error($filter('translate')('messages.inscricao_usuario_nao_administrador_equipe', {
                                'nome_equipe': error.errors[1]
                            }));
                            return;
                        }

                        if (error.errors[0] == 'messages.inscricao_equipe_administrador_existente') {
                            toastr.error($filter('translate')('messages.inscricao_equipe_administrador_existente', {
                                'nome_equipe': error.errors[1],
                                'nome_usuario': error.errors[2]
                            }));
                            return;
                        }

                        toastr.error($filter('translate')('messages.erro_inscricao'));
                    });
                $mdDialog.hide();
            }
        };

        vm.inscreverCampeonato = function (ev, id) {
            if (vm.campeonato.tipo_competidor == 'equipe') {
                Usuario.getEquipesAdministradas()
                    .success(function (data) {
                        var equipesAdministradas = data;
                        if (equipesAdministradas.length == 0) {
                            $mdDialog.show(
                                $mdDialog.alert()
                                .clickOutsideToClose(true)
                                .title($filter('translate')('messages.inscrever_titulo'))
                                .textContent($filter('translate')('messages.inscricao_equipe_nao_administrador'))
                                .ariaLabel($filter('translate')('messages.inscrever_titulo'))
                                .ok($filter('translate')('messages.close'))
                                .targetEvent(ev)
                            );
                            return;
                        }
                        $mdDialog.show({
                                locals: {
                                    tituloModal: 'fields.inscrever_titulo',
                                    equipes: equipesAdministradas
                                },
                                controller: DialogControllerInscricaoEscolherEquipe,
                                templateUrl: 'app/components/campeonato/formEscolherEquipe.html',
                                parent: angular.element(document.body),
                                targetEvent: null,
                                clickOutsideToClose: true,
                                fullscreen: true
                            })
                            .then(function () {

                            }, function () {

                            });
                    });
            } else {
                var confirm = $mdDialog.confirm(vm.campeonato.id)
                    .title(vm.textoInscreverTitulo)
                    .ariaLabel(vm.textoInscreverTitulo)
                    .targetEvent(ev)
                    .ok(vm.textoInscrever)
                    .cancel(vm.textoNo)
                    .theme('player2');
                $mdDialog.show(confirm).then(function () {
                        $rootScope.loading = true;
                        CampeonatoUsuario.save(vm.campeonato.id)
                            .success(function (data) {
                                vm.campeonato.usuarioInscrito = true;
                                vm.getParticipantes(vm.campeonato.id);
                                toastr.success($filter('translate')('messages.sucesso_inscricao'));
                            })
                            .error(function (data) {
                                //TODO se houver erro de usu??rio tentando se inscrever em campeonato em equipe, exibir mensagem aqui. Fazer um if com o retorno
                                vm.cadastraGamertagInscricao(ev);
                            });
                    },
                    function () {

                    });
            }
        };

        vm.cadastraGamertagInscricao = function (ev) {
            $mdDialog.show({
                    locals: {
                        tituloModal: $filter('translate')('messages.usuario_sem_plataforma_titulo'),
                        texto: vm.usuario_sem_plataforma_um + vm.campeonato.plataforma.descricao + vm.usuario_sem_plataforma_dois
                    },
                    controller: DialogControllerGamertag,
                    templateUrl: 'app/components/campeonato/formGamertagInscricao.html',
                    parent: angular.element(document.body),
                    targetEvent: ev,
                    clickOutsideToClose: true,
                    fullscreen: true // Only for -xs, -sm breakpoints.
                })
                .then(function () {

                }, function () {
                    toastr.error($filter('translate')('messages.usuario_sem_plataforma_um') + vm.campeonato.plataforma.descricao, $filter('translate')('messages.erro_inscricao'));
                });
        };


        function DialogControllerGamertag($scope, $mdDialog, tituloModal, texto) {
            $scope.tituloModal = tituloModal;
            $scope.texto = texto;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.salvarGamertag = function () {
                var userPlataforma = {};
                userPlataforma.plataformas_id = vm.campeonato.plataforma.id;
                userPlataforma.gamertag = $scope.gamertag;
                UserPlataforma.save(userPlataforma)
                    .success(function (data) {
                        CampeonatoUsuario.save(vm.campeonato.id)
                            .success(function (data) {
                                vm.campeonato.usuarioInscrito = true;
                                vm.getParticipantes(vm.campeonato.id);
                                toastr.success($filter('translate')('messages.sucesso_inscricao'));
                            })
                            .error(function (data) {
                                toastr.error($filter('translate')('messages.erro_inscricao'));
                            });
                    })
                    .error(function (data) {
                        toastr.error($filter('translate')('messages.erro_inscricao'));
                    });
                $mdDialog.hide();
            }
        };

        vm.sairCampeonato = function (ev) {
            var confirm = $mdDialog.confirm(vm.campeonato.id)
                .title(vm.textoDesistirCampeonato)
                .ariaLabel(vm.textoDesistirCampeonato)
                .targetEvent(ev)
                .ok(vm.textoYes)
                .cancel(vm.textoNo)
                .theme('player2');

            $mdDialog.show(confirm).then(function () {
                var i, id_campeonato_usuario;
                $rootScope.loading = true;
                Usuario.desistirCampeonato(vm.campeonato.id)
                    .success(function (data) {
                        vm.campeonato.usuarioInscrito = false;
                        vm.getParticipantes(vm.campeonato.id);
                        toastr.success($filter('translate')('messages.sucesso_desistencia'));
                    })
                    .error(function (data) {
                        toastr.error($filter('translate')('messages.erro_desistencia'));
                    });
            }, function () {

            });
        };

        vm.editarTimeUsuario = function (ev, participante) {
            if (participante != undefined) {
                vm.participanteDestaque = participante;
            }
            Time.getTimesPorModelo(vm.campeonato.tipo.modelo_campeonato_id)
                .success(function (data) {
                    vm.times = data;
                    $mdDialog.show({
                            locals: {
                                tituloModal: 'messages.inserir_time_participante',
                                participante: vm.participanteDestaque,
                                times: vm.times
                            },
                            controller: DialogControllerTime,
                            templateUrl: 'app/components/campeonato/formTime.html',
                            parent: angular.element(document.body),
                            targetEvent: ev,
                            clickOutsideToClose: true,
                            fullscreen: true // Only for -xs, -sm breakpoints.
                        })
                        .then(function () {

                        }, function () {

                        });

                });
        };

        function DialogControllerTime($scope, $mdDialog, tituloModal, participante, times) {
            $scope.tituloModal = tituloModal;
            $scope.participante = participante;
            $scope.times = times;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.salvarTime = function () {
                vm.salvarTimeUsuario($scope.participante);
                $mdDialog.hide();
            }
        };

        vm.salvarTimeUsuario = function (participante) {
            CampeonatoUsuario.salvarTime(participante.pivot.id, participante.time.id)
                .success(function (data) {
                    participante.time = data;
                    toastr.success($filter('translate')('messages.time_salvo_sucesso'));
                })
                .error(function (error) {
                    toastr.error($filter('translate')(data.messages[0]), $filter('translate')('messages.operacao_nao_concluida'));
                });
        };

        vm.carregaFiltrosPesquisa = function () {
            vm.pesquisa = {};
            Plataforma.get()
                .success(function (data) {
                    vm.plataformas = data;
                });

            Jogo.get()
                .success(function (data) {
                    vm.jogos = data;
                });

            CampeonatoTipo.get()
                .success(function (data) {
                    vm.campeonatoTipos = data;
                })
        };

        vm.limparFiltros = function () {
            vm.pesquisa = {};
            vm.resultadoPesquisa = {};
        }

        vm.filtrar = function () {
            Campeonato.pesquisaCampeonatosPorFiltros(vm.pesquisa)
                .success(function (data) {
                    vm.resultadoPesquisa = data;
                })
        };

        vm.excluirCampeonato = function (ev) {
            var confirm = $mdDialog.confirm()
                .title($filter('translate')('messages.confirma_exclusao_campeonato', {
                    'nome_campeonato': vm.campeonato.descricao,
                    'nome_plataforma': vm.campeonato.plataforma.descricao
                }))
                .ariaLabel($filter('translate')('messages.confirma_exclusao_campeonato', {
                    'nome_campeonato': vm.campeonato.descricao,
                    'nome_plataforma': vm.campeonato.plataforma.descricao
                }))
                .targetEvent(ev)
                .ok(vm.textoYes)
                .cancel(vm.textoNo)
                .theme('player2');

            $mdDialog.show(confirm).then(function () {
                Campeonato.destroy(vm.campeonato.id)
                    .success(function (data) {
                        toastr.success($filter('translate')('messages.exclusao_campeonato_sucesso'))
                        $location.path('/home');
                    }).error(function (data, status) {
                        toastr.error($filter('translate')(data.errors), $filter('translate')('messages.exclusao_campeonato_erro'));
                    });
            }, function () {

            });

        };

        vm.sortearTimes = function (ev) {
            Time.getTimesPorModelo(vm.campeonato.tipo.modelo_campeonato_id)
                .success(function (data) {
                    vm.times = data;
                    $mdDialog.show({
                            locals: {
                                tituloModal: 'messages.sortear_clubes_participantes',
                                times: vm.times
                            },
                            controller: DialogControllerSorteio,
                            templateUrl: 'app/components/campeonato/formSorteioTime.html',
                            parent: angular.element(document.body),
                            targetEvent: ev,
                            clickOutsideToClose: true,
                            fullscreen: true // Only for -xs, -sm breakpoints.
                        })
                        .then(function () {

                        }, function () {

                        });

                });
        };

        function DialogControllerSorteio($scope, $mdDialog, tituloModal, times) {
            $scope.tituloModal = tituloModal;
            $scope.times = times;
            $scope.timesSelecionados = [];

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.realizarSorteio = function () {
                if ($scope.timesSelecionados.length < vm.campeonato.participantes.length) {
                    toastr.error($filter('translate')('messages.numero_times_menor'));
                } else {
                    var sorteio = {};
                    sorteio.idCampeonato = vm.idCampeonato;
                    sorteio.timesSelecionados = $scope.timesSelecionados;
                    Campeonato.sortearClubes(sorteio)
                        .success(function (data) {
                            toastr.success($filter('translate')('messages.sorteio_sucesso'));
                            vm.getParticipantes(sorteio.idCampeonato);
                            vm.campeonato.times_sorteados = true;
                            $mdDialog.hide();
                        })
                        .error(function (error) {
                            toastr.error($filter('translate')(error.message), $filter('translate')('messages.operacao_nao_concluida'));
                        });
                }
            }

            $scope.adicionarTime = function () {
                if (($scope.timeSelecionado != undefined) && ($scope.timesSelecionados.indexOf($scope.timeSelecionado) == -1)) {
                    $scope.timesSelecionados.push($scope.timeSelecionado);
                }
                $scope.timeSelecionado = undefined;
            }

            $scope.removerTime = function (timeRemovido) {
                var index = $scope.timesSelecionados.indexOf(timeRemovido);
                if (index > -1) {
                    $scope.timesSelecionados.splice(index, 1);
                }
            }
        };


        vm.iniciaPotes = function (ev, fase) {
            var quantidade_potes = 0;
            var listas = {};
            var potes = ['Principal', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            for (var i = 0; i <= quantidade_potes; i++) {
                listas[potes[i]] = [];
            }

            vm.models = {
                selected: null,
                lists: listas
            };

            angular.forEach(vm.campeonato.participantes, function (participante) {
                var distintivoJogador = null;
                if (participante.time != null) {
                    distintivoJogador = participante.time.distintivo;
                }
                vm.models.lists.Principal.push({
                    id: participante.id,
                    label: participante.nome,
                    distintivo: distintivoJogador
                });
            });

            $mdDialog.show({
                    locals: {
                        tituloModal: 'fields.sorteio_potes',
                        models: vm.models,
                        potes: potes,
                        fase: fase
                    },
                    controller: DialogControllerPotes,
                    templateUrl: 'app/components/campeonato/formSorteioPotes.html',
                    parent: angular.element(document.body),
                    targetEvent: ev,
                    clickOutsideToClose: true,
                    fullscreen: true // Only for -xs, -sm breakpoints.
                })
                .then(function () {

                }, function () {

                });
        };

        function DialogControllerPotes($scope, $mdDialog, tituloModal, models, potes, fase) {
            $scope.tituloModal = tituloModal;
            $scope.models = models;
            $scope.potes = potes;
            $scope.fase = fase;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.adicionaPote = function () {
                var indice = 0;
                angular.forEach($scope.models.lists, function () {
                    indice++;
                });
                $scope.models.lists[$scope.potes[indice]] = [];
            };

            $scope.removePote = function () {
                var indice = -1;
                angular.forEach($scope.models.lists, function () {
                    indice++;
                });
                angular.forEach($scope.models.lists[$scope.potes[indice]], function (item) {
                    $scope.models.lists[$scope.potes[0]].push(item);
                });
                delete $scope.models.lists[$scope.potes[indice]];
            };

            $scope.iniciarFaseComPotes = function () {
                if ($scope.fase.dadosFase == undefined) {
                    toastr.error($filter('translate')('messages.preencher_campos'), $filter('translate')('messages.dados_invalidos'));
                } else {
                    var itens_sem_pote = 0;
                    angular.forEach($scope.models.lists[$scope.potes[0]], function () {
                        itens_sem_pote++;
                    });
                    if (itens_sem_pote > 0) {
                        toastr.error($filter('translate')('messages.itens_pote_principal'), $filter('translate')('messages.operacao_nao_concluida'));
                    } else {
                        $scope.fase.dadosFase.id = fase.id;
                        $scope.fase.dadosFase.potes = $scope.models.lists;
                    }
                    Campeonato.abreFase(fase.dadosFase)
                        .success(function (data) {
                            fase.aberta = true;
                            toastr.success($filter('translate')('messages.fase_iniciada_sucesso'));
                            $mdDialog.hide();
                        }).error(function (data, status) {
                            toastr.error($filter('translate')(data.messages[0]), $filter('translate')('messages.operacao_nao_concluida'));
                        });
                }
            };

        };

        vm.exibeTabelaCompleta = function () {
            Campeonato.getTabelaCompleta(vm.campeonato.id)
                .success(function (data) {
                    vm.campeonatoFases = data.fases;
                    vm.indice_fase = 0;
                    if (vm.campeonatoFases != undefined) {
                        vm.fase_atual = vm.campeonatoFases[vm.indice_fase];
                    }
                    vm.gruposDaFase = data.grupos;
                    vm.partidasDaRodada = data.partidasDaRodada;
                    vm.inicializaRodadasLimpas(data.grupos);
                });
        };

        vm.excluirParticipante = function (ev, participante) {
            var confirm = $mdDialog.confirm()
                .title($filter('translate')('messages.confirma_exclusao_participante', {
                    'nome_participante': participante.nome
                }))
                .ariaLabel($filter('translate')('messages.confirma_exclusao_participante', {
                    'nome_participante': participante.nome
                }))
                .targetEvent(ev)
                .ok(vm.textoYes)
                .cancel(vm.textoNo)
                .theme('player2');

            $mdDialog.show(confirm).then(function () {
                CampeonatoUsuario.destroy(participante.pivot.id)
                    .success(function (data) {
                        toastr.success($filter('translate')('messages.exclusao_participante_sucesso'))
                        vm.getParticipantes(vm.idCampeonato);
                        if (participante.id == $rootScope.usuarioLogado.id) {
                            vm.campeonato.usuarioInscrito = false;
                        }
                    }).error(function (data, status) {
                        toastr.error($filter('translate')(data.errors), $filter('translate')('messages.exclusao_participante_erro'));
                    });
            }, function () {

            });

        };

        vm.aplicarWO = function (partida) {
            partida.tipo_competidor = vm.campeonato.tipo_competidor;

            Agenda.getHistoricoAgendamento(partida)
                .success(function (data) {
                    $mdDialog.show({
                            locals: {
                                tituloModal: 'fields.aplicar_wo',
                                partida: partida,
                                historico_agendamento: data
                            },
                            controller: DialogControllerWO,
                            templateUrl: 'app/components/campeonato/formAplicarWO.html',
                            parent: angular.element(document.body),
                            targetEvent: null,
                            clickOutsideToClose: true,
                            fullscreen: true
                        })
                        .then(function () {

                        }, function () {

                        });
                });
        };


        function DialogControllerWO($scope, $mdDialog, tituloModal, partida, historico_agendamento) {
            $scope.tituloModal = tituloModal;
            $scope.partida = partida;
            $scope.historicoAgendamento = historico_agendamento;

            $scope.fechar = function () {
                $mdDialog.hide();
            }

            $scope.salvarWO = function () {
                $scope.partida.idCampeonato = vm.campeonato.id;
                $scope.partida.vencedorWO = $scope.vencedorWO;
                Campeonato.salvarWO($scope.partida)
                    .success(function (data) {
                        toastr.success($filter('translate')('messages.sucesso_wo'));
                        vm.carregaPartidas();
                    })
                    .error(function (error) {
                        toastr.error($filter('translate')('messages.erro_wo'));
                    });
                $mdDialog.hide();
            }
        };

        vm.carregaRodadasGerenciar = function () {
            Campeonato.getRodadas(vm.campeonato.id)
                .success(function (data) {
                    vm.rodadasGerenciar = data;
                    vm.rodada_maxima_gerenciar = data.length;
                    angular.forEach(vm.rodadasGerenciar, function (rodada) {
                        if (rodada.data_prazo != null) {
                            rodada.data_prazo = moment(rodada.data_prazo, 'YYYY-MM-DD').toDate();
                        }
                    });
                });
        };

        vm.salvarInformacoesRodada = function (rodada) {
            Campeonato.setInformacoesDaRodada(vm.campeonato.id, rodada)
                .success(function (data) {
                    vm.carregaRodadasGerenciar();
                });
        };

        function DialogControllerConvites($scope, $mdDialog, tituloModal, amigos) {
            $scope.tituloModal = tituloModal;
            $scope.amigos = amigos;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.enviarConvite = function (ev, usuario) {
                Usuario.enviarConviteCampeonato(vm.campeonato.id, usuario.id)
                    .success(function (data) {
                        ev.currentTarget.disabled = true;
                    });
            };
        };

        vm.convidarAmigo = function (ev) {
            Usuario.getSeguindo($rootScope.usuarioLogado.id)
                .success(function (data) {
                    $mdDialog
                        .show({
                            locals: {
                                tituloModal: 'messages.convidar_amigos',
                                amigos: data
                            },
                            controller: DialogControllerConvites,
                            templateUrl: 'app/components/equipe/formConvite.html',
                            parent: angular.element(document.body),
                            targetEvent: ev,
                            clickOutsideToClose: true,
                            fullscreen: true // Only for -xs, -sm breakpoints.
                        })
                        .then(function () {

                        }, function () {

                        });
                });
        };

        vm.exibeDetalhesParticipantes = function (partida) {

            if (partida.id_plataforma == undefined) {
                partida.id_plataforma = vm.campeonato.plataformas_id;
            }

            if (partida.usuarios[0].anonimo_id === null) {
                UserPlataforma.getPlataformasDoUsuario(partida.usuarios[0].users_id)
                    .success(function (data) {
                        angular.forEach(data, function (userPlataforma) {
                            if (userPlataforma.plataformas_id == partida.id_plataforma) {
                                partida.usuarios[0].gamertag = userPlataforma.gamertag;
                                return;
                            }
                        })
                    });
            }

            if (partida.usuarios[1].anonimo_id === null) {
                UserPlataforma.getPlataformasDoUsuario(partida.usuarios[1].users_id)
                    .success(function (data) {
                        angular.forEach(data, function (userPlataforma) {
                            if (userPlataforma.plataformas_id == partida.id_plataforma) {
                                partida.usuarios[1].gamertag = userPlataforma.gamertag;
                                return;
                            }
                        })
                    });
            }

            angular.forEach(partida.usuarios, function (usuarioAtual) {
                if (vm.campeonato.tipo_competidor == 'equipe') {
                    //TODO buscar usu??rios da equipe para exibir o respons??vel pelo placar
                } else {
                    if (usuarioAtual.anonimo_id === null) {
                        if (partida.usuario_placar == usuarioAtual.users_id) {
                            partida.nome_usuario_placar = usuarioAtual.nome;
                        }
                        if (partida.usuario_confirmacao == usuarioAtual.users_id) {
                            partida.nome_usuario_confirmacao = usuarioAtual.nome;
                        }
                    } else {
                        if (partida.usuario_placar == usuarioAtual.anonimo_id) {
                            partida.nome_usuario_placar = usuarioAtual.nome;
                        }
                        if (partida.usuario_confirmacao == usuarioAtual.anonimo_id) {
                            partida.nome_usuario_confirmacao = usuarioAtual.nome;
                        }
                    }
                }
            });

        };

        vm.adicionarParticipanteAnonimo = function (ev) {
            $mdDialog.show({
                    locals: {
                        tituloModal: 'messages.adicionar_participante'
                    },
                    controller: DialogControllerParticipanteAnonimo,
                    templateUrl: 'app/components/campeonato/formParticipanteAnonimo.html',
                    parent: angular.element(document.body),
                    targetEvent: ev,
                    clickOutsideToClose: true,
                    fullscreen: true // Only for -xs, -sm breakpoints.
                })
                .then(function () {

                }, function () {

                });

        };

        function DialogControllerParticipanteAnonimo($scope, $mdDialog, tituloModal) {
            $scope.tituloModal = tituloModal;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.salvar = function () {
                var userAnonimo = {};
                userAnonimo.gamertag = $scope.gamertag;
                userAnonimo.nome = $scope.nome;
                userAnonimo.sigla = $scope.sigla;
                Usuario.saveAnonimo(userAnonimo)
                    .success(function (data) {
                        $scope.inscricao = {};
                        $scope.inscricao.idCampeonato = vm.campeonato.id;
                        $scope.inscricao.idUsuarioAnonimo = data.idNovoUsuario;
                        CampeonatoUsuario.inscreverAnonimo($scope.inscricao)
                            .success(function (data) {
                                vm.campeonato.usuarioInscrito = true;
                                vm.getParticipantes(vm.campeonato.id);
                                toastr.success($filter('translate')('messages.sucesso_inscricao'));
                            })
                            .error(function (data) {
                                toastr.error($filter('translate')(data.errors[0]), $filter('translate')('messages.erro_inscricao'));
                            });

                    });
                $mdDialog.hide();
            }
        };

        vm.associarAnonimo = function (ev, participante) {
            $mdDialog.show({
                    locals: {
                        tituloModal: 'messages.associar_anonimo',
                        participante: participante
                    },
                    controller: DialogControllerAssociarAnonimo,
                    templateUrl: 'app/components/campeonato/formAssociaAnonimo.html',
                    parent: angular.element(document.body),
                    targetEvent: ev,
                    clickOutsideToClose: true,
                    fullscreen: true // Only for -xs, -sm breakpoints.
                })
                .then(function () {

                }, function () {

                });

        };

        function DialogControllerAssociarAnonimo($scope, $mdDialog, tituloModal, participante) {
            $scope.tituloModal = tituloModal;
            $scope.participante = participante;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.confirmar = function () {
                if ($scope.usuarioAssociado === undefined) {
                    toastr.error($filter('translate')('messages.sem_usuario_associado'));
                } else {
                    Usuario.associarAnonimo($scope.usuarioAssociado, participante)
                        .success(function (data) {
                            vm.getParticipantes(vm.campeonato.id);
                            toastr.success($filter('translate')('messages.sucesso_associacao'));
                        })
                        .error(function (data) {
                            toastr.error($filter('translate')(data.errors));
                        });
                    $mdDialog.hide();
                }

            }

            $scope.itensPesquisa = {};

            $scope.getPesquisaUsuario = function (texto) {
                if (texto != '') {
                    Usuario.pesquisaNome(texto)
                        .success(function (data) {
                            vm.itensPesquisa = data;
                        });
                }
            };

            $scope.querySearch = function (query) {
                var results = query ? vm.itensPesquisa.filter($scope.createFilterFor(query)) : $scope.itensPesquisa,
                    deferred;
                return results;
            };

            $scope.createFilterFor = function (query) {
                var lowercaseQuery = angular.lowercase(query);

                return function filterFn(item) {
                    var lowercaseNome = angular.lowercase(item.descricao);
                    return (lowercaseNome.indexOf(lowercaseQuery) >= 0);
                };

            };

            $scope.searchTextChange = function (text) {
                $scope.getPesquisaUsuario(text);
            };

            $scope.selectedItemChange = function (item) {
                $scope.usuarioAssociado = item;
            };
        };

        /***** AgendaController INICIO *****/

        vm.exibirAgenda = function (ev, idUsuario) {
            $mdDialog.show({
                    locals: {
                        tituloModal: 'messages.exibir_agenda',
                        idUsuario: idUsuario
                    },
                    controller: DialogControllerExibirAgenda,
                    templateUrl: 'app/components/campeonato/agendamento/formAgenda.html',
                    parent: angular.element(document.body),
                    targetEvent: ev,
                    clickOutsideToClose: true,
                    fullscreen: true // Only for -xs, -sm breakpoints.
                })
                .then(function () {

                }, function () {

                });

        };

        function DialogControllerExibirAgenda($scope, $mdDialog, tituloModal, idUsuario) {
            vm.carregarEventos(idUsuario);

            $scope.$on('carregou_eventos', function (evt, data) {
                $scope.materialEvents = vm.eventos;
            });

            $scope.tituloModal = tituloModal;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

            $scope.eventClicked = function ($selectedEvent) {
                if (idUsuario === undefined || idUsuario == $rootScope.usuarioLogado.id) {
                    $scope.evento = $selectedEvent;
                    if ($selectedEvent.status === 'ocupado') {
                        $mdDialog.show({
                            locals: {
                                tituloModal: $selectedEvent.descricaoEvento,
                                eventoSelecionado: $selectedEvent
                            },
                            controller: DialogControllerVisualizarPartida,
                            parent: angular.element(document.querySelector('agenda-content')),
                            templateUrl: 'app/components/campeonato/agendamento/dialogVisualizarPartida.html',
                            clickOutsideToClose: true,
                            multiple: true
                        }).then(function () {

                        }, function () {

                        });
                    } else if ($selectedEvent.status === 'pendente') {
                        //$filter('date')($selectedEvent.start, 'dd/MM/yyyy (EEEE) HH:mm:ss')
                        var mensagemConfirma = '';
                        var mensagemRecusa = '';

                        if ($rootScope.telaMobile) {
                            mensagemConfirma = $filter('translate')('fields.confirmar');
                            mensagemRecusa = $filter('translate')('fields.recusar');
                        } else {
                            mensagemConfirma = $filter('translate')('messages.confirmar_partida');
                            mensagemRecusa = $filter('translate')('messages.recusar_partida');
                        }

                        if ($rootScope.usuarioLogado.id !== $selectedEvent.usuario_host) {
                            var confirm = $mdDialog.confirm()
                                .parent(angular.element(document.querySelector('agenda-content')))
                                .title($filter('translate')('messages.confirma_agendamento_titulo') + $selectedEvent.descricaoEvento)
                                .htmlContent("<p>" + $selectedEvent.adversario.nome + " deseja agendar uma partida com voc??</p><h4>" + vm.campeonato.descricao + " - " + $selectedEvent.partida.rodada + $filter('translate')('messages.rodada') + "</h4>" +
                                    "<h4>" + moment($selectedEvent.start).format('DD/MM/YYYY (dddd) - HH:mm') + "</h4>")
                                .ariaLabel($filter('translate')('messages.confirma_agendamento_titulo') + $selectedEvent.descricaoEvento)
                                .ok(mensagemConfirma)
                                .cancel(mensagemRecusa)
                                .clickOutsideToClose(true)
                                .multiple(true);

                            $mdDialog.show(confirm).then(function () {
                                Agenda.confirmarAgendamento($selectedEvent)
                                    .success(function (data) {
                                        vm.carregarEventos();
                                        toastr.success($filter('translate')('messages.mensagem_confirmacao_partida', {
                                            nome_adversario: $selectedEvent.adversario.nome,
                                            data_partida: moment($selectedEvent.start).format('DD/MM/YYYY (dddd)'),
                                            hora_partida: moment($selectedEvent.start).format('HH:mm')
                                        }));
                                    })
                                    .error(function (error) {
                                        console.log(error);
                                        if (error.error == 'usuario_invalido') {
                                            toastr.error($filter('translate')('messages.mensagem_confirmacao_partida_erro_usuario_invalido'));
                                        } else {
                                            toastr.error($filter('translate')('messages.mensagem_confirmacao_partida_erro'));
                                        }
                                    });
                            }, function () {
                                var mensagemConfirmaCerteza = '';
                                var mensagemRecusaCerteza = '';

                                if ($rootScope.telaMobile) {
                                    mensagemConfirmaCerteza = $filter('translate')('messages.tenho_certeza_mobile');
                                    mensagemRecusaCerteza = $filter('translate')('messages.pensar_melhor_mobile');
                                } else {
                                    mensagemConfirmaCerteza = $filter('translate')('messages.tenho_certeza');
                                    mensagemRecusaCerteza = $filter('translate')('messages.pensar_melhor');
                                }

                                var confirm2 = $mdDialog.confirm()
                                    .parent(angular.element(document.querySelector('agenda-content')))
                                    .title($filter('translate')('fields.recusar') + " " + $selectedEvent.descricaoEvento)
                                    .textContent($filter('translate')('messages.certeza_recusar_partida'))
                                    .ariaLabel($filter('translate')('messages.confirma_agendamento_titulo') + $selectedEvent.descricaoEvento)
                                    .ok(mensagemConfirmaCerteza)
                                    .cancel(mensagemRecusaCerteza)
                                    .clickOutsideToClose(true)
                                    .multiple(true);

                                $mdDialog.show(confirm2).then(function () {
                                    Agenda.recusarAgendamento($selectedEvent)
                                        .success(function (data) {
                                            vm.carregarEventos();
                                            toastr.warning($filter('translate')('messages.mensagem_recusa_partida', {
                                                nome_adversario: $selectedEvent.adversario.nome,
                                                data_partida: moment($selectedEvent.start).format('DD/MM/YYYY (dddd)'),
                                                hora_partida: moment($selectedEvent.start).format('HH:mm')
                                            }));
                                        })
                                        .error(function (error) {
                                            if (error.error == 'usuario_invalido') {
                                                toastr.error($filter('translate')('messages.mensagem_recusa_partida_erro_usuario_invalido'));
                                            } else {
                                                toastr.error($filter('translate')('messages.mensagem_recusa_partida_erro'));
                                            }
                                        });
                                }, function () {});

                            });

                        } else {
                            $mdDialog.show({
                                locals: {
                                    tituloModal: $selectedEvent.descricaoEvento,
                                    eventoSelecionado: $selectedEvent
                                },
                                controller: DialogControllerVisualizarPartida,
                                parent: angular.element(document.querySelector('agenda-content')),
                                templateUrl: 'app/components/campeonato/agendamento/dialogVisualizarPartida.html',
                                clickOutsideToClose: true,
                                multiple: true
                            }).then(function () {

                            }, function () {

                            });
                        }

                    } else {
                        vm.showEditarEvento();
                    }
                }
            };

            $scope.dateClick = function ($date) {
                if (($date < new Date(vm.campeonato.dataInicio)) || ($date > new Date(vm.campeonato.dataFinal))) {
                    toastr.warning($filter('translate')('messages.evento_fora_do_prazo') + ": " + $filter('date')(new Date(vm.campeonato.dataInicio), 'dd/MM/yyyy') + " a " + $filter('date')(new Date(vm.campeonato.dataFinal), 'dd/MM/yyyy'));
                } else {
                    if (idUsuario === undefined || idUsuario == $rootScope.usuarioLogado.id) {
                        $scope.date = $date;
                        vm.showAdicionarEvento();
                    }
                }
            };

            $scope.inserirEvento = function (date, hora_inicio, hora_fim) {
                vm.inserirEvento(date, hora_inicio, hora_fim);
            };

            $scope.closeSide = function () {
                $mdSidenav('sidenav-evento').close();
            };

            $scope.editarEvento = function (evento) {
                vm.editarEvento(evento);
            };

            $scope.excluirEvento = function (evento) {
                vm.excluirEvento(evento);
            }

        };

        function DialogControllerVisualizarPartida($scope, $mdDialog, tituloModal, eventoSelecionado) {
            $scope.tituloModal = tituloModal;
            $scope.eventoSelecionado = eventoSelecionado;
            $scope.campeonato = vm.campeonato;
            $scope.dataAgendamento = moment(eventoSelecionado.start).format('DD/MM/YYYY (dddd) - HH:mm');

            $scope.cancelarAgendamento = function () {
                var confirm = $mdDialog.prompt(eventoSelecionado)
                    .title($filter('translate')('messages.mensagem_confirma_cancelar_agendamento'))
                    .textContent($filter('translate')('messages.motivo_cancelar_agendamento'))
                    .placeholder($filter('translate')('fields.motivo_cancelar_agendamento'))
                    .ariaLabel($filter('translate')('messages.mensagem_confirma_cancelar_agendamento'))
                    .ok(vm.textoYes)
                    .cancel(vm.textoNo)
                    .theme('player2');

                $mdDialog.show(confirm).then(function (result) {
                    eventoSelecionado.motivo = result;
                    eventoSelecionado.users_id = $rootScope.usuarioLogado.id;
                    Agenda.cancelarAgendamento(eventoSelecionado)
                        .success(function () {
                            vm.carregarEventos();
                            toastr.success($filter('translate')('messages.agendamento_cancelado_sucesso'));
                        })
                        .error(function () {
                            toastr.error($filter('translate')('messages.erro_operacao'));
                        });
                }, function () {
                    console.log('nao cancelou');
                });
            }

            $scope.recusarAgendamento = function () {
                Agenda.recusarAgendamento(eventoSelecionado)
                    .success(function (data) {
                        vm.carregarEventos();
                        toastr.warning($filter('translate')('messages.mensagem_recusa_partida', {
                            nome_adversario: eventoSelecionado.adversario.nome,
                            data_partida: moment(eventoSelecionado.start).format('DD/MM/YYYY (dddd)'),
                            hora_partida: moment(eventoSelecionado.start).format('HH:mm')
                        }));
                        $mdDialog.cancel();
                    })
                    .error(function (error) {
                        toastr.error($filter('translate')('messages.mensagem_recusa_partida_erro'));
                    });
            }

            $scope.cancel = function () {
                $mdDialog.cancel();
            };

        }

        vm.showAdicionarEvento = function () {
            $mdSidenav('sidenav-evento')
                .toggle()
                .then(function () {});

        };

        vm.inserirEvento = function (date, hora_inicio, hora_fim) {
            hora_inicio = moment(hora_inicio, 'HH:mm');
            hora_fim = moment(hora_fim, 'HH:mm');

            hora_inicio = new Date(date.getFullYear(), date.getMonth(), date.getDate(), hora_inicio.hours(), hora_inicio.minutes());
            hora_fim = new Date(date.getFullYear(), date.getMonth(), date.getDate(), hora_fim.hours(), hora_fim.minutes());
            var dados = {};
            dados.hora_inicio = hora_inicio;
            dados.hora_fim = hora_fim;
            dados.data = date;
            dados.idCampeonato = vm.campeonato.id;
            Agenda.addEvent(dados)
                .success(function (data) {
                    vm.carregarEventos();
                    $scope.hora_inicio = {};
                    $scope.hora_fim = {};
                    $mdSidenav('sidenav-evento').close();
                })
                .error(function (data) {
                    toastr.error($filter('translate')(data.error), $filter('translate')('messages.operacao_nao_concluida'));
                });
        };

        vm.carregarEventos = function (idUsuario) {
            vm.eventos = [];

            Agenda.getEventos(vm.campeonato.id, idUsuario)
                .success(function (data) {
                    var background = '';
                    var titulo = '';
                    var status = '';
                    angular.forEach(data, function (evento) {
                        if (evento.situacao == 'ocupado') {
                            background = 'bg-danger';
                            titulo = $filter('translate')('messages.agenda_jogo_contra') + evento.adversario.nome;
                        } else if (evento.situacao == 'pendente') {
                            background = 'bg-warning';
                            titulo = $filter('translate')('messages.agenda_jogo_contra') + evento.adversario.nome;
                        } else {
                            background = 'bg-success';
                            titulo = $filter('translate')('messages.agenda_livre');
                        }
                        var novoEvento = {
                            title: '',
                            start: new Date(evento.hora_inicio),
                            end: new Date(evento.hora_fim),
                            allDay: false,
                            background: background,
                            descricaoEvento: titulo,
                            status: evento.situacao,
                            partida: evento.partida,
                            adversario: evento.adversario,
                            usuario_host: evento.usuario_host,
                            id: evento.idHorario
                        };
                        vm.eventos.push(novoEvento);
                    });
                    $rootScope.$broadcast('carregou_eventos');
                });


        };

        vm.showEditarEvento = function () {
            $mdSidenav('sidenav-evento-editar')
                .toggle()
                .then(function () {});
        };

        vm.editarEvento = function (evento) {
            Agenda.editEvento(evento)
                .success(function (data) {
                    vm.carregarEventos();
                    $mdSidenav('sidenav-evento-editar').close();
                })
                .error(function (data) {
                    toastr.error($filter('translate')(data.error), $filter('translate')('messages.operacao_nao_concluida'));
                });
        };

        vm.excluirEvento = function (evento) {
            Agenda.deleteEvento(evento.id)
                .success(function (data) {
                    vm.carregarEventos();
                    toastr.success($filter('translate')('messages.horario_excluido_sucesso'));
                    $mdSidenav('sidenav-evento-editar').close();
                })
                .error(function (data) {
                    toastr.error($filter('translate')(data.error), $filter('translate')('messages.operacao_nao_concluida'));
                });
        };

        vm.agendarPartida = function (ev, partida) {
            var anonimo = false;
            angular.forEach(partida.usuarios, function (usuarioDaPartida) {
                if (usuarioDaPartida.anonimo_id != null) {
                    toastr.warning($filter('translate')('messages.usuario_anonimo_sem_agenda'));
                    anonimo = true;
                }
            });

            if (!anonimo) {
                var idUsuario;
                var nomeUsuario;
                var podeIr = true;

                angular.forEach(partida.usuarios, function (usuarioDaPartida) {
                    if (podeIr) {
                        if (usuarioDaPartida.users_id != $rootScope.usuarioLogado.id) {
                            idUsuario = usuarioDaPartida.users_id;
                            nomeUsuario = usuarioDaPartida.nome;
                            podeIr = false;
                        }
                    }
                });

                $mdDialog.show({
                        locals: {
                            tituloModal: $filter('translate')('messages.agenda_adversario') + ' ' + nomeUsuario,
                            partida: partida,
                            idUsuario: idUsuario
                        },
                        controller: DialogControllerAgendarPartida,
                        templateUrl: 'app/components/campeonato/agendamento/agendaAdversario.html',
                        parent: angular.element(document.body),
                        targetEvent: ev,
                        clickOutsideToClose: true,
                        fullscreen: true // Only for -xs, -sm breakpoints.
                    })
                    .then(function () {

                    }, function () {

                    });
            }

        };

        function DialogControllerAgendarPartida($scope, $mdDialog, tituloModal, partida, idUsuario) {
            $scope.indice_mes = 0;
            $scope.listaMeses = [];
            var mesInicio = moment(vm.campeonato.dataInicio).startOf('month');
            var mesFim = moment(vm.campeonato.dataFinal).startOf('month');
            while (mesInicio < mesFim) {
                var itemMes = {};
                itemMes.formatado = mesInicio.format('MMMM/YYYY');
                itemMes.formatadoMobile = mesInicio.format('MMM/YY');
                itemMes.completo = mesInicio.format('YYYY-MM-DD h:mm:ss');
                var inicioMesAtual = moment().startOf('month').format('YYYY-MM-DD h:mm:ss');
                if (inicioMesAtual == itemMes.completo) {
                    $scope.mesEscolhido = itemMes.completo;
                    $scope.indice_mes = $scope.listaMeses.length;
                }
                $scope.listaMeses.push(itemMes);
                mesInicio.add(1, 'M');
            }


            Agenda.listaAgenda(vm.campeonato.id, idUsuario)
                .success(function (data) {
                    $scope.listaHorarios = data;
                });

            $scope.tituloModal = tituloModal;
            $scope.partida = partida;

            $scope.cancel = function () {
                $mdDialog.cancel();
            };


            $scope.exibeProximoMes = function () {
                if ($scope.indice_mes < ($scope.listaMeses.length - 1)) {
                    var mesAtualTemporario = $scope.listaMeses[$scope.indice_mes + 1];
                    $scope.mesEscolhido = mesAtualTemporario.completo;
                    $scope.mudarMesAgenda();
                }
            };

            $scope.exibeMesAnterior = function () {
                if ($scope.indice_mes > 0) {
                    var mesAtualTemporario = $scope.listaMeses[$scope.indice_mes - 1];
                    $scope.mesEscolhido = mesAtualTemporario.completo;
                    $scope.mudarMesAgenda();
                }
            };

            $scope.mudarMesAgenda = function () {
                Agenda.listaAgenda(vm.campeonato.id, idUsuario, $scope.mesEscolhido)
                    .success(function (data) {
                        $scope.listaHorarios = data;
                        angular.forEach($scope.listaMeses, function (mes, key) {
                            if (mes.completo == $scope.mesEscolhido) {
                                $scope.indice_mes = key;
                            }
                        });
                    });
            };

            $scope.marcarJogo = function (data, horario) {
                $mdSidenav('right')
                    .toggle()
                    .then(function () {
                        $scope.dataAgendamento = data;
                        $scope.intervaloAgendamento = horario;
                        $scope.campeonato = vm.campeonato;
                        $scope.intervalosDisponiveis = [];
                        var horarioInicio = moment(horario.hora_inicio, "HH:mm");
                        var horarioFim = moment(horario.hora_fim, 'HH:mm');
                        while (horarioInicio < horarioFim) {
                            $scope.intervalosDisponiveis.push(horarioInicio.format('HH:mm'));
                            horarioInicio = horarioInicio.add(30, 'minutes');
                        }
                    });
            };

            $scope.atualizaHoraFinal = function () {
                $scope.horaFinalAgendamento = moment($scope.horaInicioAgendamento, "HH:mm").add(30, 'minutes').format('HH:mm');
            };


            $scope.salvarAgendamento = function () {
                var marcacao = {};
                marcacao.partidas_id = $scope.partida.id;
                marcacao.usuario_host = $rootScope.usuarioLogado.id;
                marcacao.usuario_convidado = idUsuario;
                marcacao.horario_inicio = moment($scope.dataAgendamento + ' ' + $scope.horaInicioAgendamento).format('YYYY-MM-DD HH:mm:ss');
                marcacao.duracao = 30;
                marcacao.campeonato_id = vm.campeonato.id;

                Agenda.agendarPartida(marcacao)
                    .success(function (data) {
                        Agenda.listaAgenda(vm.campeonato.id, idUsuario)
                            .success(function (data) {
                                $scope.listaHorarios = data;
                            });
                        $mdSidenav('right').close()
                    })
                    .error(function (error) {
                        toastr.error($filter('translate')(error.error), $filter('translate')('messages.operacao_invalida'));
                    });
            };

            $scope.closeSide = function () {
                $mdSidenav('right').close()
            };

        };

        /***** AgendaController FIM *****/


                }]);

}());

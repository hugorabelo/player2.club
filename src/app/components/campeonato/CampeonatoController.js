/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('CampeonatoController', ['$scope', '$rootScope', '$filter', '$mdDialog', '$translate', '$state', '$mdSidenav', '$stateParams', '$location', '$timeout', 'toastr', 'localStorageService', 'Campeonato', 'UserPlataforma', 'Usuario', 'Partida', 'ModeloCampeonato', 'Plataforma', 'Jogo', 'CampeonatoTipo', 'CampeonatoUsuario', 'Time', function ($scope, $rootScope, $filter, $mdDialog, $translate, $state, $mdSidenav, $stateParams, $location, $timeout, toastr, localStorageService, Campeonato, UserPlataforma, Usuario, Partida, ModeloCampeonato, Plataforma, Jogo, CampeonatoTipo, CampeonatoUsuario, Time) {

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

        vm.carregaListaCampeonatos();

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
            angular.forEach(listaDeGrupos, function (item) {
                if (!vm.fase_atual.matamata) {
                    vm.rodada_atual.push(1);
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

        vm.exibeRodadaAnteriorGerenciar = function (indice) {
            if (vm.rodada_atual_gerenciar > 1) {
                vm.rodada_atual_gerenciar = vm.rodada_atual_gerenciar - 1;
                vm.carregaPartidas();
            }
        };

        vm.exibeProximaRodadaGerenciar = function (indice) {
            if (vm.rodada_atual_gerenciar < vm.rodada_maxima) {
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
                                vm.getPlataformasDoUsuario(participante);
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
                                vm.getPlataformasDoUsuario(vm.participanteDestaque);
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
                                toastr.error($filter('translate')(data.messages[0]), $filter('translate')('messages.operacao_nao_concluida'));
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
                        toastr.error($filter('translate')(data.messages[0]), $filter('translate')('messages.operacao_nao_concluida'));
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

        vm.salvarPlacar = function (partida) {
            partida.usuarioLogado = $rootScope.usuarioLogado.id;
            Partida.salvarPlacar(partida)
                .success(function () {
                    toastr.success($filter('translate')('messages.sucesso_placar'));
                    vm.carregaPartidasDoUsuario(vm.partidasAbertas);
                    vm.carregaPartidasEmAberto();
                })
                .error(function (data) {
                    toastr.error($filter('translate')(data.errors[0]));
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
                    vm.carregaPartidasDoUsuario(vm.partidasAbertas);
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

        vm.aplicarWO = function (partida) {

        };


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
                                //TODO se houver erro de usuário tentando se inscrever em campeonato em equipe, exibir mensagem aqui. Fazer um if com o retorno
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
                userPlataforma.users_id = $rootScope.usuarioLogado.id;
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
                    vm.fase_atual = vm.campeonatoFases[vm.indice_fase];
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
            $mdDialog.show({
                    locals: {
                        tituloModal: 'fields.aplicar_wo',
                        partida: partida
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
        };


        function DialogControllerWO($scope, $mdDialog, tituloModal, partida) {
            $scope.tituloModal = tituloModal;
            $scope.partida = partida;

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
                }]);

}());

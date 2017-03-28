/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('CampeonatoController', ['$scope', '$rootScope', '$filter', '$mdDialog', '$translate', '$state', '$mdSidenav', '$stateParams', 'toastr', 'Campeonato', 'UserPlataforma', 'Usuario', 'Partida', 'ModeloCampeonato', 'Plataforma', 'Jogo', 'CampeonatoTipo', 'CampeonatoUsuario', function ($scope, $rootScope, $filter, $mdDialog, $translate, $state, $mdSidenav, $stateParams, toastr, Campeonato, UserPlataforma, Usuario, Partida, ModeloCampeonato, Plataforma, Jogo, CampeonatoTipo, CampeonatoUsuario) {

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

        vm.partidasDaRodada = [];

        vm.partidasAbertas = false;

        vm.opcoesEditor = {
            language: 'pt_br',
            //                toolbarButtons: ["bold", "italic", "underline", "|", "align", "formatOL", "formatUL"],
        };

        vm.carregaCampeonato = function () {
            vm.carregaInformacoesCampeonato(vm.idCampeonato);
            vm.currentNavItem = 'tabela';
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
            Campeonato.getInformacoes(id)
                .success(function (data) {
                    vm.campeonato = data;
                    vm.carregaFases(id);
                    vm.getParticipantes(id);
                    vm.carregaAdministradores(id);
                    vm.carregaPartidasDoUsuario(vm.partidasAbertas);
                    vm.carregaPartidasContestadas();
                    vm.campeonato.usuarioAdministrador = true;
                });
        };

        vm.getParticipantes = function (id) {
            Campeonato.getParticipantes(id)
                .success(function (data) {
                    vm.campeonato.participantes = data;
                    vm.campeonato.usuarioInscrito = false;
                    angular.forEach(data, function (usuario) {
                        if (usuario.id == $rootScope.usuarioLogado.id) {
                            vm.campeonato.usuarioInscrito = true;
                        }
                    });
                });
        };

        vm.carregaAdministradores = function (id) {
            Campeonato.getAdministradores(id)
                .success(function (data) {
                    vm.campeonato.campeonatoAdministradores = data;
                    vm.campeonato.usuarioAdministrador = false;
                    angular.forEach(data, function (administrador) {
                        if (administrador.users_id == $rootScope.usuarioLogado.id) {
                            vm.campeonato.usuarioAdministrador = true;
                        }
                    });
                });
        };

        vm.carregaListaCampeonatos = function () {
            Campeonato.get()
                .success(function (data) {
                    vm.campeonatos = data;
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
                var lowercaseNome = angular.lowercase(participante.nome);
                return (lowercaseNome.indexOf(lowercaseQuery) >= 0);
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
            console.log(fase);
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
                        Campeonato.abreFase(fase.dadosFase)
                            .success(function (data) {
                                fase.aberta = true;
                            }).error(function (data, status) {
                                toastr.error($filter('translate')(data.messages[0]), $filter('translate')('messages.operacao_nao_concluida'));
                            });
                    }
                },
                function () {

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
                        toastr.error($filter('translate')(data.messages[0]), $filter('translate')('messages.operacao_nao_concluida'));
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

        vm.carregaPartidasDoUsuario = function (abertas) {
            if (abertas === undefined) {
                abertas = false;
            }
            if (abertas) {
                Usuario.getPartidasEmAberto($rootScope.usuarioLogado.id)
                    .success(function (data) {
                        vm.partidasDoUsuario = data;
                        vm.partidasAbertas = true;
                    });
            } else {
                Usuario.getPartidas($rootScope.usuarioLogado.id, vm.campeonato.id)
                    .success(function (data) {
                        vm.partidasDoUsuario = data;
                        vm.partidasAbertas = false;
                    });

            }
        };

        //        vm.carregaPartidasEmAbertoDoUsuario = function () {
        //            Usuario.getPartidasEmAberto($rootScope.usuarioLogado.id)
        //                .success(function (data) {
        //                    vm.partidasDoUsuario = data;
        //                });
        //        };

        vm.carregaPartidasContestadas = function () {
            Campeonato.getPartidasContestadas(vm.campeonato.id)
                .success(function (data) {
                    vm.partidasContestadas = data;
                });
        };

        vm.salvarPlacar = function (partida) {
            partida.usuarioLogado = $rootScope.usuarioLogado.id;
            Partida.salvarPlacar(partida)
                .success(function () {
                    toastr.success($filter('translate')('messages.sucesso_placar'));
                    vm.carregaPartidasDoUsuario(vm.partidasAbertas);
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

        vm.cancelarPlacarContestado = function (partida) {
            partida.edita_contestacao = true;
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
                })
                .error(function (data) {});
        };

        vm.salvarPlacarContestacao = function (partida) {
            partida.usuarioLogado = $rootScope.usuarioLogado.id;
            Partida.salvarPlacar(partida)
                .success(function () {
                    vm.confirmarPlacarContestacao(partida.id);
                })
                .error(function (data) {
                    //TODO melhorar a exibição deste erro
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

        vm.inscreverCampeonato = function (ev, id) {
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
                            vm.cadastraGamertagInscricao(ev);
                        });
                },
                function () {

                });
        };

        vm.cadastraGamertagInscricao = function (ev) {
            var confirm = $mdDialog.prompt()
                .title(vm.erro_inscricao)
                .textContent(vm.usuario_sem_plataforma_um + vm.campeonato.plataforma.descricao + vm.usuario_sem_plataforma_dois)
                .placeholder(vm.gamertag)
                .ariaLabel(vm.gamertag)
                .initialValue('')
                .targetEvent(ev)
                .ok(vm.saveField)
                .cancel(vm.textoClose);

            $mdDialog.show(confirm).then(function (result) {
                if (result == undefined) {
                    toastr.error($filter('translate')('messages.usuario_sem_plataforma_um') + vm.campeonato.plataforma.descricao, $filter('translate')('messages.erro_inscricao'));
                } else {
                    var userPlataforma = {};
                    userPlataforma.plataformas_id = vm.campeonato.plataforma.id;
                    userPlataforma.users_id = $rootScope.usuarioLogado.id;
                    userPlataforma.gamertag = result;
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

                }
            }, function () {
                toastr.error($filter('translate')('messages.usuario_sem_plataforma_um') + vm.campeonato.plataforma.descricao, $filter('translate')('messages.erro_inscricao'));
            });
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
                    })
                    .error(function (data) {

                    });
            }, function () {

            });
        };
                }]);
}());

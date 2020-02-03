/*global angular */
(function () {
    'use strict';

    angular.module('player2')
        .controller('CriarCampeonatoController', ['$scope', '$rootScope', '$translate', '$location', '$filter', 'toastr', 'Campeonato', 'Plataforma', 'Jogo', 'CampeonatoTipo', 'ModeloCampeonato', 'Tutorial', function ($scope, $rootScope, $translate, $location, $filter, toastr, Campeonato, Plataforma, Jogo, CampeonatoTipo, ModeloCampeonato, Tutorial) {

            var vm = this;

            vm.barConfig = {
                group: 'criterios',
                animation: 150,
                onSort: function (evt) {}
            };

            vm.campeonato = {};
            vm.criteriosClassificacaoSelecionados = [];
            vm.posicoesPremiacaoSelecionadas = [];

            vm.opcoesEditor = {
                language: 'pt_br',
                //                toolbarButtons: ["bold", "italic", "underline", "|", "align", "formatOL", "formatUL"],
            };

            $scope.$watch(angular.bind(vm, function () {
                if (vm.campeonato.detalhes !== undefined) {
                    return vm.campeonato.detalhes.ida_volta;
                }
            }), function () {
                if (vm.campeonato.detalhes !== undefined) {
                    if (!vm.campeonato.detalhes.ida_volta) {
                        vm.campeonato.detalhes.fora_casa = {};
                    }
                }
            });

            vm.criaZonaClassificacao = function () {
                vm.pontosZonaClassificacao = [];
                var i;
                for (i = 0; i < vm.campeonato.zona_classificacao; i = i + 1) {
                    vm.pontosZonaClassificacao[i] = 0;
                }
            };

            vm.create = function () {
                $rootScope.loading = true;
                vm.carregaTiposDeAcessoDoCampeonato();
                vm.carregaTiposDeCompetidores();
                vm.carregaPlataformas();
                $rootScope.loading = false;
            };

            vm.carregaPlataformas = function () {
                $rootScope.loading = true;
                Plataforma.get()
                    .success(function (data) {
                        vm.plataformas = data;
                        vm.campeonato = {};
                        vm.messages = null;
                        $rootScope.loading = false;
                    });
            };

            vm.carregaJogosDaPlataforma = function () {
                $rootScope.loading = true;
                Plataforma.getJogos(vm.campeonato.plataformas_id, true)
                    .success(function (data) {
                        vm.jogos = data;
                        if (vm.jogos.length > 0) {
                            vm.campeonato.jogos_id = vm.jogos[0].id;
                            vm.carregaTiposDeCampeonatoDoJogo();
                        }
                        vm.messages = null;
                        $rootScope.loading = false;
                    });
            };

            vm.carregaTiposDeCampeonatoDoJogo = function () {
                $rootScope.loading = true;
                Jogo.getTiposDeCampeonato(vm.campeonato.jogos_id)
                    .success(function (data) {
                        vm.campeonatoTipos = data;
                        if (vm.campeonatoTipos.length > 0) {
                            vm.campeonato.campeonato_tipos_id = vm.campeonatoTipos[0].id;
                            vm.carregaDetalhesCampeonato();
                        }
                        vm.messages = null;
                        $rootScope.loading = false;
                    });
            };

            vm.carregaTiposDeCompetidores = function () {
                Campeonato.getTiposDeCompetidores()
                    .success(function (data) {
                        vm.tiposDeCompetidores = data;
                        vm.messages = null;
                        $rootScope.loading = false;
                    });
            };

            vm.carregaTiposDeAcessoDoCampeonato = function () {
                Campeonato.getTiposDeAcessoDoCampeonato()
                    .success(function (data) {
                        vm.tiposDeAcessosDoCampeonato = data;
                        vm.messages = null;
                        $rootScope.loading = false;
                    });
            };

            vm.carregaDetalhesCampeonato = function () {
                $rootScope.loading = true;
                CampeonatoTipo.edit(vm.campeonato.campeonato_tipos_id)
                    .success(function (data) {
                        vm.templateDetalhes = data.arquivo_detalhes;
                        vm.carregaCriteriosClassificacao(data.modelo_campeonato_id);
                        vm.messages = null;
                        $rootScope.loading = false;
                    });
                $rootScope.loading = false;
            };

            vm.carregaCriteriosClassificacao = function (id) {
                $rootScope.loading = true;
                ModeloCampeonato.getCriteriosClassificacao(id)
                    .success(function (data) {
                        vm.criteriosClassificacao = data;
                        vm.messages = null;
                        $rootScope.loading = false;
                    });
                $rootScope.loading = false;
            };

            vm.salvarCampeonato = function () {
                vm.loadingSalvarCampeonato = true;
                vm.atualizaCriteriosClassificacao();
                vm.atualizaPosicoesPremiacoes();
                vm.campeonato.criador = $rootScope.usuarioLogado.id;
                Campeonato.save(vm.campeonato)
                    .success(function (data) {
                        vm.loadingSalvarCampeonato = false;
                        $location.path('/campeonato/' + data.id);
                    }).error(function (data, status) {
                        var listaErros = '';
                        angular.forEach(data.errors, function (erro) {
                            listaErros += "<br>" + $filter('translate')(erro);
                        });
                        toastr.error('<h3>' + $filter('translate')('messages.erro_configuracao') + '</h3>' + listaErros);
                        vm.messages = data.errors;
                        vm.status = status;
                        vm.loadingSalvarCampeonato = false;
                    });
            };

            vm.cancel = function () {
                $location.path('/home');
            };

            vm.atualizaCriteriosClassificacao = function () {
                vm.campeonato.criteriosClassificacaoSelecionados = vm.criteriosClassificacaoSelecionados;
            };

            vm.adicionaNovoCriterio = function() {
                var criterioExiste = false;
                var novoObjeto = JSON.parse(vm.novoCriterio);
                angular.forEach(vm.criteriosClassificacaoSelecionados, function (criterio) {
                    if (novoObjeto.id === criterio.id) {
                        criterioExiste = true;
                    }
                });
                if(!criterioExiste) {
                    vm.criteriosClassificacaoSelecionados.push(novoObjeto);
                }
                vm.novoCriterio = {};
            };

            vm.removerCriterio = function(idCriterio) {
                angular.forEach(vm.criteriosClassificacaoSelecionados, function (criterio, index) {
                    if (idCriterio === criterio.id) {
                        vm.criteriosClassificacaoSelecionados.splice(index, 1);
                    }
                });
            }

            vm.openCalendar = function ($event, objeto) {
                $event.preventDefault();
                $event.stopPropagation();

                if (objeto === 'inicio') {
                    vm.openedInicio = true;
                } else {
                    vm.openedFim = true;
                }
            };

            vm.dateOptions = {
                formatYear: 'yy',
                startingDay: 1
            };

            vm.iniciaValoresPremiacao = function() {
                vm.campeonato.detalhes_premiacao = {};
                vm.campeonato.detalhes_premiacao.valor_inscricao = 0;
                vm.campeonato.detalhes_premiacao.taxa_administracao = 0
                vm.campeonato.detalhes_premiacao.valor_total_arrecadado = 0;
                vm.campeonato.detalhes_premiacao.valor_administrador = 0;
                vm.campeonato.detalhes_premiacao.valor_player2 = 0;
            }

            vm.atualizaValoresPremiacao = function() {
                if (vm.campeonato !== undefined && vm.campeonato.detalhes !== undefined && vm.campeonato.detalhes.quantidade_competidores !== undefined 
                    && vm.campeonato.detalhes_premiacao != undefined) {
                    var taxa_player2 = 0.2;
                    vm.campeonato.detalhes_premiacao.valor_total_arrecadado = vm.campeonato.detalhes_premiacao.valor_inscricao * vm.campeonato.detalhes.quantidade_competidores;
                    if(isNaN(vm.campeonato.detalhes_premiacao.valor_total_arrecadado)) {
                        vm.campeonato.detalhes_premiacao.valor_total_arrecadado = 0;
                    }
                    vm.campeonato.detalhes_premiacao.valor_administrador = vm.campeonato.detalhes_premiacao.valor_total_arrecadado * vm.campeonato.detalhes_premiacao.taxa_administracao;
                    if(isNaN(vm.campeonato.detalhes_premiacao.valor_administrador)) {
                        vm.campeonato.detalhes_premiacao.valor_administrador = 0;
                    }
                    vm.campeonato.detalhes_premiacao.valor_player2 = vm.campeonato.detalhes_premiacao.valor_total_arrecadado * taxa_player2;
                    if(isNaN(vm.campeonato.detalhes_premiacao.valor_player2)) {
                        vm.campeonato.detalhes_premiacao.valor_player2 = 0;
                    }

                    //Se a quantidade de competidores for alterada para um valor menor do que a quantidade de posições por premiação inserida, remover as posições excedentes
                    if(vm.campeonato.detalhes.quantidade_competidores < vm.posicoesPremiacaoSelecionadas.length) {
                        var quantidade_extra = vm.posicoesPremiacaoSelecionadas.length - vm.campeonato.detalhes.quantidade_competidores;
                        vm.posicoesPremiacaoSelecionadas.splice(vm.campeonato.detalhes.quantidade_competidores, quantidade_extra);
                    }

                    var valor_distribuido_premiacao = 0;

                    angular.forEach(vm.posicoesPremiacaoSelecionadas, function (posicaoPremiacao) {
                        valor_distribuido_premiacao += posicaoPremiacao.percentual * vm.campeonato.detalhes_premiacao.valor_total_arrecadado;
                    });

                    vm.campeonato.detalhes_premiacao.total_distribuido = vm.campeonato.detalhes_premiacao.valor_administrador + vm.campeonato.detalhes_premiacao.valor_player2 + 
                                                                         valor_distribuido_premiacao;

                    if(vm.campeonato.detalhes_premiacao.total_distribuido < vm.campeonato.detalhes_premiacao.valor_total_arrecadado) {
                        vm.status_distribuicao_premiacao = 'menor';
                    } else if(vm.campeonato.detalhes_premiacao.total_distribuido > vm.campeonato.detalhes_premiacao.valor_total_arrecadado) {
                        vm.status_distribuicao_premiacao = 'maior';
                    } else {
                        vm.status_distribuicao_premiacao = 'igual';
                    }

                }
            }

            vm.inserePosicaoPremiacao = function() {
                if(vm.campeonato.detalhes === undefined) {
                    toastr.error('<h3>' + $filter('translate')('messages.erro_configuracao') + '</h3>' + $filter('translate')('messages.quantidade_competidores_indefinida'));
                    return;
                }
                var novaPosicao = vm.posicoesPremiacaoSelecionadas.length + 1;
                if(novaPosicao > vm.campeonato.detalhes.quantidade_competidores) {
                    return;
                }
                var novaPosicao = {};
                novaPosicao.percentual = 0;
                vm.posicoesPremiacaoSelecionadas.push(novaPosicao);
            }

            vm.removerPosicaoPremiacao = function(index) {
                vm.posicoesPremiacaoSelecionadas.splice(index, 1);
                vm.atualizaValoresPremiacao();
            }

            vm.atualizaPosicoesPremiacoes = function () {
                vm.campeonato.posicoes_premiacao_selecionadas = vm.posicoesPremiacaoSelecionadas;
            };

        }]);
        
}());

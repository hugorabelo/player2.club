/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('TopNavController', ['$rootScope', '$scope', '$translate', '$location', '$mdDateLocale', '$filter', '$mdMedia', '$mdSidenav', '$http', '$window', '$mdDialog', 'Auth', 'Usuario', 'Atividade', 'Partida', 'Agenda', function ($rootScope, $scope, $translate, $location, $mdDateLocale, $filter, $mdMedia, $mdSidenav, $http, $window, $mdDialog, Auth, Usuario, Atividade, Partida, Agenda) {

        var vm = this;

        $scope.$watch(function () {
            return $mdMedia('xs');
        }, function (telaMobile) {
            $rootScope.telaMobile = telaMobile;
        });

        /*Verificar ações não realizadas pelo usuário
         * Partida agendada não realizada
         * Avaliação de usuário não realizada
         * Partida não confirmada
         */

         vm.exibeModalPendencias = function(pendenciasUsuario) {
            $mdDialog.show({
                locals: {
                    tituloModal: 'fields.pendencias',
                    pendenciasUsuario: pendenciasUsuario
                },
                controller: DialogControllerPendencias,
                templateUrl: 'app/components/comum/pendencias.html',
                parent: angular.element(document.body),
                targetEvent: null,
                clickOutsideToClose: false,
                escapeToClose: false,
                fullscreen: true,
                multiple: true
            })
            .then(function () {

            }, function () {

            });
         }

         vm.ocultaModalPendencias = function() {
             $mdDialog.hide();
         }

         function DialogControllerPendencias($scope, $mdDialog, tituloModal, pendenciasUsuario) {
            $scope.tituloModal = tituloModal;
            $scope.pendenciasUsuario = pendenciasUsuario;
            $scope.editPlacar = {};
            $scope.editMotivo = {};

            $scope.fechar = function () {
                $mdDialog.hide();
            }

            $scope.confirmarPlacar = function (idPartida) {
                var dados = {};
                dados.id_partida = idPartida;
                Partida.confirmarPlacar(dados)
                    .success(function () {
                        vm.atualizaPendencias();
                    })
                    .error(function (data) {
                        toastr.error($filter('translate')(data.errors[0]));
                    });
            }

            // $scope.contestarPlacar = function(idPartida) {
            //     vm.contestacao_resultado = {};
            //     vm.contestacao_resultado.partidas_id = idPartida;
            //     vm.contestacao_resultado.users_id = $rootScope.usuarioLogado.id;
            //     $mdDialog.show({
            //             locals: {
            //                 tituloModal: 'messages.partida_contestar',
            //                 contestacao_resultado: vm.contestacao_resultado
            //             },
            //             controller: DialogControllerContestacao,
            //             templateUrl: 'app/components/campeonato/formContestacaoResultado.html',
            //             parent: angular.element(document.body),
            //             targetEvent: ev,
            //             clickOutsideToClose: true,
            //             fullscreen: true // Only for -xs, -sm breakpoints.
            //         })
            //         .then(function () {
            //             toastr.success($filter('translate')('messages.sucesso_contestacao_solicitada'));
            //         }, function () {

            //         });
            // }

            $scope.abrirFormInserirPlacar = function(partida) {
                $scope.editPlacar[partida.id] = true;
            }

            $scope.abrirFormMotivo = function(partida) {
                $scope.editMotivo[partida.id] = true;
            }

            $scope.salvarPlacar = function(partida) {
                partida.id = partida.partidas_id;
                Partida.salvarPlacar(partida)
                    .success(function () {
                        vm.atualizaPendencias();
                    })
                    .error(function (data) {
                        console.log(data);
                    });
            }

            $scope.cancelarPlacar = function(partida) {
                $scope.editPlacar[partida.id] = false;
            }

            $scope.salvarMotivo = function(partida) {
                console.log(partida);
                Agenda.justificaPartidaNaoRealizada(partida)
                    .success(function(data) {
                        vm.atualizaPendencias();
                    })
                    .error(function (error) {
                        console.log(error);
                    });
            }

            $scope.cancelarMotivo = function(partida) {
                $scope.editMotivo[partida.id] = false;
            }
        };

        vm.verificaPendencias = function () {
            vm.pendenciasUsuario = {};
            Usuario.verificarPendencias()
                .success(function(data) {
                    vm.getPendencias(data);
                    if(vm.pendenciasUsuario.partidasNaoRealizadas || vm.pendenciasUsuario.partidasNaoConfirmadas) {
                        vm.exibeModalPendencias(vm.pendenciasUsuario);
                    }
                })
                .error(function (error) {
                    console.log(error);
                });
        };

        vm.getPendencias = function(data) {
            vm.pendenciasUsuario.partidasNaoRealizadas = data.partidas_nao_realizadas;
            vm.pendenciasUsuario.partidasNaoConfirmadas = data.partidas_nao_confirmadas;
        }

        vm.atualizaPendencias = function() {
            Usuario.verificarPendencias()
                .success(function(data) {
                    vm.getPendencias(data);
                    if(!vm.pendenciasUsuario.partidasNaoRealizadas && !vm.pendenciasUsuario.partidasNaoConfirmadas) {
                        vm.ocultaModalPendencias();
                    }
                })
                .error(function (error) {
                    console.log(error);
                });
        };

        $scope.$on('userProfileSet', function () {
            vm.verificaPendencias();
        });

        $scope.$on('salvouContestacaoPendencia', function () {
            vm.atualizaPendencias();
        });


        /****** FIM DO VERIFICAR PENDÊNCIAS */

        var originatorEv;

        vm.itensPesquisa = {};

        vm.openMenu = function ($mdOpenMenu, ev) {
            originatorEv = ev;
            $mdOpenMenu(ev);
        };

        vm.searchBox = {
            isOpen: false,
            count: 0
        };

        vm.mudaIdioma = function (idioma) {
            $translate.use(idioma);

            if (idioma === 'en_us') {
                moment.locale('en-us');

                var localeDate = moment.localeData();

                $mdDateLocale.months = localeDate._months;
                $mdDateLocale.shortMonths = localeDate._monthsShort;
                $mdDateLocale.days = localeDate._weekdays;
                $mdDateLocale.shortDays = localeDate._weekdaysMin;

                $mdDateLocale.msgCalendar = $translate.instant('MSG_CALENDAR');
                $mdDateLocale.msgOpenCalendar = $translate.instant('MSG_OPEN_CALENDAR');


                $http.get('api/mudaIdioma/en');

            } else if (idioma === 'pt_br') {
                moment.locale('pt-br');

                $mdDateLocale.formatDate = function (date) {
                    return date ? moment(date).format('DD/MM/YYYY') : '';
                };

                $mdDateLocale.parseDate = function (dateString) {
                    var m = moment(dateString, 'DD/MM/YYYY', true);
                    return m.isValid() ? m.toDate() : new Date(NaN);
                };

                $mdDateLocale.months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                $mdDateLocale.shortMonths = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                $mdDateLocale.days = ['Domingo', 'Segunda-Feira', 'Terça-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'Sábado'];
                $mdDateLocale.shortDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];


                $http.get('api/mudaIdioma/pt-br');
            }

        };

        vm.logout = function () {
            Auth.logout();
        };

        vm.getItensPesquisa = function (texto) {
            if (texto != '') {
                Atividade.getPesquisaveis(texto)
                    .success(function (data) {
                        vm.itensPesquisa = data;
                    });
            }
        };

        vm.querySearch = function (query) {
            var results = query ? vm.itensPesquisa.filter(vm.createFilterFor(query)) : vm.itensPesquisa,
                deferred;
            return results;
        };

        vm.createFilterFor = function (query) {
            var lowercaseQuery = angular.lowercase(query);

            return function filterFn(item) {
                var lowercaseNome = angular.lowercase(item.descricao);
                return (lowercaseNome.indexOf(lowercaseQuery) >= 0);
            };

        };

        vm.searchTextChange = function (text) {
            vm.getItensPesquisa(text);
        };

        vm.selectedItemChange = function (item) {
            $location.path('/' + item.tipo + '/' + item.id);
        };

        vm.isOpenSideNav = function () {
            return $mdSidenav('sideNavPrincipal').isOpen();
        };

        vm.toggleSideNav = buildToggler('sideNavPrincipal');

        function buildToggler(navID) {
            return function () {
                $mdSidenav(navID)
                    .toggle()
                    .then(function () {});
            };
        };

        vm.closeSideNav = function () {
            $mdSidenav('sideNavPrincipal').close()
                .then(function () {
                    //          $log.debug("close LEFT is done");
                });
        };

        vm.getNotificacoesDoUsuario = function (tipo) {
            if (tipo != undefined) {
                tipo = 'lidas';
            }
            Usuario.getNotificacoes(tipo)
                .success(function (data) {
                    vm.notificacoesUsuario = data;
                    vm.quantidadeNotificacoesNaoLidas = 0;
                    angular.forEach(vm.notificacoesUsuario, function (notificacao) {
                        if (notificacao.nome_fase != null && notificacao.nome_fase != undefined) {
                            notificacao.nome_fase = $filter('translate')(notificacao.nome_fase);
                        }
                        if (!notificacao.lida) {
                            vm.quantidadeNotificacoesNaoLidas++;
                        }
                    });
                });
        };

        vm.exibeDetalhesNotificacao = function (notificacao) {
            Usuario.lerNotificacao(notificacao)
                .success(function (data) {
                    switch (notificacao.tipo_evento) {
                        case "salvou_placar":
                        case "confirmou_placar":
                        case "contestou_resultado":
                            $location.path('home/partidas_usuario');
                            break;
                        case "fase_iniciada":
                        case "fase_encerrada":
                        case "fase_encerramento_breve":
                        case "sorteou_clubes":
                            $location.path('campeonato/' + notificacao.item_id);
                            break;
                        case "seguir_usuario":
                            $location.path('profile/' + notificacao.id_remetente);
                            break;
                        case "convite_equipe":
                        case "solicitacao_equipe":
                        case "aceitacao_equipe":
                        case "convite_equipe_aceito":
                            $location.path('equipe/' + notificacao.item_id);
                            break;
                        case "convite_campeonato":
                            $location.path('campeonato/' + notificacao.item_id);
                            break;
                    }
                });
        };

        $rootScope.$on('$stateChangeSuccess', function () {
            vm.getNotificacoesDoUsuario();
            vm.getConversasDoUsuario();
        });

        vm.exibeData = function (data) {
            var dataExibida = moment(data, "YYYY-MM-DD HH:mm:ss").toDate();
            return $filter('date')(dataExibida, 'dd/MM/yyyy HH:mm');
        };

        vm.getConversasDoUsuario = function () {
            Usuario.getConversas()
                .success(function (data) {
                    vm.conversasUsuario = data;
                    vm.quantidadeMensagensNaoLidas = 0;
                    angular.forEach(vm.conversasUsuario, function (conversa) {
                        if (conversa.nao_lidas > 0) {
                            vm.quantidadeMensagensNaoLidas += conversa.nao_lidas;
                        }
                    });
                });
        };

    }]);
}());

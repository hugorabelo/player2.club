/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('EquipeController', ['$scope', '$rootScope', '$mdDialog', '$translate', '$location', '$q', '$mdSidenav', '$stateParams', '$filter', '$interval', 'toastr', 'localStorageService', 'Usuario', 'Equipe',
        function ($scope, $rootScope, $mdDialog, $translate, $location, $q, $mdSidenav, $stateParams, $filter, $interval, toastr, localStorageService, Usuario, Equipe) {
            var vm = this;

            vm.equipe = {};

            vm.idEquipe = $stateParams.idEquipe;
            if (vm.idEquipe !== undefined) {
                Equipe.show(vm.idEquipe)
                    .success(function (data) {
                        vm.equipe = data;
                        vm.getFuncoesEquipe();
                    });
            }

            vm.getEquipesUsuario = function (idUsuario) {
                Usuario.getEquipes(idUsuario)
                    .success(function (data) {
                        vm.equipesDoUsuario = data;
                    })
                    .error(function (error) {});
            };

            vm.getFuncoesEquipe = function () {
                Equipe.getFuncoes()
                    .success(function (data) {
                        vm.funcoesEquipe = data;
                    });
            };

            function DialogController($scope, $mdDialog, tituloModal, novoItem, equipe) {
                $scope.tituloModal = tituloModal;
                $scope.novoItem = novoItem;
                $scope.equipe = equipe;

                $scope.cancel = function () {
                    $mdDialog.cancel();
                };

                $scope.save = function () {
                    vm.save($scope.equipe, $scope.files[0]);
                    $mdDialog.hide();
                };

                $scope.update = function () {
                    vm.update($scope.equipe, $scope.files[0]);
                    $mdDialog.hide();
                };

                $scope.$watch('files.length', function (newVal, oldVal) {});
            }

            vm.create = function (ev) {
                vm.equipeNovo = {};
                $mdDialog
                    .show({
                        locals: {
                            tituloModal: 'messages.equipe_add',
                            novoItem: true,
                            equipe: vm.equipeNovo
                        },
                        controller: DialogController,
                        templateUrl: 'app/components/equipe/formModal.html',
                        parent: angular.element(document.body),
                        targetEvent: ev,
                        clickOutsideToClose: true,
                        fullscreen: true // Only for -xs, -sm breakpoints.
                    })
                    .then(function () {

                    }, function () {

                    });
            };

            vm.save = function (equipe, arquivo) {
                $rootScope.loading = true;
                Equipe.save(equipe, arquivo)
                    .success(function (data) {
                        vm.getEquipesUsuario();
                        $rootScope.loading = false;
                    }).error(function (data, status) {
                        vm.messages = data.errors;
                        vm.status = status;
                        $rootScope.loading = false;
                    });
            };

            vm.edit = function (ev) {
                $mdDialog
                    .show({
                        locals: {
                            tituloModal: 'messages.editar_equipe',
                            novoItem: false,
                            equipe: vm.equipe
                        },
                        controller: DialogController,
                        templateUrl: 'app/components/equipe/formModal.html',
                        parent: angular.element(document.body),
                        targetEvent: ev,
                        clickOutsideToClose: true,
                        fullscreen: true // Only for -xs, -sm breakpoints.
                    })
                    .then(function () {

                    }, function () {

                    });
            };

            vm.update = function (equipe, arquivo) {
                $rootScope.loading = true;
                Equipe.update(equipe, arquivo)
                    .success(function (data) {
                        Equipe.show(vm.idEquipe)
                            .success(function (data) {
                                vm.equipe = data;
                            });
                        $rootScope.loading = false;
                    }).error(function (data, status) {
                        vm.messages = data.errors;
                        vm.status = status;
                        $rootScope.loading = false;
                    });
            };

            function DialogControllerMensagem($scope, $mdDialog, tituloModal, novaMensagem, nomeEquipe) {
                $scope.tituloModal = tituloModal;
                $scope.novaMensagem = novaMensagem;
                $scope.nomeDestinatario = $filter('translate')('fields.equipe') + ' ' + nomeEquipe;

                $scope.cancel = function () {
                    $mdDialog.cancel();
                };

                $scope.enviarMensagem = function () {
                    vm.enviarMensagemEquipe(novaMensagem);
                    $mdDialog.hide();
                };

            }

            vm.escreverMensagem = function (ev) {
                vm.novaMensagem = {};
                vm.novaMensagem.id_equipe = vm.equipe.id;
                $mdDialog
                    .show({
                        locals: {
                            tituloModal: 'messages.escrever_mensagem',
                            novaMensagem: vm.novaMensagem,
                            nomeEquipe: vm.equipe.descricao
                        },
                        controller: DialogControllerMensagem,
                        templateUrl: 'app/components/dashboard/escreverMensagem.html',
                        parent: angular.element(document.body),
                        targetEvent: ev,
                        clickOutsideToClose: true,
                        fullscreen: true // Only for -xs, -sm breakpoints.
                    })
                    .then(function () {

                    }, function () {

                    });
            };

            vm.enviarMensagemEquipe = function (novaMensagem) {
                Equipe.enviarMensagem(novaMensagem)
                    .success(function (data) {
                        toastr.success($filter('translate')('messages.mensagem_enviada'));
                    })
                    .error(function (error) {
                        toastr.error(error.message);
                    });
            };

            vm.excluir = function (ev) {
                var confirm = $mdDialog.confirm(vm.equipe.id)
                    .title($filter('translate')('messages.confirma_exclusao_equipe', {
                        'nome_equipe': vm.equipe.descricao
                    }))
                    .ariaLabel($filter('translate')('messages.confirma_exclusao_equipe', {
                        'nome_equipe': vm.equipe.descricao
                    }))
                    .targetEvent(ev)
                    .ok($filter('translate')('messages.yes'))
                    .cancel($filter('translate')('messages.no'))
                    .theme('player2');

                $mdDialog.show(confirm).then(function () {
                    $rootScope.loading = true;
                    Equipe.destroy(vm.equipe.id)
                        .success(function (data) {
                            toastr.success($filter('translate')('messages.exclusao_equipe_sucesso'));
                            $location.path('/home/equipes');
                        }).error(function (data, status) {
                            toastr.error($filter('translate')(data.errors), $filter('translate')('messages.exclusao_equipe_erro'));
                        });
                    $rootScope.loading = false;
                }, function () {

                });
            };

            function DialogControllerIntegrantes($scope, $mdDialog, tituloModal, integrantes, funcoesEquipe) {
                $scope.tituloModal = tituloModal;
                $scope.integrantes = integrantes;
                $scope.funcoesEquipe = funcoesEquipe;

                $scope.cancel = function () {
                    $mdDialog.cancel();
                };

                $scope.editarIntegrante = function (ev, integrante) {
                    vm.editarIntegrante(ev, integrante);
                };

                $scope.excluirIntegrante = function (ev, integrante) {
                    vm.excluirIntegrante(ev, integrante);
                };

                $scope.salvarNovaFuncao = function (integrante) {
                    vm.salvarNovaFuncao(integrante);
                };
            }

            vm.gerenciarParticipantes = function (ev) {
                if (!$rootScope.telaMobile) {
                    return;
                }
                $mdDialog
                    .show({
                        locals: {
                            tituloModal: 'messages.gerenciar_participantes',
                            integrantes: vm.equipe.integrantes,
                            funcoesEquipe: vm.funcoesEquipe
                        },
                        controller: DialogControllerIntegrantes,
                        templateUrl: 'app/components/equipe/formIntegrantes.html',
                        parent: angular.element(document.body),
                        targetEvent: ev,
                        clickOutsideToClose: true,
                        fullscreen: true // Only for -xs, -sm breakpoints.
                    })
                    .then(function () {

                    }, function () {

                    });
            };

            vm.excluirIntegrante = function (ev, integrante) {
                var confirm = $mdDialog.confirm(integrante)
                    .title($filter('translate')('messages.confirma_exclusao_integrante', {
                        'nome_integrante': integrante.nome
                    }))
                    .ariaLabel($filter('translate')('messages.confirma_exclusao_integrante', {
                        'nome_integrante': integrante.nome
                    }))
                    .targetEvent(ev)
                    .ok($filter('translate')('messages.yes'))
                    .cancel($filter('translate')('messages.no'))
                    .theme('player2');

                $mdDialog.show(confirm).then(function () {
                    $rootScope.loading = true;
                    Equipe.removeIntegrante(vm.equipe.id, integrante.id)
                        .success(function (data) {
                            toastr.success($filter('translate')('messages.exclusao_integrante_sucesso'));
                            Equipe.getIntegrantes(vm.idEquipe)
                                .success(function (data) {
                                    vm.equipe.integrantes = data;
                                    vm.gerenciarParticipantes(ev);
                                });
                        }).error(function (data, status) {
                            toastr.error($filter('translate')(data.errors), $filter('translate')('messages.exclusao_integrante_erro'));
                        });
                    $rootScope.loading = false;
                }, function () {
                    vm.gerenciarParticipantes(ev);
                });
            };

            vm.editarIntegrante = function (ev, integrante) {
                integrante.edit = true;
            };

            vm.salvarNovaFuncao = function (integrante) {
                Equipe.atualizarIntegrante(integrante)
                    .success(function (data) {
                        Equipe.getIntegrantes(vm.idEquipe)
                            .success(function (data) {
                                vm.equipe.integrantes = data;
                                vm.gerenciarParticipantes();
                            });
                    });
            };

            vm.sair = function (ev) {
                var confirm = $mdDialog.confirm()
                    .title($filter('translate')('messages.confirma_sair_equipe', {
                        'nome_equipe': vm.equipe.descricao
                    }))
                    .ariaLabel($filter('translate')('messages.confirma_sair_equipe', {
                        'nome_equipe': vm.equipe.descricao
                    }))
                    .targetEvent(ev)
                    .ok($filter('translate')('messages.yes'))
                    .cancel($filter('translate')('messages.no'))
                    .theme('player2');

                $mdDialog.show(confirm).then(function () {
                    $rootScope.loading = true;
                    Equipe.sair(vm.equipe.id)
                        .success(function (data) {
                            toastr.success($filter('translate')('messages.saida_equipe_sucesso'));
                            Equipe.show(vm.idEquipe)
                                .success(function (data) {
                                    vm.equipe = data;
                                });
                        }).error(function (data, status) {
                            toastr.error($filter('translate')(data.errors), $filter('translate')('messages.saida_equipe_erro'));
                        });
                    $rootScope.loading = false;
                }, function () {

                });
            };

            vm.entrar = function (ev) {
                var confirm = $mdDialog.confirm()
                    .title($filter('translate')('messages.confirma_entrar_equipe', {
                        'nome_equipe': vm.equipe.descricao
                    }))
                    .ariaLabel($filter('translate')('messages.confirma_entrar_equipe', {
                        'nome_equipe': vm.equipe.descricao
                    }))
                    .targetEvent(ev)
                    .ok($filter('translate')('messages.yes'))
                    .cancel($filter('translate')('messages.no'))
                    .theme('player2');

                $mdDialog.show(confirm).then(function () {
                    $rootScope.loading = true;
                    Equipe.entrar(vm.equipe.id)
                        .success(function (data) {
                            toastr.success($filter('translate')('messages.solicitacao_entrada_equipe_sucesso'));
                            Equipe.show(vm.idEquipe)
                                .success(function (data) {
                                    vm.equipe = data;
                                });
                        }).error(function (data, status) {
                            toastr.error($filter('translate')(data.errors), $filter('translate')('messages.solicitacao_entrada_equipe_erro'));
                        });
                    $rootScope.loading = false;
                }, function () {

                });
            };

            vm.cancelarSolicitacao = function (ev) {
                var confirm = $mdDialog.confirm()
                    .title($filter('translate')('messages.confirma_cancelar_solicitacao_equipe', {
                        'nome_equipe': vm.equipe.descricao
                    }))
                    .ariaLabel($filter('translate')('messages.confirma_cancelar_solicitacao_equipe', {
                        'nome_equipe': vm.equipe.descricao
                    }))
                    .targetEvent(ev)
                    .ok($filter('translate')('messages.yes'))
                    .cancel($filter('translate')('messages.no'))
                    .theme('player2');

                $mdDialog.show(confirm).then(function () {
                    $rootScope.loading = true;
                    Equipe.cancelarSolicitacao(vm.equipe.id)
                        .success(function (data) {
                            toastr.success($filter('translate')('messages.cancelar_solicitacao_equipe_sucesso'));
                            Equipe.show(vm.idEquipe)
                                .success(function (data) {
                                    vm.equipe = data;
                                });
                        }).error(function (data, status) {
                            toastr.error($filter('translate')(data.errors), $filter('translate')('messages.cancelar_solicitacao_equipe_erro'));
                        });
                    $rootScope.loading = false;
                }, function () {

                });
            };

            function DialogControllerSolicitacoes($scope, $mdDialog, tituloModal, solicitacoes) {
                $scope.tituloModal = tituloModal;
                $scope.solicitacoes = solicitacoes;

                $scope.cancel = function () {
                    $mdDialog.cancel();
                };

                $scope.visitarPerfil = function (ev, solicitacao) {
                    $location.path('profile/' + solicitacao.id);
                    $mdDialog.cancel();
                }

                $scope.aceitarSolicitacao = function (ev, solicitacao) {
                    vm.aceitarSolicitacao(ev, solicitacao);
                };

                $scope.recusarSolicitacao = function (ev, solicitacao) {
                    vm.recusarSolicitacao(ev, solicitacao);
                };

            }

            vm.gerenciarSolicitacoes = function (ev) {
                if (!$rootScope.telaMobile) {
                    return;
                }
                Equipe.getSolicitacoes(vm.equipe.id)
                    .success(function (data) {
                        console.log(data);
                        $mdDialog
                            .show({
                                locals: {
                                    tituloModal: 'messages.gerenciar_solicitacoes',
                                    solicitacoes: data
                                },
                                controller: DialogControllerSolicitacoes,
                                templateUrl: 'app/components/equipe/formSolicitacoes.html',
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

            vm.aceitarSolicitacao = function (ev, solicitacao) {

            };


            vm.recusarSolicitacao = function (ev, solicitacao) {

            };
        }]);

}());

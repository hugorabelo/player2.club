/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('HomeController', ['$scope', '$rootScope', '$mdDialog', '$translate', '$location', '$q', '$mdSidenav', 'toastr', 'localStorageService', 'Usuario', 'Campeonato', 'CampeonatoUsuario', 'UserPlataforma', 'Plataforma', 'Jogo', 'NotificacaoEvento',
        function ($scope, $rootScope, $mdDialog, $translate, $location, $q, $mdSidenav, toastr, localStorageService, Usuario, Campeonato, CampeonatoUsuario, UserPlataforma, Plataforma, Jogo, NotificacaoEvento) {
            var vm = this;

            $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no', 'messages.confirma_desistir_campeonato', 'messages.inscrever_titulo', 'messages.inscrever']).then(function (translations) {
                vm.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
                vm.textoYes = translations['messages.yes'];
                vm.textoNo = translations['messages.no'];
                vm.textoDesistirCampeonato = translations['messages.confirma_desistir_campeonato'];
                vm.textoInscreverTitulo = translations['messages.inscrever_titulo'];
                vm.textoInscrever = translations['messages.inscrever'];
            });

            $scope.$on('userProfileSet', function () {
                vm.inicializa();
                $rootScope.usuarioLogado = localStorageService.get('usuarioLogado');
            });

            vm.inicializa = function () {
                var usuarioLogado = localStorageService.get('usuarioLogado');
                if (usuarioLogado !== null) {
                    vm.idUsuario = usuarioLogado.id;
                    Usuario.show(vm.idUsuario)
                        .success(function (data) {
                            vm.usuario = data;
                            vm.getCampeonatosDisponiveis();
                            vm.getJogosDisponiveis();
                        });
                }
            };

            vm.getCampeonatosDisponiveis = function () {
                vm.userCampeonatosDisponiveis = {};
                //                Usuario.getCampeonatosDisponiveis(vm.usuario.id)
                //                    .success(function (data) {
                //                        vm.userCampeonatosDisponiveis = data;
                //                    })
                //                    .error(function (data) {});
                Campeonato.get()
                    .success(function (data) {
                        vm.userCampeonatosDisponiveis = data;
                    })
                    .error(function (data) {});
            };

            vm.getJogosDisponiveis = function () {
                Jogo.get()
                    .success(function (data) {
                        vm.jogosDisponiveis = data;
                    });
            };

            vm.inscreverCampeonato = function (ev, id) {
                vm.idCampeonato = id;
                Campeonato.getInformacoes(id)
                    .success(function (data) {
                        vm.campeonatoSelecionado = data;
                        //                    var mensagem = vm.campeonatoSelecionado.detalhes;
                        var confirm = $mdDialog.confirm(id)
                            .title(vm.textoInscreverTitulo)
                            .ariaLabel(vm.textoInscreverTitulo)
                            .targetEvent(ev)
                            .ok(vm.textoInscrever)
                            .cancel(vm.textoNo)
                            .theme('player2');

                        $mdDialog.show(confirm).then(function () {
                            $rootScope.loading = true;
                            CampeonatoUsuario.save(vm.usuario.id, vm.idCampeonato)
                                .success(function (data) {
                                    vm.getCampeonatosInscritos();
                                    vm.getCampeonatosDisponiveis();
                                });
                        }, function () {

                        });
                    });
            };

            vm.getCampeonatosInscritos = function () {
                vm.userCampeonatosInscritos = {};
                Usuario.getCampeonatosInscritos(vm.usuario.id)
                    .success(function (data) {
                        vm.userCampeonatosInscritos = data;
                    })
                    .error(function (data) {});
            };

            vm.sairCampeonato = function (ev, id) {
                vm.idRegistroExcluir = id;
                var confirm = $mdDialog.confirm(id)
                    .title(vm.textoDesistirCampeonato)
                    .ariaLabel(vm.textoDesistirCampeonato)
                    .targetEvent(ev)
                    .ok(vm.textoYes)
                    .cancel(vm.textoNo)
                    .theme('player2');

                $mdDialog.show(confirm).then(function () {
                    var i, id_campeonato_usuario;
                    $rootScope.loading = true;
                    CampeonatoUsuario.getUsuarios(vm.idRegistroExcluir)
                        .success(function (data) {
                            for (i = 0; i < data.length; i = i + 1) {
                                if (data[i].users_id === vm.usuario.id) {
                                    id_campeonato_usuario = data[i].id;
                                    break;
                                }
                            }
                            CampeonatoUsuario.destroy(id_campeonato_usuario)
                                .success(function (data) {
                                    vm.getCampeonatosInscritos();
                                    vm.getCampeonatosDisponiveis();
                                })
                                .error(function (data) {

                                });
                        });
                }, function () {

                });
            };

            vm.editaPerfil = function () {
                Usuario.show(localStorageService.get('usuarioLogado').id)
                    .success(function (data) {
                        vm.perfilEditar = data;
                        vm.getGamertagsDoUsuario(vm.perfilEditar.id);
                        vm.carregaPlataformas();
                        vm.getEventosDeNotificacao();
                    });
            };

            vm.updatePerfil = function () {
                Usuario.update(vm.perfilEditar, vm.files_perfil[0], vm.files_capa[0])
                    .success(function (data) {
                        $location.path('/home');
                    })
                    .error(function (data) {

                    })
            };

            vm.getGamertagsDoUsuario = function (idUsuario) {
                vm.gamertags = {};
                UserPlataforma.getPlataformasDoUsuario(idUsuario)
                    .success(function (data) {
                        vm.gamertags = data;
                    })
                    .error(function (data) {

                    });
            };

            vm.adicionarGamerTag = function (ev) {
                vm.userPlataforma = {};
                vm.userPlataforma.users_id = localStorageService.get('usuarioLogado').id;
                $mdDialog.show({
                        locals: {
                            tituloModal: 'messages.adicionar_gamertag',
                            userPlataforma: vm.userPlataforma,
                            plataformas: vm.plataformas
                        },
                        controller: DialogControllerGamerTag,
                        templateUrl: 'app/components/dashboard/formGamerTag.html',
                        parent: angular.element(document.body),
                        targetEvent: ev,
                        clickOutsideToClose: true,
                        fullscreen: true // Only for -xs, -sm breakpoints.
                    })
                    .then(function () {

                    }, function () {

                    });
            };

            function DialogControllerGamerTag($scope, $mdDialog, tituloModal, userPlataforma, plataformas) {
                $scope.tituloModal = tituloModal;
                $scope.userPlataforma = userPlataforma;
                $scope.plataformas = plataformas;

                $scope.cancel = function () {
                    $mdDialog.cancel();
                };

                $scope.salvarGamerTag = function () {
                    vm.salvarGamerTag($scope.userPlataforma);
                    $mdDialog.hide();
                }
            };

            vm.excluirGamertag = function (ev, id) {
                vm.idRegistroExcluir = id;
                var confirm = $mdDialog.confirm(id)
                    .title(vm.textoConfirmaExclusao)
                    .ariaLabel(vm.textoConfirmaExclusao)
                    .targetEvent(ev)
                    .ok(vm.textoYes)
                    .cancel(vm.textoNo)
                    .theme('player2');

                $mdDialog.show(confirm).then(function () {
                    $rootScope.loading = true;
                    UserPlataforma.destroy(vm.idRegistroExcluir)
                        .success(function (data) {
                            vm.getGamertagsDoUsuario(vm.perfilEditar.id);
                        });
                }, function () {

                });
            };


            vm.salvarGamerTag = function () {
                vm.userPlataforma.users_id = localStorageService.get('usuarioLogado').id;
                UserPlataforma.save(vm.userPlataforma)
                    .success(function (data) {
                        vm.getGamertagsDoUsuario(vm.perfilEditar.id);
                    }).error(function (data, status) {
                        vm.message = data.message;
                        vm.status = status;
                    });
            };

            vm.carregaPlataformas = function () {
                $rootScope.loading = true;
                Plataforma.get()
                    .success(function (data) {
                        vm.plataformas = data;
                    });
            };

            vm.carregaPartidas = function () {
                Usuario.getPartidasEmAberto(localStorageService.get('usuarioLogado').id)
                    .success(function (data) {
                        vm.partidasDoUsuario = data;
                    });
            };

            vm.getEventosDeNotificacao = function () {
                NotificacaoEvento.get()
                    .success(function (data) {
                        vm.eventosDeNotificacao = data;
                    });
            };

            vm.editaNotificacao = function (objeto, idEvento) {
                console.log(objeto);
                console.log(idEvento);
            };
    }]);

}());

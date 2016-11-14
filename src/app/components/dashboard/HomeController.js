/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('HomeController', ['$scope', '$rootScope', '$mdDialog', '$translate', 'Usuario', 'Campeonato', 'CampeonatoUsuario', function ($scope, $rootScope, $mdDialog, $translate, Usuario, Campeonato, CampeonatoUsuario) {
        var vm = this;

        $translate(['messages.confirma_exclusao', 'messages.yes', 'messages.no', 'messages.confirma_desistir_campeonato', 'messages.inscrever_titulo', 'messages.inscrever']).then(function (translations) {
            vm.textoConfirmaExclusao = translations['messages.confirma_exclusao'];
            vm.textoYes = translations['messages.yes'];
            vm.textoNo = translations['messages.no'];
            vm.textoDesistirCampeonato = translations['messages.confirma_desistir_campeonato'];
            vm.textoInscreverTitulo = translations['messages.inscrever_titulo'];
            vm.textoInscrever = translations['messages.inscrever'];
        });

        vm.inicializa = function () {
            vm.idUsuario = $rootScope.usuarioLogado.id;
            Usuario.show(vm.idUsuario)
                .success(function (data) {
                    vm.usuario = data;
                    vm.getCampeonatosDisponiveis();
                });
        }

        vm.getCampeonatosDisponiveis = function () {
            vm.userCampeonatosDisponiveis = {};
            Usuario.getCampeonatosDisponiveis(vm.usuario.id)
                .success(function (data) {
                    vm.userCampeonatosDisponiveis = data;
                })
                .error(function (data) {});
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
            Usuario.show($rootScope.usuarioLogado.id)
                .success(function (data) {
                    vm.perfilEditar = data;
                });
        }
    }]);
}());

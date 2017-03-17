/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('CampeonatoFrontController', ['$scope', '$rootScope', '$filter', 'Campeonato', '$state', function ($scope, $rootScope, $filter, Campeonato, $state) {

        var vm = this;

        vm.rodada_atual = [];

        vm.partidasDaRodada = [];

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
                    $rootScope.loading = false;
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

        vm.funcaoTeste = function (grupo, indice) {
            grupo.placarNovo = indice;
        };

        /*
            angular.forEach(listaDeGrupos, function(item) {
				vm.rodada_atual.push(1);
				vm.carregaJogosDaRodada(indice, item.id);
				indice++;
				vm.rodada_maxima = Object.keys(item.rodadas).length;
			});
        */

    }]);
}());

(function () {
    'use strict';

    angular.module('AplicacaoLiga').controller('CriarCampeonatoController', CriarCampeonatoController);

    CriarCampeonatoController.$inject = ['$scope', '$rootScope', 'Campeonato', 'Plataforma', 'Jogo', 'CampeonatoTipo'];

    function CriarCampeonatoController($scope, $rootScope, Campeonato, Plataforma, Jogo, CampeonatoTipo) {
        var vm = this;

        var items = ['Pontos', 'Vitórias', 'Saldo de Gols', 'Gols Pró', 'Gols Contra', 'Confronto Direto'];
        var barConfig = {
            group: 'foobar',
            animation: 150,
            onSort: function (evt) {
                // @see https://github.com/RubaXa/Sortable/blob/master/ng-sortable.js#L18-L24
            }
        };

        var criteriosClassificacao = {};

        vm.items = items;
        vm.barConfig = barConfig;
        vm.criteriosClassificacao = criteriosClassificacao;

        vm.criaZonaClassificacao = criaZonaClassificacao;
        vm.create = create;
        vm.carregaJogosDaPlataforma = carregaJogosDaPlataforma;
        vm.carregaTiposDeCampeonatoDoJogo = carregaTiposDeCampeonatoDoJogo;
        vm.carregaDetalhesCampeonato = carregaDetalhesCampeonato;
        vm.salvarCampeonato = salvarCampeonato;

        function criaZonaClassificacao() {
            $scope.pontosZonaClassificacao = [];
            for (i = 0; i < $scope.campeonato.zona_classificacao; i++) {
                $scope.pontosZonaClassificacao[i] = 0;
            }
        }

        function create() {
            $rootScope.loading = true;
            Plataforma.get()
                .success(function (data) {
                    $scope.plataformas = data;
                    $scope.campeonato = {};
                    $scope.messages = null;
                    $rootScope.loading = false;
                });
        }

        function carregaJogosDaPlataforma() {
            $rootScope.loading = true;
            Plataforma.getJogos($scope.campeonato.plataformas_id)
                .success(function (data) {
                    $scope.jogos = data;
                    if($scope.jogos.length > 0) {
                        $scope.campeonato.jogos_id = $scope.jogos[0].id;
                        $scope.carregaTiposDeCampeonatoDoJogo();
                    }
                    $scope.messages = null;
                    $rootScope.loading = false;
                });
        }

        function carregaTiposDeCampeonatoDoJogo() {
            $rootScope.loading = true;
            Jogo.getTiposDeCampeonato($scope.campeonato.jogos_id)
                .success(function (data) {
                    $scope.campeonatoTipos = data;
                    if($scope.campeonatoTipos.length > 0) {
                        $scope.campeonato.campeonato_tipos_id = $scope.campeonatoTipos[0].id;
                        $scope.carregaDetalhesCampeonato();
                    }
                    $scope.messages = null;
                    $rootScope.loading = false;
                });
        }

        function carregaDetalhesCampeonato() {
            $rootScope.loading = true;
            CampeonatoTipo.edit($scope.campeonato.campeonato_tipos_id)
                .success(function (data) {
                    $scope.templateDetalhes = data.arquivo_detalhes;
                    $scope.messages = null;
                    $rootScope.loading = false;
                });
            $rootScope.loading = false;
        }

        function salvarCampeonato() {

        }
    }
})();

//AplicacaoLiga
//    .controller('CriarCampeonatoController', ['$scope', '$rootScope', 'Campeonato', 'Plataforma', 'Jogo', 'CampeonatoTipo', function ($scope, $rootScope, Campeonato, Plataforma, Jogo, CampeonatoTipo) {
//        $scope.items = ['Pontos', 'Vitórias', 'Saldo de Gols', 'Gols Pró', 'Gols Contra', 'Confronto Direto'];
//        $scope.barConfig = {
//            group: 'foobar',
//            animation: 150,
//            onSort: function ( /** ngSortEvent */ evt) {
//                // @see https://github.com/RubaXa/Sortable/blob/master/ng-sortable.js#L18-L24
//            }
//        };
//
//        $scope.criteriosClassificacao = {};
//
//        $scope.criaZonaClassificacao = function () {
//            $scope.pontosZonaClassificacao = [];
//            for (i = 0; i < $scope.campeonato.zona_classificacao; i++) {
//                $scope.pontosZonaClassificacao[i] = 0;
//            }
//        }
//
//        $scope.create = function () {
//            $rootScope.loading = true;
//            Plataforma.get()
//                .success(function (data) {
//                    $scope.plataformas = data;
//                    $scope.campeonato = {};
//                    $scope.messages = null;
//                    $rootScope.loading = false;
//                });
//        }
//
//        $scope.carregaJogosDaPlataforma = function () {
//            $rootScope.loading = true;
//            Plataforma.getJogos($scope.campeonato.plataformas_id)
//                .success(function (data) {
//                    $scope.jogos = data;
//                    if($scope.jogos.length > 0) {
//                        $scope.campeonato.jogos_id = $scope.jogos[0].id;
//                        $scope.carregaTiposDeCampeonatoDoJogo();
//                    }
//                    $scope.messages = null;
//                    $rootScope.loading = false;
//                });
//        }
//
//        $scope.carregaTiposDeCampeonatoDoJogo = function () {
//            $rootScope.loading = true;
//            Jogo.getTiposDeCampeonato($scope.campeonato.jogos_id)
//                .success(function (data) {
//                    $scope.campeonatoTipos = data;
//                    if($scope.campeonatoTipos.length > 0) {
//                        $scope.campeonato.campeonato_tipos_id = $scope.campeonatoTipos[0].id;
//                        $scope.carregaDetalhesCampeonato();
//                    }
//                    $scope.messages = null;
//                    $rootScope.loading = false;
//                });
//        }
//
//        $scope.carregaDetalhesCampeonato = function () {
//            $rootScope.loading = true;
//            CampeonatoTipo.edit($scope.campeonato.campeonato_tipos_id)
//                .success(function (data) {
//                    $scope.templateDetalhes = data.arquivo_detalhes;
//                    $scope.messages = null;
//                    $rootScope.loading = false;
//                });
//            $rootScope.loading = false;
//        }
//
//        $scope.salvarCampeonato = function() {
//
//        }
//    }]);

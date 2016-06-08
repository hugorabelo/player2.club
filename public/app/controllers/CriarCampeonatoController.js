AplicacaoLiga
    .controller('CriarCampeonatoController', ['$scope', '$rootScope', 'Campeonato', 'Plataforma', 'Jogo', 'CampeonatoTipo', function ($scope, $rootScope, Campeonato, Plataforma, Jogo, CampeonatoTipo) {
        $scope.items = ['Pontos', 'Vitórias', 'Saldo de Gols', 'Gols Pró', 'Gols Contra', 'Confronto Direto'];
        $scope.barConfig = {
            group: 'foobar',
            animation: 150,
            onSort: function ( /** ngSortEvent */ evt) {
                // @see https://github.com/RubaXa/Sortable/blob/master/ng-sortable.js#L18-L24
            }
        };

        $scope.criteriosClassificacao = {};

        $scope.criaZonaClassificacao = function () {
            $scope.pontosZonaClassificacao = [];
            for (i = 0; i < $scope.campeonato.zona_classificacao; i++) {
                $scope.pontosZonaClassificacao[i] = 0;
            }
        }

        $scope.create = function () {
            $rootScope.loading = true;
            Plataforma.get()
                .success(function (data) {
                    $scope.plataformas = data;
                    $scope.campeonato = {};
                    $scope.messages = null;
                    $rootScope.loading = false;
                });
        }

        $scope.carregaJogosDaPlataforma = function () {
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

        $scope.carregaTiposDeCampeonatoDoJogo = function () {
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

        $scope.carregaDetalhesCampeonato = function () {
            $rootScope.loading = true;
            CampeonatoTipo.edit($scope.campeonato.campeonato_tipos_id)
                .success(function (data) {
                    $scope.templateDetalhes = data.arquivo_detalhes;
                    $scope.messages = null;
                    $rootScope.loading = false;
                });
            $rootScope.loading = false;
        }
    }]);

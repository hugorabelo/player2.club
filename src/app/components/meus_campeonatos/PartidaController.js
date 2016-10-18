angular.module('player2').controller('PartidaController', ['$scope', '$rootScope', '$filter', '$mdDialog', '$translate', 'Campeonato', 'Usuario', 'Partida', '$state', '$timeout', function ($scope, $rootScope, $filter, $mdDialog, $translate, Campeonato, Usuario, Partida, $state, $timeout) {

    var vm = this;

    vm.campeonato = {};

    vm.exibeDetalhes = false;

    vm.carregaPartidas = function (id_usuario) {
        $rootScope.loading = true;
        Usuario.getPartidas(id_usuario)
            .success(function (data) {
                vm.partidas = data;
                $rootScope.loading = false;
            });
    };

    vm.salvarPlacar = function (partida) {
        $rootScope.loading = true;

        partida.usuarioLogado = $rootScope.usuarioLogado.id;
        Partida.salvarPlacar(partida)
            .success(function () {
                vm.carregaPartidas($rootScope.usuarioLogado.id);
                $rootScope.loading = false;
            })
            .error(function (data) {
                //TODO melhorar a exibição deste erro
                $rootScope.loading = false;
            });

    };

    vm.confirmarPlacar = function (id_partida) {
        $rootScope.loading = true;

        var dados = {};
        dados.id_partida = id_partida;
        dados.usuarioLogado = $rootScope.usuarioLogado.id;
        Partida.confirmarPlacar(dados)
            .success(function () {
                vm.carregaPartidas($rootScope.usuarioLogado.id);
                $rootScope.loading = false;
            })
            .error(function (data) {
                $rootScope.loading = false;
            });
    };

    vm.contestarPlacar = function (ev, id_partida) {
        vm.contestacao_resultado = {};
        vm.contestacao_resultado.partidas_id = id_partida;
        vm.contestacao_resultado.usuario_partidas_id = $rootScope.usuarioLogado.id;
        $mdDialog.show({
                locals: {
                    tituloModal: 'messages.partida_contestar',
                    contestacao_resultado: vm.contestacao_resultado
                },
                controller: DialogController,
                templateUrl: 'app/components/meus_campeonatos/formContestacaoResultado.html',
                parent: angular.element(document.body),
                targetEvent: ev,
                clickOutsideToClose: true,
                fullscreen: true // Only for -xs, -sm breakpoints.
            })
            .then(function () {

            }, function () {

            });
    };

    vm.cancelarPlacar = function (id_partida) {
        $rootScope.loading = true;

        var dados = {};
        dados.id_partida = id_partida;
        dados.usuarioLogado = $rootScope.usuarioLogado.id;
        Partida.cancelarPlacar(dados)
            .success(function () {
                vm.carregaPartidas($rootScope.usuarioLogado.id);
                $rootScope.loading = false;
            })
            .error(function (data) {
                console.log(data.errors);
                $rootScope.loading = false;
            });
    }

    vm.salvarContestacao = function (contestacao_resultado, arquivo) {
        $rootScope.loading = true;
        Partida.contestarResultado(contestacao_resultado, arquivo)
            .success(function (data) {
                vm.carregaPartidas($rootScope.usuarioLogado.id);
                $rootScope.loading = false;
            }).error(function (data, status) {
                vm.messages = data.errors;
                vm.status = status;
                $rootScope.loading = false;
            });
    };

    vm.exibeDataLimite = function (data_limite) {
        dataLimite = new Date(data_limite);
        return $filter('date')(dataLimite, 'dd/MM/yyyy HH:mm');
    };

    function DialogController($scope, $mdDialog, tituloModal, contestacao_resultado) {
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
}]);

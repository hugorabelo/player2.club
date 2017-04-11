/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('TopNavController', ['$rootScope', '$scope', '$translate', '$location', '$mdDateLocale', '$filter', '$mdMedia', '$mdSidenav', 'Auth', 'Usuario', 'Atividade', function ($rootScope, $scope, $translate, $location, $mdDateLocale, $filter, $mdMedia, $mdSidenav, Auth, Usuario, Atividade) {

        var vm = this;

        $rootScope.telaMobile = $mdMedia('xs');

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
                var localeDate = moment.localeData();

                $mdDateLocale.months = localeDate._months;
                $mdDateLocale.shortMonths = localeDate._monthsShort;
                $mdDateLocale.days = localeDate._weekdays;
                $mdDateLocale.shortDays = localeDate._weekdaysMin;

                $mdDateLocale.msgCalendar = $translate.instant('MSG_CALENDAR');
                $mdDateLocale.msgOpenCalendar = $translate.instant('MSG_OPEN_CALENDAR');

            } else if (idioma === 'pt_br') {
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
                    .then(function () {
                        //                        console.log("toggle " + navID + " is done");
                    });
            };
        };

        vm.closeSideNav = function () {
            $mdSidenav('sideNavPrincipal').close()
                .then(function () {
                    //          $log.debug("close LEFT is done");
                });
        };

        vm.getNotificacoesDoUsuario = function () {
            Usuario.getNotificacoes('lidas')
                .success(function (data) {
                    vm.notificacoesUsuario = data;
                    angular.forEach(vm.notificacoesUsuario, function (notificacao) {
                        if (notificacao.nome_fase != null && notificacao.nome_fase != undefined) {
                            notificacao.nome_fase = $filter('translate')(notificacao.nome_fase);
                        }
                    });
                });
        };

        vm.exibeDetalhesNotificacao = function (notificacao) {
            switch (notificacao.tipo_evento) {
                case "salvou_placar":
                case "confirmou_placar":
                case "contestou_resultado":
                    $location.path('home/partidas_usuario');
                    break;
                case "fase_iniciada":
                case "fase_encerrada":
                case "fase_encerramento_breve":
                    $location.path('campeonato/' + notificacao.item_id);
                    break;
                case "comentar_post":
                    break;
                case "curtir_post":
                    break;
                case "curtir_comentario":
                    break;
                case "seguir_usuario":
                    $location.path('profile/' + notificacao.id_remetente);
                    break;
            }
        };

    }]);
}());

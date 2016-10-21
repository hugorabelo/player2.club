/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('TopNavController', ['$rootScope', '$scope', '$translate', '$location', '$mdDateLocale', 'Auth', 'Usuario', function ($rootScope, $scope, $translate, $location, $mdDateLocale, Auth, Usuario) {

        var vm = this;

        var originatorEv;

        vm.openMenu = function ($mdOpenMenu, ev) {
            originatorEv = ev;
            $mdOpenMenu(ev);
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

        vm.mudaUsuarioLogado = function (ev) {
            if (ev.keyCode === 13) {
                Usuario.show(vm.usuarioLogado.id)
                    .success(function (data) {
                        $rootScope.usuarioLogado = data;
                        $location.path('/');
                    });
            }
        };

    }]);
}());

(function () {
    'use strict';

    angular.module('player2', [
        'ngAnimate',
        'ngCookies',
        'ngSanitize',
        'ngMessages',
        'ngAria',
        'ngResource',
        'ui.router',
        'ui.bootstrap',
        'pascalprecht.translate',
        'jcs-autoValidate',
        'ui.tree',
        'summernote',
        'ui.checkbox',
        'ngMaterial',
        'lfNgMdFileInput'
    ]);

    //    angular.module('player2').config(function ($locationProvider) {
    //        $locationProvider.html5Mode(true);
    //    });



    angular.module('player2').config(function ($translateProvider) {
        $translateProvider.useStaticFilesLoader({
            prefix: 'app/lang/locale-',
            suffix: '.json'
        });

        $translateProvider.preferredLanguage('pt_br');
        $translateProvider.fallbackLanguage('pt_br');

        $translateProvider.useSanitizeValueStrategy('escape');

    });

    angular.module('player2')
        .config(function ($mdThemingProvider) {
            $mdThemingProvider.theme('default')
                .primaryPalette('teal')
                .accentPalette('orange');
        });

    angular.module('player2')
        .config(function ($mdDateLocaleProvider, $translateProvider) {

            $mdDateLocaleProvider.formatDate = function (date) {
                return date ? moment(date).format('DD/MM/YYYY') : '';
            };

            $mdDateLocaleProvider.parseDate = function (dateString) {
                var m = moment(dateString, 'DD/MM/YYYY', true);
                return m.isValid() ? m.toDate() : new Date(NaN);
            };

            $mdDateLocaleProvider.months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            $mdDateLocaleProvider.shortMonths = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $mdDateLocaleProvider.days = ['Domingo', 'Segunda-Feira', 'Terça-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'Sábado'];
            $mdDateLocaleProvider.shortDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        });

})();
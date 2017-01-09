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
        'trumbowyg-ng',
        'ui.checkbox',
        'ngMaterial',
        'lfNgMdFileInput',
        'monospaced.elastic',
        'ngScrollSpy',
        'bootstrapLightbox',
        'toastr'
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
        .config(function ($mdDateLocaleProvider, $translateProvider) {

            $mdDateLocaleProvider.parseDate = function (dateString) {
                var m = moment(dateString, 'DD/MM/YYYY', true);
                return m.isValid() ? m.toDate() : new Date(NaN);
            };

            $mdDateLocaleProvider.formatDate = function (date) {
                return date ? moment(date).format('DD/MM/YYYY') : '';
            };


            $mdDateLocaleProvider.months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            $mdDateLocaleProvider.shortMonths = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $mdDateLocaleProvider.days = ['Domingo', 'Segunda-Feira', 'Terça-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'Sábado'];
            $mdDateLocaleProvider.shortDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        });

    angular.module('player2')
        .config(function ($httpProvider) {
            $httpProvider.interceptors.push(apiInterceptor);
        });

    var API_URL = 'http://localhost/player2/public/';
    //    var API_URL = '/';

    function apiInterceptor($q) {
        return {
            request: function (config) {
                var url = config.url;

                // ignore template requests
                var extensao = url.substr(url.length - 5);
                if (extensao == '.html' || extensao == '.json') {
                    return config || $q.when(config);
                }

                config.url = API_URL + config.url;
                return config || $q.when(config);
            }
        }
    };

    angular.module('player2').config(function (LightboxProvider) {
        LightboxProvider.templateUrl = 'app/components/common/lightbox-modal.html';

        LightboxProvider.calculateModalDimensions = function (dimensions) {
            var width = Math.max(400, dimensions.imageDisplayWidth + 60);

            if (width >= dimensions.windowWidth - 20 || dimensions.windowWidth < 768) {
                width = 'auto';
            }

            return {
                'width': width, // default
                'height': 'auto' // custom
            };
        };

        LightboxProvider.getImageUrl = function (image) {
            return 'uploads/imagens/' + image.url;
        };
    });

    angular.module('player2').config(function (toastrConfig) {
        angular.extend(toastrConfig, {
            newestOnTop: true,
            positionClass: 'toast-bottom-center',
            preventDuplicates: true,
            extendedTimeOut: 1000,
            progressBar: true,
            tapToDismiss: true,
            timeOut: 2000,
            allowHtml: true
        });
    });

})();

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
        'ui.checkbox',
        'ngMaterial',
        'lfNgMdFileInput',
        'monospaced.elastic',
        'ngScrollSpy',
        'bootstrapLightbox',
        'toastr',
        'angular-jwt',
        'LocalStorageModule',
        'ng-sortable',
        'froala',
        'infinite-scroll',
        'dndLists',
        'angular-intro',
        'mgo-angular-wizard',
        'material.components.expansionPanels',
        'material.components.eventCalendar',
        'auth0.auth0',
        'ui.utils.masks',
        'md.data.table',
        'idf.br-filters'
    ]);

    //'auth0.lock',


    //Ambiente: local | dev | beta | hugorabelo
    var ambiente = 'player2.local';
    var apiUrlAmbiente;
    var redirectUrlAmbiente;

    if (ambiente == 'local') {
        apiUrlAmbiente = "http://localhost/player2/public/";
        redirectUrlAmbiente = "http://localhost:3000";
    } else if (ambiente == 'localMac') {
        apiUrlAmbiente = "http://player2.local/";
        redirectUrlAmbiente = "http://localhost:3000";
    } else if (ambiente == 'player2.local') {
        apiUrlAmbiente = "/";
        redirectUrlAmbiente = "http://player2.local";
    } else if (ambiente == 'dev') {
        apiUrlAmbiente = "/";
        redirectUrlAmbiente = "http://dev.player2.club";
    } else if (ambiente == "hugorabelo") {
        apiUrlAmbiente = "/";
        redirectUrlAmbiente = "http://beta.hugorabelo.com";
    } else {
        apiUrlAmbiente = "/";
        redirectUrlAmbiente = "http://beta.player2.club";
    }

    angular.module('player2').config(['$compileProvider', function ($compileProvider) {
        $compileProvider.debugInfoEnabled(false);
    }]);

    angular.module('player2').config(function ($translateProvider) {
        $translateProvider.useStaticFilesLoader({
            prefix: 'app/lang/locale-',
            suffix: '.json'
        });

        $translateProvider.preferredLanguage('pt_br');
        $translateProvider.fallbackLanguage('pt_br');

        moment.locale('pt-br');

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


    function apiInterceptor($q, $rootScope, localStorageService) {
        return {
            request: function (config) {
                //                apiUrlAmbiente = localStorageService.get('API_URL');
                //                redirectUrlAmbiente = localStorageService.get('redirectUrl');
                var url = config.url;

                // ignore template requests
                var extensao = url.substr(url.length - 5);
                var extensaoReduzida = url.substr(url.length - 4);
                if (extensao == '.html' || extensao == '.json' || url == 'ambiente.properties' || extensaoReduzida == '.svg') {
                    return config || $q.when(config);
                }

                config.url = apiUrlAmbiente + config.url;
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
            extendedTimeOut: 1000,
            progressBar: true,
            tapToDismiss: true,
            timeOut: 8000,
            allowHtml: true,
            closeButton: true
        });
    });

    angular.module('player2').config(configAuth);

    configAuth.$inject = [
        '$locationProvider',
        'angularAuth0Provider',
        '$httpProvider',
        'jwtOptionsProvider'
    ];

    function configAuth(
        $locationProvider,
        angularAuth0Provider,
        $httpProvider,
        jwtOptionsProvider
    ) {
        // Initialization for the angular-auth0 library
        angularAuth0Provider.init({
            clientID: 'BM9k9idztM2AEtMuogR0WnRmrTSOu2pm',
            domain: 'hugorabelo.auth0.com',
            responseType: 'token id_token',
            redirectUri: redirectUrlAmbiente,
            scope: 'openid profile'
        });

        jwtOptionsProvider.config({
            /*tokenGetter: function () {
                return localStorage.getItem('access_token');
            },*/
            tokenGetter: ['options', function (options) {
                if (options && options.url.indexOf('http://auth0.com') === 0) {
                    return localStorage.getItem('auth0.id_token');
                }
                return localStorage.getItem('id_token');
            }],
            whiteListedDomains: ['localhost'],
            unauthenticatedRedirectPath: '/login'
        });

        $httpProvider.interceptors.push('jwtInterceptor');

        $locationProvider.hashPrefix('');

        /// Comment out the line below to run the app
        // without HTML5 mode (will use hashes in routes)
        //$locationProvider.html5Mode(true);
    };

})();

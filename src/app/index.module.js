(function () {
    'use strict';

    var player2App = angular.module('player2', [
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
        'LocalStorageModule',
        'ng-sortable',
        'froala',
        'infinite-scroll',
        'dndLists',
        'angular-intro',
        'mgo-angular-wizard',
        'material.components.expansionPanels',
        'material.components.eventCalendar',
        'angular-oauth2',
        'satellizer'
    ]);

    //Ambiente: local | dev | beta | hugorabelo
    var ambiente = 'beta';
    var apiUrlAmbiente;
    var redirectUrlAmbiente;
    var authClientID;
    var authClientSecret;
    var apiUrlAuth;

    if (ambiente == 'local') {
        apiUrlAmbiente = "http://localhost/player2/public/";
        redirectUrlAmbiente = "http://localhost:3000";
        authClientID = 'p2id';
        authClientSecret = 'secret';
        apiUrlAuth = "/";
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
        redirectUrlAmbiente = "https://beta.player2.club";
        authClientID = 'e03daaa46bab1ff439b28a411bcb4928';
        authClientSecret = 'e4eb96705075dc4a0925662705a08ceb';
        apiUrlAuth = "beta.player2.club/";
    }

    player2App.config(['$compileProvider', function ($compileProvider) {
        $compileProvider.debugInfoEnabled(false);
    }]);

    player2App.config(function ($translateProvider) {
        $translateProvider.useStaticFilesLoader({
            prefix: 'app/lang/locale-',
            suffix: '.json'
        });

        $translateProvider.preferredLanguage('pt_br');
        $translateProvider.fallbackLanguage('pt_br');

        moment.locale('pt-br');

        $translateProvider.useSanitizeValueStrategy('escape');
    });

    player2App.config(function ($mdDateLocaleProvider, $translateProvider) {

            $mdDateLocaleProvider.parseDate = function (dateString) {
                var m = moment(dateString, 'DD/MM/YYYY', true);
                return m.isValid() ? m.toDate() : new Date(NaN);
            };

            $mdDateLocaleProvider.formatDate = function (date) {
                return date ? moment(date).format('DD/MM/YYYY') : '';
            };


            $mdDateLocaleProvider.months = ['Janeiro', 'Fevereiro', 'Mar??o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            $mdDateLocaleProvider.shortMonths = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $mdDateLocaleProvider.days = ['Domingo', 'Segunda-Feira', 'Ter??a-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'S??bado'];
            $mdDateLocaleProvider.shortDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S??b'];

        });

    player2App.config(function ($httpProvider) {
        $httpProvider.interceptors.push(apiInterceptor);
        $httpProvider.interceptors.push('oauthFixInterceptor');
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

    player2App.config(function (LightboxProvider) {
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

    player2App.config(function (toastrConfig) {
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

    player2App.config(configAuth);

    configAuth.$inject = [
        '$locationProvider',
        'OAuthProvider',
        'OAuthTokenProvider'
    ];

    function configAuth(
        $locationProvider,
        OAuthProvider,
        OAuthTokenProvider
    ) {
        OAuthProvider.configure({
            baseUrl: 'api',
            clientId: authClientID,
            clientSecret: authClientSecret, // optional
            grantPath: 'oauth/access_token'
        });
        OAuthTokenProvider.configure({
            name: 'token',
            options: {
              secure: false
            }
        });
        $locationProvider.hashPrefix('');

        /// Comment out the line below to run the app
        // without HTML5 mode (will use hashes in routes)
        //$locationProvider.html5Mode(true);
    };

    player2App.config(['$authProvider', function ($authProvider) {
        $authProvider.facebook({
            clientId: '3148215308557100',
            url: apiUrlAuth + 'api/auth/facebook',
            redirectUri: redirectUrlAmbiente + '/',
            responseType: 'code',
            responseParams: {
                code: 'code',
                clientId: 'client_id',
                redirectUri: 'redirect_uri'
            }
        });

        $authProvider.google({
            clientId: '687694969410-1tvfs14oljk1vtcdantrjod1gsvfkebe.apps.googleusercontent.com',
            url: apiUrlAuth + 'api/auth/google',
            redirectUri: redirectUrlAmbiente,
            responseType: 'code',
            responseParams: {
                code: 'code',
                clientId: 'client_id',
                redirectUri: 'redirect_uri'
            }
        });

        $authProvider.live({
            clientId: '245c979b-5458-449a-afde-c00b4ec17be3',
            url: apiUrlAuth + 'api/auth/live',
            authorizationEndpoint: 'https://login.microsoftonline.com/6170b526-4643-4a76-8365-572cde287ff0/oauth2/v2.0/authorize',
            redirectUri: redirectUrlAmbiente,
            responseType: 'code',
            scope: ['openid'],
            responseParams: {
                code: 'code',
                clientId: 'client_id',
                redirectUri: 'redirect_uri'
            }
        });
    }]);

})();

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
        'auth0.lock',
        'angular-jwt',
        'LocalStorageModule',
        'ng-sortable',
        'froala',
        'infinite-scroll',
        'dndLists'
    ]);

    //    angular.module('player2').config(function ($locationProvider) {
    //        $locationProvider.html5Mode(true);
    //    });

    //Ambiente: local | dev | beta
    var ambiente = 'local';
    var apiUrlAmbiente;
    var redirectUrlAmbiente;
    var responseTypeAmbiente;
    var clientIdAmbiente;

    if (ambiente == 'local') {
        apiUrlAmbiente = "http://localhost/player2/public/";
        redirectUrlAmbiente = "http://localhost:3000";
        responseTypeAmbiente = "token";
        clientIdAmbiente = 'BM9k9idztM2AEtMuogR0WnRmrTSOu2pm';
    } else if (ambiente == 'dev') {
        apiUrlAmbiente = "/";
        redirectUrlAmbiente = "http://dev.player2.club";
        responseTypeAmbiente = "token";
        clientIdAmbiente = 'i421bkb06C6mMCue0yXC67BwJGL3pSlY';
    } else {
        apiUrlAmbiente = "/";
        redirectUrlAmbiente = "http://beta.player2.club";
        responseTypeAmbiente = "token";
        clientIdAmbiente = 'BM9k9idztM2AEtMuogR0WnRmrTSOu2pm';
    }

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


    function apiInterceptor($q, $rootScope, localStorageService) {
        return {
            request: function (config) {
                //                apiUrlAmbiente = localStorageService.get('API_URL');
                //                redirectUrlAmbiente = localStorageService.get('redirectUrl');
                //                responseTypeAmbiente = localStorageService.get('responseType');
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

    angular.module('player2').config(['$httpProvider', 'lockProvider', 'jwtOptionsProvider', 'jwtInterceptorProvider', function ($httpProvider, lockProvider, jwtOptionsProvider, jwtInterceptorProvider) {
        lockProvider.init({
            clientID: clientIdAmbiente,
            domain: 'hugorabelo.auth0.com',
            options: {
                auth: {
                    params: {
                        scope: 'openid email picture name picture_large'
                    },
                    redirectUrl: redirectUrlAmbiente,
                    responseType: responseTypeAmbiente
                },
                theme: {
                    logo: 'http://www.player2.club/img/player2_azul.png',
                    primaryColor: "#0c486b"
                },
                languageDictionary: {
                    title: ""
                },
                language: "pt-BR",
                allowSignUp: false
            }
        });

        // Configuration for angular-jwt
        jwtOptionsProvider.config({
            tokenGetter: ['options', function (options) {
                if (options && options.url.indexOf('http://auth0.com') === 0) {
                    return localStorage.getItem('auth0.id_token');
                }
                return localStorage.getItem('id_token');
            }],
            whiteListedDomains: ['localhost'],
            unauthenticatedRedirectPath: '/login'
        });

        // Add the jwtInterceptor to the array of HTTP interceptors
        // so that JWTs are attached as Authorization headers
        $httpProvider.interceptors.push('jwtInterceptor');
    }]);

    angular.module('player2').config(function (localStorageServiceProvider) {
        localStorageServiceProvider
            .setStorageType('sessionStorage')
            .setNotify(true, true)
            .setDefaultToCookie(false);
    });

})();

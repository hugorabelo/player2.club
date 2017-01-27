(function () {
    'use strict';

    angular
        .module('player2')
        .run(mudaState);

    angular
        .module('player2')
        .run(['defaultErrorMessageResolver', defaultErrorMessageResolver]);

    angular
        .module('player2')
        .run(redireciona);

    angular
        .module('player2')
        .run(runAuth);

    function mudaState($rootScope, $state, $window, $http) {
        $rootScope.$state = $state;

        $rootScope.$on('$stateChangeSuccess', function (event, toState, toParam, fromState, fromParam) {
            $http.get('api/validaAutenticacao');
            if ($rootScope.usuarioLogado == null) {
                $rootScope.usuarioLogado = JSON.parse($window.localStorage.getItem('usuarioLogado'));
                //                console.log($window.localStorage);
                //            $http.get('api/validaAutenticacao')
                //                runAuth();
            }
        });
    }

    function defaultErrorMessageResolver(defaultErrorMessageResolver) {
        defaultErrorMessageResolver.setI18nFileRootPath('bower_components/angular-auto-validate/dist/lang');
        defaultErrorMessageResolver.setCulture('pt-br');
    }

    function redireciona($rootScope, $state) {
        $rootScope.$on('$stateChangeStart', function (evt, to, params) {
            if (to.redirectTo) {
                evt.preventDefault();
                $state.go(to.redirectTo, params, {
                    location: 'replace'
                })
            }
        });
    }

    runAuth.$inject = ['$rootScope', 'authService', 'authManager', 'lock'];

    function runAuth($rootScope, authService, authManager, lock) {
        console.log('runAuth');
        // Register the synchronous hash parser
        // when using UI Router
        lock.interceptHash();

        // Put the authService on $rootScope so its methods
        // can be accessed from the nav bar
        $rootScope.authService = authService;

        // Register the authentication listener that is
        // set up in auth.service.js
        authService.registerAuthenticationListener();

        // Use the authManager from angular-jwt to check for
        // the user's authentication state when the page is
        // refreshed and maintain authentication
        authManager.checkAuthOnRefresh();

        // Listen for 401 unauthorized requests and redirect
        // the user to the login page
        authManager.redirectWhenUnauthenticated();
    }

})();

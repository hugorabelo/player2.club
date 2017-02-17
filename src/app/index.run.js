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

    function mudaState($rootScope, $state, $window, $http, localStorageService, lock) {
        $rootScope.$state = $state;

        $rootScope.$on('$stateChangeSuccess', function (event, toState, toParam, fromState, fromParam) {
            $http.get('api/validaAutenticacao')
                .then(function (result) {
                    lock.getProfile(localStorage.getItem('idToken'), function (error, profile) {
                        localStorageService.set('usuarioLogado', result.data);
                        $rootScope.$broadcast('userProfileSet', profile);
                    });
                }, function (error) {
                    localStorage.removeItem('id_token');
                    console.log('usuário não existe');
                });
            if ($rootScope.usuarioLogado == null) {
                $rootScope.usuarioLogado = localStorageService.get('usuarioLogado');
            }
        });
    }

    function defaultErrorMessageResolver(defaultErrorMessageResolver) {
        defaultErrorMessageResolver.setI18nFileRootPath('app/lang');
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

    runAuth.$inject = ['$rootScope', '$window', 'authService', 'authManager', 'lock', 'localStorageService'];

    function runAuth($rootScope, $window, authService, authManager, lock, localStorageService) {
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

    //    angular.module('player2')
    //        .factory('validacaoCustomizada', ['toastr',
    //            function (toastr) {
    //                var
    //                /**
    //                 * @ngdoc function
    //                 * @name myCustomElementModifier#makeValid
    //                 * @methodOf myCustomElementModifier
    //                 *
    //                 * @description
    //                 * Makes an element appear valid by apply custom styles and child elements.
    //                 *
    //                 * @param {Element} el - The input control element that is the target of the validation.
    //                 */
    //                    makeValid = function (el) {
    //                        // do some code here...
    //                    },
    //
    //                    /**
    //                     * @ngdoc function
    //                     * @name myCustomElementModifier#makeInvalid
    //                     * @methodOf myCustomElementModifier
    //                     *
    //                     * @description
    //                     * Makes an element appear invalid by apply custom styles and child elements.
    //                     *
    //                     * @param {Element} el - The input control element that is the target of the validation.
    //                     * @param {String} errorMsg - The validation error message to display to the user.
    //                     */
    //                    makeInvalid = function (el, errorMsg) {
    //                        toastr.error(errorMsg, 'Erro de Validação');
    //                        // do some code here...
    //                    },
    //
    //
    //                    /**
    //                     * @ngdoc function
    //                     * @name myCustomElementModifier#makeDefault
    //                     * @methodOf myCustomElementModifier
    //                     *
    //                     * @description
    //                     * Makes an element appear in its default visual state.
    //                     *
    //                     * @param {Element} el - The input control element that is the target of the validation.
    //                     */
    //                    makeDefault = function (el) {
    //                        // return the element to a default visual state i.e. before any form of validation was applied
    //                    };
    //
    //                return {
    //                    makeValid: makeValid,
    //                    makeInvalid: makeInvalid,
    //                    makeDefault: makeDefault,
    //                    key: 'myCustomModifierKey'
    //                };
    //            }
    //        ]);
    //
    //    // now register the custom element modifier with the auto-validate module and set it as the default one for all elements
    //    angular.module('player2')
    //        .run([
    //        'validator',
    //        'validacaoCustomizada',
    //        function (validator, myCustomElementModifier) {
    //                validator.registerDomModifier(myCustomElementModifier.key, myCustomElementModifier);
    //                validator.setDefaultElementModifier(myCustomElementModifier.key);
    //        }
    //    ]);

})();

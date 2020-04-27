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

    function mudaState($rootScope, $state, $window, $http, localStorageService) {
        $rootScope.$state = $state;

        $rootScope.$on('$stateChangeSuccess', function (event, toState, toParam, fromState, fromParam) {
            $rootScope.stateHome = ($state.current.name == 'home');
            if ((fromState.url != '^') && (fromState.name != 'login') && (fromState.name != 'recuperar_senha') && (fromState.name != 'redefinir_senha')) {
                localStorageService.set('previousState', fromState);
                localStorageService.set('previousParams', fromParam);
            }
            if ($rootScope.usuarioLogado == null) {
                $rootScope.usuarioLogado = localStorageService.get('usuarioLogado');
            }
        });
    }

    function defaultErrorMessageResolver(defaultErrorMessageResolver) {
        defaultErrorMessageResolver.setI18nFileRootPath('app/lang');
        defaultErrorMessageResolver.setCulture('pt-br');
    }

    function redireciona($rootScope, $location) {
        $rootScope.$on('$stateChangeStart', function (evt, to, params) {
            if (to.redirectTo) {
                evt.preventDefault();
                $location.path(to.redirectTo, params, {
                    location: 'replace'
                })
            }
        });
    }

    runAuth.$inject = [
        '$rootScope', 
        '$state', 
        'OAuth',
        '$auth'
    ];

    function runAuth($rootScope, $state, OAuth, $auth) {
        $rootScope.$on('$stateChangeStart', function (event, next) {
            if (next.name !== 'login' && next.name !== 'recuperar_senha' && next.name !== 'redefinir_senha') {
                if(!OAuth.isAuthenticated() && !$auth.isAuthenticated()) {
                    return $state.go('login');
                }
            }
        });
        $rootScope.$on('oauth:error', function(event, rejection) {
            // Ignore `invalid_grant` error - should be catched on `LoginController`.
            if ('invalid_grant' === rejection.data.error) {
              return;
            }
      
            // Refresh token when a `invalid_token` error occurs.
            if ('access_denied' === rejection.data.error) {
              return OAuth.getRefreshToken();
            }
      
            // Redirect to `/login` with the `error_reason`.
            return $state.go('login');
        });
    }

    angular.module('player2')
        .factory('validacaoCustomizada', [
                    function () {
                var
                    /**
                     * @ngdoc function
                     * @name myCustomElementModifier#makeValid
                     * @methodOf myCustomElementModifier
                     *
                     * @description
                     * Makes an element appear valid by apply custom styles and child elements.
                     *
                     * @param {Element} el - The input control element that is the target of the validation.
                     */
                    makeValid = function (el) {
                        // do some code here...
                    },

                    /**
                     * @ngdoc function
                     * @name myCustomElementModifier#makeInvalid
                     * @methodOf myCustomElementModifier
                     *
                     * @description
                     * Makes an element appear invalid by apply custom styles and child elements.
                     *
                     * @param {Element} el - The input control element that is the target of the validation.
                     * @param {String} errorMsg - The validation error message to display to the user.
                     */
                    makeInvalid = function (el, errorMsg) {
                        var name = el[0].id + '-error';
                        var objeto = document.getElementById(name);
                        if (objeto == null) {
                            var sp1 = document.createElement("span");
                            sp1.id = name;
                            sp1.className = "validation-error";
                            sp1.innerHTML = errorMsg;
                            el[0].parentNode.insertBefore(sp1, el[0].nextSibling);
                        } else {
                            objeto.innerHTML = errorMsg;
                        }
                    },


                    /**
                     * @ngdoc function
                     * @name myCustomElementModifier#makeDefault
                     * @methodOf myCustomElementModifier
                     *
                     * @description
                     * Makes an element appear in its default visual state.
                     *
                     * @param {Element} el - The input control element that is the target of the validation.
                     */
                    makeDefault = function (el) {
                        // return the element to a default visual state i.e. before any form of validation was applied
                    };

                return {
                    makeValid: makeValid,
                    makeInvalid: makeInvalid,
                    makeDefault: makeDefault,
                    key: 'myCustomModifierKey'
                };
                    }
                ]);

})();

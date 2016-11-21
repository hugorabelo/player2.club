(function () {
    'use strict';

    angular
        .module('player2')
        .run(mudaState)

    angular
        .module('player2')
        .run(['defaultErrorMessageResolver', defaultErrorMessageResolver]);

    angular
        .module('player2')
        .run(redireciona);

    function mudaState($rootScope, $state) {
        $rootScope.$state = $state;
        if ($rootScope.usuarioLogado == null) {
            $rootScope.usuarioLogado = {};
            $rootScope.usuarioLogado.id = 35;
        }
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

})();

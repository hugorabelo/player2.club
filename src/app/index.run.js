(function () {
    'use strict';

    angular
        .module('player2')
        .run(mudaState)

    angular
        .module('player2')
        .run(['defaultErrorMessageResolver', defaultErrorMessageResolver]);

    function mudaState($rootScope, $state) {
        $rootScope.$state = $state;
        if ($rootScope.usuarioLogado == null) {
            $rootScope.usuarioLogado = 1;
        }
    }

    function defaultErrorMessageResolver(defaultErrorMessageResolver) {
        defaultErrorMessageResolver.setI18nFileRootPath('bower_components/angular-auto-validate/dist/lang');
        defaultErrorMessageResolver.setCulture('pt-br');
    }

})();

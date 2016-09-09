(function () {
    'use strict';

    angular
        .module('inspinia')
        .run(runBlock);
    angular
        .module('inspinia')
        .run(mudaState)

    angular
        .module('inspinia')
        .run(['defaultErrorMessageResolver', defaultErrorMessageResolver]);

    /** @ngInject */
    function runBlock($log) {
        $log.debug('runBlock end');
    }

    function mudaState($rootScope, $state) {
        $rootScope.$state = $state;
        if ($rootScope.usuarioLogado == null) {
            $rootScope.usuarioLogado = 1;
        }
    };

    function defaultErrorMessageResolver(defaultErrorMessageResolver) {
        defaultErrorMessageResolver.setI18nFileRootPath('bower_components/angular-auto-validate/dist/lang');
        defaultErrorMessageResolver.setCulture('pt-br');
    };

})();

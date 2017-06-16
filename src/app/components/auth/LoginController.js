(function () {
    'use strict';

    angular
        .module('player2')
        .controller('LoginController', ['$rootScope', 'authService', function ($rootScope, authService) {

            var vm = this;

            vm.inicializa = function () {
                $rootScope.escondeBarra = true;
                if (!$rootScope.exibindoAlerta) {
                    authService.login();
                }
            }

            vm.logout = function () {
                authService.logout();
            }

    }]);
})();

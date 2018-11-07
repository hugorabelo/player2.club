(function () {
    'use strict';

    angular
        .module('player2')
        .controller('LoginController', ['$rootScope', 'authService', function ($rootScope, authService) {

            var vm = this;

            vm.auth = authService;

            vm.profile;

            vm.inicializa = function () {
                $rootScope.escondeBarra = true;
                if (!$rootScope.exibindoAlerta) {
                    authService.login();
                }
            }

            vm.logout = function () {
                authService.logout();
            }



            if (authService.getCachedProfile()) {
                vm.profile = authService.getCachedProfile();
            } else {
                authService.getProfile(function (err, profile) {
                    vm.profile = profile;
                    console.log(profile);
                });
            }


    }]);
})();

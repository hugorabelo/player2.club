(function () {
    'use strict';

    angular
        .module('player2')
        .controller('LoginController', ['$rootScope', '$location', '$filter', 'toastr', 'authService', 'OAuth', 'OAuthToken', function ($rootScope, $location, $filter, toastr, authService, OAuth, OAuthToken) {

            var vm = this;

            vm.auth = authService;

            vm.user = {
                username: '',
                password: ''
            };

            vm.login = function() {
                if(vm.formLogin.$valid) {
                    authService.login(vm.user)
                        .then(function () {
                            $rootScope.escondeBarra = false;
                            authService.handleAuthentication();
                            return $location.path('home');
                        }, function (error) {
                            toastr.error($filter('translate')('messages_login.' + error.data.error));
                        });
                }
            };

            vm.inicializa = function () {
                $rootScope.escondeBarra = true;
            };

            vm.logout = function () {
                OAuthToken.removeToken(); 
                $location.path('login');
            };

    }]);
})();

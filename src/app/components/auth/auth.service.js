(function () {

    'use strict';

    angular
        .module('player2')
        .service('authService', authService);

    authService.$inject = ['$location', '$state', '$http', 'localStorageService', '$rootScope', '$mdDialog', '$filter', 'OAuth'];

    var userProfile;

    function authService($location, $state, $http, localStorageService, $rootScope, $mdDialog, $filter, OAuth) {

        function login(user) {
            return OAuth.getAccessToken(user);
        }

        function handleAuthentication() {
            $http.get('api/validaAutenticacao')
                .then(function (result) {
                    localStorageService.set('usuarioLogado', result.data);
                    $rootScope.$broadcast('userProfileSet', userProfile);
                    var previousState = localStorageService.get('previousState');
                    var previousParams = localStorageService.get('previousParams');
                    $state.go(previousState.name, previousParams);
                }, function (error) {
                    $location.path('login');
                });
        }

        function logout() {
            OAuthToken.removeToken(); 

            userProfile = {};
            localStorageService.remove('usuarioLogado');

            $location.path('login');
        }

        function isAuthenticated() {
            return OAuth.isAuthenticated();
        }

        return {
            login: login,
            handleAuthentication: handleAuthentication,
            logout: logout,
            isAuthenticated: isAuthenticated
        }
    }

})();

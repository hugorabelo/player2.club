(function () {

    'use strict';

    angular
        .module('player2')
        .service('authService', authService);

    authService.$inject = ['lock', 'authManager', '$http', '$rootScope', '$window', '$location', '$state', 'localStorageService'];

    function authService(lock, authManager, $http, $rootScope, $window, $location, $state, localStorageService) {

        var userProfile = JSON.parse(localStorage.getItem('profile')) || {};

        function login() {
            lock.show();
        }

        // Set up the logic for when a user authenticates
        // This method is called from app.run.js
        function registerAuthenticationListener() {
            lock.on('authenticated', function (authResult) {
                // Chamar um validaAutenticacao
                localStorage.setItem('id_token', authResult.idToken);
                $http.get('api/validaAutenticacao')
                    .then(function (result) {
                        authManager.authenticate();

                        lock.getProfile(authResult.idToken, function (error, profile) {
                            if (error) {
                                console.log(error);
                            }

                            localStorage.setItem('profile', JSON.stringify(profile));
                            localStorageService.set('usuarioLogado', result.data);
                            $rootScope.$broadcast('userProfileSet', profile);
                        });
                        var previousState = localStorageService.get('previousState');
                        var previousParams = localStorageService.get('previousParams');
                        $state.go(previousState.name, previousParams);
                    }, function (error) {
                        localStorage.removeItem('id_token');
                        console.log('usuário não existe');
                    });
            });

        }

        function logout() {
            localStorage.removeItem('id_token');
            localStorage.removeItem('profile');
            localStorage.removeItem('usuarioLogado');
            authManager.unauthenticate();
            userProfile = {};
            localStorageService.remove('usuarioLogado');
            $location.path('/login');
        }

        return {
            userProfile: userProfile,
            login: login,
            logout: logout,
            registerAuthenticationListener: registerAuthenticationListener
        }
    }
})();

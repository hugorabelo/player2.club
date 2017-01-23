(function () {

    'use strict';

    angular
        .module('player2')
        .service('authService', authService);

    authService.$inject = ['lock', 'authManager', '$http', '$rootScope', '$window'];

    function authService(lock, authManager, $http, $rootScope, $window) {

        var userProfile = JSON.parse(localStorage.getItem('profile')) || {};

        function login() {
            lock.show();
        }

        // Set up the logic for when a user authenticates
        // This method is called from app.run.js
        function registerAuthenticationListener() {
            console.log('registerAuthenticationListener');
            lock.on('authenticated', function (authResult) {
                console.log('authenticated');
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
                            $rootScope.$broadcast('userProfileSet', profile);
                            $window.localStorage.setItem('usuarioLogado', angular.toJson(result.data));
                            console.log(localStorage.getItem('usuarioLogado'));
                            //                            $rootScope.usuarioLogado = result.data;
                            //                            console.log($rootScope.usuarioLogado);
                        });
                    }, function (error) {
                        localStorage.removeItem('id_token');
                        console.log('usuário não existe');
                    });
            });
        }

        function logout() {
            localStorage.removeItem('id_token');
            localStorage.removeItem('profile');
            authManager.unauthenticate();
            userProfile = {};
        }

        return {
            userProfile: userProfile,
            login: login,
            logout: logout,
            registerAuthenticationListener: registerAuthenticationListener
        }
    }
})();

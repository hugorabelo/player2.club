(function () {

    'use strict';

    angular
        .module('player2')
        .service('authService', authService);

    authService.$inject = ['lock', 'authManager', '$http'];

    function authService(lock, authManager, $http) {

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
                            //                    $rootScope.$broadcast('userProfileSet', profile);
                        });
                    }, function (error) {
                        localStorage.removeItem('id_token');
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

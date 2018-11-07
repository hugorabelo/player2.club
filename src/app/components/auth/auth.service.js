(function () {

    'use strict';

    angular
        .module('player2')
        .service('authService', authService);

    authService.$inject = ['$state', 'angularAuth0', '$timeout'];

    function authService($state, angularAuth0, $timeout) {

        function login() {
            angularAuth0.authorize();
        }

        function handleAuthentication() {
            angularAuth0.parseHash(function (err, authResult) {
                if (authResult && authResult.accessToken && authResult.idToken) {
                    setSession(authResult);
                    $state.go('home');
                } else if (err) {
                    $timeout(function () {
                        $state.go('home');
                    });
                    console.log(err);
                }
            });
        }

        function setSession(authResult) {
            // Set the time that the Access Token will expire at
            let expiresAt = JSON.stringify((authResult.expiresIn * 1000) + new Date().getTime());
            localStorage.setItem('access_token', authResult.accessToken);
            localStorage.setItem('id_token', authResult.idToken);
            localStorage.setItem('expires_at', expiresAt);
        }

        function logout() {
            // Remove tokens and expiry time from localStorage
            localStorage.removeItem('access_token');
            localStorage.removeItem('id_token');
            localStorage.removeItem('expires_at');
        }

        function isAuthenticated() {
            // Check whether the current time is past the
            // Access Token's expiry time
            let expiresAt = JSON.parse(localStorage.getItem('expires_at'));
            return new Date().getTime() < expiresAt;
        }

        return {
            login: login,
            handleAuthentication: handleAuthentication,
            logout: logout,
            isAuthenticated: isAuthenticated
        }
    }

    /*

    authService.$inject = ['lock', 'authManager', '$http', '$rootScope', '$window', '$location', '$state', '$mdDialog', '$filter', 'localStorageService'];

    function authService(lock, authManager, $http, $rootScope, $window, $location, $state, $mdDialog, $filter, localStorageService) {

        var userProfile = JSON.parse(localStorage.getItem('profile')) || {};

        function login() {
            //lock.show();
            webAuth.authorize();
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
                        showAlert();
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

        function showAlert(ev) {
            $rootScope.exibindoAlerta = true;
            $mdDialog.show(
                $mdDialog.alert()
                .parent(angular.element(document.querySelector('#popupContainer')))
                .clickOutsideToClose(true)
                .title($filter('translate')('messages.titulo_alerta_login'))
                .textContent($filter('translate')('messages.conteudo_alerta_login'))
                .ariaLabel($filter('translate')('messages.titulo_alerta_login'))
                .ok($filter('translate')('messages.close'))
                .targetEvent(ev)
            ).then(function () {
                $rootScope.exibindoAlerta = false;
                login();
            });
        };

        return {
            userProfile: userProfile,
            login: login,
            logout: logout,
            registerAuthenticationListener: registerAuthenticationListener
        }
    }
    */


})();

(function () {

    'use strict';

    angular
        .module('player2')
        .service('authService', authService);

    authService.$inject = ['$state', '$timeout', '$http', 'localStorageService', '$rootScope', '$mdDialog', '$filter'];

    var userProfile;

    function authService($state, $timeout, $http, localStorageService, $rootScope, $mdDialog, $filter) {

        function login() {
            console.log('login');
        }

        function handleAuthentication() {
            console.log('handleAuthentication');
        }

        function setSession(authResult) {
            // Set the time that the Access Token will expire at
            var expiresAt = JSON.stringify((authResult.expiresIn * 1000) + new Date().getTime());
            localStorage.setItem('access_token', authResult.accessToken);
            localStorage.setItem('id_token', authResult.idToken);
            localStorage.setItem('expires_at', expiresAt);
        }

        function logout() {
            // Remove tokens and expiry time from localStorage
            localStorage.removeItem('access_token');
            localStorage.removeItem('id_token');
            localStorage.removeItem('expires_at');

            userProfile = {};
            localStorageService.remove('usuarioLogado');

            $state.go('login');
        }

        function isAuthenticated() {
            // Check whether the current time is past the
            // Access Token's expiry time
            var expiresAt = JSON.parse(localStorage.getItem('expires_at'));
            return new Date().getTime() < expiresAt;
        }



        function getProfile(cb) {
            var accessToken = localStorage.getItem('access_token');
            if (!accessToken) {
                //throw new Error('Access Token must exist to fetch profile');
                console.log('Access Token must exist to fetch profile');
                return;
            }
        }

        function setUserProfile(profile) {
            userProfile = profile;
        }

        function getCachedProfile() {
            return userProfile;
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
            login: login,
            handleAuthentication: handleAuthentication,
            logout: logout,
            isAuthenticated: isAuthenticated,
            getProfile: getProfile,
            setUserProfile: setUserProfile,
            getCachedProfile: getCachedProfile
        }
    }

})();

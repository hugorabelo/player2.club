(function () {

    'use strict';

    angular
        .module('player2')
        .service('authService', authService);

    authService.$inject = ['$state', 'angularAuth0', '$timeout', '$http', 'localStorageService', '$rootScope', '$mdDialog', '$filter'];

    var userProfile;

    function authService($state, angularAuth0, $timeout, $http, localStorageService, $rootScope, $mdDialog, $filter) {

        function login() {
            angularAuth0.authorize();
        }

        function handleAuthentication() {
            angularAuth0.parseHash(function (err, authResult) {
                if (authResult && authResult.accessToken && authResult.idToken) {
                    setSession(authResult);

                    $http.get('api/validaAutenticacao')
                        .then(function (result) {

                            localStorageService.set('usuarioLogado', result.data);
                            $rootScope.$broadcast('userProfileSet', userProfile);
                            var previousState = localStorageService.get('previousState');
                            var previousParams = localStorageService.get('previousParams');
                            $state.go(previousState.name, previousParams);
                        }, function (error) {
                            localStorage.removeItem('id_token');
                            showAlert();
                        });

                    //$state.go('home');

                } else if (err) {
                    $timeout(function () {
                        $state.go('login');
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

            $state.go('login');
        }

        function isAuthenticated() {
            // Check whether the current time is past the
            // Access Token's expiry time
            let expiresAt = JSON.parse(localStorage.getItem('expires_at'));
            return new Date().getTime() < expiresAt;
        }



        function getProfile(cb) {
            var accessToken = localStorage.getItem('access_token');
            if (!accessToken) {
                //throw new Error('Access Token must exist to fetch profile');
                console.log('Access Token must exist to fetch profile');
                return;
            }
            angularAuth0.client.userInfo(accessToken, function (err, profile) {
                if (profile) {
                    setUserProfile(profile);
                }
                cb(err, profile);
            });
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

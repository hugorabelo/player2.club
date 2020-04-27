(function () {

    'use strict';

    angular
        .module('player2')
        .service('authService', authService);

    authService.$inject = ['$location', '$state', '$http', 'localStorageService', '$rootScope', '$mdDialog', '$filter', 'OAuth', '$auth'];

    var userProfile;

    function authService($location, $state, $http, localStorageService, $rootScope, $mdDialog, $filter, OAuth, $auth) {

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
            $auth.removeToken();

            userProfile = {};
            localStorageService.remove('usuarioLogado');

            $location.path('login');
        }

        function isAuthenticated() {
            if(!OAuth.isAuthenticated() && !$auth.isAuthenticated()) {
                return false
            }
            return true;
        }

        function enviarNovaSenha(user) {
            return $http({
                method: 'POST',
                url: 'api/usuario/enviarNovaSenha',
                data: $.param(user),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        }

        function cadastrarNovaSenha(dados) {
            return $http({
                method: 'POST',
                url: 'api/usuario/redefinirSenha',
                data: $.param(dados),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        }

        function authenticate(provider) {
            var extra = {
                'grant_type': 'authorization_code'
            }
            return $auth.authenticate(provider, extra);
        }

        return {
            login: login,
            handleAuthentication: handleAuthentication,
            logout: logout,
            isAuthenticated: isAuthenticated,
            enviarNovaSenha: enviarNovaSenha,
            cadastrarNovaSenha: cadastrarNovaSenha,
            authenticate: authenticate
        }
    }

})();

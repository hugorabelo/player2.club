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

        return {
            login: login,
            handleAuthentication: handleAuthentication,
            logout: logout,
            isAuthenticated: isAuthenticated,
            enviarNovaSenha: enviarNovaSenha,
            cadastrarNovaSenha: cadastrarNovaSenha
        }
    }

})();

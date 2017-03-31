/*global angular */
(function () {
    'use strict';

    angular.module('player2').controller('MainController', ['$rootScope', '$http', 'localStorageService', function ($rootScope, $http, localStorageService) {
        var vm = this;

        vm.carregaAmbiente = function () {
            $http.get('ambiente.properties')
                .success(function (response) {
                    localStorageService.set('API_URL', response.API_URL);
                    localStorageService.set('redirectUrl', response.redirectUrl);
                    localStorageService.set('responseType', response.responseType);
                });
        }
    }]);
}());

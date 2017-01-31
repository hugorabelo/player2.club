(function () {

    'use strict';

    angular
        .module('player2')
        .controller('PingController', PingController);

    function PingController($http) {

        var vm = this;

        vm.ping = function () {
            console.log('ping');
            $http.get('api/validaAutenticacao')
                .then(function (result) {
                    console.log('ok');
                    console.log(result);
                    vm.pingResult = result.data.text;
                }, function (error) {
                    console.log(error);
                    vm.pingResult = error.statusText;
                });
        }
    }

}());

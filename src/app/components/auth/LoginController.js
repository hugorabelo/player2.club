(function () {
    'use strict';

    angular
        .module('player2')
        .controller('LoginController', LoginController);

    function LoginController(authService) {

        var vm = this;

        vm.authService = authService;

    }
})();

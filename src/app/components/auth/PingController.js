(function () {

  'use strict';

  angular
    .module('player2')
    .controller('PingController', PingController);

  function PingController($http) {

    vm.ping = function () {
      $http.get('http://localhost/player2/public/api/protected')
        .then(function (result) {
          vm.pingResult = result.data.text;
        }, function (error) {
          vm.pingResult = error.statusText;
        });
    }
  }

}());

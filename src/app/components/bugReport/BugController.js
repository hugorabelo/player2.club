(function () {
    'use strict';

    angular
        .module('player2')
        .controller('BugController', ['$rootScope', '$scope', '$mdDialog', 'toastr', 'Permissao', function ($rootScope, $scope, $mdDialog, toastr, Permissao) {

            var vm = this;

            vm.darFeedback = function (ev) {
                $mdDialog.show({
                        controller: DialogController,
                        templateUrl: 'app/components/bugReport/formFeedback.tmpl.html',
                        targetEvent: ev,
                        parent: angular.element(document.body),
                        clickOutsideToClose: true,
                        fullscreen: true
                    })
                    .then(function (feedback) {
                        vm.salvar(feedback);
                    }, function () {
                        $scope.status = 'cancel';
                    });
            };

            function DialogController($scope, $mdDialog) {
                $scope.feedback = {};

                $scope.cancel = function () {
                    $mdDialog.cancel();
                };

                $scope.salvar = function () {
                    $mdDialog.hide($scope.feedback);
                };
            };

            vm.salvar = function (feedback) {
                Permissao.reportarBug(feedback)
                    .success(function (data) {
                        toastr.success('Feedback cadastrado com sucesso');
                    })
                    .error(function (error) {
                        toastr.error('Ocorreu um problema ao salvar o feedback');
                    });
            }

    }]);
})();

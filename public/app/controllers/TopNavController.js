AplicacaoLiga.controller('TopNavController', ['$scope', '$translate', 'Auth', function ($scope, $translate, Auth) {

    $scope.mudaIdioma = function(idioma) {
        $translate.use(idioma);
    }

    $scope.logout = function() {
        Auth.logout();
    }

}]);

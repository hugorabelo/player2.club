AplicacaoLiga.controller('TopNavController', ['$scope', '$translate', 'Auth', function ($scope, $translate, Auth) {

    $scope.mudaIdioma = function(idioma) {
        $translate.use(idioma);
        idiomaBootBox = idioma == 'en_us' ? 'en' : 'br';
        bootbox.setLocale(idiomaBootBox);
    }

    $scope.logout = function() {
        Auth.logout();
    }

}]);

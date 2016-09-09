angular.module('player2').controller('TopNavController', ['$rootScope', '$scope', '$translate', '$location', 'Auth', function ($rootScope, $scope, $translate, $location, Auth) {

    $scope.mudaIdioma = function(idioma) {
        $translate.use(idioma);
        idiomaBootBox = idioma == 'en_us' ? 'en' : 'br';
        bootbox.setLocale(idiomaBootBox);
    }

    $scope.logout = function() {
        Auth.logout();
    }

	 $scope.mudaUsuarioLogado = function() {
		 $rootScope.usuarioLogado = $scope.usuarioLogado;
         $location.path('/');
	 }

}]);

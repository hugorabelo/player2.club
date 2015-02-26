AplicacaoLiga.controller('ProfileController', ['$scope', 'Usuario', 'UserPlataforma', function ($scope, Usuario, UserPlataforma) {

    $scope.usuario = {};

    //$rootScope.loading = true;
    Usuario.show(1)
        .success(function(data) {
            $scope.usuario = data;
//            this.getPlataformas();
            //$rootScope.loading = false;
        })
        .error(function(data, status) {
        });

//    $scope.getPlataformas = function() {
//        $scope.userPlataformas = {};
//        alert($scope.usuario.id);
//        UserPlataforma.getPlataformas($scope.usuario.id)
//            .success(function (data) {
//                $scope.userPlataformas = data;
//            })
//            .error(function (data) {
//
//            });
//    };

}]);

AplicacaoLiga.controller('ProfileController', ['$scope', '$filter', 'Usuario', 'UserPlataforma', 'Plataforma', function ($scope, $filter, Usuario, UserPlataforma, Plataforma) {

    $scope.usuario = {};
    $scope.exibeFormulario = false;
    $scope.exibeFormularioPerfil = false;
	$scope.exibeFormularioImagem = false;

	$scope.files = [];

    $scope.$on("fileSelected", function (event, args) {
        $scope.$apply(function () {
            //add the file object to the scope's files collection
            $scope.files.push(args.file);
        });
    });

    $scope.abreFormularioGamertag = function() {
        $scope.exibeFormulario = !$scope.exibeFormulario;
    };

    $scope.abreFormularioPerfil = function() {
        $scope.exibeFormularioPerfil = true;
    };

    $scope.abreFormularioImagemPerfil = function() {
        $scope.exibeFormularioImagem = true;
    };

    //$rootScope.loading = true;
    Usuario.show(1)
        .success(function(data) {
            $scope.usuario = data;
            $scope.getPlataformasDoUsuario();
            $scope.getPlataformas();
        })
        .error(function(data, status) {
        });

    $scope.getPlataformasDoUsuario = function() {
        $scope.userPlataformas = {};
        UserPlataforma.getPlataformasDoUsuario($scope.usuario.id)
            .success(function (data) {
                $scope.userPlataformas = data;
            })
            .error(function (data) {

            });
    };

    $scope.getPlataformas = function () {
        $scope.plataformas = {};
        $scope.userPlataforma = {};
        Plataforma.get()
            .success(function (data) {
                $scope.plataformas = data;
                $scope.userPlataforma.users_id = $scope.usuario.id
            })
            .error(function (data) {

            });
    };

    $scope.salvaUserPlataforma = function() {
        UserPlataforma.save($scope.userPlataforma)
            .success(function (data) {
                $scope.getPlataformasDoUsuario();
                $scope.exibeFormulario = false;
            }).error(function(data, status) {
                $scope.messagePontuacao = data.message;
                $scope.status = status;
            });
    };

    $scope.excluiUserPlataforma = function(id) {
        var $translate = $filter('translate');
        var mensagem = $translate('messages.confirma_exclusao');
        bootbox.confirm(mensagem, function(result) {
            if(result) {
                UserPlataforma.destroy(id)
                    .success(function (data) {
                        $scope.getPlataformasDoUsuario();
                    }).error(function (data) {

                    });
            }
        });
    };

    $scope.salvaPerfil = function() {
        Usuario.update($scope.usuario, $scope.files[0])
        .success(function (data) {
            $scope.carregaDadosUsuario($scope.usuario.id);
            $scope.exibeFormularioPerfil = false;
			$scope.exibeFormularioImagem = false;
			$scope.files = [];
        })
    };

    $scope.carregaDadosUsuario = function(id) {
        Usuario.show(id)
        .success(function(data) {
            $scope.usuario = data;
            $scope.getPlataformasDoUsuario();
            $scope.getPlataformas();
        })
        .error(function(data, status) {
        });
    };

}]);

//;

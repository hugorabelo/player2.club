AplicacaoLiga.controller('ProfileController', ['$scope', '$filter', 'Usuario', 'UserPlataforma', 'Plataforma', 'Campeonato', 'CampeonatoUsuario', function ($scope, $filter, Usuario, UserPlataforma, Plataforma, Campeonato, CampeonatoUsuario) {

    $scope.usuario = {};
    $scope.exibeFormulario = false;
    $scope.exibeFormularioPerfil = false;
	$scope.exibeFormularioImagem = false;

	$scope.files = [];

    //$rootScope.loading = true;
    Usuario.show(1)
        .success(function(data) {
            $scope.usuario = data;
            $scope.carregaDadosUsuario($scope.usuario.id);
        })
        .error(function(data, status) {
        });

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
                $scope.carregaDadosUsuario($scope.usuario.id);
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
                        $scope.carregaDadosUsuario($scope.usuario.id);
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
            $scope.getCampeonatosInscritos();
            $scope.getCampeonatosDisponiveis();
        })
        .error(function(data, status) {
        });
    };

    $scope.getCampeonatosInscritos = function() {
        $scope.userCampeonatosInscritos = {};
        Usuario.getCampeonatosInscritos($scope.usuario.id)
            .success(function (data) {
                $scope.userCampeonatosInscritos = data;
            })
            .error(function (data) {
            });
    };

    $scope.getCampeonatosDisponiveis = function() {
        $scope.userCampeonatosDisponiveis = {};
        Usuario.getCampeonatosDisponiveis($scope.usuario.id)
            .success(function (data) {
                $scope.userCampeonatosDisponiveis = data;
            })
            .error(function (data) {
            });
    };

	$scope.inscreverCampeonato = function(id) {
		$scope.campeonatoSelecionado = null;
		var $translate = $filter('translate');
		Campeonato.getInformacoes(id)
			.success(function (data) {
				$scope.campeonatoSelecionado = data;
				var mensagem = $scope.campeonatoSelecionado.detalhes;
				bootbox.dialog({
					message: mensagem,
					title: $translate('messages.inscrever_titulo'),
					buttons: {
						danger: {
							label: $translate('fields.cancel'),
							className: "btn-default"
						},
						success: {
							label: $translate('messages.inscrever'),
							className: "btn-primary",
							callback: function() {
								$scope.confirmaInscricao($scope.campeonatoSelecionado.id);
							}
						}
					}
				});
			}).error(function (data) {
			});
	};

	$scope.confirmaInscricao = function (id_campeonato) {
		CampeonatoUsuario.save($scope.usuario.id, id_campeonato)
			.success(function (data) {
				alert('deu certo');
			}).error(function (data) {
				alert('deu zebra: ' + data.message);
			});
	};

}]);

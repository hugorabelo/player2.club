(function () {
    'use strict';

    angular
        .module('player2')
        .controller('LoginController', ['$rootScope', '$location', '$filter', '$stateParams', 'toastr', 'authService', 'OAuth', 'OAuthToken', function ($rootScope, $location, $filter, $stateParams, toastr, authService, OAuth, OAuthToken) {

            var vm = this;

            vm.auth = authService;

            vm.user = {
                username: '',
                password: '',
                email: ''
            };

            vm.senhaEnviada = false;
            
            vm.new_password = '';
            vm.repeat_password = '';
            
            vm.login = function() {
                if(vm.formLogin.$valid) {
                    authService.login(vm.user)
                        .then(function () {
                            $rootScope.escondeBarra = false;
                            authService.handleAuthentication();
                            return $location.path('home');
                        }, function (error) {
                            toastr.error($filter('translate')('messages_login.' + error.data.error));
                        });
                }
            };

            vm.inicializa = function () {
                $rootScope.escondeBarra = true;
                vm.senhaEnviada = false;
                vm.loadingRecuperaSenha = false;
            };

            vm.logout = function () {
                OAuthToken.removeToken(); 
                $location.path('login');
            };

            vm.enviarNovaSenha = function() {
                if(vm.formRecuperarSenha.$valid) {
                    vm.loadingRecuperaSenha = true;
                    authService.enviarNovaSenha(vm.user)
                        .then(function (data) {
                            vm.senhaEnviada = true;
                            toastr.success($filter('translate')('messages.senha_enviada'));
                        }, function(error) {
                            toastr.error($filter('translate')(error.data.errors));
                        })
                        .finally(function () {
                            vm.loadingRecuperaSenha = false;
                        });
                }
            }

            vm.cadastrarNovaSenha = function() {
                if(vm.formRedefinirSenha.$valid) {
                    vm.loadingRecuperaSenha = true;
                    vm.dadosSenha = {
                        tokenRedefinirSenha: $stateParams.token,
                        novaSenha: vm.new_password,
                        repetirSenha: vm.repeat_password
                    }
                    authService.cadastrarNovaSenha(vm.dadosSenha)
                        .then(function (data) {
                            vm.senhaCriada = true;
                            toastr.success($filter('translate')('messages.senha_criada_sucesso'));
                            $location.path('login');
                        }, function(error) {
                            toastr.error($filter('translate')(error.data.errors));
                            if(error.data.error_type === 'token_error') {
                                $location.path('recuperar_senha');
                            }
                        })
                        .finally(function () {
                            vm.loadingRecuperaSenha = false;
                        });
                }
            }

    }]);
})();

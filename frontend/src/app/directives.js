'use strict';

//Directive used to set metisMenu and minimalize button
angular.module('player2')
    .directive('pageTitle', function ($rootScope, $timeout) {
        return {
            link: function (scope, element) {
                var listener = function (event, toState, toParams, fromState, fromParams) {
                    // Default title - load on Dashboard 1
                    var title = 'player2.club';
                    // Create your own title pattern
                    if (toState.data && toState.data.pageTitle) title = 'player2.club | ' + toState.data.pageTitle;
                    $timeout(function () {
                        element.text(title);
                    });
                };
                $rootScope.$on('$stateChangeStart', listener);
            }
        }
    })
    .directive('sideNavigation', function ($timeout) {
        return {
            restrict: 'A',
            link: function (scope, element) {
                // Call metsi to build when user signup
                scope.$watch('authentication.user', function () {
                    $timeout(function () {
                        element.metisMenu();
                    });
                });

            }
        };
    })
    .directive('minimalizaSidebar', function ($timeout) {
        return {
            restrict: 'A',
            template: '<a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="" ng-click="minimalize()"><i class="fa fa-bars"></i></a>',
            controller: function ($scope) {
                $scope.minimalize = function () {
                    angular.element('body').toggleClass('mini-navbar');
                    if (!angular.element('body').hasClass('mini-navbar') || angular.element('body').hasClass('body-small')) {
                        // Hide menu in order to smoothly turn on when maximize menu
                        angular.element('#side-menu').hide();
                        // For smoothly turn on menu
                        $timeout(function () {
                            angular.element('#side-menu').fadeIn(400);
                        }, 200);
                    } else {
                        // Remove all inline style from jquery fadeIn function to reset menu state
                        angular.element('#side-menu').removeAttr('style');
                    }
                };
            }
        };
    })
    .directive('iboxTools', function ($timeout) {
        return {
            restrict: 'A',
            scope: true,
            templateUrl: 'app/components/comum/ibox_tools.html',
            controller: function ($scope, $element) {
                // Function for collapse ibox
                $scope.showhide = function () {
                        var ibox = $element.closest('div.ibox');
                        var icon = $element.find('i:first');
                        var content = ibox.find('div.ibox-content');
                        content.slideToggle(200);
                        // Toggle icon from up to down
                        icon.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
                        ibox.toggleClass('').toggleClass('border-bottom');
                        $timeout(function () {
                            ibox.resize();
                            ibox.find('[id^=map-]').resize();
                        }, 50);
                    },
                    // Function for close ibox
                    $scope.closebox = function () {
                        var ibox = $element.closest('div.ibox');
                        ibox.remove();
                    }
            }
        };
    })
    .directive('icheck', function ($timeout) {
        return {
            restrict: 'A',
            require: 'ngModel',
            link: function ($scope, element, $attrs, ngModel) {
                return $timeout(function () {
                    var value;
                    value = $attrs['value'];

                    $scope.$watch($attrs['ngModel'], function (newValue) {
                        $(element).iCheck('update');
                    })

                    return $(element).iCheck({
                        checkboxClass: 'icheckbox_square-green',
                        radioClass: 'iradio_square-green'

                    }).on('ifChanged', function (event) {
                        if ($(element).attr('type') === 'checkbox' && $attrs['ngModel']) {
                            $scope.$apply(function () {
                                return ngModel.$setViewValue(event.target.checked);
                            });
                        }
                        if ($(element).attr('type') === 'radio' && $attrs['ngModel']) {
                            return $scope.$apply(function () {
                                return ngModel.$setViewValue(value);
                            });
                        }
                    });
                });
            }
        };
    })
    .directive('modalConfirma', function () {
        return {
            templateUrl: 'app/components/comum/confirmaModal.html',
            replace: true
        };
    })
    .directive('formularioCampeonato', function () {
        return {
            templateUrl: 'app/components/campeonato/formModal.html',
            replace: true
        };
    })
    .directive('detalhesCampeonato', function () {
        return {
            templateUrl: 'app/components/campeonato/detalhesCampeonato.html',
            replace: true
        };
    })
    .directive('formularioFase', function () {
        return {
            templateUrl: 'app/components/campeonato/formModalFase.html',
            replace: true
        };
    })
    .directive('formularioDetalhesFase', function () {
        return {
            templateUrl: 'app/components/campeonato/formModalDetalhesFase.html',
            replace: true
        };
    })
    .directive('formularioCampeonatoTipo', function () {
        return {
            templateUrl: 'app/components/campeonatoTipo/formModal.html',
            replace: true
        };
    })
    .directive('formularioPlataforma', function () {
        return {
            templateUrl: 'app/components/plataforma/formModal.html',
            replace: true
        };
    })
    .directive('formularioJogo', function () {
        return {
            templateUrl: 'app/components/jogo/formModal.html',
            replace: true
        };
    })
    .directive('formularioUsuarioTipo', function () {
        return {
            templateUrl: 'app/components/usuarioTipo/formModal.html',
            replace: true
        };
    })
    .directive('formularioUsuario', function () {
        return {
            templateUrl: 'app/components/usuario/formModal.html',
            replace: true
        };
    })
    .directive('formularioMenu', function () {
        return {
            templateUrl: 'app/components/menu/formModal.html',
            replace: true
        };
    })
    .directive('formularioContestacaoResultado', function () {
        return {
            templateUrl: 'app/components/meus_campeonatos/formContestacaoResultado.html',
            replace: true
        };
    })
    .directive('fileUpload', function () {
        return {
            scope: true, //create a new scope
            link: function (scope, el, attrs) {
                el.bind('change', function (event) {
                    var files = event.target.files;
                    //iterate files since 'multiple' may be specified on the element
                    for (var i = 0; i < files.length; i++) {
                        //emit event upward
                        scope.$emit("fileSelected", {
                            file: files[i]
                        });
                    }
                });
            }
        };
    });

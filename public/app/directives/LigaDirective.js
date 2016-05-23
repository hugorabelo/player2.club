/**
 * pageTitle - Directive for set Page title - mata title
 */
function pageTitle($rootScope, $timeout) {
    return {
        link: function(scope, element) {
            var listener = function(event, toState, toParams, fromState, fromParams) {
                // Default title - load on Dashboard 1
                var title = 'LIGA VIRTUAL';
                // Create your own title pattern
                if (toState.data && toState.data.pageTitle) title = 'LIGA VIRTUAL | ' + toState.data.pageTitle;
                $timeout(function() {
                    element.text(title);
                });
            };
            $rootScope.$on('$stateChangeStart', listener);
        }
    }
};

/**
 * sideNavigation - Directive for run metsiMenu on sidebar navigation
 */
function sideNavigation() {
    return {
        restrict: 'A',
        link: function(scope, element) {
            // Call the metsiMenu plugin and plug it to sidebar navigation
            element.metisMenu();
        }
    };
};

/**
 * iboxTools - Directive for iBox tools elements in right corner of ibox
 */
function iboxTools($timeout) {
    return {
        restrict: 'A',
        scope: true,
        templateUrl: 'app/views/comum/ibox_tools.html',
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
};

/**
 * minimalizaSidebar - Directive for minimalize sidebar
*/
function minimalizaSidebar($timeout) {
    return {
        restrict: 'A',
        template: '<a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="" ng-click="minimalize()"><i class="fa fa-bars"></i></a>',
        controller: function ($scope, $element) {
            $scope.minimalize = function () {
                $("body").toggleClass("mini-navbar");
                if (!$('body').hasClass('mini-navbar') || $('body').hasClass('body-small')) {
                    // Hide menu in order to smoothly turn on when maximize menu
                    $('#side-menu').hide();
                    // For smoothly turn on menu
                    $timeout(function () {
                        $('#side-menu').fadeIn(500);
                        }, 100);
                } else {
                    // Remove all inline style from jquery fadeIn function to reset menu state
                    $('#side-menu').removeAttr('style');
                }
            }
        }
    };
};

/**
 * icheck - Directive for custom checkbox icheck
 */
function icheck($timeout) {
    return {
        restrict: 'A',
        require: 'ngModel',
        link: function($scope, element, $attrs, ngModel) {
            return $timeout(function() {
                var value;
                value = $attrs['value'];

                $scope.$watch($attrs['ngModel'], function(newValue){
                    $(element).iCheck('update');
                })

                return $(element).iCheck({
                    checkboxClass: 'icheckbox_square-green',
                    radioClass: 'iradio_square-green'

                }).on('ifChanged', function(event) {
                        if ($(element).attr('type') === 'checkbox' && $attrs['ngModel']) {
                            $scope.$apply(function() {
                                return ngModel.$setViewValue(event.target.checked);
                            });
                        }
                        if ($(element).attr('type') === 'radio' && $attrs['ngModel']) {
                            return $scope.$apply(function() {
                                return ngModel.$setViewValue(value);
                            });
                        }
                    });
            });
        }
    };
};

/**
 *
 * Pass all functions into module
 */
AplicacaoLiga.directive('pageTitle', pageTitle);
AplicacaoLiga.directive('sideNavigation', sideNavigation);
AplicacaoLiga.directive('iboxTools', iboxTools);
AplicacaoLiga.directive('minimalizaSidebar', minimalizaSidebar);
AplicacaoLiga.directive('icheck', icheck);

// Fim das diretivas do Inspinia

AplicacaoLiga.directive('modalConfirma', [function () {
	return {
		templateUrl: 'app/views/comum/confirmaModal.html',
        replace: true
	};
}]);

AplicacaoLiga.directive('formularioCampeonato', [function () {
	return {
		templateUrl: 'app/views/campeonato/formModal.html',
        replace: true
	};
}]);

AplicacaoLiga.directive('detalhesCampeonato', [function () {
	return {
		templateUrl: 'app/views/campeonato/detalhesCampeonato.html',
        replace: true
	};
}]);

AplicacaoLiga.directive('formularioFase', [function () {
	return {
		templateUrl: 'app/views/campeonato/formModalFase.html',
        replace: true
	};
}]);

AplicacaoLiga.directive('formularioDetalhesFase', [function () {
	return {
		templateUrl: 'app/views/campeonato/formModalDetalhesFase.html',
        replace: true
	};
}]);

AplicacaoLiga.directive('formularioCampeonatoTipo', [function () {
	return {
		templateUrl: 'app/views/campeonatoTipo/formModal.html',
        replace: true
	};
}]);

AplicacaoLiga.directive('formularioPlataforma', [function () {
	return {
		templateUrl: 'app/views/plataforma/formModal.html',
        replace: true
	};
}]);

AplicacaoLiga.directive('formularioJogo', [function () {
	return {
		templateUrl: 'app/views/jogo/formModal.html',
        replace: true
	};
}]);

AplicacaoLiga.directive('formularioUsuarioTipo', [function () {
	return {
		templateUrl: 'app/views/usuarioTipo/formModal.html',
        replace: true
	};
}]);

AplicacaoLiga.directive('formularioUsuario', [function () {
	return {
		templateUrl: 'app/views/usuario/formModal.html',
        replace: true
	};
}]);

AplicacaoLiga.directive('formularioMenu', [function () {
	return {
		templateUrl: 'app/views/menu/formModal.html',
        replace: true
	};
}]);

AplicacaoLiga.directive('formularioContestacaoResultado', [function () {
	return {
		templateUrl: 'app/views/meus_campeonatos/formContestacaoResultado.html',
        replace: true
	};
}]);


AplicacaoLiga.directive('fileUpload', function () {
    return {
        scope: true,        //create a new scope
        link: function (scope, el, attrs) {
            el.bind('change', function (event) {
                var files = event.target.files;
                //iterate files since 'multiple' may be specified on the element
                for (var i = 0;i<files.length;i++) {
                    //emit event upward
                    scope.$emit("fileSelected", { file: files[i] });
                }
            });
        }
    };
});

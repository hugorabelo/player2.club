/*global angular */
(function () {
    'use strict';

    //Directive used to set metisMenu and minimalize button
    angular.module('player2')
        .directive('pageTitle', function ($rootScope, $timeout, $filter) {
            return {
                link: function (scope, element) {
                    var listener = function (event, toState, toParams, fromState, fromParams) {
                        // Default title - load on Dashboard 1
                        var title = 'player2.club';
                        // Create your own title pattern
                        //toState.data.pageTitle
                        var titleComplemento = '';
                        //                        titleComplemento = $filter('translate')(toState.data.pageTitle);
                        if (toState.data && titleComplemento) title = title + ' | ' + titleComplemento;
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
        .directive('clockPicker', function () {
            return {
                restrict: 'A',
                link: function (scope, element, attrs) {
                    element.clockpicker();
                }
            }
        });
}());

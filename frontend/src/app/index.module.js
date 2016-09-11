(function () {
    'use strict';

    angular.module('player2', [
        'ngAnimate',
        'ngCookies',
        'ngTouch',
        'ngSanitize',
        'ngMessages',
        'ngAria',
        'ngResource',
        'ui.router',
        'ui.bootstrap',
        'pascalprecht.translate',
        'jcs-autoValidate',
        'ui.tree',
        'summernote',
         'ui.checkbox'
    ]);

//    angular.module('player2').config(function ($locationProvider) {
//        $locationProvider.html5Mode(true);
//    });



//    angular.module('player2').config(function ($translateProvider) {
//        $translateProvider.useStaticFilesLoader({
//            prefix: 'app/lang/locale-',
//            suffix: '.json'
//        });
//
//        $translateProvider.preferredLanguage('pt_br');
//        $translateProvider.fallbackLanguage('pt_br');
//
//        bootbox.setLocale('br');
//
//    });

})();

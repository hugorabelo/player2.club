(function () {
    'use strict';

    angular.module('inspinia', [
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

//    angular.module('inspinia').config(function ($locationProvider) {
//        $locationProvider.html5Mode(true);
//    });



//    angular.module('inspinia').config(function ($translateProvider) {
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

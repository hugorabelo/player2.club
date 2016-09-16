(function () {
    'use strict';

    angular.module('player2', [
        'ngAnimate',
        'ngCookies',
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
        'ui.checkbox',
        'ngMaterial',
        'lfNgMdFileInput'
    ]);

    //    angular.module('player2').config(function ($locationProvider) {
    //        $locationProvider.html5Mode(true);
    //    });



    angular.module('player2').config(function ($translateProvider) {
        $translateProvider.useStaticFilesLoader({
            prefix: 'app/lang/locale-',
            suffix: '.json'
        });

        $translateProvider.preferredLanguage('pt_br');
        $translateProvider.fallbackLanguage('pt_br');

        $translateProvider.useSanitizeValueStrategy('escape');

        //        bootbox.setLocale('br');

    });

    angular.module('player2')
        .config(function ($mdThemingProvider) {
            $mdThemingProvider.theme('default')
                .primaryPalette('teal')
                .accentPalette('orange');
        });

})();

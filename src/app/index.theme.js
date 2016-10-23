angular.module('player2')
    .config(function ($mdThemingProvider) {
        $mdThemingProvider.definePalette('mightyslate', {
            '50': '#e4e7ea',
            '100': '#b8c1c9',
            '200': '#98a4b1',
            '300': '#708092',
            '400': '#627181',
            '500': '#556270',
            '600': '#48535f',
            '700': '#3b444d',
            '800': '#2d343c',
            '900': '#20252a',
            'A100': '#e4e7ea',
            'A200': '#b8c1c9',
            'A400': '#627181',
            'A700': '#3b444d',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 200 A100 A200'
        });

        $mdThemingProvider.definePalette('pacifica', {
            '50': '#ffffff',
            '100': '#e1f7f5',
            '200': '#b5eae6',
            '300': '#7edad4',
            '400': '#66d4cc',
            '500': '#4ecdc4',
            '600': '#38c5bb',
            '700': '#31ada4',
            '800': '#2a958d',
            '900': '#237d77',
            'A100': '#ffffff',
            'A200': '#e1f7f5',
            'A400': '#66d4cc',
            'A700': '#31ada4',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 200 300 400 500 600 700 A100 A200 A400 A700'
        });

        $mdThemingProvider.definePalette('applechic', {
            '50': '#ffffff',
            '100': '#ffffff',
            '200': '#f4fde0',
            '300': '#dcf89d',
            '400': '#d1f681',
            '500': '#c7f464',
            '600': '#bdf247',
            '700': '#b2f02b',
            '800': '#a7eb11',
            '900': '#93cf0f',
            'A100': '#ffffff',
            'A200': '#ffffff',
            'A400': '#d1f681',
            'A700': '#b2f02b',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 200 300 400 500 600 700 800 900 A100 A200 A400 A700'
        });

        $mdThemingProvider.definePalette('cheerypink', {
            '50': '#ffffff',
            '100': '#ffffff',
            '200': '#fff0f0',
            '300': '#ffa8a8',
            '400': '#ff8a8a',
            '500': '#ff6b6b',
            '600': '#ff4c4c',
            '700': '#ff2e2e',
            '800': '#ff0f0f',
            '900': '#f00000',
            'A100': '#ffffff',
            'A200': '#ffffff',
            'A400': '#ff8a8a',
            'A700': '#ff2e2e',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 200 300 400 500 600 A100 A200 A400'
        });

        $mdThemingProvider.definePalette('grandmaspillow', {
            '50': '#ffffff',
            '100': '#f3dbdd',
            '200': '#e5b1b5',
            '300': '#d37b83',
            '400': '#cc646e',
            '500': '#c44d58',
            '600': '#b63c48',
            '700': '#9f353f',
            '800': '#882d36',
            '900': '#71252c',
            'A100': '#ffffff',
            'A200': '#f3dbdd',
            'A400': '#cc646e',
            'A700': '#9f353f',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 200 300 400 A100 A200 A400'
        });

        $mdThemingProvider.definePalette('sunlitsea', {
            '50': '#ffffff',
            '100': '#ffffff',
            '200': '#ffffff',
            '300': '#e9f8d3',
            '400': '#dcf4b8',
            '500': '#cff09e',
            '600': '#c2ec83',
            '700': '#b5e869',
            '800': '#a8e44e',
            '900': '#9be034',
            'A100': '#ffffff',
            'A200': '#ffffff',
            'A400': '#dcf4b8',
            'A700': '#b5e869',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 200 300 400 500 600 700 800 900 A100 A200 A400 A700'
        });

        $mdThemingProvider.definePalette('seafoaming', {
            '50': '#ffffff',
            '100': '#ffffff',
            '200': '#ffffff',
            '300': '#d3edd3',
            '400': '#bee4be',
            '500': '#a8dba8',
            '600': '#92d292',
            '700': '#7dc97d',
            '800': '#67c067',
            '900': '#51b751',
            'A100': '#ffffff',
            'A200': '#ffffff',
            'A400': '#bee4be',
            'A700': '#7dc97d',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 200 300 400 500 600 700 800 900 A100 A200 A400 A700'
        });

        $mdThemingProvider.definePalette('seashowinggreen', {
            '50': '#ffffff',
            '100': '#f7fbf9',
            '200': '#d2e9dd',
            '300': '#a2d1b9',
            '400': '#8dc7a9',
            '500': '#79bd9a',
            '600': '#64b38b',
            '700': '#52a77b',
            '800': '#48926c',
            '900': '#3e7e5d',
            'A100': '#ffffff',
            'A200': '#f7fbf9',
            'A400': '#8dc7a9',
            'A700': '#52a77b',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 200 300 400 500 600 700 A100 A200 A400 A700'
        });

        $mdThemingProvider.definePalette('therewecouldsail', {
            '50': '#dbefef',
            '100': '#a6d8d8',
            '200': '#7fc7c7',
            '300': '#4eb0b0',
            '400': '#449b9b',
            '500': '#3b8686',
            '600': '#327171',
            '700': '#285b5b',
            '800': '#1f4646',
            '900': '#163131',
            'A100': '#dbefef',
            'A200': '#a6d8d8',
            'A400': '#449b9b',
            'A700': '#285b5b',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 200 300 400 A100 A200 A400'
        });

        $mdThemingProvider.definePalette('adriftindreams', {
            '50': '#8ccef3',
            '100': '#47b0ec',
            '200': '#1799e3',
            '300': '#116da2',
            '400': '#0e5b87',
            '500': '#0b486b',
            '600': '#08354f',
            '700': '#052333',
            '800': '#021018',
            '900': '#000000',
            'A100': '#8ccef3',
            'A200': '#47b0ec',
            'A400': '#0e5b87',
            'A700': '#052333',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 A100 A200'
        });


        $mdThemingProvider.theme('player2')
            .primaryPalette('applechic')
            .accentPalette('adriftindreams', {
                'default': '500'
            })
            .warnPalette('grandmaspillow');

        //        $mdThemingProvider.alwaysWatchTheme(true);
        $mdThemingProvider.setDefaultTheme('player2');
    });

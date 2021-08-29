const mix = require('laravel-mix');

mix.setPublicPath('./public');

mix.js('resources/js/components.js', 'public/services/typeform/js');

mix.webpackConfig({
    externals: {
        '@bristol-su/frontend-toolkit': 'Toolkit',
        'vue': 'Vue',
    }
});

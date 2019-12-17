const mix = require('laravel-mix');

mix.setPublicPath('./public');

mix.js('resources/js/components.js', 'public/services/typeform/js');

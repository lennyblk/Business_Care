const mix = require('laravel-mix');

mix.js('views/js/app.js', 'public/js')
   .postCss('views/css/app.css', 'public/css', [
       //
   ]);

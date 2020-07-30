let mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

mix.disableNotifications();

mix
  .autoload({
    jquery: ["window.jQuery"],
  })
  .js("src/scripts/main.js", "dist/scripts/")
  .sass("src/styles/main.scss", "dist/styles/")
  .options({
    autoprefixer: {
      options: {
        browsers: [">0.2%", "not dead", "not op_mini all"],
      },
    },
  });

mix.copy(`src/libraries/*`, "dist/libraries");

mix.browserSync({
  proxy: "contactform.test",
  files: ["includes/templates/*", "src/**/*"],
});

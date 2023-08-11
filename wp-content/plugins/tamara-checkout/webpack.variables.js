/* Set webpack variables */

var webpackParams = {
    // Input file path
    entryPath: {
        main: ['./assets/src/js/main.js', './assets/src/scss/main.scss'],
        admin: ['./assets/src/js/admin.js', './assets/src/scss/admin.scss'],
        tamaraSuccess: ['./assets/src/js/tamaraSuccess.js'],
    },

    // Output for CSS and JS
    jsOutputPath: 'js/[name].js',
    cssOutputPath: 'css/[name].css',

};

module.exports = {webpackParams};

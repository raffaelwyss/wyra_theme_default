
module.exports = function (grunt) {

    grunt.initConfig({

        concat: {
            options: {
                stripBanners: true,
                separator: ';\n'
            },
            app: {
                src: [
                    'node_modules/jquery/dist/jquery.js',
                    'node_modules/jquery-ui-dist/jquery-ui.js',
                    'node_modules/angular/angular.js',
                    'node_modules/angular-i18n/angular-locale_de-ch.js',
                    'node_modules/angular-route/angular-route.js',
                    'node_modules/angular-sanitize/angular-sanitize.js',
                    'node_modules/angular-animate/angular-animate.js',
                    'node_modules/angular-ui-mask/dist/mask.js',
                    'node_modules/moment/moment.js',
                    'node_modules/moment/locale/de.js',
                    'node_modules/bootstrap-sass/assets/bootstrap.js',

                    'src/Themes/Default/JavaScript/dependencies.js',
                    'src/Themes/Default/JavaScript/Core/*.js',
                    'src/Themes/Default/JavaScript/Form/*.js'

                ],
                dest: '../../../web/dist/Theme/Default/app.js'
            }
        },

        sass: {
            dist : {
                options: {
                    style: 'expanded'
                },
                files: {
                    '../../../web/dist/Theme/Default/app.css': 'src/Themes/Default/SCSS/theme.scss'
                }
            }
        },

        uglify: {
            options: {
                mangle: false,
                preserveComments: false,
                screwIE8: true
            },
            dist: {
                files: {
                    'dist/app.js': ['<%= concat.app.dest %>'],
                }
            }
        },

        cssmin: {
            target: {
                files: {
                    'dist/theme.css': 'dist/theme.css'
                }
            }
        },

    });

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-sass');

    grunt.registerTask('default', ['concat', 'sass']);
    grunt.registerTask('publish', ['concat', 'sass', 'uglify', 'cssmin']);


}


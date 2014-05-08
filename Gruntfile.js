/*global exports:false, module:false, require:false */

module.exports = function( grunt ) {
	'use strict';

	require('matchdep').filterDev('grunt-*').forEach( grunt.loadNpmTasks );

	grunt.initConfig({

		autoprefixer: {
			options: {
				browsers: ['> 1%', 'last 2 versions', 'ff 17', 'opera 12.1', 'ie 8', 'android 4']
			},
			plugin: {
				src: 'assets/css/admin.css'
			}
		},

		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			plugin: [
				'Gruntfile.js',
				'assets/js/*.js'
			]
		},

		makepot: {
			plugin: {
				options: {
					type: 'wp-plugin'
				}
			}
		},

		watch: {
			js: {
				files: ['<%= jshint.plugin %>'],
				tasks: ['jshint', 'uglify']
			}
		}

	});

	grunt.registerTask('default', ['jshint', 'autoprefixer', 'watch']);

};
